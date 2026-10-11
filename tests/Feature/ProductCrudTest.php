<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCrudTest extends TestCase
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

    public function test_admin_can_create_product(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Buffets',
            'slug' => 'buffets',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Test Buffet Deluxe',
            'slug' => 'test-buffet-deluxe',
            'short_description' => 'Ein Testprodukt',
            'description' => 'Beschreibung des Testprodukts',
            'price' => 29.90,
            'price_type' => 'person',
            'minimum_quantity' => 1,
            'is_active' => true,
            'is_featured' => false,
            'is_bestseller' => false,
            'is_vegan' => false,
            'is_vegetarian' => false,
            'calories' => 220,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'name' => 'Test Buffet Deluxe',
            'slug' => 'test-buffet-deluxe',
            'category_id' => $category->id,
            'calories' => 220,
        ]);

        $product = Product::query()->where('slug', 'test-buffet-deluxe')->first();
        $this->assertNotNull($product);
        $response->assertRedirect(route('admin.products.edit', $product));
    }
}
