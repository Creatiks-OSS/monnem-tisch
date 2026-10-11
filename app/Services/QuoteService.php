<?php

namespace App\Services;

use App\Mail\QuoteProposalMail;
use App\Models\ContactMessage;
use App\Models\Customer;
use App\Models\EventType;
use App\Models\Order;
use App\Models\Quote;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class QuoteService
{
    public function __construct(
        protected CartService $cart,
        protected PricingService $pricing,
        protected OrderService $orders,
        protected NotificationService $notifications,
        protected ActivityLogService $activity
    ) {}

    public function createFromRequest(array $data, bool $notify = true): Quote
    {
        return DB::transaction(function () use ($data, $notify) {
            $customer = $this->findOrCreateCustomer($data);

            $quote = Quote::query()->create([
                'quote_number' => $this->generateQuoteNumber(),
                'customer_id' => $customer->id,
                'assigned_user_id' => $data['assigned_user_id'] ?? null,
                'status' => 'neu',
                'first_name' => $data['first_name'] ?? $customer->first_name,
                'last_name' => $data['last_name'] ?? $customer->last_name,
                'company' => $data['company'] ?? $customer->company,
                'email' => $data['email'] ?? $customer->email,
                'phone' => $data['phone'] ?? $customer->phone,
                'event_type_id' => $data['event_type_id'] ?? null,
                'event_date' => $data['event_date'] ?? ($data['date'] ?? null),
                'event_time' => $data['event_time'] ?? ($data['time'] ?? null),
                'persons' => $data['persons'] ?? null,
                'location' => $data['location'] ?? null,
                'budget' => $data['budget'] ?? null,
                'catering_wunsch' => $data['catering_wunsch'] ?? null,
                'message' => $data['message'] ?? null,
                'subtotal' => (float) ($data['subtotal'] ?? 0),
                'delivery_fee' => (float) ($data['delivery_fee'] ?? 0),
                'discount' => (float) ($data['discount'] ?? 0),
                'tax_amount' => (float) ($data['tax_amount'] ?? 0),
                'total' => (float) ($data['total'] ?? 0),
                'valid_until' => $data['valid_until'] ?? now()->addDays(14)->toDateString(),
                'source' => $data['source'] ?? 'website',
                'gclid' => $data['gclid'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] ?? [] as $item) {
                $quote->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'name' => $item['name'] ?? 'Position',
                    'sku' => $item['sku'] ?? null,
                    'quantity' => (int) ($item['quantity'] ?? 1),
                    'unit_price' => (float) ($item['unit_price'] ?? 0),
                    'persons' => (int) ($item['persons'] ?? ($data['persons'] ?? 1)),
                    'line_total' => (float) ($item['line_total'] ?? 0),
                    'options' => $item['options'] ?? null,
                ]);
            }

            $this->activity->log('Anfrage erstellt', $quote, [
                'quote_number' => $quote->quote_number,
            ]);

            if ($notify) {
                $this->notifications->notifyAdmins(
                    'quote.created',
                    'Yeni teklif talebi '.$quote->quote_number,
                    trim($quote->first_name.' '.$quote->last_name).' bir teklif talebi gönderdi.',
                    [
                        'quote_id' => $quote->id,
                        'url' => route('admin.quotes.show', $quote),
                    ]
                );

                $this->sendQuoteMail($quote);

                try {
                    app(BusinessNotificationService::class)->notifyQuoteCreated($quote);
                } catch (\Throwable $e) {
                    Log::warning('Quote business notifications failed', [
                        'quote_id' => $quote->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return $quote->load('items');
        });
    }

    public function createFromContact(ContactMessage $message): Quote
    {
        if ($message->quote_id) {
            $existing = Quote::query()->find($message->quote_id);
            if ($existing) {
                return $existing->load('items');
            }
        }

        [$firstName, $lastName] = $this->splitPersonName((string) $message->name);
        $eventTypeId = $this->resolveEventTypeId($message->event_type);

        $notes = trim(implode("\n", array_filter([
            $message->subject ? 'Kontakt-Betreff: '.$message->subject : null,
            $message->event_type && ! $eventTypeId ? 'Event-Art: '.$message->event_type : null,
            'Erstellt aus Kontaktnachricht #'.$message->id,
        ])));

        $quote = $this->createFromRequest([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $message->email,
            'phone' => $message->phone,
            'event_type_id' => $eventTypeId,
            'event_date' => $message->event_date?->toDateString(),
            'message' => $message->message,
            'catering_wunsch' => $message->subject,
            'source' => 'kontakt',
            'notes' => $notes,
        ], false);

        $message->update([
            'quote_id' => $quote->id,
            'status' => 'bearbeitet',
            'is_read' => true,
        ]);

        $this->activity->log('Kontaktnachricht in Angebot umgewandelt', $quote, [
            'contact_message_id' => $message->id,
            'quote_number' => $quote->quote_number,
        ]);

        return $quote;
    }

    public function sendProposalEmail(Quote $quote): Quote
    {
        $quote->loadMissing('items');

        if ($quote->items->isEmpty()) {
            throw new \RuntimeException('Teklif kalemi yok. Önce yapay zekâ ile teklif oluşturun.');
        }

        $path = app(PdfService::class)->generateQuotePdf($quote);

        Mail::to($quote->email)->send(new QuoteProposalMail($quote, $path));

        $this->updateStatus($quote, 'angebot_erstellt');
        $this->activity->log('Angebots-PDF versendet', $quote);

        return $quote->fresh(['items', 'eventType', 'customer']);
    }

    public function createFromCart(array $data): Quote
    {
        $cart = $this->cart->getCart();
        $totals = $this->cart->totals();

        $payload = array_merge($data, [
            'event_type_id' => $data['event_type_id'] ?? ($cart['event']['event_type_id'] ?? null),
            'event_date' => $data['event_date'] ?? ($cart['event']['date'] ?? null),
            'event_time' => $data['event_time'] ?? ($cart['event']['time'] ?? null),
            'persons' => $data['persons'] ?? ($cart['event']['persons'] ?? null),
            'subtotal' => $totals['subtotal'],
            'delivery_fee' => $totals['delivery_fee'],
            'discount' => $totals['discount'],
            'tax_amount' => $totals['tax_amount'],
            'total' => $totals['total'],
            'source' => $data['source'] ?? 'cart',
            'items' => array_map(function (array $item) {
                return [
                    'product_id' => $item['product_id'] ?? null,
                    'name' => $item['name'] ?? 'Produkt',
                    'sku' => $item['sku'] ?? null,
                    'quantity' => (int) ($item['quantity'] ?? 1),
                    'unit_price' => (float) ($item['unit_price'] ?? 0),
                    'persons' => (int) ($item['persons'] ?? 1),
                    'line_total' => (float) ($item['line_total'] ?? 0),
                    'options' => $item['options'] ?? null,
                ];
            }, array_values($cart['items'] ?? [])),
        ]);

        // Append extras as named line items without product_id
        foreach ($cart['extras'] ?? [] as $extraRow) {
            $payload['items'][] = [
                'product_id' => null,
                'name' => 'Extra: '.($extraRow['name'] ?? 'Extra'),
                'sku' => null,
                'quantity' => (int) ($extraRow['quantity'] ?? 1),
                'unit_price' => (float) ($extraRow['unit_price'] ?? 0),
                'persons' => (int) ($payload['persons'] ?? 1),
                'line_total' => (float) ($extraRow['line_total'] ?? 0),
                'options' => ['extra_id' => $extraRow['extra_id'] ?? null],
            ];
        }

        $quote = $this->createFromRequest($payload);
        $this->cart->clear();

        return $quote;
    }

    public function updateDurum(Quote $quote, string $status): Quote
    {
        return $this->updateStatus($quote, $status);
    }

    public function updateStatus(Quote $quote, string $status): Quote
    {
        $allowed = ['neu', 'kontaktiert', 'angebot_erstellt', 'verhandlung', 'gewonnen', 'verloren'];

        if (! in_array($status, $allowed, true)) {
            throw new \InvalidArgumentException('Ungültiger Angebotsstatus: '.$status);
        }

        $previous = $quote->status;
        $quote->status = $status;
        $quote->save();

        $this->activity->log('Angebotsstatus geändert', $quote, [
            'from' => $previous,
            'to' => $status,
        ]);

        return $quote->fresh();
    }

    public function convertToOrder(Quote $quote): Order
    {
        return DB::transaction(function () use ($quote) {
            $quote->loadMissing(['items', 'customer']);

            $customerData = [
                'first_name' => $quote->first_name,
                'last_name' => $quote->last_name,
                'company' => $quote->company,
                'email' => $quote->email,
                'phone' => $quote->phone,
                'source' => 'quote',
            ];

            $eventData = [
                'event_type_id' => $quote->event_type_id,
                'date' => $quote->event_date,
                'time' => $quote->event_time,
                'persons' => $quote->persons ?? 1,
                'notes' => $quote->message,
            ];

            // Seed cart from quote then create order, or create order directly
            $orderNumber = 'VEL-'.now()->format('Ymd').'-'.strtoupper(Str::random(4));
            while (Order::query()->where('order_number', $orderNumber)->exists()) {
                $orderNumber = 'VEL-'.now()->format('Ymd').'-'.strtoupper(Str::random(4));
            }

            $order = Order::query()->create([
                'order_number' => $orderNumber,
                'customer_id' => $quote->customer_id,
                'user_id' => auth()->id(),
                'status' => 'neu',
                'payment_method' => 'anfrage',
                'payment_status' => 'pending',
                'event_type_id' => $quote->event_type_id,
                'event_date' => $quote->event_date,
                'event_time' => $quote->event_time,
                'persons' => $quote->persons ?? 1,
                'subtotal' => $quote->subtotal,
                'delivery_fee' => $quote->delivery_fee,
                'service_fee' => 0,
                'discount' => $quote->discount,
                'tax_rate' => shop_tax_rate(),
                'tax_amount' => $quote->tax_amount,
                'total' => $quote->total,
                'currency' => 'EUR',
                'billing_first_name' => $quote->first_name,
                'billing_last_name' => $quote->last_name,
                'billing_company' => $quote->company,
                'billing_email' => $quote->email,
                'billing_phone' => $quote->phone,
                'shipping_first_name' => $quote->first_name,
                'shipping_last_name' => $quote->last_name,
                'shipping_phone' => $quote->phone,
                'notes' => $quote->message,
            ]);

            foreach ($quote->items as $item) {
                $extraId = is_array($item->options) ? ($item->options['extra_id'] ?? null) : null;

                if ($extraId || str_starts_with((string) $item->name, 'Extra:')) {
                    $order->extras()->create([
                        'extra_id' => $extraId,
                        'name' => preg_replace('/^Extra:\s*/', '', (string) $item->name),
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'line_total' => $item->line_total,
                    ]);
                } else {
                    $order->items()->create([
                        'product_id' => $item->product_id,
                        'name' => $item->name,
                        'sku' => $item->sku,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'persons' => $item->persons,
                        'line_total' => $item->line_total,
                        'options' => $item->options,
                    ]);
                }
            }

            $this->updateStatus($quote, 'gewonnen');

            $this->activity->log('Angebot in Bestellung umgewandelt', $order, [
                'quote_number' => $quote->quote_number,
                'order_number' => $order->order_number,
            ]);

            $order = $order->load(['items', 'extras', 'customer']);

            try {
                app(IngredientAnalysisService::class)->queue($order);
            } catch (\Throwable $e) {
                Log::warning('Quote-to-order ingredient analysis queue failed', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return $order;
        });
    }

    protected function findOrCreateCustomer(array $data): Customer
    {
        $email = $data['email'] ?? null;
        $customer = $email
            ? Customer::query()->where('email', $email)->first()
            : null;

        if ($customer) {
            return $customer;
        }

        return Customer::query()->create([
            'user_id' => auth()->id(),
            'first_name' => $data['first_name'] ?? 'Interessent',
            'last_name' => $data['last_name'] ?? '',
            'company' => $data['company'] ?? null,
            'email' => $email ?? ('anfrage-'.Str::lower(Str::random(8)).'@velora.local'),
            'phone' => $data['phone'] ?? null,
            'source' => $data['source'] ?? 'quote',
        ]);
    }

    protected function generateQuoteNumber(): string
    {
        do {
            $number = 'ANG-'.now()->format('Ymd').'-'.strtoupper(Str::random(4));
        } while (Quote::query()->where('quote_number', $number)->exists());

        return $number;
    }

    protected function sendQuoteMail(Quote $quote): void
    {
        try {
            app(BusinessNotificationService::class)->sendCustomerQuoteReceived($quote);
        } catch (\Throwable $e) {
            Log::warning('Quote mail failed', [
                'quote_id' => $quote->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function splitPersonName(string $name): array
    {
        $normalized = trim((string) preg_replace('/\s+/u', ' ', $name));
        if ($normalized === '') {
            return ['Interessent', 'Kontakt'];
        }

        $parts = explode(' ', $normalized, 2);

        return [$parts[0], $parts[1] ?? $parts[0]];
    }

    protected function resolveEventTypeId(?string $name): ?int
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        $id = EventType::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->value('id');

        return $id !== null ? (int) $id : null;
    }
}
