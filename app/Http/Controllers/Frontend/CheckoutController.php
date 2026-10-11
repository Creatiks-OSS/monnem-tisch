<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\CheckoutRequest;
use App\Models\EventType;
use App\Services\AvailabilityService;
use App\Services\CartService;
use App\Services\GoogleDistanceService;
use App\Services\OrderService;
use App\Services\PaymentGatewayService;
use App\Services\PayPalPaymentService;
use App\Services\StripePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cart,
        protected OrderService $orders,
        protected AvailabilityService $availability,
        protected PaymentGatewayService $gateways,
        protected GoogleDistanceService $distance
    ) {}

    public function index(): View|RedirectResponse
    {
        $couponCode = $this->cart->appliedCouponCode() ?? '';
        $cart = $this->cart->getCart();

        if (empty($cart['items']) && empty($cart['extras'])) {
            return redirect()
                ->route('packages.index', 'buffets')
                ->with('error', 'Bitte wählen Sie zuerst ein Menü.');
        }

        $eventTypes = EventType::query()
            ->active()
            ->orderBy('sort_order')
            ->get();

        return view('frontend.checkout.index', [
            'cart' => $cart,
            'totals' => $this->cart->totals(),
            'couponCode' => $couponCode,
            'eventTypes' => $eventTypes,
            'blockedDates' => $this->availability->getBlockedDates(now(), now()->addMonths(18)),
            'paymentMethods' => $this->gateways->checkoutMethods(),
            'deliveryCostPerKm' => (float) config('delivery.cost_per_km', 1.00),
            'deliveryQuoteUrl' => route('checkout.delivery-distance'),
        ]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $cart = $this->cart->getCart();

        if (empty($cart['items']) && empty($cart['extras'])) {
            return redirect()
                ->route('packages.index', 'buffets')
                ->with('error', 'Bitte wählen Sie zuerst ein Menü.');
        }

        $data = $request->validated();

        if (! empty($data['event_date']) && ! $this->availability->isDateAvailable($data['event_date'])) {
            return back()
                ->withInput()
                ->withErrors(['event_date' => 'Dieses Datum ist leider nicht verfügbar.']);
        }

        if (! empty($data['coupon_code'])) {
            $applied = $this->cart->applyCoupon($data['coupon_code']);
            if (! $applied) {
                return back()
                    ->withInput($request->except('coupon_code'))
                    ->withErrors([
                        'coupon_code' => 'Der Gutscheincode ist ungültig oder nicht anwendbar.',
                    ]);
            }
        }

        $sameAsBilling = $request->boolean('shipping_same_as_billing', true);

        $billing = [
            'company' => $data['company'] ?? null,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'street' => $data['billing_street'],
            'house_number' => $data['billing_house_number'],
            'postal_code' => $data['billing_postal_code'],
            'city' => $data['billing_city'],
            'country' => $data['billing_country'] ?? 'DE',
        ];

        $shipping = $sameAsBilling ? $billing : [
            'company' => $data['company'] ?? null,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'street' => $data['shipping_street'] ?? $billing['street'],
            'house_number' => $data['shipping_house_number'] ?? $billing['house_number'],
            'postal_code' => $data['shipping_postal_code'] ?? $billing['postal_code'],
            'city' => $data['shipping_city'] ?? $billing['city'],
            'country' => $data['shipping_country'] ?? 'DE',
        ];

        $this->cart->setEvent([
            'event_type_id' => $data['event_type_id'] ?? null,
            'date' => $data['event_date'] ?? null,
            'time' => $data['event_time'] ?? null,
            'persons' => $data['persons'] ?? 1,
        ]);

        $typedAddress = $sameAsBilling
            ? trim((string) ($data['billing_address'] ?? ''))
            : trim((string) ($data['shipping_address'] ?? ''));
        if ($typedAddress === '') {
            $typedAddress = $this->distance->buildDestination(
                (string) $shipping['street'],
                (string) $shipping['house_number'],
                (string) $shipping['postal_code'],
                (string) $shipping['city']
            );
        }

        $quote = $this->distance->quoteLine($typedAddress);

        if ($quote['ok']) {
            $isFreeCity = (bool) ($quote['isStuttgart'] ?? $quote['isHannover']);
            $distanceKm = (float) $quote['distanceKm'];
            $deliveryCost = $this->distance->costFor($isFreeCity, $distanceKm);
        } elseif ($this->distance->isStuttgartRegion($typedAddress, (string) $shipping['postal_code'])) {
            $isFreeCity = true;
            $distanceKm = 0.0;
            $deliveryCost = 0.0;
        } else {
            $isFreeCity = false;
            $distanceKm = max(0, (float) ($data['delivery_km'] ?? 0));
            $deliveryCost = $distanceKm > 0
                ? $this->distance->costFor(false, $distanceKm)
                : 0.0;
        }

        $this->cart->setDelivery([
            'postal_code' => $shipping['postal_code'],
            'city' => $shipping['city'],
            'street' => $shipping['street'],
            'house_number' => $shipping['house_number'],
            'distance_km' => $distanceKm,
            'cost' => $deliveryCost,
            'is_stuttgart' => $isFreeCity,
            'formatted_address' => $quote['formattedAddress'] ?: $typedAddress,
        ]);

        $this->cart->setTip((float) ($data['tip_amount'] ?? 0));

        $order = $this->orders->createFromCart(
            [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'company' => $data['company'] ?? null,
                'notes' => $data['notes'] ?? null,
                'source' => 'checkout',
            ],
            [
                'event_type_id' => $data['event_type_id'] ?? null,
                'date' => $data['event_date'] ?? null,
                'time' => $data['event_time'] ?? null,
                'persons' => $data['persons'] ?? 1,
                'notes' => $data['notes'] ?? null,
            ],
            [
                'billing' => $billing,
                'shipping' => $shipping,
            ],
            $data['payment_method'] ?? 'anfrage',
            false
        );

        $method = (string) ($data['payment_method'] ?? $order->payment_method);

        try {
            if ($method === 'stripe') {
                $url = app(StripePaymentService::class)->createCheckoutUrl($order);
                $this->cart->clear();

                return redirect()->away($url);
            }
            if ($method === 'paypal') {
                $url = app(PayPalPaymentService::class)->createCheckoutUrl($order);
                $this->cart->clear();

                return redirect()->away($url);
            }
        } catch (\Throwable $e) {
            Log::error('Checkout payment redirect failed', [
                'order_id' => $order->id,
                'method' => $method,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', $e->getMessage() ?: 'Die Zahlung konnte nicht gestartet werden. Bitte prüfen Sie die Stripe-Einstellungen oder versuchen Sie es erneut.');
        }

        $this->cart->clear();

        return redirect()
            ->route('checkout.success', $order->order_number)
            ->with('success', 'Vielen Dank! Ihre Bestellung wurde übermittelt.');
    }

    public function success(string $orderNumber): View
    {
        $order = \App\Models\Order::query()
            ->where('order_number', $orderNumber)
            ->with(['items', 'extras', 'customer'])
            ->firstOrFail();

        return view('frontend.checkout.success', [
            'order' => $order,
        ]);
    }

    public function quoteDistance(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'house_number' => ['nullable', 'string', 'max:20'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:100'],
        ]);

        $line = trim((string) ($validated['address'] ?? ''));
        if ($line === '') {
            $line = $this->distance->buildDestination(
                (string) ($validated['street'] ?? ''),
                (string) ($validated['house_number'] ?? ''),
                (string) ($validated['postal_code'] ?? ''),
                (string) ($validated['city'] ?? '')
            );
        }

        $quote = $this->distance->quoteLine($line);

        return response()->json($quote);
    }
}
