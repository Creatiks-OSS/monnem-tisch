<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\KitchenService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KitchenPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_kitchen_board_requires_pin(): void
    {
        $this->get(route('kitchen.board'))->assertRedirect(route('kitchen.login'));
    }

    public function test_pin_login_and_board_shows_orders_within_two_days(): void
    {
        app(KitchenService::class)->setPin('4821');

        $soon = $this->makeOrder(['event_date' => now()->addDays(2)->toDateString()]);
        $later = $this->makeOrder(['event_date' => now()->addDays(10)->toDateString()]);

        $this->from(route('kitchen.login'))
            ->post(route('kitchen.login.store'), ['pin' => '4821'])
            ->assertRedirect(route('kitchen.board'));

        $this->get(route('kitchen.board'))
            ->assertOk()
            ->assertSee($soon->order_number)
            ->assertDontSee($later->order_number);
    }

    public function test_admin_can_send_order_to_kitchen_early(): void
    {
        app(KitchenService::class)->setPin('4821');
        $order = $this->makeOrder(['event_date' => now()->addDays(20)->toDateString()]);

        $admin = User::factory()->create(['is_active' => true, 'must_change_password' => false]);
        $admin->assignRole('Super Admin');

        $this->actingAs($admin)
            ->post(route('admin.orders.send-kitchen', $order))
            ->assertRedirect();

        $this->assertNotNull($order->fresh()->sent_to_kitchen_at);

        $this->post(route('kitchen.login.store'), ['pin' => '4821']);
        $this->get(route('kitchen.board'))
            ->assertOk()
            ->assertSee($order->order_number);
    }

    public function test_kitchen_login_while_admin_can_still_send_to_kitchen(): void
    {
        app(KitchenService::class)->setPin('4821');
        $order = $this->makeOrder(['event_date' => now()->addDays(20)->toDateString()]);

        $admin = User::factory()->create(['is_active' => true, 'must_change_password' => false]);
        $admin->assignRole('Super Admin');

        $this->actingAs($admin);
        $this->post(route('kitchen.login.store'), ['pin' => '4821'])
            ->assertRedirect(route('kitchen.board'));

        $this->post(route('admin.orders.send-kitchen', $order))->assertRedirect();
        $this->assertNotNull($order->fresh()->sent_to_kitchen_at);
    }

    public function test_wrong_pin_is_rejected(): void
    {
        app(KitchenService::class)->setPin('4821');

        $this->from(route('kitchen.login'))
            ->post(route('kitchen.login.store'), ['pin' => '0000'])
            ->assertRedirect(route('kitchen.login'))
            ->assertSessionHasErrors('pin');
    }

    public function test_completing_an_order_removes_it_from_the_board(): void
    {
        app(KitchenService::class)->setPin('4821');
        $order = $this->makeOrder(['event_date' => now()->toDateString()]);

        $this->post(route('kitchen.login.store'), ['pin' => '4821']);

        $this->get(route('kitchen.board'))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Sipariş tamamlandı');

        $this->get(route('kitchen.ping'))
            ->assertOk()
            ->assertJsonFragment(['unacked' => [$order->id]]);

        $this->post(route('kitchen.complete', $order))->assertRedirect(route('kitchen.board'));

        $fresh = $order->fresh();
        $this->assertNotNull($fresh->kitchen_done_at);
        $this->assertSame('abgeschlossen', $fresh->status);

        $this->get(route('kitchen.board'))
            ->assertOk()
            ->assertDontSee($order->order_number);

        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('Super Admin');
        $this->actingAs($admin)
            ->get(route('admin.orders.index', ['status' => 'abgeschlossen']))
            ->assertOk()
            ->assertSee($order->order_number);
    }

    public function test_kitchen_views_show_product_images(): void
    {
        app(KitchenService::class)->setPin('4821');
        $order = $this->makeOrder(['event_date' => now()->toDateString()]);
        $order->items()->create([
            'name' => 'Buffet Classic',
            'quantity' => 1,
            'unit_price' => 100,
            'persons' => 20,
            'line_total' => 100,
            'options' => [
                'package_image' => 'https://cdn.example.com/package.jpg',
                'selections' => [[
                    'slot' => 'Hauptgang',
                    'products' => [[
                        'id' => 12,
                        'name' => 'Hähnchen',
                        'image' => 'https://cdn.example.com/chicken.jpg',
                    ]],
                ]],
            ],
        ]);

        $this->post(route('kitchen.login.store'), ['pin' => '4821']);

        $this->get(route('kitchen.board'))
            ->assertOk()
            ->assertSee('https://cdn.example.com/chicken.jpg', false)
            ->assertSee(route('kitchen.show', $order, false), false);

        $this->get(route('kitchen.show', $order))
            ->assertOk()
            ->assertSee('https://cdn.example.com/chicken.jpg', false)
            ->assertSee('https://cdn.example.com/package.jpg', false)
            ->assertSee('Hähnchen');
    }

    public function test_accepting_an_order_stops_alert_but_keeps_it_on_the_board(): void
    {
        app(KitchenService::class)->setPin('4821');
        $order = $this->makeOrder(['event_date' => now()->toDateString()]);

        $this->post(route('kitchen.login.store'), ['pin' => '4821']);

        $this->get(route('kitchen.ping'))
            ->assertOk()
            ->assertJsonPath('incoming.0.number', $order->order_number)
            ->assertJsonPath('unacked', [$order->id]);

        $this->postJson(route('kitchen.accept', $order))
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('unacked', [])
            ->assertJsonPath('incoming', []);

        $fresh = $order->fresh();
        $this->assertNotNull($fresh->kitchen_seen_at);
        $this->assertNull($fresh->kitchen_done_at);

        $this->get(route('kitchen.board'))
            ->assertOk()
            ->assertSee($order->order_number);
    }

    protected function makeOrder(array $overrides = []): Order
    {
        return Order::query()->create(array_merge([
            'status' => 'neu',
            'payment_method' => 'anfrage',
            'payment_status' => 'pending',
            'persons' => 20,
            'subtotal' => 100,
            'tax_amount' => 7,
            'total' => 107,
            'currency' => 'EUR',
            'billing_first_name' => 'Test',
            'billing_last_name' => 'Kunde',
            'billing_email' => 'test@example.com',
        ], $overrides));
    }
}
