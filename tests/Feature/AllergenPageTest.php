<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\AllergenCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllergenPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_allergene_page_lists_official_codes(): void
    {
        $html = $this->get(route('legal.allergene'))->assertOk()->getContent();

        $this->assertStringContainsString('Glutenhaltiges Getreide (Weizen)', $html);
        $this->assertStringContainsString('Krebstiere und Krebstiererzeugnisse', $html);
        $this->assertStringContainsString('Milch und Milcherzeugnisse', $html);
        $this->assertStringContainsString('>a1<', $html);
    }

    public function test_footer_links_to_allergene_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(route('legal.allergene'), false)
            ->assertSee('Allergene', false);
    }

    public function test_dish_selection_shows_allergen_codes(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Vorspeisen',
            'slug' => 'vorspeisen-allergene',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Mini Cheeseburger',
            'slug' => 'mini-cheeseburger-allergene',
            'price' => 0,
            'is_active' => true,
        ]);

        $codes = $product->allergenCodes();

        $this->assertContains('a1', $codes);
        $this->assertContains('g', $codes);
        $this->assertSame('Glutenhaltiges Getreide (Weizen)', app(AllergenCatalog::class)->label('a1'));
    }
}
