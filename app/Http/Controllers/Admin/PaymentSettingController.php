<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\ActivityLogService;
use App\Services\PaymentGatewayService;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentSettingController extends Controller
{
    public function __construct(
        protected SettingService $settings,
        protected PaymentGatewayService $gateways,
        protected ActivityLogService $activity
    ) {}

    public function index(): View
    {
        $payments = Payment::query()
            ->with('order')
            ->latest('id')
            ->paginate(15);

        return view('admin.payments.index', [
            'stripe' => $this->settings->group('stripe'),
            'paypal' => $this->settings->group('paypal'),
            'offline' => $this->settings->group('payments'),
            'payments' => $payments,
            'stripeWebhook' => url('/webhooks/stripe'),
            'paypalWebhook' => url('/webhooks/paypal'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'stripe_enabled' => ['nullable', 'boolean'],
            'stripe_mode' => ['required', 'in:test,live'],
            'stripe_public_key' => ['nullable', 'string', 'max:500'],
            'stripe_secret_key' => ['nullable', 'string', 'max:500'],
            'stripe_webhook_secret' => ['nullable', 'string', 'max:500'],
            'paypal_enabled' => ['nullable', 'boolean'],
            'paypal_mode' => ['required', 'in:sandbox,live'],
            'paypal_client_id' => ['nullable', 'string', 'max:255'],
            'paypal_secret' => ['nullable', 'string', 'max:255'],
            'paypal_webhook_id' => ['nullable', 'string', 'max:255'],
        ]);

        $this->settings->set('enabled', $request->boolean('stripe_enabled'), 'stripe', 'boolean');
        $this->settings->set('mode', $data['stripe_mode'], 'stripe');
        $this->settings->set('public_key', $data['stripe_public_key'] ?? '', 'stripe');
        $this->keepSecret('secret_key', $data['stripe_secret_key'] ?? null, 'stripe');
        $this->keepSecret('webhook_secret', $data['stripe_webhook_secret'] ?? null, 'stripe');

        $this->settings->set('enabled', $request->boolean('paypal_enabled'), 'paypal', 'boolean');
        $this->settings->set('mode', $data['paypal_mode'], 'paypal');
        $this->settings->set('client_id', $data['paypal_client_id'] ?? '', 'paypal');
        $this->keepSecret('secret', $data['paypal_secret'] ?? null, 'paypal');
        $this->settings->set('webhook_id', $data['paypal_webhook_id'] ?? '', 'paypal');

        $this->settings->forgetCache('stripe');
        $this->settings->forgetCache('paypal');
        $this->settings->forgetCache('payments');
        $this->activity->log('Ödeme sistemleri güncellendi');

        return redirect()->route('admin.payments.index')->with('success', 'Ödeme ayarları kaydedildi.');
    }

    protected function keepSecret(string $key, ?string $value, string $group): void
    {
        if ($value !== null && trim($value) !== '') {
            $this->settings->set($key, trim($value), $group);

            return;
        }

        if ($this->settings->string($key, '', $group) === '') {
            $this->settings->set($key, '', $group);
        }
    }
}
