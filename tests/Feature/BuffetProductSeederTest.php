<?php

namespace Tests\Feature;

use App\Models\CateringPackage;
use App\Models\Product;
use App\Services\PackageBuilderService;
use Database\Seeders\CategorySeeder;
use Database\Seeders\PackageSeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuffetProductSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_buffet_dishes_are_seeded_into_selection_groups(): void
    {
        $this->seed(CategorySeeder::class);
        $this->seed(ProductSeeder::class);
        $this->seed(PackageSeeder::class);

        $this->assertSame(204, Product::query()->count());
        $this->assertSame(37, Product::query()->whereHas('category', fn ($q) => $q->where('slug', 'vorspeisen'))->count());
        $this->assertSame(32, Product::query()->whereHas('category', fn ($q) => $q->where('slug', 'hauptgerichte'))->count());
        $this->assertSame(13, Product::query()->whereHas('category', fn ($q) => $q->where('slug', 'beilagen'))->count());
        $this->assertSame(16, Product::query()->whereHas('category', fn ($q) => $q->where('slug', 'desserts'))->count());
        $this->assertSame(41, Product::query()->whereHas('category', fn ($q) => $q->where('slug', 'fingerfood'))->count());
        $this->assertSame(31, Product::query()->whereHas('category', fn ($q) => $q->where('slug', 'kalte-fruehstueck'))->count());
        $this->assertSame(12, Product::query()->whereHas('category', fn ($q) => $q->where('slug', 'warme-speisen'))->count());
        $this->assertSame(16, Product::query()->whereHas('category', fn ($q) => $q->where('slug', 'grillgut-fleisch'))->count());
        $this->assertSame(6, Product::query()->whereHas('category', fn ($q) => $q->where('slug', 'grillgut-beilagen'))->count());
        $this->assertSame(0, Product::query()->whereHas('category', fn ($q) => $q->where('slug', 'haeppchen'))->count());

        $this->assertDatabaseHas('products', ['name' => "Baba's Knusperplatte"]);
        $this->assertDatabaseHas('products', ['name' => 'Veganes Chili sin Carne']);
        $this->assertDatabaseHas('products', ['slug' => 'kaeseplatte']);
        $this->assertDatabaseHas('products', ['slug' => 'kaeseplatte-dessert', 'name' => 'Käseplatte']);
        $this->assertDatabaseHas('products', ['name' => 'Hähnchen Wrap', 'slug' => 'hahnchen-wrap-fingerfood']);
        $this->assertDatabaseHas('products', ['name' => 'Obstplatte', 'slug' => 'obstplatte-fingerfood']);

        $classic = CateringPackage::query()->where('slug', 'fingerfood-classic')->with('slots')->first();
        $this->assertNotNull($classic);
        $this->assertCount(1, $classic->slots);
        $this->assertSame('Fingerfood', $classic->slots->first()->name);
        $this->assertSame(4, $classic->slots->first()->required_count);

        $breakfast = CateringPackage::query()->where('slug', 'fruhstuck-classic')->with('slots.category')->first()
            ?? CateringPackage::query()->where('name', 'Frühstück Classic')->with('slots.category')->first();
        $this->assertNotNull($breakfast);
        $this->assertCount(2, $breakfast->slots);
        $this->assertEqualsCanonicalizing(
            ['kalte-fruehstueck', 'warme-speisen'],
            $breakfast->slots->pluck('category.slug')->all()
        );

        $grill = CateringPackage::query()->where('slug', 'grill-deluxe')->with('slots.category')->first();
        $this->assertNotNull($grill);
        $this->assertEqualsCanonicalizing(
            ['vorspeisen', 'grillgut-fleisch', 'grillgut-beilagen', 'desserts'],
            $grill->slots->pluck('category.slug')->all()
        );
        $this->assertDatabaseHas('products', ['name' => 'Hähnchensteak Honig-Senf']);
        $this->assertDatabaseHas('products', ['name' => 'Gegrillte Ananas']);

        $this->assertSame(0, Product::query()->whereNull('calories')->count());
        $this->assertSame(150, Product::query()->where('name', 'Datteln im Speckmantel')->value('calories'));
        $this->assertCount(1, Product::query()->where('name', 'Obstplatte')->pluck('calories')->unique());

        $buffet = CateringPackage::query()->where('name', 'Buffet Mega M')->first();
        $this->assertNotNull($buffet);
        $datteln = collect(app(PackageBuilderService::class)->slotsPayload($buffet)->first()['products'])
            ->firstWhere('name', 'Datteln im Speckmantel');
        $this->assertSame(150, $datteln['calories']);

        $veg = CateringPackage::query()->where('slug', 'vegetarisch-deluxe')->with('slots')->first();
        $this->assertNotNull($veg);
        $this->assertSame('vegetarisch', $veg->menu_group);
        $this->assertCount(4, $veg->slots);

        $vegan = CateringPackage::query()->where('slug', 'vegan-deluxe')->first();
        $this->assertNotNull($vegan);
        $this->assertSame('vegan', $vegan->menu_group);
        foreach (app(PackageBuilderService::class)->slotsPayload($vegan) as $slot) {
            $this->assertNotEmpty($slot['products'], $slot['name']);
            foreach ($slot['products'] as $product) {
                $this->assertTrue($product['is_vegan'], $product['name'].' should be vegan');
            }
        }

        $vegFinger = CateringPackage::query()->where('slug', 'vegetarisch-fingerfood')->first();
        $this->assertNotNull($vegFinger);
        foreach (app(PackageBuilderService::class)->slotsPayload($vegFinger) as $slot) {
            $this->assertGreaterThanOrEqual((int) $slot['required'], count($slot['products']));
            foreach ($slot['products'] as $product) {
                $this->assertTrue($product['is_vegetarian'] || $product['is_vegan'], $product['name']);
            }
        }
    }
}
