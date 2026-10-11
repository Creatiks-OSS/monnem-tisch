<?php

namespace Tests\Feature;

use App\Models\ProductCategory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
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

    public function test_admin_can_create_category(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => 'Fingerfood Test',
            'slug' => 'fingerfood-test',
            'short_description' => 'Kleine Häppchen',
            'description' => 'Beschreibung Fingerfood',
            'starting_price' => 18.50,
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 1,
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('product_categories', [
            'name' => 'Fingerfood Test',
            'slug' => 'fingerfood-test',
        ]);

        $this->assertNotNull(ProductCategory::query()->where('slug', 'fingerfood-test')->first());
    }
}
