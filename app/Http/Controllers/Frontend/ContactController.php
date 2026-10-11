<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\ContactRequest;
use App\Models\BusinessHour;
use App\Models\ContactMessage;
use App\Models\EventType;
use App\Services\AvailabilityService;
use App\Services\NotificationService;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function __construct(
        protected SettingService $settings,
        protected NotificationService $notifications,
        protected AvailabilityService $availability
    ) {}

    public function show(): View
    {
        $eventTypes = EventType::query()
            ->active()
            ->orderBy('sort_order')
            ->get();

        $hours = BusinessHour::query()
            ->orderByRaw('CASE WHEN day_of_week = 0 THEN 7 ELSE day_of_week END')
            ->get();

        $blockedDates = collect($this->availability->getBlockedDates(now(), now()->addMonths(18)))
            ->map(fn ($d) => \Illuminate\Support\Carbon::parse($d)->toDateString())
            ->values();

        return view('frontend.contact', [
            'eventTypes' => $eventTypes,
            'hours' => $hours,
            'blockedDates' => $blockedDates,
            'contact' => [
                'phone' => $this->settings->string('phone', '', 'contact'),
                'email' => $this->settings->string('email', '', 'contact'),
                'address' => $this->settings->string('street', '', 'contact')
                    ?: $this->settings->string('address', '', 'contact'),
                'city' => $this->settings->string('city', '', 'contact'),
                'postal_code' => $this->settings->string('postal_code', '', 'contact'),
            ],
        ]);
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $message = ContactMessage::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'] ?? null,
            'event_type' => $data['event_type'] ?? null,
            'event_date' => $data['event_date'] ?? null,
            'message' => $data['message'],
            'source' => 'website',
            'gclid' => $data['gclid'] ?? null,
            'is_read' => false,
            'status' => 'neu',
            'gdpr_consent' => true,
        ]);

        $this->notifications->notifyAdmins(
            'contact.created',
            'Yeni iletişim mesajı',
            $message->name.' bir mesaj gönderdi'.($message->subject ? ': '.$message->subject : '.'),
            [
                'contact_message_id' => $message->id,
                'url' => route('admin.contact-messages.show', $message),
            ]
        );

        $this->sendConfirmation($message);

        try {
            app(\App\Services\BusinessNotificationService::class)->notifyContactCreated($message);
        } catch (\Throwable $e) {
            Log::warning('Contact business notifications failed', [
                'id' => $message->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('contact.show')
            ->with('success', 'Vielen Dank! Ihre Nachricht wurde gesendet. Wir melden uns zeitnah.')
            ->with('lead_tracked', true)
            ->with('lead_params', [
                'method' => 'contact_form',
                'form_type' => 'anfrage',
            ]);
    }

    protected function sendConfirmation(ContactMessage $message): void
    {
        try {
            app(\App\Services\BusinessNotificationService::class)->sendCustomerContactReceived($message);
        } catch (\Throwable $e) {
            Log::warning('Contact confirmation mail failed', [
                'id' => $message->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
