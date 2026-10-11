<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
        $this->admin->assignRole('Super Admin');
    }

    public function test_admin_sees_delete_button_on_incoming_orders(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->admin)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('Sil')
            ->assertSee(route('admin.orders.destroy', $order), false);

        $this->actingAs($this->admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Siparişi sil');
    }

    public function test_admin_can_delete_an_incoming_order(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->admin)
            ->delete(route('admin.orders.destroy', $order))
            ->assertRedirect(route('admin.orders.index'));

        $this->assertSoftDeleted('orders', ['id' => $order->id]);
    }

    public function test_kitchen_cannot_delete_orders(): void
    {
        $order = $this->makeOrder();
        $kitchen = User::factory()->create([
            'email' => 'kitchen@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
        $kitchen->assignRole('Kitchen');

        $this->actingAs($kitchen)
            ->delete(route('admin.orders.destroy', $order))
            ->assertForbidden();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'deleted_at' => null]);
    }

    protected function makeOrder(): Order
    {
        return Order::query()->create([
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
        ]);
    }
}
