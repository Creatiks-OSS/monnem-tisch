<?php

namespace Tests\Feature;

use App\Models\BlockedDate;
use App\Models\CateringPackage;
use App\Models\Order;
use App\Models\Setting;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_store_creates_order(): void
    {
        Setting::set('enabled', true, 'stripe', 'boolean');
        Setting::set('secret_key', 'sk_test_123', 'stripe');

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_test_123',
                'url' => 'https://checkout.stripe.com/c/pay/cs_test_123',
            ], 200),
        ]);

        $package = CateringPackage::query()->create([
            'name' => 'Business Buffet',
            'slug' => 'business-buffet-pay',
            'price_per_person' => 18.90,
            'min_persons' => 1,
            'is_active' => true,
            'menu_group' => 'buffets',
        ]);

        app(CartService::class)->addPackage($package, 15, []);

        $eventDate = now()->addDays(10)->toDateString();

        $response = $this->post(route('checkout.store'), [
            'first_name' => 'Max',
            'last_name' => 'Mustermann',
            'email' => 'max@example.com',
            'phone' => '+49 511 111111',
            'company' => 'Muster GmbH',
            'event_date' => $eventDate,
            'event_time' => '12:00',
            'persons' => 15,
            'billing_address' => 'Hauptstraße 1, 30159 Hannover',
            'billing_street' => 'Hauptstraße',
            'billing_house_number' => '1',
            'billing_postal_code' => '30159',
            'billing_city' => 'Hannover',
            'billing_country' => 'DE',
            'shipping_same_as_billing' => true,
            'payment_method' => 'stripe',
            'gdpr_consent' => '1',
            'notes' => 'Bitte pünktlich liefern',
        ]);

        $order = Order::query()->latest('id')->first();
        $this->assertNotNull($order);
        $response->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_123');

        $this->assertDatabaseHas('orders', [
            'billing_email' => 'max@example.com',
            'persons' => 15,
            'status' => 'neu',
            'payment_method' => 'stripe',
        ]);
        $this->assertDatabaseCount('order_items', 2);
    }

    public function test_checkout_rejects_blocked_event_date(): void
    {
        $package = CateringPackage::query()->create([
            'name' => 'Business Buffet',
            'slug' => 'business-buffet',
            'price_per_person' => 18.90,
            'min_persons' => 1,
            'is_active' => true,
            'menu_group' => 'buffets',
        ]);

        app(CartService::class)->addPackage($package, 15, []);

        $eventDate = now()->addDays(10)->toDateString();

        BlockedDate::query()->create([
            'date' => $eventDate,
            'type' => 'blocked',
            'note' => 'Ausgebucht',
        ]);

        $this->from(route('checkout.index'))
            ->post(route('checkout.store'), [
                'first_name' => 'Max',
                'last_name' => 'Mustermann',
                'email' => 'max@example.com',
                'phone' => '+49 511 111111',
                'event_date' => $eventDate,
                'event_time' => '12:00',
                'persons' => 15,
                'billing_address' => 'Hauptstraße 1, 30159 Hannover',
                'billing_street' => 'Hauptstraße',
                'billing_house_number' => '1',
                'billing_postal_code' => '30159',
                'billing_city' => 'Hannover',
                'billing_country' => 'DE',
                'shipping_same_as_billing' => true,
                'payment_method' => 'stripe',
                'gdpr_consent' => '1',
            ])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors('event_date');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_hides_payment_methods_disabled_in_admin(): void
    {
        Setting::set('enabled', true, 'stripe', 'boolean');
        Setting::set('secret_key', 'sk_test_123', 'stripe');
        Setting::set('enabled', false, 'paypal', 'boolean');
        Setting::set('secret', 'paypal_secret', 'paypal');
        Setting::set('client_id', 'paypal_client', 'paypal');

        $package = CateringPackage::query()->create([
            'name' => 'Business Buffet',
            'slug' => 'business-buffet-hide-pay',
            'price_per_person' => 18.90,
            'min_persons' => 1,
            'is_active' => true,
            'menu_group' => 'buffets',
        ]);

        app(CartService::class)->addPackage($package, 15, []);

        $this->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('Kreditkarte')
            ->assertDontSee('PayPal-Konto oder Karte über PayPal', false);

        $this->from(route('checkout.index'))
            ->post(route('checkout.store'), [
                'first_name' => 'Max',
                'last_name' => 'Mustermann',
                'email' => 'max@example.com',
                'phone' => '+49 511 111111',
                'event_date' => now()->addDays(10)->toDateString(),
                'event_time' => '12:00',
                'persons' => 15,
                'billing_address' => 'Hauptstraße 1, 30159 Hannover',
                'billing_street' => 'Hauptstraße',
                'billing_house_number' => '1',
                'billing_postal_code' => '30159',
                'billing_city' => 'Hannover',
                'billing_country' => 'DE',
                'shipping_same_as_billing' => true,
                'payment_method' => 'paypal',
                'gdpr_consent' => '1',
            ])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors('payment_method');
    }

    public function test_checkout_hides_stripe_without_secret_key(): void
    {
        Setting::set('enabled', true, 'stripe', 'boolean');
        Setting::set('secret_key', '', 'stripe');

        $package = CateringPackage::query()->create([
            'name' => 'Business Buffet',
            'slug' => 'business-buffet-no-stripe-key',
            'price_per_person' => 18.90,
            'min_persons' => 1,
            'is_active' => true,
            'menu_group' => 'buffets',
        ]);

        app(CartService::class)->addPackage($package, 15, []);

        $this->get(route('checkout.index'))
            ->assertOk()
            ->assertDontSee('Kreditkarte', false)
            ->assertSee('keine Online-Zahlung', false);
    }

    public function test_checkout_stripe_api_error_keeps_cart_and_stays_on_checkout(): void
    {
        Setting::set('enabled', true, 'stripe', 'boolean');
        Setting::set('secret_key', 'sk_test_123', 'stripe');

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response([
                'error' => ['message' => 'Invalid API Key provided'],
            ], 401),
        ]);

        $package = CateringPackage::query()->create([
            'name' => 'Business Buffet',
            'slug' => 'business-buffet-stripe-fail',
            'price_per_person' => 18.90,
            'min_persons' => 1,
            'is_active' => true,
            'menu_group' => 'buffets',
        ]);

        app(CartService::class)->addPackage($package, 15, []);

        $this->from(route('checkout.index'))
            ->post(route('checkout.store'), [
                'first_name' => 'Max',
                'last_name' => 'Mustermann',
                'email' => 'max@example.com',
                'phone' => '+49 511 111111',
                'event_date' => now()->addDays(10)->toDateString(),
                'event_time' => '12:00',
                'persons' => 15,
                'billing_address' => 'Hauptstraße 1, 30159 Hannover',
                'billing_street' => 'Hauptstraße',
                'billing_house_number' => '1',
                'billing_postal_code' => '30159',
                'billing_city' => 'Hannover',
                'billing_country' => 'DE',
                'shipping_same_as_billing' => true,
                'payment_method' => 'stripe',
                'gdpr_consent' => '1',
            ])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHas('error');

        $this->assertNotEmpty(session('cart')['items'] ?? []);
        $this->assertDatabaseHas('orders', [
            'billing_email' => 'max@example.com',
            'payment_method' => 'stripe',
            'payment_status' => 'pending',
        ]);
    }
}
