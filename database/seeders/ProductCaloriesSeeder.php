<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Services\ProductCalorieCatalog;
use Illuminate\Database\Seeder;

class ProductCaloriesSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = app(ProductCalorieCatalog::class);
        $updated = 0;

        Product::query()->with('category')->withTrashed()->each(function (Product $product) use ($catalog, &$updated) {
            $calories = $catalog->forName($product->name, $product->category?->slug);
            if ((int) $product->calories === $calories) {
                return;
            }

            $product->calories = $calories;
            $product->saveQuietly();
            $updated++;
        });

        $this->command?->info('Kalorien gesetzt: '.$updated.' Produkte aktualisiert.');
    }
}
