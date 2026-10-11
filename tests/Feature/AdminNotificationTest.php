<?php

namespace Tests\Feature;

use App\Models\AdminNotification;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_delete_own_notification(): void
    {
        $admin = $this->adminUser();
        $notification = AdminNotification::query()->create([
            'user_id' => $admin->id,
            'type' => 'order.created',
            'title' => 'Neue Bestellung',
            'body' => 'Bestellung eingegangen.',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.notifications.index'))
            ->delete(route('admin.notifications.destroy', $notification))
            ->assertRedirect(route('admin.notifications.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('admin_notifications', ['id' => $notification->id]);
    }

    public function test_admin_cannot_delete_another_users_notification(): void
    {
        $admin = $this->adminUser();
        $other = User::factory()->create(['is_active' => true]);
        $notification = AdminNotification::query()->create([
            'user_id' => $other->id,
            'type' => 'order.created',
            'title' => 'Neue Bestellung',
            'body' => 'Bestellung eingegangen.',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.notifications.destroy', $notification))
            ->assertForbidden();

        $this->assertDatabaseHas('admin_notifications', ['id' => $notification->id]);
    }

    public function test_admin_can_delete_all_own_notifications(): void
    {
        $admin = $this->adminUser();
        $other = User::factory()->create(['is_active' => true]);

        AdminNotification::query()->create([
            'user_id' => $admin->id,
            'type' => 'quote.created',
            'title' => 'Neue Anfrage',
            'body' => 'Anfrage eingegangen.',
        ]);
        AdminNotification::query()->create([
            'user_id' => $other->id,
            'type' => 'quote.created',
            'title' => 'Andere Anfrage',
            'body' => 'Bleibt stehen.',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.notifications.index'))
            ->delete(route('admin.notifications.destroy-all'))
            ->assertRedirect(route('admin.notifications.index'));

        $this->assertDatabaseMissing('admin_notifications', ['user_id' => $admin->id]);
        $this->assertDatabaseHas('admin_notifications', ['user_id' => $other->id]);
    }

    protected function adminUser(): User
    {
        $user = User::factory()->create([
            'is_active' => true,
            'must_change_password' => false,
        ]);
        $user->assignRole('Super Admin');

        return $user;
    }
}
