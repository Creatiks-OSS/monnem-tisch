<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Extra;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        protected CartService $cart,
        protected PricingService $pricing,
        protected CouponService $coupons,
        protected NotificationService $notifications,
        protected ActivityLogService $activity,
        protected PaymentService $payments
    ) {}

    public function createFromCart(array $customerData, array $eventData, array $addresses, ?string $paymentMethod = 'anfrage', bool $clearCart = true): Order
    {
        $cart = $this->cart->getCart();
        $totals = $this->cart->totals();

        $isOnlineCheckout = in_array($paymentMethod, ['stripe', 'paypal'], true);

        return DB::transaction(function () use ($customerData, $eventData, $addresses, $paymentMethod, $cart, $totals, $clearCart, $isOnlineCheckout) {
            $customer = $this->findOrCreateCustomer($customerData);

            $billing = $addresses['billing'] ?? $addresses;
            $shipping = $addresses['shipping'] ?? $billing;

            $orderNumber = $this->generateOrderNumber();

            $order = Order::query()->create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'user_id' => auth()->id(),
                'status' => 'neu',
                'payment_method' => $paymentMethod ?: 'anfrage',
                'payment_status' => 'pending',
                'event_type_id' => $eventData['event_type_id'] ?? ($cart['event']['event_type_id'] ?? null),
                'event_date' => $eventData['date'] ?? ($cart['event']['date'] ?? null),
                'event_time' => $eventData['time'] ?? ($cart['event']['time'] ?? null),
                'persons' => (int) ($eventData['persons'] ?? ($cart['event']['persons'] ?? 1)),
                'subtotal' => $totals['subtotal'],
                'delivery_fee' => $totals['delivery_fee'],
                'delivery_km' => (float) ($cart['delivery']['distance_km'] ?? 0),
                'service_fee' => $totals['service_fee'],
                'tip_amount' => $totals['tip_amount'] ?? 0,
                'discount' => $totals['discount'],
                'tax_rate' => $totals['tax_rate'],
                'tax_amount' => $totals['tax_amount'],
                'tax_standard_rate' => $totals['tax_standard_rate'] ?? shop_standard_tax_rate(),
                'tax_standard_amount' => $totals['tax_standard_amount'] ?? 0,
                'total' => $totals['total'],
                'currency' => 'EUR',
                'coupon_code' => is_array($cart['coupon'] ?? null) ? ($cart['coupon']['code'] ?? null) : null,
                'billing_company' => $billing['company'] ?? ($customerData['company'] ?? null),
                'billing_first_name' => $billing['first_name'] ?? ($customerData['first_name'] ?? null),
                'billing_last_name' => $billing['last_name'] ?? ($customerData['last_name'] ?? null),
                'billing_email' => $billing['email'] ?? ($customerData['email'] ?? null),
                'billing_phone' => $billing['phone'] ?? ($customerData['phone'] ?? null),
                'billing_street' => $billing['street'] ?? null,
                'billing_house_number' => $billing['house_number'] ?? null,
                'billing_postal_code' => $billing['postal_code'] ?? null,
                'billing_city' => $billing['city'] ?? null,
                'billing_country' => $billing['country'] ?? 'DE',
                'shipping_company' => $shipping['company'] ?? null,
                'shipping_first_name' => $shipping['first_name'] ?? ($customerData['first_name'] ?? null),
                'shipping_last_name' => $shipping['last_name'] ?? ($customerData['last_name'] ?? null),
                'shipping_phone' => $shipping['phone'] ?? ($customerData['phone'] ?? null),
                'shipping_street' => $shipping['street'] ?? null,
                'shipping_house_number' => $shipping['house_number'] ?? null,
                'shipping_postal_code' => $shipping['postal_code'] ?? ($cart['delivery']['postal_code'] ?? null),
                'shipping_city' => $shipping['city'] ?? null,
                'shipping_country' => $shipping['country'] ?? 'DE',
                'notes' => $customerData['notes'] ?? ($eventData['notes'] ?? null),
            ]);

            foreach ($cart['items'] ?? [] as $item) {
                $options = $item['options'] ?? [];
                if (! empty($item['image']) && empty($options['package_image'])) {
                    $options['package_image'] = $item['image'];
                }

                $order->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'name' => $item['name'] ?? 'Produkt',
                    'sku' => $item['sku'] ?? null,
                    'quantity' => (int) ($item['quantity'] ?? 1),
                    'unit_price' => (float) ($item['unit_price'] ?? 0),
                    'persons' => (int) ($item['persons'] ?? 1),
                    'line_total' => (float) ($item['line_total'] ?? 0),
                    'options' => $options ?: null,
                ]);
            }

            $persons = (int) ($order->persons ?: 1);
            foreach ($cart['extras'] ?? [] as $extraRow) {
                $extra = Extra::query()->find($extraRow['extra_id'] ?? null);
                $quantity = (int) ($extraRow['quantity'] ?? 1);
                $unit = (float) ($extraRow['unit_price'] ?? ($extra?->price ?? 0));
                $line = (float) ($extraRow['line_total'] ?? ($extra
                    ? $this->pricing->calculateExtraPrice($extra, $quantity, $persons)
                    : $unit * $quantity));

                $order->extras()->create([
                    'extra_id' => $extraRow['extra_id'] ?? null,
                    'name' => $extraRow['name'] ?? ($extra?->name ?? 'Extra'),
                    'kind' => $extraRow['kind'] ?? ($extra?->kind ?? 'service'),
                    'quantity' => $quantity,
                    'unit_price' => $unit,
                    'line_total' => $line,
                ]);
            }

            $deliveryFee = (float) ($totals['delivery_fee'] ?? 0);
            $deliveryKm = (float) ($cart['delivery']['distance_km'] ?? 0);
            $deliveryName = $deliveryFee > 0
                ? 'Lieferkosten ('.number_format($deliveryKm, 1, ',', '.').' km)'
                : 'Lieferkosten (Kostenlos)';

            $order->items()->create([
                'product_id' => null,
                'name' => $deliveryName,
                'sku' => 'DELIVERY',
                'quantity' => 1,
                'unit_price' => $deliveryFee,
                'persons' => 1,
                'line_total' => $deliveryFee,
                'options' => [
                    'kind' => 'delivery',
                    'km' => $deliveryKm,
                    'formatted_address' => $cart['delivery']['formatted_address'] ?? null,
                ],
            ]);

            $tipAmount = (float) ($totals['tip_amount'] ?? 0);
            if ($tipAmount > 0) {
                $order->items()->create([
                    'product_id' => null,
                    'name' => 'Trinkgeld',
                    'sku' => 'TIP',
                    'quantity' => 1,
                    'unit_price' => $tipAmount,
                    'persons' => 1,
                    'line_total' => $tipAmount,
                    'options' => [
                        'kind' => 'tip',
                    ],
                ]);
            }

            if (! empty($order->coupon_code) && ! $isOnlineCheckout) {
                try {
                    $this->coupons->redeem($order->coupon_code, (float) $order->subtotal);
                    $order->forceFill(['coupon_redeemed_at' => now()])->save();
                } catch (\Throwable $e) {
                    Log::info('Coupon redeem skipped', ['code' => $order->coupon_code, 'error' => $e->getMessage()]);
                }
            }

            if ($clearCart) {
                $this->cart->clear();
            }

            $this->activity->log('Bestellung erstellt', $order, [
                'order_number' => $order->order_number,
                'total' => $order->total,
                'abandoned' => $isOnlineCheckout,
            ]);

            try {
                app(CustomerSegmentService::class)->syncOrder($order);
            } catch (\Throwable $e) {
                Log::warning('Abandoned cart segment sync failed', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }

            if (! $isOnlineCheckout) {
                $this->notifications->notifyAdmins(
                    'order.created',
                    'Yeni sipariş '.$order->order_number,
                    number_format((float) $order->total, 2, ',', '.').' € tutarında yeni bir sipariş alındı.',
                    [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'url' => route('admin.orders.show', $order),
                    ]
                );

                $this->sendOrderPlacedMail($order);

                try {
                    app(BusinessNotificationService::class)->notifyOrderCreated($order);
                } catch (\Throwable $e) {
                    Log::warning('Order created business notifications failed', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $this->payments->recordPending($order, $paymentMethod ?: 'anfrage');

            $order = $order->load(['items', 'extras', 'customer']);

            if (! $isOnlineCheckout) {
                try {
                    app(IngredientAnalysisService::class)->queue($order);
                } catch (\Throwable $e) {
                    Log::warning('Order ingredient analysis queue failed', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return $order;
        });
    }

    public function updateStatus(Order $order, string $status): Order
    {
        $allowed = [
            'neu', 'bestätigt', 'in_bearbeitung', 'vorbereitung',
            'unterwegs', 'geliefert', 'abgeschlossen', 'storniert',
        ];

        if (! in_array($status, $allowed, true)) {
            throw new \InvalidArgumentException('Ungültiger Bestellstatus: '.$status);
        }

        $previous = $order->status;
        $order->status = $status;

        if ($status === 'bestätigt' && ! $order->confirmed_at) {
            $order->confirmed_at = now();
        }

        if (in_array($status, ['geliefert', 'abgeschlossen'], true) && ! $order->delivered_at) {
            $order->delivered_at = now();
        }

        $order->save();

        try {
            app(CustomerSegmentService::class)->syncOrder($order->fresh());
        } catch (\Throwable $e) {
            Log::warning('Order status segment sync failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        $this->activity->log('Bestellstatus geändert', $order, [
            'from' => $previous,
            'to' => $status,
        ]);

        return $order->fresh();
    }

    public function updateDurum(Order $order, string $status): Order
    {
        return $this->updateStatus($order, $status);
    }

    public function cancel(Order $order): Order
    {
        return $this->updateStatus($order, 'storniert');
    }

    protected function findOrCreateCustomer(array $data): Customer
    {
        $email = $data['email'] ?? null;

        $customer = $email
            ? Customer::query()->where('email', $email)->first()
            : null;

        if ($customer) {
            $customer->fill([
                'first_name' => $data['first_name'] ?? $customer->first_name,
                'last_name' => $data['last_name'] ?? $customer->last_name,
                'company' => $data['company'] ?? $customer->company,
                'phone' => $data['phone'] ?? $customer->phone,
            ])->save();

            return $customer;
        }

        return Customer::query()->create([
            'user_id' => auth()->id(),
            'first_name' => $data['first_name'] ?? 'Gast',
            'last_name' => $data['last_name'] ?? '',
            'company' => $data['company'] ?? null,
            'email' => $email ?? ('gast-'.Str::lower(Str::random(8)).'@velora.local'),
            'phone' => $data['phone'] ?? null,
            'notes' => $data['notes'] ?? null,
            'source' => $data['source'] ?? 'webshop',
        ]);
    }

    protected function generateOrderNumber(): string
    {
        do {
            $number = 'VEL-'.now()->format('Ymd').'-'.strtoupper(Str::random(4));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }

    protected function sendOrderPlacedMail(Order $order): void
    {
        try {
            app(BusinessNotificationService::class)->sendCustomerOrderReceived($order);
        } catch (\Throwable $e) {
            Log::warning('Order mail failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
