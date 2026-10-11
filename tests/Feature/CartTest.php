<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_add_product_to_cart_and_view_cart(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Buffets',
            'slug' => 'buffets',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Klassisches Buffet',
            'slug' => 'klassisches-buffet',
            'price' => 25.00,
            'price_type' => 'person',
            'is_active' => true,
            'minimum_quantity' => 1,
            'minimum_persons' => 1,
        ]);

        $add = $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'persons' => 20,
        ]);

        $add->assertOk()
            ->assertJsonPath('success', true);

        $this->assertNotEmpty($add->json('cart.items'));

        $this->get(route('cart.index'))
            ->assertRedirect(route('checkout.index'));
    }
}
