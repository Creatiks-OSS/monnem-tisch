<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingUpdateRequest;
use App\Models\Setting;
use App\Services\ActivityLogService;
use App\Services\BusinessNotificationService;
use App\Services\OpenAiService;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SettingController extends Controller
{
    public const GROUPS = [
        'general' => 'Genel',
        'contact' => 'İletişim',
        'social' => 'Sosyal medya',
        'seo' => 'SEO',
        'analytics' => 'Analitik',
        'mail' => 'E-posta',
        'notifications' => 'Bildirimler',
        'appearance' => 'Görünüm',
        'whatsapp' => 'WhatsApp',
        'ai' => 'Yapay zekâ',
        'kitchen' => 'Mutfak',
    ];

    public function __construct(
        protected SettingService $settings,
        protected ActivityLogService $activity
    ) {}

    public function index(?string $group = 'general'): View|RedirectResponse
    {
        $group = $group && isset(self::GROUPS[$group]) ? $group : 'general';

        if ($group === 'stripe') {
            return redirect()->route('admin.payments.index');
        }
        $values = $this->settings->group($group === 'appearance' ? 'theme' : $group);
        if ($group === 'mail') {
            $values = array_merge($this->settings->group('smtp'), $values);
        }
        if ($group === 'general' && empty($values['tax_rate'])) {
            $values['tax_rate'] = setting('tax_rate', setting('tax_rate', 7, 'business'), 'shop') ?: 7;
        }
        $groups = self::GROUPS;
        $maintenanceEnabled = $this->settings->bool('enabled', false, 'maintenance');
        $maintenanceTitle = $this->settings->string('title', 'Wir sind bald für Sie da', 'maintenance');
        $maintenanceMessage = $this->settings->string(
            'message',
            'Unsere Website befindet sich gerade im Aufbau. Monnemtisch Catering in Mannheim – bald wieder für Sie erreichbar.',
            'maintenance'
        );

        return view('admin.settings.index', compact(
            'group',
            'values',
            'groups',
            'maintenanceEnabled',
            'maintenanceTitle',
            'maintenanceMessage'
        ));
    }

    public function toggleMaintenance(Request $request): RedirectResponse
    {
        $enabled = $request->boolean('enabled');
        $this->settings->set('enabled', $enabled, 'maintenance', 'boolean');
        $this->settings->forgetCache('maintenance');
        $this->activity->log($enabled ? 'Yapım aşaması açıldı' : 'Yapım aşaması kapatıldı');

        return back()->with('success', $enabled
            ? 'Yapım aşaması sayfası aktif. Ziyaretçiler bu sayfayı görür.'
            : 'Yapım aşaması kapatıldı. Site tekrar yayında.');
    }

    public function updateMaintenance(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:160'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        $this->settings->set('title', trim((string) ($data['title'] ?? '')), 'maintenance');
        $this->settings->set('message', trim((string) ($data['message'] ?? '')), 'maintenance');
        $this->settings->forgetCache('maintenance');

        return back()->with('success', 'Yapım aşaması metinleri kaydedildi.');
    }

    public function update(SettingUpdateRequest $request): RedirectResponse
    {
        $group = $request->validated('group');
        $storeGroup = $group === 'appearance' ? 'theme' : $group;
        $settings = $request->validated('settings');

        if ($group === 'notifications') {
            foreach ([
                'email_customer_order_paid',
                'email_admin_order',
                'email_admin_order_paid',
                'email_admin_quote',
                'email_admin_contact',
                'telegram_enabled',
                'telegram_on_order',
                'telegram_on_order_paid',
                'telegram_on_quote',
                'telegram_on_contact',
            ] as $checkbox) {
                $settings[$checkbox] = ! empty($settings[$checkbox]) && $settings[$checkbox] !== '0' ? '1' : '0';
            }

            if (trim((string) ($settings['telegram_bot_token'] ?? '')) === '') {
                unset($settings['telegram_bot_token']);
            }
        }

        if ($group === 'ai') {
            $settings['enabled'] = ! empty($settings['enabled']) && $settings['enabled'] !== '0' ? '1' : '0';
            if (trim((string) ($settings['api_key'] ?? '')) === '') {
                unset($settings['api_key']);
            }
            if (trim((string) ($settings['model'] ?? '')) === '') {
                $settings['model'] = 'gpt-4o-mini';
            }
        }

        if ($group === 'kitchen') {
            $pin = strtoupper(trim((string) ($settings['pin'] ?? '')));
            unset($settings['pin'], $settings['pin_hash']);

            if ($pin !== '') {
                $request->validate([
                    'settings.pin' => ['size:4', 'regex:/^[A-Za-z0-9]{4}$/'],
                ], [
                    'settings.pin.size' => 'Mutfak PIN’i tam 4 karakter olmalı.',
                    'settings.pin.regex' => 'PIN yalnızca harf ve rakam olabilir.',
                ]);
                app(\App\Services\KitchenService::class)->setPin($pin);
            }

            $this->activity->log('Ayarlar güncellendi', null, ['group' => $group]);

            return redirect()->route('admin.settings.group', $group)->with('success', $pin !== ''
                ? 'Mutfak PIN’i kaydedildi.'
                : 'Değişiklik yok — mevcut PIN korundu.');
        }

        if ($group === 'mail') {
            if (trim((string) ($settings['password'] ?? '')) === '') {
                unset($settings['password']);
            }

            $mailKeys = ['from_name', 'from_address', 'reply_to'];
            $smtpKeys = ['host', 'port', 'username', 'password', 'encryption'];

            foreach ($mailKeys as $key) {
                if (! array_key_exists($key, $settings)) {
                    continue;
                }
                Setting::set($key, $settings[$key], 'mail', 'string');
            }
            foreach ($smtpKeys as $key) {
                if (! array_key_exists($key, $settings)) {
                    continue;
                }
                $type = $key === 'port' ? 'integer' : 'string';
                Setting::set($key, $settings[$key], 'smtp', $type);
            }

            $this->settings->forgetCache('mail');
            $this->settings->forgetCache('smtp');
            $this->activity->log('Ayarlar güncellendi', null, ['group' => $group]);

            return redirect()->route('admin.settings.group', $group)->with('success', 'E-posta ayarları kaydedildi.');
        }

        if ($group === 'general') {
            $request->validate([
                'logo' => ['nullable', 'file', 'max:15360', 'mimes:jpg,jpeg,png,webp,svg,gif'],
                'remove_logo' => ['sometimes', 'boolean'],
            ]);

            if ($request->hasFile('logo')) {
                $previous = (string) Setting::get('logo', '', 'general');
                $file = $request->file('logo');
                $ext = strtolower($file->getClientOriginalExtension() ?: 'png');
                $path = $file->storeAs('branding', 'logo-'.Str::lower(Str::random(8)).'.'.$ext, 'public');
                Setting::set('logo', $path, 'general');
                $this->deleteStoredLogo($previous);
            } elseif ($request->boolean('remove_logo')) {
                $this->deleteStoredLogo((string) Setting::get('logo', '', 'general'));
                Setting::set('logo', '', 'general');
            }
        }

        foreach ($settings as $key => $value) {
            if ($key === 'logo') {
                continue;
            }
            $type = is_bool($value) || in_array($value, ['0', '1', 'true', 'false'], true) ? 'boolean' : 'string';
            if (is_array($value)) {
                $type = 'json';
            }
            if (is_numeric($value) && ! is_string($value)) {
                $type = str_contains((string) $value, '.') ? 'float' : 'integer';
            }
            if (in_array($key, ['telegram_bot_token', 'telegram_chat_id', 'admin_email', 'from_name', 'from_address', 'reply_to', 'api_key', 'model'], true)) {
                $type = 'string';
            }
            if ($key === 'tax_rate') {
                $type = 'float';
                $value = (float) str_replace(',', '.', (string) $value);
            }
            Setting::set($key, $value, $storeGroup, $type);
            if ($key === 'tax_rate') {
                Setting::set('tax_rate', $value, 'shop', 'float');
                Setting::set('tax_rate', $value, 'business', 'float');
                $this->settings->forgetCache('shop');
                $this->settings->forgetCache('business');
            }
            if ($key === 'legal_name') {
                Setting::set('legal_name', $value, 'company', 'string');
                $this->settings->forgetCache('company');
            }
            if ($key === 'tax_id') {
                Setting::set('tax_id', $value, 'legal', 'string');
                $this->settings->forgetCache('legal');
            }
            if ($key === 'street') {
                Setting::set('address', $value, 'contact', 'string');
            }
        }

        $this->settings->forgetCache($storeGroup);
        $this->activity->log('Ayarlar güncellendi', null, ['group' => $group]);

        return redirect()->route('admin.settings.group', $group)->with('success', 'Ayarlar kaydedildi.');
    }

    public function testMail(): RedirectResponse
    {
        $to = $this->settings->string('admin_email', '', 'notifications')
            ?: $this->settings->string('from_address', '', 'mail')
            ?: $this->settings->string('email', 'info@monnemtisch.de', 'contact');

        if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return back()->with('error', 'Geçerli bir alıcı e-posta adresi yok. Bildirimler veya E-posta sekmesine bir adres yazın.');
        }

        try {
            \Illuminate\Support\Facades\Mail::raw(
                "Monnemtisch Catering Test.\n\nWenn Sie diese Nachricht sehen, ist der SMTP-Versand aktiv.",
                function ($message) use ($to) {
                    $message->to($to)->subject('Test: Monnemtisch Catering');
                }
            );
        } catch (\Throwable $e) {
            return back()->with('error', 'Test maili gönderilemedi: '.$e->getMessage());
        }

        return back()->with('success', 'Test maili gönderildi: '.$to);
    }

    public function testTelegram(BusinessNotificationService $notifications): RedirectResponse
    {
        $result = $notifications->sendTelegramTest();

        if ($result['ok'] ?? false) {
            return back()->with('success', 'Telegram test mesajı gönderildi.');
        }

        return back()->with('error', 'Telegram gönderilemedi: '.($result['error'] ?? 'Bilinmeyen hata'));
    }

    public function testAi(OpenAiService $openai): RedirectResponse
    {
        if (! $openai->isConfigured()) {
            return back()->with('error', 'Önce OpenAI API anahtarını kaydedin.');
        }

        $result = $openai->ping();
        if ($result['ok'] ?? false) {
            return back()->with('success', 'OpenAI bağlantısı başarılı ('.$openai->model().').');
        }

        return back()->with('error', 'OpenAI testi başarısız: '.($result['error'] ?? 'Bilinmeyen hata'));
    }

    protected function deleteStoredLogo(string $path): void
    {
        $path = trim($path);
        if ($path === '' || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}