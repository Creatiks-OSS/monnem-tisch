<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class StripePaymentService
{
    public function __construct(
        protected PaymentGatewayService $gateways,
        protected PaymentService $payments
    ) {}

    public function createCheckoutUrl(Order $order): string
    {
        $secret = $this->gateways->stripeSecret();
        if ($secret === '') {
            throw new RuntimeException('Stripe ist nicht konfiguriert. Bitte Secret Key in den Zahlungseinstellungen hinterlegen.');
        }

        $successUrl = url('/zahlung/stripe/erfolg').'?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = url('/zahlung/abgebrochen/'.$order->order_number);

        $payload = [
            'mode' => 'payment',
            'locale' => 'de',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => (string) $order->id,
            'metadata[order_id]' => (string) $order->id,
            'metadata[order_number]' => $order->order_number,
        ];

        $email = trim((string) $order->billing_email);
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $payload['customer_email'] = $email;
        }

        foreach ($this->lineItems($order) as $index => $item) {
            $payload["line_items[{$index}][quantity]"] = $item['quantity'];
            $payload["line_items[{$index}][price_data][currency]"] = $item['currency'];
            $payload["line_items[{$index}][price_data][unit_amount]"] = $item['unit_amount'];
            $payload["line_items[{$index}][price_data][product_data][name]"] = $item['name'];
        }

        try {
            $response = Http::asForm()
                ->withToken($secret)
                ->acceptJson()
                ->timeout(20)
                ->post('https://api.stripe.com/v1/checkout/sessions', $payload);
        } catch (\Throwable $e) {
            Log::error('Stripe session request failed', ['error' => $e->getMessage()]);
            throw new RuntimeException('Stripe ist gerade nicht erreichbar. Bitte später erneut versuchen.');
        }

        if (! $response->successful() || empty($response->json('url'))) {
            Log::warning('Stripe session failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            $message = (string) data_get($response->json(), 'error.message', '');
            throw new RuntimeException($message !== '' ? $message : 'Stripe-Sitzung konnte nicht erstellt werden.');
        }

        $this->attachGatewayMeta($order, 'stripe', [
            'session_id' => $response->json('id'),
        ]);

        return (string) $response->json('url');
    }

    public function completeFromSession(string $sessionId): Order
    {
        $secret = $this->gateways->stripeSecret();
        $response = Http::withToken($secret)->get('https://api.stripe.com/v1/checkout/sessions/'.$sessionId);

        if (! $response->successful()) {
            throw new RuntimeException('Stripe-Sitzung nicht gefunden.');
        }

        $session = $response->json();
        $orderId = (int) ($session['client_reference_id'] ?? $session['metadata']['order_id'] ?? 0);
        $order = Order::query()->findOrFail($orderId);

        if (($session['payment_status'] ?? '') === 'paid' || ($session['status'] ?? '') === 'complete') {
            $this->payments->markPaid($order, 'stripe', $session['payment_intent'] ?? $sessionId, [
                'session_id' => $sessionId,
            ]);
        }

        return $order->fresh();
    }

    public function handleWebhook(Request $request): void
    {
        $payload = $request->getContent();
        $secret = $this->gateways->stripeWebhookSecret();

        if ($secret === '') {
            throw new RuntimeException('Stripe webhook secret fehlt.');
        }

        if (! $this->validSignature($payload, (string) $request->header('Stripe-Signature'), $secret)) {
            throw new RuntimeException('Ungültige Stripe-Signatur.');
        }

        $event = json_decode($payload, true) ?: [];
        $type = $event['type'] ?? '';
        $object = $event['data']['object'] ?? [];

        if (! in_array($type, ['checkout.session.completed', 'payment_intent.succeeded'], true)) {
            return;
        }

        $orderId = (int) ($object['client_reference_id'] ?? $object['metadata']['order_id'] ?? 0);
        if (! $orderId) {
            return;
        }

        $order = Order::query()->find($orderId);
        if (! $order) {
            return;
        }

        $this->payments->markPaid(
            $order,
            'stripe',
            $object['payment_intent'] ?? $object['id'] ?? null,
            ['event' => $type]
        );
    }

    protected function validSignature(string $payload, string $header, string $secret): bool
    {
        $parts = [];
        foreach (explode(',', $header) as $item) {
            [$k, $v] = array_pad(explode('=', trim($item), 2), 2, null);
            if ($k && $v) {
                $parts[$k][] = $v;
            }
        }

        $timestamp = $parts['t'][0] ?? null;
        $signatures = $parts['v1'] ?? [];
        if (! $timestamp || $signatures === []) {
            return false;
        }

        if (abs(time() - (int) $timestamp) > 300) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        foreach ($signatures as $signature) {
            if (hash_equals($expected, $signature)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, array{quantity: int, currency: string, unit_amount: int, name: string}>
     */
    protected function lineItems(Order $order): array
    {
        $currency = strtolower($order->currency ?: 'eur');
        $deliveryCents = (int) round(((float) $order->delivery_fee) * 100);
        $tipCents = (int) round(((float) ($order->tip_amount ?? 0)) * 100);
        $totalCents = (int) round(((float) $order->total) * 100);
        $cateringCents = max(0, $totalCents - $deliveryCents - $tipCents);

        $items = [];

        if ($cateringCents > 0) {
            $items[] = [
                'quantity' => 1,
                'currency' => $currency,
                'unit_amount' => $cateringCents,
                'name' => 'Monnemtisch Catering '.$order->order_number,
            ];
        }

        if ($deliveryCents > 0) {
            $km = number_format((float) ($order->delivery_km ?? 0), 1, ',', '.');
            $items[] = [
                'quantity' => 1,
                'currency' => $currency,
                'unit_amount' => $deliveryCents,
                'name' => 'Lieferkosten ('.$km.' km)',
            ];
        }

        if ($tipCents > 0) {
            $items[] = [
                'quantity' => 1,
                'currency' => $currency,
                'unit_amount' => $tipCents,
                'name' => 'Trinkgeld',
            ];
        }

        if ($items === []) {
            $items[] = [
                'quantity' => 1,
                'currency' => $currency,
                'unit_amount' => max(50, $totalCents),
                'name' => 'Monnemtisch Catering '.$order->order_number,
            ];
        }

        return $items;
    }

    protected function attachGatewayMeta(Order $order, string $method, array $meta): void
    {
        $payment = $order->payments()->latest('id')->first();
        if ($payment) {
            $payment->update(['meta' => array_merge($payment->meta ?? [], $meta)]);

            return;
        }

        $this->payments->recordPending($order, $method, $meta);
    }
}
