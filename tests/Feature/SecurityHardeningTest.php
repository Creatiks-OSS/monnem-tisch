<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_responses_include_security_headers(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertStringContainsString("'unsafe-eval'", (string) $response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString("object-src 'none'", (string) $response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString("frame-ancestors 'self'", (string) $response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString('https://checkout.stripe.com', (string) $response->headers->get('Content-Security-Policy'));
    }

    public function test_webhook_get_is_not_allowed(): void
    {
        $this->get('/webhooks/stripe')->assertStatus(405);
        $this->get('/webhooks/paypal')->assertStatus(405);
    }

    public function test_unsigned_stripe_webhook_is_rejected(): void
    {
        $this->postJson('/webhooks/stripe', [
            'type' => 'checkout.session.completed',
            'data' => ['object' => ['client_reference_id' => '1']],
        ])->assertStatus(400);
    }

    public function test_signed_stripe_webhook_is_accepted(): void
    {
        $secret = 'whsec_test_secret';
        \App\Models\Setting::set('webhook_secret', $secret, 'stripe');

        $payload = json_encode([
            'type' => 'payment_intent.succeeded',
            'data' => ['object' => ['id' => 'pi_test']],
        ], JSON_THROW_ON_ERROR);
        $timestamp = (string) time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        $response = $this->call(
            'POST',
            '/webhooks/stripe',
            [],
            [],
            [],
            [
                'HTTP_STRIPE_SIGNATURE' => 't='.$timestamp.',v1='.$signature,
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        );

        $response->assertOk();
        $response->assertSee('ok');
    }

    public function test_unsigned_paypal_webhook_is_rejected(): void
    {
        $this->postJson('/webhooks/paypal', [
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => ['custom_id' => '1'],
        ])->assertStatus(400);
    }

    public function test_paypal_webhook_requires_verified_signature(): void
    {
        \App\Models\Setting::set('webhook_id', 'WH-TEST', 'paypal');
        \App\Models\Setting::set('client_id', 'paypal_client', 'paypal');
        \App\Models\Setting::set('secret', 'paypal_secret', 'paypal');

        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'token'], 200),
            '*/v1/notifications/verify-webhook-signature' => Http::response([
                'verification_status' => 'FAILURE',
            ], 200),
        ]);

        $this->withHeaders([
            'PAYPAL-AUTH-ALGO' => 'SHA256withRSA',
            'PAYPAL-CERT-URL' => 'https://api.paypal.com/v1/notifications/certs/CERT-test',
            'PAYPAL-TRANSMISSION-ID' => 'id-1',
            'PAYPAL-TRANSMISSION-SIG' => 'sig',
            'PAYPAL-TRANSMISSION-TIME' => now()->toIso8601String(),
        ])->postJson('/webhooks/paypal', [
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => ['custom_id' => '1'],
        ])->assertStatus(400);
    }

    public function test_admin_login_is_rate_limited(): void
    {
        $this->seed(RolePermissionSeeder::class);

        User::factory()->create([
            'email' => 'lock@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->from(route('admin.login'))->post(route('admin.login'), [
                'email' => 'lock@example.com',
                'password' => 'wrong-password',
            ])->assertRedirect(route('admin.login'));
        }

        $this->from(route('admin.login'))->post(route('admin.login'), [
            'email' => 'lock@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }
}
