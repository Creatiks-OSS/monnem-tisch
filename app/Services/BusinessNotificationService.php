<?php

namespace App\Services;

use App\Models\ContactMessage;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Quote;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class BusinessNotificationService
{
    public function __construct(
        protected SettingService $settings,
        protected TelegramService $telegram,
        protected NotificationService $notifications
    ) {}

    public function notifyOrderCreated(Order $order): void
    {
        $order->loadMissing(['items.product.category', 'extras.extra', 'eventType', 'customer']);

        $this->sendMail(
            $this->enabled('email_admin_order'),
            $this->adminEmail(),
            'Neue Bestellung '.$order->order_number,
            'emails.order-admin',
            [
                'order' => $order,
                'headline' => 'Neue Bestellung',
                'intro' => 'Es ist eine neue Bestellung eingegangen.',
                'headerTitle' => 'Neue Bestellung',
                'badge' => 'Neue Bestellung',
                'preheader' => 'Neue Bestellung '.$order->order_number.' – '.format_price($order->total),
                'company' => $this->company(),
                'adminUrl' => $this->adminOrderUrl($order),
                'lines' => $this->orderLines($order),
            ]
        );

        if ($this->enabled('telegram_on_order')) {
            $this->sendTelegram($this->telegramOrderText($order, false));
        }
    }

    public function sendAbandonedCart(Order $order): void
    {
        $order->loadMissing(['items.product.category', 'extras.extra', 'eventType', 'customer']);

        $email = trim((string) $order->billing_email);
        $resumeUrl = URL::temporarySignedRoute(
            'payments.resume',
            now()->addDays(30),
            ['orderNumber' => $order->order_number]
        );

        $data = [
            'order' => $order,
            'company' => $this->company(),
            'homeUrl' => url('/'),
            'headerTitle' => 'Ihr Warenkorb wartet',
            'barTitle' => 'Ihr Warenkorb wartet',
            'preheader' => 'Ihre Auswahl ist noch gespeichert – '.format_price($order->total),
            'lines' => $this->orderLines($order),
            'resumeUrl' => $resumeUrl,
        ];

        try {
            Mail::send('emails.abandoned-cart', $data, function ($message) use ($email) {
                $message->to($email)->subject('Ihr Warenkorb wartet noch');
            });
        } catch (\Throwable $e) {
            Log::warning('Abandoned cart mail failed', [
                'order_id' => $order->id,
                'to' => $email,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function notifyOrderPaid(Order $order): void
    {
        $order->loadMissing(['items.product.category', 'extras.extra', 'eventType', 'customer', 'payments']);

        $customerEmail = trim((string) $order->billing_email);
        $this->sendMail(
            $this->enabled('email_customer_order_paid') && $customerEmail !== '',
            $customerEmail,
            'Zahlung bestätigt – Bestellung '.$order->order_number,
            'emails.order-paid-customer',
            [
                'order' => $order,
                'company' => $this->company(),
                'headerTitle' => 'Bestellung bestätigt',
                'barTitle' => 'Bestellung bestätigt',
                'preheader' => 'Wir haben Ihre Bestellung '.$order->order_number.' bestätigt.',
                'lines' => $this->orderLines($order),
            ]
        );

        $this->sendMail(
            $this->enabled('email_admin_order_paid'),
            $this->adminEmail(),
            'Zahlung eingegangen – '.$order->order_number,
            'emails.order-admin',
            [
                'order' => $order,
                'headline' => 'Zahlung eingegangen',
                'intro' => 'Die Zahlung für diese Bestellung wurde erfolgreich abgeschlossen.',
                'headerTitle' => 'Zahlung eingegangen',
                'badge' => 'Bezahlt',
                'preheader' => 'Zahlung für '.$order->order_number.' – '.format_price($order->total),
                'company' => $this->company(),
                'adminUrl' => $this->adminOrderUrl($order),
                'lines' => $this->orderLines($order),
            ]
        );

        if ($this->enabled('telegram_on_order_paid')) {
            $this->sendTelegram($this->telegramOrderText($order, true));
        }

        try {
            $this->notifications->notifyAdmins(
                'order.paid',
                'Ödeme alındı '.$order->order_number,
                number_format((float) $order->total, 2, ',', '.').' € ödendi.',
                [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'url' => $this->adminOrderUrl($order),
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Admin paid notification failed', ['error' => $e->getMessage()]);
        }
    }

    public function notifyQuoteCreated(Quote $quote): void
    {
        $quote->loadMissing(['items', 'eventType', 'customer']);

        $this->sendMail(
            $this->enabled('email_admin_quote'),
            $this->adminEmail(),
            'Neue Angebotsanfrage '.$quote->quote_number,
            'emails.quote-admin',
            [
                'quote' => $quote,
                'company' => $this->company(),
                'headerTitle' => 'Neue Angebotsanfrage',
                'badge' => 'Anfrage',
                'preheader' => 'Neue Angebotsanfrage '.$quote->quote_number,
                'adminUrl' => $this->safeRoute('admin.quotes.show', $quote),
            ]
        );

        if ($this->enabled('telegram_on_quote')) {
            $name = trim($quote->first_name.' '.$quote->last_name);
            $this->sendTelegram(
                "<b>Neue Angebotsanfrage</b>\n"
                .'<b>Nr.:</b> '.e($quote->quote_number)."\n"
                .'<b>Kunde:</b> '.e($name)."\n"
                .'<b>E-Mail:</b> '.e((string) $quote->email)."\n"
                .($quote->phone ? '<b>Telefon:</b> '.e($quote->phone)."\n" : '')
                .($quote->event_date ? '<b>Event:</b> '.$quote->event_date->format('d.m.Y')."\n" : '')
                .($quote->persons ? '<b>Personen:</b> '.(int) $quote->persons."\n" : '')
                .($quote->location ? '<b>Ort:</b> '.e($quote->location)."\n" : '')
                .($quote->total > 0 ? '<b>Summe:</b> '.e(format_price($quote->total))."\n" : '')
                .($this->safeRoute('admin.quotes.show', $quote) ? "\n".$this->safeRoute('admin.quotes.show', $quote) : '')
            );
        }
    }

    public function notifyContactCreated(ContactMessage $message): void
    {
        $this->sendMail(
            $this->enabled('email_admin_contact'),
            $this->adminEmail(),
            'Neue Kontaktanfrage'.($message->subject ? ': '.$message->subject : ''),
            'emails.contact-admin',
            [
                'contact' => $message,
                'company' => $this->company(),
                'headerTitle' => 'Neue Kontaktanfrage',
                'badge' => 'Kontakt',
                'preheader' => 'Neue Nachricht von '.$message->name,
                'adminUrl' => $this->safeRoute('admin.contact-messages.show', $message),
            ]
        );

        if ($this->enabled('telegram_on_contact')) {
            $this->sendTelegram(
                "<b>Neue Kontaktanfrage</b>\n"
                .'<b>Name:</b> '.e($message->name)."\n"
                .'<b>E-Mail:</b> '.e($message->email)."\n"
                .($message->phone ? '<b>Telefon:</b> '.e($message->phone)."\n" : '')
                .($message->subject ? '<b>Betreff:</b> '.e($message->subject)."\n" : '')
                ."\n".e(\Illuminate\Support\Str::limit((string) $message->message, 400))
                .($this->safeRoute('admin.contact-messages.show', $message) ? "\n\n".$this->safeRoute('admin.contact-messages.show', $message) : '')
            );
        }
    }

    public function sendCustomerOrderReceived(Order $order): void
    {
        $order->loadMissing(['items.product.category', 'extras.extra', 'eventType']);
        $email = trim((string) $order->billing_email);
        if ($email === '') {
            return;
        }

        $this->sendMail(
            true,
            $email,
            'Bestellung bestätigt '.$order->order_number,
            'emails.order-received-customer',
            [
                'order' => $order,
                'headerTitle' => 'Bestellung bestätigt',
                'barTitle' => 'Bestellung bestätigt',
                'preheader' => 'Wir haben Ihre Bestellung '.$order->order_number.' bestätigt.',
                'lines' => $this->orderLines($order),
            ]
        );
    }

    public function sendCustomerQuoteReceived(Quote $quote): void
    {
        $email = trim((string) $quote->email);
        if ($email === '') {
            return;
        }

        $this->sendMail(
            true,
            $email,
            'Ihre Anfrage '.$quote->quote_number,
            'emails.quote-received-customer',
            [
                'quote' => $quote,
                'headerTitle' => 'Anfrage erhalten',
                'badge' => 'Angebot',
                'preheader' => 'Vielen Dank – wir haben Ihre Anfrage '.$quote->quote_number.' erhalten.',
            ]
        );
    }

    public function sendCustomerInvoice(Invoice $invoice, string $pdfPath): void
    {
        $invoice->loadMissing(['customer', 'order.items.product.category', 'order.extras.extra', 'order.eventType']);

        $email = trim((string) ($invoice->billing_email ?: $invoice->customer?->email ?: $invoice->order?->billing_email));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('Müşteri e-posta adresi bulunamadı.');
        }

        if (! is_file($pdfPath)) {
            throw new \RuntimeException('Fatura PDF dosyası oluşturulamadı.');
        }

        $order = $invoice->order;
        $orderDate = $order?->event_date
            ?? $order?->created_at
            ?? $invoice->issued_at;
        $orderDateFormatted = $orderDate?->format('d.m.Y') ?: now()->format('d.m.Y');
        $subject = $order
            ? 'Ihre Rechnung für die Bestellung vom '.$orderDateFormatted
            : 'Ihre Rechnung '.$invoice->invoice_number;

        Mail::send('emails.invoice-sent-customer', [
            'invoice' => $invoice,
            'orderDateFormatted' => $orderDateFormatted,
            'headerTitle' => 'Ihre Rechnung',
            'barTitle' => 'Ihre Rechnung',
            'preheader' => $subject.' – PDF im Anhang.',
            'company' => $this->company(),
            'homeUrl' => url('/'),
            'lines' => $order ? $this->orderLines($order) : $this->manualInvoiceLines($invoice),
        ], function ($message) use ($email, $subject, $pdfPath, $invoice) {
            $message->to($email)
                ->subject($subject)
                ->attach($pdfPath, [
                    'as' => $invoice->invoice_number.'.pdf',
                    'mime' => 'application/pdf',
                ]);
        });
    }

    public function sendCustomerContactReceived(ContactMessage $message): void
    {
        $email = trim((string) $message->email);
        if ($email === '') {
            return;
        }

        $this->sendMail(
            true,
            $email,
            'Ihre Nachricht bei Monnemtisch Catering',
            'emails.contact-received-customer',
            [
                'contact' => $message,
                'headerTitle' => 'Nachricht erhalten',
                'badge' => 'Kontakt',
                'preheader' => 'Vielen Dank – wir haben Ihre Nachricht erhalten.',
            ]
        );
    }

    public function sendTelegramTest(): array
    {
        return $this->telegram->send(
            "<b>Monnemtisch Catering</b>\nTelegram-Test erfolgreich.\nBenachrichtigungen sind aktiv."
        );
    }

    public function adminEmail(): string
    {
        $override = trim($this->settings->string('admin_email', '', 'notifications'));
        if (filter_var($override, FILTER_VALIDATE_EMAIL)) {
            return $override;
        }

        $contact = trim($this->settings->string('email', 'info@monnemtisch.de', 'contact'));

        return filter_var($contact, FILTER_VALIDATE_EMAIL) ? $contact : 'info@monnemtisch.de';
    }

    /**
     * @return array<string, string>
     */
    public function company(): array
    {
        return [
            'name' => $this->settings->string('name', config('app.name', 'Monnemtisch Catering'), 'company')
                ?: $this->settings->string('company_name', 'Monnemtisch Catering', 'general'),
            'email' => $this->settings->string('email', 'info@monnemtisch.de', 'contact'),
            'phone' => $this->settings->string('phone', '', 'contact'),
            'city' => $this->settings->string('city', 'Mannheim', 'contact'),
        ];
    }

    protected function enabled(string $key, bool $default = true): bool
    {
        return $this->settings->bool($key, $default, 'notifications');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function sendMail(bool $enabled, string $to, string $subject, string $view, array $data): void
    {
        if (! $enabled || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $data = array_merge([
            'company' => $this->company(),
            'homeUrl' => url('/'),
        ], $data);

        try {
            Mail::send($view, $data, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::warning('Business notification mail failed', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function sendTelegram(string $text): void
    {
        if (! $this->settings->bool('telegram_enabled', false, 'notifications')) {
            return;
        }

        $result = $this->telegram->send($text);
        if (! ($result['ok'] ?? false)) {
            Log::warning('Telegram notification skipped', ['error' => $result['error'] ?? 'unknown']);
        }
    }

    /**
     * @return list<array{name: string, image: null, qty: string, amount: string, details: list<string>, slots: array<int, never>}>
     */
    public function manualInvoiceLines(Invoice $invoice): array
    {
        $lines = [];
        foreach ($invoice->line_items ?? [] as $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $lines[] = [
                'name' => (string) ($item['name'] ?? ''),
                'image' => null,
                'qty' => rtrim(rtrim(number_format($qty, 2, ',', '.'), '0'), ','),
                'amount' => format_price($item['line_total'] ?? 0),
                'details' => [],
                'slots' => [],
            ];
        }

        return $lines;
    }

    public function orderLines(Order $order): array
    {
        $lines = [];

        foreach ($order->items as $item) {
            if (in_array($item->options['kind'] ?? $item->sku, ['delivery', 'tip', 'DELIVERY', 'TIP'], true)) {
                continue;
            }

            $slots = [];
            $details = [];
            foreach ($item->options['selections'] ?? [] as $slot) {
                $slotName = trim((string) ($slot['slot'] ?? $slot['name'] ?? ''));
                $dishes = [];
                foreach ($slot['products'] ?? [] as $product) {
                    $dish = trim((string) ($product['name'] ?? ''));
                    if ($dish !== '') {
                        $dishes[] = $dish;
                    }
                }
                if ($dishes === []) {
                    continue;
                }
                $slots[] = [
                    'label' => $slotName,
                    'items' => implode(', ', $dishes),
                ];
                $details[] = $slotName !== '' ? $slotName.': '.implode(', ', $dishes) : implode(', ', $dishes);
            }

            $qty = (int) $item->quantity;
            $persons = (int) ($item->persons ?: $order->persons ?: 1);
            $qtyLabel = $qty > 1
                ? $qty.' × '.$persons.' Pers.'
                : $persons.' Pers.';

            $lines[] = [
                'name' => (string) $item->name,
                'image' => $this->itemImage($item),
                'qty' => $qtyLabel,
                'amount' => format_price($item->line_total),
                'details' => $details,
                'slots' => $slots,
            ];
        }

        foreach ($order->extras as $extra) {
            $image = $extra->extra?->displayImage() ?? velora_photo_for($extra->name, (int) ($extra->extra_id ?? $extra->id));
            $lines[] = [
                'name' => (string) $extra->name,
                'image' => $this->absoluteUrl($image),
                'qty' => (string) ((int) $extra->quantity),
                'amount' => format_price($extra->line_total),
                'details' => [],
                'slots' => [],
            ];
        }

        return $lines;
    }

    protected function itemImage(OrderItem $item): string
    {
        $options = $item->options ?? [];
        $path = $options['package_image'] ?? $options['image'] ?? null;

        if (is_string($path) && $path !== '') {
            return $this->absoluteUrl(velora_media_url($path, $item->name, (int) $item->id));
        }

        if ($item->product) {
            return $this->absoluteUrl($item->product->displayImage());
        }

        return $this->absoluteUrl(velora_photo_for($item->name, (int) $item->id));
    }

    protected function absoluteUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return velora_photo('buffet');
        }
        return mail_public_url($url);
    }

    protected function adminOrderUrl(Order $order): string
    {
        return $this->safeRoute('admin.orders.show', $order) ?: '';
    }

    protected function safeRoute(string $name, mixed $params = []): string
    {
        try {
            return route($name, $params);
        } catch (\Throwable) {
            return '';
        }
    }

    protected function telegramOrderText(Order $order, bool $paid): string
    {
        $name = trim(($order->billing_first_name ?? '').' '.($order->billing_last_name ?? ''));
        $address = trim(implode(' ', array_filter([
            $order->shipping_street ?: $order->billing_street,
            $order->shipping_house_number ?: $order->billing_house_number,
        ])));
        $city = trim(($order->shipping_postal_code ?: $order->billing_postal_code).' '.($order->shipping_city ?: $order->billing_city));

        $items = $order->items->map(fn ($item) => '• '.e($item->name).' – '.e(format_price($item->line_total)))->implode("\n");
        $extras = $order->extras->map(fn ($extra) => '• '.e($extra->name).' – '.e(format_price($extra->line_total)))->implode("\n");

        return ($paid ? "<b>Zahlung eingegangen</b>\n" : "<b>Neue Bestellung</b>\n")
            .'<b>Nr.:</b> '.e($order->order_number)."\n"
            .'<b>Kunde:</b> '.e($name)."\n"
            .'<b>E-Mail:</b> '.e((string) $order->billing_email)."\n"
            .($order->billing_phone ? '<b>Telefon:</b> '.e($order->billing_phone)."\n" : '')
            .($order->event_date ? '<b>Event:</b> '.$order->event_date->format('d.m.Y')."\n" : '')
            .'<b>Personen:</b> '.(int) $order->persons."\n"
            .($address !== '' ? '<b>Adresse:</b> '.e($address).', '.e($city)."\n" : '')
            .'<b>MwSt. ('.e(format_mwst_percent($order->tax_rate)).'%):</b> '.e(format_price($order->tax_amount))."\n"
            .((float) ($order->tax_standard_amount ?? 0) > 0
                ? '<b>MwSt. ('.e(format_mwst_percent($order->tax_standard_rate ?? shop_standard_tax_rate())).'%):</b> '.e(format_price($order->tax_standard_amount))."\n"
                : '')
            .'<b>Gesamt:</b> '.e(format_price($order->total))."\n"
            .($items !== '' ? "\n<b>Menüs</b>\n".$items : '')
            .($extras !== '' ? "\n<b>Extras</b>\n".$extras : '')
            .($this->adminOrderUrl($order) !== '' ? "\n\n".$this->adminOrderUrl($order) : '');
    }
}
