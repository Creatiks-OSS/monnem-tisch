<?php

namespace App\Services;

class PaymentGatewayService
{
    public const OFFLINE = [
        'anfrage' => 'Auf Anfrage',
        'rechnung' => 'Rechnung',
        'vorkasse' => 'Vorkasse',
        'bar' => 'Bar / vor Ort',
    ];

    public function __construct(protected SettingService $settings) {}

    public function stripeEnabled(): bool
    {
        return $this->settings->bool('enabled', false, 'stripe') && $this->stripeSecret() !== '';
    }

    public function paypalEnabled(): bool
    {
        return $this->settings->bool('enabled', false, 'paypal')
            && $this->paypalClientId() !== ''
            && $this->paypalSecret() !== '';
    }

    public function stripeMode(): string
    {
        $mode = $this->settings->string('mode', 'test', 'stripe');

        return $mode === 'live' ? 'live' : 'test';
    }

    public function paypalMode(): string
    {
        $mode = $this->settings->string('mode', 'sandbox', 'paypal');

        return $mode === 'live' ? 'live' : 'sandbox';
    }

    public function stripePublicKey(): string
    {
        $key = $this->settings->string('public_key', '', 'stripe');

        return $key !== '' ? $key : (string) config('services.stripe.key', '');
    }

    public function stripeSecret(): string
    {
        $key = trim($this->settings->string('secret_key', '', 'stripe'));

        return $key !== '' ? $key : trim((string) config('services.stripe.secret', ''));
    }

    public function stripeWebhookSecret(): string
    {
        $key = $this->settings->string('webhook_secret', '', 'stripe');

        return $key !== '' ? $key : (string) config('services.stripe.webhook_secret', '');
    }

    public function paypalClientId(): string
    {
        $key = $this->settings->string('client_id', '', 'paypal');

        return $key !== '' ? $key : (string) config('services.paypal.client_id', '');
    }

    public function paypalSecret(): string
    {
        $key = $this->settings->string('secret', '', 'paypal');

        return $key !== '' ? $key : (string) config('services.paypal.secret', '');
    }

    public function paypalWebhookId(): string
    {
        return $this->settings->string('webhook_id', '', 'paypal');
    }

    public function paypalBaseUrl(): string
    {
        return $this->paypalMode() === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    public function offlineEnabled(string $key): bool
    {
        return $this->settings->bool($key, true, 'payments');
    }

    public function stripeOffered(): bool
    {
        return $this->settings->bool('enabled', false, 'stripe');
    }

    public function paypalOffered(): bool
    {
        return $this->settings->bool('enabled', false, 'paypal');
    }

    /**
     * @return array<string, string>
     */
    public function checkoutMethods(): array
    {
        $methods = [];

        if ($this->stripeEnabled()) {
            $methods['stripe'] = 'Kreditkarte';
        }

        if ($this->paypalEnabled()) {
            $methods['paypal'] = 'PayPal';
        }

        return $methods;
    }

    public function isOnline(string $method): bool
    {
        return in_array($method, ['stripe', 'paypal'], true);
    }

    public function methodLabel(string $method): string
    {
        return $this->checkoutMethods()[$method]
            ?? (self::OFFLINE[$method] ?? strtoupper($method));
    }
}
