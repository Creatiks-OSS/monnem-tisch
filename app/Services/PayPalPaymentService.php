<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PayPalPaymentService
{
    public function __construct(
        protected PaymentGatewayService $gateways,
        protected PaymentService $payments
    ) {}

    public function createCheckoutUrl(Order $order): string
    {
        $token = $this->accessToken();
        $response = Http::withToken($token)
            ->post($this->gateways->paypalBaseUrl().'/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'custom_id' => (string) $order->id,
                    'invoice_id' => $order->order_number,
                    'amount' => [
                        'currency_code' => strtoupper($order->currency ?: 'EUR'),
                        'value' => number_format((float) $order->total, 2, '.', ''),
                    ],
                ]],
                'application_context' => [
                    'brand_name' => 'Monnemtisch Catering',
                    'user_action' => 'PAY_NOW',
                    'return_url' => route('payments.paypal.return'),
                    'cancel_url' => route('payments.cancel', ['order' => $order->order_number]),
                ],
            ]);

        if (! $response->successful()) {
            Log::warning('PayPal order failed', ['body' => $response->json()]);
            throw new RuntimeException($response->json('message') ?: 'PayPal-Zahlung konnte nicht gestartet werden.');
        }

        $this->attachGatewayMeta($order, 'paypal', [
            'paypal_order_id' => $response->json('id'),
        ]);

        foreach ($response->json('links', []) as $link) {
            if (($link['rel'] ?? '') === 'approve' && ! empty($link['href'])) {
                return $link['href'];
            }
        }

        throw new RuntimeException('PayPal-Weiterleitung fehlt.');
    }

    public function capture(string $paypalOrderId): Order
    {
        $token = $this->accessToken();
        $response = Http::withToken($token)
            ->withBody('{}', 'application/json')
            ->post($this->gateways->paypalBaseUrl().'/v2/checkout/orders/'.$paypalOrderId.'/capture');

        if (! $response->successful()) {
            throw new RuntimeException('PayPal-Zahlung konnte nicht bestätigt werden.');
        }

        $data = $response->json();
        $customId = (int) ($data['purchase_units'][0]['custom_id'] ?? 0);
        $captureId = $data['purchase_units'][0]['payments']['captures'][0]['id'] ?? $paypalOrderId;
        $order = Order::query()->findOrFail($customId);

        if (($data['status'] ?? '') === 'COMPLETED') {
            $this->payments->markPaid($order, 'paypal', $captureId, [
                'paypal_order_id' => $paypalOrderId,
            ]);
        }

        return $order->fresh();
    }

    public function handleWebhook(Request $request): void
    {
        if (! $this->verifyWebhookSignature($request)) {
            throw new RuntimeException('Ungültige PayPal-Signatur.');
        }

        $event = $request->all();
        $type = $event['event_type'] ?? '';

        if (! in_array($type, ['PAYMENT.CAPTURE.COMPLETED', 'CHECKOUT.ORDER.APPROVED'], true)) {
            return;
        }

        $resource = $event['resource'] ?? [];
        $customId = (int) ($resource['custom_id'] ?? $resource['purchase_units'][0]['custom_id'] ?? 0);

        if (! $customId) {
            return;
        }

        $order = Order::query()->find($customId);
        if (! $order) {
            return;
        }

        $this->payments->markPaid(
            $order,
            'paypal',
            $resource['id'] ?? null,
            ['event' => $type]
        );
    }

    protected function verifyWebhookSignature(Request $request): bool
    {
        $webhookId = $this->gateways->paypalWebhookId();
        if ($webhookId === '') {
            return false;
        }

        $algo = (string) $request->header('PAYPAL-AUTH-ALGO', '');
        $certUrl = (string) $request->header('PAYPAL-CERT-URL', '');
        $transmissionId = (string) $request->header('PAYPAL-TRANSMISSION-ID', '');
        $transmissionSig = (string) $request->header('PAYPAL-TRANSMISSION-SIG', '');
        $transmissionTime = (string) $request->header('PAYPAL-TRANSMISSION-TIME', '');

        if ($algo === '' || $certUrl === '' || $transmissionId === '' || $transmissionSig === '' || $transmissionTime === '') {
            return false;
        }

        $host = strtolower((string) (parse_url($certUrl, PHP_URL_HOST) ?: ''));
        if (! in_array($host, ['api.paypal.com', 'api.sandbox.paypal.com', 'api-m.paypal.com', 'api-m.sandbox.paypal.com'], true)) {
            return false;
        }

        try {
            $response = Http::withToken($this->accessToken())
                ->post($this->gateways->paypalBaseUrl().'/v1/notifications/verify-webhook-signature', [
                    'auth_algo' => $algo,
                    'cert_url' => $certUrl,
                    'transmission_id' => $transmissionId,
                    'transmission_sig' => $transmissionSig,
                    'transmission_time' => $transmissionTime,
                    'webhook_id' => $webhookId,
                    'webhook_event' => $request->json()->all() ?: $request->all(),
                ]);
        } catch (\Throwable $e) {
            Log::warning('PayPal webhook verification failed', ['error' => $e->getMessage()]);

            return false;
        }

        return $response->successful() && strtoupper((string) $response->json('verification_status')) === 'SUCCESS';
    }

    protected function accessToken(): string
    {
        return Cache::remember('paypal.access_token.'.$this->gateways->paypalMode(), 50 * 60, function () {
            $response = Http::asForm()
                ->withBasicAuth($this->gateways->paypalClientId(), $this->gateways->paypalSecret())
                ->post($this->gateways->paypalBaseUrl().'/v1/oauth2/token', [
                    'grant_type' => 'client_credentials',
                ]);

            if (! $response->successful() || empty($response->json('access_token'))) {
                throw new RuntimeException('PayPal-Anmeldung fehlgeschlagen. Bitte Client ID und Secret prüfen.');
            }

            return (string) $response->json('access_token');
        });
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
