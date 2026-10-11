<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Buffets',
                'slug' => 'buffets',
                'short_description' => 'Vielfältige Buffets für große und kleine Feiern.',
                'description' => 'Unsere Buffets kombinieren warme und kalte Speisen zu einem harmonischen Gesamtbild – ideal für Hochzeiten, Firmenevents und private Feiern.',
                'starting_price' => 24.90,
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Fingerfood',
                'slug' => 'fingerfood',
                'short_description' => 'Kleine Häppchen mit großer Wirkung.',
                'description' => 'Elegantes Fingerfood für Empfänge, Networking-Events und Stehempfänge – praktisch, stilvoll und geschmackvoll.',
                'starting_price' => 18.50,
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Frühstück',
                'slug' => 'fruehstueck',
                'short_description' => 'Frische Frühstückspakete für Büro und Event.',
                'description' => 'Ob Meeting oder Brunch: Unsere Frühstücksangebote bringen Energie und Genuss an Ihren Tisch.',
                'starting_price' => 12.90,
                'is_featured' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Grillcatering',
                'slug' => 'grillcatering',
                'short_description' => 'Live am Grill – aromatisch und gesellig.',
                'description' => 'Grillbuffets und Live-Cooking mit hochwertigem Fleisch, Fisch und vegetarischen Alternativen.',
                'starting_price' => 29.90,
                'is_featured' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Extras',
                'slug' => 'extras',
                'short_description' => 'Getränke, Süßes und Ergänzungen zu jedem Menü.',
                'description' => 'Getränkepakete, Sweet Corner und Käseplatten – frei kombinierbar zum Festpreis.',
                'starting_price' => 6.90,
                'is_featured' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Hochzeit',
                'slug' => 'hochzeit',
                'short_description' => 'Unvergessliches Hochzeitscatering.',
                'description' => 'Vom Sektempfang bis zum Mitternachtssnack – wir begleiten Ihren besonderen Tag kulinarisch.',
                'starting_price' => 49.00,
                'is_featured' => false,
                'sort_order' => 15,
            ],
            [
                'name' => 'Business Catering',
                'slug' => 'business-catering',
                'short_description' => 'Professionell für Meetings und Konferenzen.',
                'description' => 'Pünktlich, repräsentativ und flexibel – Catering für Unternehmen in Mannheim und Umgebung.',
                'starting_price' => 16.90,
                'is_featured' => false,
                'sort_order' => 16,
            ],
            [
                'name' => 'Messe Catering',
                'slug' => 'messe-catering',
                'short_description' => 'Catering für Messestände und VIP-Areas.',
                'description' => 'Zuverlässige Versorgung auf Messen – von Snacks bis Premium-Lunch für Gäste und Teams.',
                'starting_price' => 14.90,
                'is_featured' => false,
                'sort_order' => 17,
            ],
            [
                'name' => 'Private Events',
                'slug' => 'private-events',
                'short_description' => 'Geburtstage, Jubiläen und Familienfeiern.',
                'description' => 'Individuelle Menüs für private Anlässe – persönlich beraten und stilvoll umgesetzt.',
                'starting_price' => 22.00,
                'is_featured' => false,
                'sort_order' => 18,
            ],
            [
                'name' => 'Getränke',
                'slug' => 'getraenke',
                'short_description' => 'Passende Getränkepakete zu jedem Menü.',
                'description' => 'Softgetränke, Säfte, Kaffee und ausgewählte Weine – abgestimmt auf Ihr Event.',
                'starting_price' => 4.50,
                'is_featured' => false,
                'sort_order' => 9,
            ],
            [
                'name' => 'Vorspeisen',
                'slug' => 'vorspeisen',
                'short_description' => 'Kalte und warme Einstiege für Ihr Buffet.',
                'description' => 'Vorspeisen zur freien Auswahl in den Buffet-Paketen.',
                'starting_price' => 0,
                'is_featured' => false,
                'sort_order' => 20,
            ],
            [
                'name' => 'Hauptgerichte',
                'slug' => 'hauptgerichte',
                'short_description' => 'Herzhaftes für das Hauptbuffet.',
                'description' => 'Hauptgerichte zur freien Auswahl in den Buffet-Paketen.',
                'starting_price' => 0,
                'is_featured' => false,
                'sort_order' => 21,
            ],
            [
                'name' => 'Beilagen',
                'slug' => 'beilagen',
                'short_description' => 'Passende Beilagen zu jedem Hauptgericht.',
                'description' => 'Beilagen zur freien Auswahl in den Buffet-Paketen.',
                'starting_price' => 0,
                'is_featured' => false,
                'sort_order' => 22,
            ],
            [
                'name' => 'Desserts',
                'slug' => 'desserts',
                'short_description' => 'Süße Abschlüsse.',
                'description' => 'Desserts zur freien Auswahl in den Buffet-Paketen.',
                'starting_price' => 0,
                'is_featured' => false,
                'sort_order' => 23,
            ],
            ['name' => 'Kalte Frühstücks-Komponenten', 'slug' => 'kalte-fruehstueck', 'short_description' => 'Kalte Speisen fürs Frühstücksbuffet.', 'description' => 'Kalte Frühstücks-Komponenten zur freien Auswahl.', 'starting_price' => 0, 'is_featured' => false, 'sort_order' => 40],
            ['name' => 'Warme Speisen', 'slug' => 'warme-speisen', 'short_description' => 'Warme Speisen fürs Frühstück und Brunch.', 'description' => 'Warme Speisen zur freien Auswahl im Frühstück.', 'starting_price' => 0, 'is_featured' => false, 'sort_order' => 41],
            ['name' => 'Grillgut | Fleisch & Veggie', 'slug' => 'grillgut-fleisch', 'short_description' => 'Fleisch und vegetarische Grilloptionen.', 'description' => 'Grillgut zur freien Auswahl.', 'starting_price' => 0, 'is_featured' => false, 'sort_order' => 50],
            ['name' => 'Grillgut | Beilagen', 'slug' => 'grillgut-beilagen', 'short_description' => 'Beilagen vom Grill.', 'description' => 'Grill-Beilagen zur freien Auswahl.', 'starting_price' => 0, 'is_featured' => false, 'sort_order' => 51],
            ['name' => 'Süßes Extra', 'slug' => 'suesses-extras', 'short_description' => 'Sweet Corner Speisen.', 'description' => 'Zur freien Auswahl bei Extras.', 'starting_price' => 0, 'is_featured' => false, 'sort_order' => 61],
            ['name' => 'Käseplatten', 'slug' => 'kaese-platten', 'short_description' => 'Käse und Antipasti.', 'description' => 'Zur freien Auswahl bei Extras.', 'starting_price' => 0, 'is_featured' => false, 'sort_order' => 62],
        ];

        foreach ($categories as $data) {
            ProductCategory::query()->updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, [
                    'image' => '/images/placeholders/category.svg',
                    'is_active' => true,
                    'seo_title' => $data['name'].' | Monnemtisch Catering Mannheim',
                    'seo_description' => $data['short_description'],
                ])
            );
        }

        $buffetId = ProductCategory::query()->where('slug', 'buffets')->value('id');
        if ($buffetId) {
            ProductCategory::query()
                ->whereIn('slug', ['vorspeisen', 'hauptgerichte', 'beilagen', 'desserts'])
                ->update(['parent_id' => $buffetId, 'is_featured' => false]);
        }

        $fingerId = ProductCategory::query()->where('slug', 'fingerfood')->value('id');
        if ($fingerId) {
            ProductCategory::query()
                ->where('slug', 'fingerfood')
                ->update(['parent_id' => null]);

            ProductCategory::query()
                ->whereIn('slug', ['haeppchen', 'dips-saucen', 'suesses-fingerfood'])
                ->get()
                ->each(function (ProductCategory $category) use ($fingerId): void {
                    $category->products()->update(['category_id' => $fingerId]);
                    $category->delete();
                });
        }

        $breakfastId = ProductCategory::query()->where('slug', 'fruehstueck')->value('id');
        if ($breakfastId) {
            ProductCategory::query()
                ->whereIn('slug', ['kalte-fruehstueck', 'warme-speisen'])
                ->update(['parent_id' => $breakfastId, 'is_featured' => false]);

            ProductCategory::query()
                ->whereIn('slug', ['brot-aufstriche', 'kaese-wurst-ei', 'obst-muesli'])
                ->get()
                ->each(function (ProductCategory $category): void {
                    $category->delete();
                });
        }

        $grillId = ProductCategory::query()->where('slug', 'grillcatering')->value('id');
        if ($grillId) {
            ProductCategory::query()
                ->whereIn('slug', ['grillgut-fleisch', 'grillgut-beilagen'])
                ->update(['parent_id' => $grillId, 'is_featured' => false]);

            ProductCategory::query()
                ->whereIn('slug', ['grillfleisch', 'grillgemuese', 'grill-beilagen'])
                ->get()
                ->each(function (ProductCategory $category): void {
                    $category->delete();
                });
        }

        $extrasId = ProductCategory::query()->where('slug', 'extras')->value('id');
        if ($extrasId) {
            ProductCategory::query()
                ->whereIn('slug', ['getraenke', 'suesses-extras', 'kaese-platten'])
                ->update(['parent_id' => $extrasId, 'is_featured' => false]);
        }

        ProductCategory::query()
            ->whereIn('slug', ['hochzeit', 'business-catering', 'messe-catering', 'private-events'])
            ->update(['is_active' => false, 'is_featured' => false]);
    }
}
