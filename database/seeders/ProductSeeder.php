<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\AllergenCatalog;
use App\Services\ProductCalorieCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ProductCategory::query()->pluck('id', 'slug');
        $calories = app(ProductCalorieCatalog::class);
        $allergens = app(AllergenCatalog::class);

        $groups = [
            'vorspeisen' => [
                ['name' => 'Antipasti Platte', 'veg' => true],
                ['name' => 'Auberginen-Carpaccio', 'veg' => true, 'vegan' => true],
                ['name' => "Baba's Knusperplatte"],
                ['name' => 'Brotkorb & Butter', 'veg' => true],
                ['name' => 'Bruschetta', 'veg' => true, 'vegan' => true],
                ['name' => 'Caesar Salad'],
                ['name' => 'Chicken Nuggets Platte'],
                ['name' => 'Crostini mit Ziegenkäse', 'veg' => true],
                ['name' => 'Datteln im Speckmantel'],
                ['name' => 'Fiesta Mexicana Platte'],
                ['name' => 'Frühlingsrollen Thai Style', 'veg' => true],
                ['name' => 'Garnelen Spieße'],
                ['name' => 'Gartensalat', 'veg' => true, 'vegan' => true],
                ['name' => 'Gemüsesticks Platte', 'veg' => true, 'vegan' => true],
                ['name' => 'Griechischer Salat', 'veg' => true],
                ['name' => 'Große Wurstplatte'],
                ['name' => 'Hackfleischbällchen'],
                ['name' => 'Halbe gefüllte Eier', 'veg' => true],
                ['name' => 'Kartoffelsalat', 'veg' => true],
                ['name' => 'Käseplatte', 'veg' => true, 'slug' => 'kaeseplatte'],
                ['name' => 'Lachs Canapé'],
                ['name' => 'Lachsplatte'],
                ['name' => 'Melonen-Feta-Salat', 'veg' => true],
                ['name' => 'Melonenschiffchen', 'veg' => true],
                ['name' => 'Mini Cheeseburger'],
                ['name' => 'Mini Crispy Chicken Burger'],
                ['name' => 'Mini Pizza-Mix'],
                ['name' => 'Mini Quiche Mix-Platte', 'veg' => true],
                ['name' => 'Mini Veggi Burger', 'veg' => true],
                ['name' => 'Mini-Döner'],
                ['name' => 'Mini-Schnitzel Platte'],
                ['name' => 'Nudelsalat Mediterran', 'veg' => true],
                ['name' => 'Oriental Mezze Platte', 'veg' => true],
                ['name' => 'Orientalische Hähnchenspieße'],
                ['name' => 'Samosas Indische Art', 'veg' => true],
                ['name' => 'Tomate Mozzarella', 'veg' => true],
                ['name' => 'Vitello Tonnato'],
            ],
            'hauptgerichte' => [
                ['name' => 'Backfisch & Remoulade'],
                ['name' => 'Cheeseburger Station'],
                ['name' => 'Chicken Tikka Masala'],
                ['name' => 'Currywurst'],
                ['name' => 'Fettucine Salmone'],
                ['name' => 'Gefüllte Paprika', 'veg' => true],
                ['name' => 'Gnocchi Spinaci', 'veg' => true],
                ['name' => 'Hähnchen Cordon Bleu'],
                ['name' => 'Hähnchen-Jägerschnitzel'],
                ['name' => 'Hähnchenbrust Caprese'],
                ['name' => 'Hähnchenbrustfilet'],
                ['name' => 'Hähnchengeschnetzeltes'],
                ['name' => 'Hühnerfrikassee'],
                ['name' => 'Karotte Kartoffel Ingwer Suppe', 'veg' => true, 'vegan' => true],
                ['name' => 'Kartoffelgratin', 'veg' => true],
                ['name' => 'Lachs-Brokkoli Auflauf'],
                ['name' => 'Lachsfilet'],
                ['name' => 'Lasagne Bolognese'],
                ['name' => 'Linguini mit Spargel', 'veg' => true],
                ['name' => 'Paella Pollo'],
                ['name' => 'Pangasiusfilet'],
                ['name' => 'Pasta Station Duo'],
                ['name' => 'Putensteak Mediterran'],
                ['name' => 'Ratatouille de Provence', 'veg' => true, 'vegan' => true],
                ['name' => 'Rinder Hüftsteaks'],
                ['name' => 'Rinderbraten'],
                ['name' => 'Rindergulasch'],
                ['name' => 'Schnitzel'],
                ['name' => 'Spargel & Sauce Hollandaise', 'veg' => true],
                ['name' => 'Veganes Chili sin Carne', 'veg' => true, 'vegan' => true],
                ['name' => 'Vegetarische Lasagne', 'veg' => true],
                ['name' => 'Wiener Würstchen'],
            ],
            'beilagen' => [
                ['name' => 'Baguettescheiben', 'veg' => true, 'vegan' => true],
                ['name' => 'Basmati Reis', 'veg' => true, 'vegan' => true],
                ['name' => 'Bratkartoffeln', 'veg' => true, 'vegan' => true],
                ['name' => 'Gemüsepfanne', 'veg' => true, 'vegan' => true],
                ['name' => 'Kaisergemüse', 'veg' => true, 'vegan' => true],
                ['name' => 'Kartoffelecken', 'veg' => true, 'vegan' => true],
                ['name' => 'Kartoffelklöße', 'veg' => true],
                ['name' => 'Penne Nudeln', 'veg' => true, 'vegan' => true],
                ['name' => 'Rosmarinkartoffeln', 'veg' => true, 'vegan' => true],
                ['name' => 'Rotkraut', 'veg' => true, 'vegan' => true],
                ['name' => 'Sauerkraut', 'veg' => true, 'vegan' => true],
                ['name' => 'Spätzle', 'veg' => true],
                ['name' => 'Tagliatelle', 'veg' => true],
            ],
            'desserts' => [
                ['name' => 'Mousse au chocolat', 'veg' => true],
                ['name' => 'Dubai-Schoko-Tiramisu', 'veg' => true],
                ['name' => 'Tiramisu', 'veg' => true],
                ['name' => 'Erdbeer-Tiramisu', 'veg' => true],
                ['name' => 'Himbeer-Mascarponecreme', 'veg' => true],
                ['name' => 'Panna Cotta', 'veg' => true],
                ['name' => 'Erdbeer-Minz Quark', 'veg' => true],
                ['name' => 'Zitronencreme-Panna-Cotta', 'veg' => true],
                ['name' => 'Rote Grütze mit Vanillecreme', 'veg' => true],
                ['name' => 'Oreo Creme Dessert', 'veg' => true],
                ['name' => 'Obstplatte', 'veg' => true, 'vegan' => true],
                ['name' => 'Käseplatte', 'veg' => true, 'slug' => 'kaeseplatte-dessert'],
                ['name' => 'Kaiserschmarrn', 'veg' => true],
                ['name' => 'Apfelstrudel', 'veg' => true],
                ['name' => 'Käsekuchen', 'veg' => true],
                ['name' => 'Obstkuchen', 'veg' => true],
            ],
            'fingerfood' => [
                ['name' => 'Mini Cheese Burger'],
                ['name' => 'Mini Crispy Chicken Burger'],
                ['name' => 'Mini Veggi Burger', 'veg' => true],
                ['name' => 'Hähnchen Wrap'],
                ['name' => 'Lachs-Ei Wrap'],
                ['name' => 'Tomaten Wrap', 'veg' => true],
                ['name' => 'Mini-Döner'],
                ['name' => 'Mini Pizza-Mix'],
                ['name' => 'Mini-Schnitzel Platte'],
                ['name' => 'Hackfleischbällchen'],
                ['name' => 'Bruschetta', 'veg' => true, 'vegan' => true],
                ['name' => 'Crostini mit Ziegenkäse', 'veg' => true],
                ['name' => 'Hähnchen Canape'],
                ['name' => 'Lachs Canapé'],
                ['name' => 'Pastrami Canape'],
                ['name' => 'Ciabatta Pastrami-Käse'],
                ['name' => 'Ciabatta Tomate-Mozzarella', 'veg' => true],
                ['name' => 'Käsebrötchen', 'veg' => true],
                ['name' => 'Tomate-Mozzarella Brötchen', 'veg' => true],
                ['name' => 'Lachs Bagel'],
                ['name' => 'Käse-Schinken Bagel'],
                ['name' => 'Avocado Bagel', 'veg' => true],
                ['name' => 'Clubsandwich'],
                ['name' => 'Mini-Sandwich-Mix'],
                ['name' => 'Mini Quiche Mix-Platte', 'veg' => true],
                ['name' => 'Samosas Indische Art', 'veg' => true],
                ['name' => 'Frühlingsrollen Thai Style', 'veg' => true],
                ['name' => 'Gemüsesticks Platte', 'veg' => true, 'vegan' => true],
                ['name' => 'Melone-Feta-Salat im Glas', 'veg' => true],
                ['name' => 'Kartoffelsalat im Glas', 'veg' => true],
                ['name' => 'Nudelsalat im Glas', 'veg' => true],
                ['name' => 'Caesar Salad im Glas'],
                ['name' => 'Tomate-Mozzarella Spieß', 'veg' => true],
                ['name' => 'Käsespieße', 'veg' => true],
                ['name' => 'Hähnchen-Ananas Spieß'],
                ['name' => 'Orientalische Hähnchenspieße'],
                ['name' => 'Garnelen Spieß'],
                ['name' => 'Vegetarische Grillspieße', 'veg' => true],
                ['name' => 'Dattel Spieße', 'veg' => true],
                ['name' => 'Melonenplatte', 'veg' => true, 'vegan' => true],
                ['name' => 'Obstplatte', 'veg' => true, 'vegan' => true],
            ],
            'kalte-fruehstueck' => [
                ['name' => 'Große Käseplatte', 'veg' => true],
                ['name' => 'Große Wurstplatte'],
                ['name' => 'Brot- und Brötchenkorb', 'veg' => true],
                ['name' => 'Laugengebäck Mix', 'veg' => true],
                ['name' => 'Mettbrötchen'],
                ['name' => 'Käsebrötchen', 'veg' => true],
                ['name' => 'Salamibrötchen'],
                ['name' => 'Tomate-Mozzarella Brötchen', 'veg' => true],
                ['name' => 'Lachs Bagel'],
                ['name' => 'Käse-Schinken Bagel'],
                ['name' => 'Bacon-Ei Bagel'],
                ['name' => 'Clubsandwich'],
                ['name' => 'Hähnchen Wrap'],
                ['name' => 'Tomaten Wrap', 'veg' => true],
                ['name' => 'Lachs-Ei Wrap'],
                ['name' => 'Gemüsesticks Platte', 'veg' => true, 'vegan' => true],
                ['name' => 'Melonenplatte', 'veg' => true, 'vegan' => true],
                ['name' => 'Tomate Mozzarella', 'veg' => true],
                ['name' => 'Antipasti Platte', 'veg' => true],
                ['name' => 'Lachsplatte'],
                ['name' => 'Herzhafte Aufstriche', 'veg' => true],
                ['name' => 'Nudelsalat Mediterran', 'veg' => true],
                ['name' => 'Eiersalat', 'veg' => true],
                ['name' => 'Gekochte Eier', 'veg' => true],
                ['name' => 'Marmeladen Nutella Mix', 'veg' => true],
                ['name' => 'Quark & Honig', 'veg' => true],
                ['name' => 'Obstplatte', 'veg' => true, 'vegan' => true],
                ['name' => 'Berliner', 'veg' => true],
                ['name' => 'Schoko Croissants', 'veg' => true],
                ['name' => 'Mini-Muffin Platte', 'veg' => true],
                ['name' => 'Blechkuchen', 'veg' => true],
            ],
            'warme-speisen' => [
                ['name' => 'Rührei', 'veg' => true],
                ['name' => 'Rührei mit Gemüse', 'veg' => true],
                ['name' => 'Rührei mit Speck'],
                ['name' => 'Bratkartoffeln mit Gemüse', 'veg' => true, 'vegan' => true],
                ['name' => 'Schweizer Rösti mit Spiegelei', 'veg' => true],
                ['name' => 'Nürnberger Würstchen'],
                ['name' => 'Wiener Würstchen'],
                ['name' => 'Weißwurst & Brezel'],
                ['name' => 'Pfannkuchen mit Ahornsirup', 'veg' => true],
                ['name' => 'French Toasts', 'veg' => true],
                ['name' => 'Waffeln mit Beeren', 'veg' => true],
                ['name' => 'Kaiserschmarrn', 'veg' => true],
            ],
            'grillgut-fleisch' => [
                ['name' => 'Hähnchensteak Honig-Senf'],
                ['name' => 'Hähnchensteak Mediterran'],
                ['name' => 'Putensteak Provence'],
                ['name' => 'Rinder Hüftsteaks'],
                ['name' => 'Bratwürste'],
                ['name' => 'Vegetarische Bratwürste', 'veg' => true],
                ['name' => 'Puten Schaschlik Spieße'],
                ['name' => 'Hähnchen-Champignon Spieß'],
                ['name' => 'Cheeseburger Station'],
                ['name' => 'Kebab Grillspieße'],
                ['name' => 'Limonen-Lachsfilet'],
                ['name' => 'Chicken Wings Honey-BBQ'],
                ['name' => 'Nürnberger Rostbratwürste'],
                ['name' => 'Käsewürstchen', 'veg' => true],
                ['name' => 'Halloumi-Gemüse Spieße', 'veg' => true],
                ['name' => 'BBQ Tofu-Spieße', 'veg' => true, 'vegan' => true],
            ],
            'grillgut-beilagen' => [
                ['name' => 'Halloumi Grillkäse', 'veg' => true],
                ['name' => 'Maiskolben', 'veg' => true, 'vegan' => true],
                ['name' => 'Grillgemüse', 'veg' => true, 'vegan' => true],
                ['name' => 'Grillkartoffeln mit Quark', 'veg' => true],
                ['name' => 'Kräuterbaguette vom Grill', 'veg' => true],
                ['name' => 'Gegrillte Ananas', 'veg' => true, 'vegan' => true],
            ],
        ];

        $menuLabel = [
            'vorspeisen' => 'Buffet',
            'hauptgerichte' => 'Buffet',
            'beilagen' => 'Buffet',
            'desserts' => 'Buffet',
            'fingerfood' => 'Fingerfood',
            'kalte-fruehstueck' => 'Frühstück',
            'warme-speisen' => 'Frühstück',
            'grillgut-fleisch' => 'Grill',
            'grillgut-beilagen' => 'Grill',
        ];

        $slugSuffix = [
            'fingerfood' => '-fingerfood',
            'kalte-fruehstueck' => '-fruehstueck',
            'warme-speisen' => '-fruehstueck',
            'grillgut-fleisch' => '-grill',
            'grillgut-beilagen' => '-grill',
        ];

        foreach ($groups as $categorySlug => $items) {
            $categoryId = $categories[$categorySlug] ?? null;
            if (! $categoryId) {
                continue;
            }

            foreach ($items as $index => $row) {
                $slug = $row['slug'] ?? (Str::slug($row['name']).($slugSuffix[$categorySlug] ?? ''));
                $vegan = ! empty($row['vegan']);
                $veg = $vegan || ! empty($row['veg']);
                $label = $menuLabel[$categorySlug] ?? 'Menü';

                $product = Product::withTrashed()->firstOrNew(['slug' => $slug]);
                $product->fill([
                    'category_id' => $categoryId,
                    'name' => $row['name'],
                    'short_description' => 'Zur freien Auswahl im '.$label.'.',
                    'description' => $row['name'].' – frisch zubereitet von Monnemtisch Catering.',
                    'price' => 0,
                    'price_type' => 'person',
                    'minimum_quantity' => 1,
                    'minimum_persons' => 10,
                    'maximum_persons' => 500,
                    'calories' => $product->calories ?? $calories->forName($row['name'], $categorySlug),
                    'is_vegan' => $vegan,
                    'is_vegetarian' => $veg,
                    'is_gluten_free' => false,
                    'is_lactose_free' => false,
                    'is_spicy' => false,
                    'is_featured' => false,
                    'is_bestseller' => false,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                    'seo_title' => $row['name'].' | Monnemtisch Catering',
                    'seo_description' => $row['name'].' zur freien Auswahl im '.$label.'.',
                ]);
                $product->deleted_at = null;
                if (empty($product->allergens)) {
                    $product->allergens = $allergens->forProduct($product);
                }
                $product->save();
            }
        }
    }
}
