<?php

namespace Tests\Feature;

use App\Models\CateringPackage;
use App\Models\Faq;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchemaMarkupTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_includes_recommended_schema_types(): void
    {
        Faq::query()->create([
            'question' => 'Liefern Sie in Mannheim?',
            'answer' => 'Ja, innerhalb Mannheims ist die Lieferung kostenlos.',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $html = $this->get('/')->assertOk()->getContent();

        foreach ([
            '"Organization"',
            '"WebSite"',
            '"WebPage"',
            '"FoodEstablishment"',
            '"Person"',
            '"ItemList"',
            '"HowTo"',
            '"FAQPage"',
            '"SpeakableSpecification"',
            '"SearchAction"',
            '"Product"',
            '"Offer"',
            '"headline"',
        ] as $type) {
            $this->assertStringContainsString($type, $html, 'Missing schema '.$type);
        }

        $this->assertSame(1, substr_count($html, 'application/ld+json'));
        $this->assertStringNotContainsString('"BreadcrumbList"', $html);
        $this->assertStringNotContainsString('aria-label="Breadcrumb"', $html);
        $this->assertStringNotContainsString('"LocalBusiness"', $html);
        $this->assertDoesNotMatchRegularExpression('/"@type"\s*:\s*\[\s*"FoodEstablishment"/', $html);
        $this->assertMatchesRegularExpression('/"@type"\s*:\s*"WebPage"/', $html);
        $this->assertMatchesRegularExpression('/"headline"\s*:\s*"Catering Mannheim/', $html);
        $this->assertStringContainsString('name="author"', $html);
        $this->assertStringContainsString('article:published_time', $html);
        $this->assertStringContainsString('article:modified_time', $html);
        $this->assertStringContainsString('"datePublished"', $html);
        $this->assertStringContainsString('"dateModified"', $html);
        $this->assertStringContainsString('"sameAs"', $html);
        $this->assertStringContainsString('"publisher"', $html);
        $this->assertStringContainsString('class="author', $html);
        $this->assertStringContainsString('rel="author"', $html);
        $this->assertStringContainsString('Anatolian Gourmet GmbH', $html);
    }

    public function test_package_pages_include_item_and_product_schema(): void
    {
        $package = CateringPackage::query()->create([
            'name' => 'Buffet Classic',
            'slug' => 'buffet-classic-schema',
            'price_per_person' => 29.90,
            'min_persons' => 10,
            'is_active' => true,
            'menu_group' => 'buffets',
        ]);

        $buffets = $this->get(route('packages.index', 'buffets'))
            ->assertOk()
            ->assertSee('"ItemList"', false)
            ->assertSee('aria-label="Breadcrumb"', false)
            ->assertSee('"BreadcrumbList"', false);
        $this->assertStringNotContainsString('"HowTo"', $buffets->getContent());
        $this->assertStringNotContainsString('"FoodEstablishment"', $buffets->getContent());

        $this->get($package->publicUrl())
            ->assertOk()
            ->assertSee('"Product"', false)
            ->assertSee('Buffet Classic', false)
            ->assertSee('"BreadcrumbList"', false);
    }

    public function test_home_includes_recipe_schema_for_dishes_with_ingredients(): void
    {
        $category = \App\Models\ProductCategory::query()->create([
            'name' => 'Vorspeisen',
            'slug' => 'vorspeisen-schema',
            'is_active' => true,
        ]);

        Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Falafel Teller',
            'slug' => 'falafel-teller-schema',
            'short_description' => 'Kichererbsen, Kräuter, Joghurt.',
            'description' => 'Falafel frisch frittieren. Mit Kräutern anrichten.',
            'ingredients' => "Kichererbsen\nPetersilie\nKnoblauch",
            'calories' => 420,
            'price' => 8.50,
            'is_active' => true,
        ]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('"Recipe"', $html);
        $this->assertStringContainsString('"NutritionInformation"', $html);
        $this->assertStringContainsString('Kichererbsen', $html);
        $this->assertStringContainsString('"HowToStep"', $html);
    }
}
