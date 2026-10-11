<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::query()->where('email', 'admin@example.com')->first()
            ?? User::query()->first();

        if (! $author) {
            return;
        }

        $categories = [
            [
                'name' => 'Catering Tipps',
                'slug' => 'catering-tipps',
                'description' => 'Praxisleitfäden für Firmencatering, Buffets und Empfänge in Mannheim: Mengen, Ablauf, Allergene und Budget.',
            ],
            [
                'name' => 'Rezepte & Trends',
                'slug' => 'rezepte-trends',
                'description' => 'Saisonale Küche, vegane Menüs und Catering-Trends 2026 – Inspiration aus der Küche von Monnemtisch.',
            ],
            [
                'name' => 'Events in Mannheim',
                'slug' => 'events-mannheim',
                'description' => 'Hochzeiten, Messen und Events in Mannheim und der Metropolregion: Catering-Logistik, Timing und Checklisten.',
            ],
        ];

        $oldStuttgartCategory = BlogCategory::query()->where('slug', 'events-stuttgart')->first();
        if ($oldStuttgartCategory && BlogCategory::query()->where('slug', 'events-mannheim')->doesntExist()) {
            $oldStuttgartCategory->update([
                'slug' => 'events-mannheim',
                'name' => 'Events in Mannheim',
                'description' => $categories[2]['description'],
            ]);
        }

        $categoryIds = [];
        foreach ($categories as $index => $cat) {
            $model = BlogCategory::query()->updateOrCreate(
                ['slug' => $cat['slug']],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
            $categoryIds[$cat['slug']] = $model->id;
        }

        $posts = array_merge(
            [
            [
                'category' => 'catering-tipps',
                'slug' => 'so-planen-sie-das-perfekte-firmenbuffet',
                'title' => 'So planen Sie das perfekte Firmenbuffet',
                'file' => 'so-planen-sie-das-perfekte-firmenbuffet.html',
                'excerpt' => 'Firmenbuffet in Mannheim planen: Personenanzahl, Speisenmix, Allergene und Zeitplan – Checkliste für ein repräsentatives Firmencatering.',
                'seo_title' => 'Firmenbuffet planen in Mannheim',
                'seo_description' => 'So gelingt Ihr Firmenbuffet in Mannheim: Mengen, vegetarische Optionen, Allergene und Lieferlogistik. Checkliste von Monnemtisch Catering.',
                'keywords' => 'Firmenbuffet Mannheim, Firmencatering, Buffet planen, Catering Unternehmen Mannheim',
                'focus_keyword' => 'Firmenbuffet Mannheim',
                'tags' => ['Firmenbuffet', 'Mannheim', 'Firmencatering', 'Buffet'],
            ],
            [
                'category' => 'catering-tipps',
                'slug' => 'fingerfood-fur-empfange-mengen-richtig-kalkulieren',
                'title' => 'Fingerfood für Empfänge: Mengen richtig kalkulieren',
                'file' => 'fingerfood-fur-empfange-mengen-richtig-kalkulieren.html',
                'excerpt' => 'Häppchen pro Gast und Stunde: Faustregeln für Stehempfänge, After-Work und VIP-Lounges – plus typische Rechenbeispiele für Mannheim.',
                'seo_title' => 'Fingerfood Mengen kalkulieren',
                'seo_description' => 'Wie viele Häppchen pro Gast? Faustregeln für Empfänge, Networking und Fingerfood-Catering in Mannheim – mit Rechenbeispielen.',
                'keywords' => 'Fingerfood Empfang, Häppchen kalkulieren, Stehempfang Catering, Fingerfood Mannheim',
                'focus_keyword' => 'Fingerfood Empfang',
                'tags' => ['Fingerfood', 'Empfang', 'Häppchen', 'Mannheim'],
            ],
            [
                'category' => 'rezepte-trends',
                'slug' => 'trend-2026-regionale-zutaten-im-event-catering',
                'title' => 'Trend 2026: Regionale Zutaten im Event-Catering',
                'file' => 'trend-2026-regionale-zutaten-im-event-catering.html',
                'excerpt' => 'Warum saisonale und regionale Produkte Event-Catering in Mannheim glaubwürdiger und besser machen – mit Saisonplan für die Küche.',
                'seo_title' => 'Regionale Zutaten im Event-Catering',
                'seo_description' => 'Trend 2026: regionale Zutaten im Event-Catering. Saisonplan, Buffet-Tipps und ehrliche Herkunft – Catering aus Mannheim von Monnemtisch.',
                'keywords' => 'regionales Catering Mannheim, saisonales Event-Catering, Catering Trends 2026',
                'focus_keyword' => 'regionales Catering Mannheim',
                'tags' => ['Regional', 'Saison', 'Event-Catering', 'Trends'],
            ],
            [
                'category' => 'rezepte-trends',
                'slug' => 'vegane-optionen-die-alle-begeistern',
                'title' => 'Vegane Optionen, die alle begeistern',
                'file' => 'vegane-optionen-die-alle-begeistern.html',
                'excerpt' => 'Veganes Catering in Mannheim ohne Beilagen-Image: Bowls, Fingerfood, Desserts, Kennzeichnung und realistische Mengen am Buffet.',
                'seo_title' => 'Veganes Catering Mannheim',
                'seo_description' => 'Vegane Buffets und Fingerfood, die alle nehmen. Tipps zu Mengen, Allergenen und Präsentation – veganes Catering in Mannheim.',
                'keywords' => 'veganes Catering Mannheim, vegane Buffets, vegetarisch Events, pflanzliches Fingerfood',
                'focus_keyword' => 'veganes Catering Mannheim',
                'tags' => ['Vegan', 'Vegetarisch', 'Buffet', 'Mannheim'],
            ],
            [
                'category' => 'events-mannheim',
                'slug' => 'hochzeitscatering-in-mannheim-was-paare-wissen-sollten',
                'title' => 'Hochzeitscatering in Mannheim: Was Paare wissen sollten',
                'file' => 'hochzeitscatering-in-mannheim-was-paare-wissen-sollten.html',
                'excerpt' => 'Hochzeitscatering in Mannheim: Empfang, Buffet, Torte, Spätstück, Location und Zeitplan – was Paare vor der Buchung klären sollten.',
                'seo_title' => 'Hochzeitscatering in Mannheim',
                'seo_description' => 'Hochzeitscatering in Mannheim und der Region: Ablauf, Buffet vs. Menü, Allergene und Lieferlogistik. Praxisleitfaden für Paare.',
                'keywords' => 'Hochzeitscatering Mannheim, Hochzeit Buffet, Catering Hochzeit Rhein-Neckar',
                'focus_keyword' => 'Hochzeitscatering Mannheim',
                'tags' => ['Hochzeit', 'Mannheim', 'Hochzeitscatering', 'Buffet'],
            ],
            [
                'category' => 'events-mannheim',
                'slug' => 'messe-catering-mannheim-so-bleibt-ihr-stand-attraktiv',
                'title' => 'Messe-Catering Mannheim: So bleibt Ihr Stand attraktiv',
                'file' => 'messe-catering-mannheim-so-bleibt-ihr-stand-attraktiv.html',
                'excerpt' => 'Messe-Catering in Mannheim: Snacks, Coffee Breaks, VIP-Lounge und Nachschub – damit der Stand den ganzen Tag einladend bleibt.',
                'seo_title' => 'Messe-Catering Mannheim',
                'seo_description' => 'Messe-Catering in Mannheim: Fingerfood, Coffee Breaks und Lounge-Snacks mit Nachschub-Logik. So bleibt Ihr Stand attraktiv.',
                'keywords' => 'Messe-Catering Mannheim, Messestand Catering, Coffee Break Messe, Fingerfood Messe',
                'focus_keyword' => 'Messe-Catering Mannheim',
                'tags' => ['Messe', 'Mannheim', 'Fingerfood', 'Standcatering'],
            ],
            ],
            require __DIR__.DIRECTORY_SEPARATOR.'blog'.DIRECTORY_SEPARATOR.'extra_posts.php'
        );

        $images = [
            '/images/hero-buffet-monnemtisch.webp',
            '/images/hero-monnemtisch.webp',
            '/images/table-monnemtisch.webp',
            '/images/gallery/Buffet_Catering_Mannheim.webp',
            '/images/gallery/Fingerfood_Catering_Mannheim.webp',
            '/images/gallery/Hochzeitscatering_Mannheim.webp',
            '/images/gallery/Event_Catering_Mannheim.webp',
            '/images/gallery/Catering_Mannheim.webp',
            '/images/gallery/business-catering-mannheim.webp',
            '/images/gallery/firmen-catering-mannheim.webp',
            '/images/gallery/party-catering-mannheim.webp',
            '/images/gallery/event-catering-mannheim.webp',
            '/images/gallery/hochzeitscatering-mannheim.webp',
            '/images/gallery/buffet-catering-mannheim.webp',
            '/images/gallery/catering-fuer-firmen-mannheim.webp',
            '/images/packages/package-buffet-mega-l.webp',
            '/images/packages/package-fingerfood-deluxe.webp',
            '/images/packages/package-brunch-deluxe.webp',
            '/images/packages/package-grill-deluxe.webp',
            '/images/packages/package-fruehstueck-classic.webp',
            '/images/dishes/oriental-mezze-platte.webp',
            '/images/dishes/garnelen-spiess.webp',
            '/images/gallery/halal-catering-mannheim.webp',
            '/images/gallery/catering-in-mannheim.webp',
        ];

        $total = count($posts);
        $latest = now()->subDays(12)->startOfDay();

        foreach ($posts as $index => $post) {
            $slug = $post['slug'] ?? Str::slug($post['title']);
            $path = __DIR__.DIRECTORY_SEPARATOR.'blog'.DIRECTORY_SEPARATOR.$post['file'];
            $content = is_file($path) ? file_get_contents($path) : '';
            $publishedAt = $latest->copy()->subDays((($total - 1 - $index) * 14) + ($index % 5));
            $updatedAt = $publishedAt->copy()->addDays(4 + ($index % 16));
            if ($updatedAt->isFuture()) {
                $updatedAt = now()->subDays(2);
            }

            $payload = [
                'blog_category_id' => $categoryIds[$post['category']] ?? null,
                'author_id' => $author->id,
                'title' => $post['title'],
                'slug' => $slug,
                'excerpt' => $post['excerpt'],
                'content' => $content,
                'featured_image' => $this->imageForSlug($slug, $images, $index),
                'is_published' => true,
                'published_at' => $publishedAt,
            ];

            $model = BlogPost::query()->updateOrCreate(
                ['slug' => $slug],
                $payload
            );

            $model->timestamps = false;
            $model->forceFill([
                'created_at' => $publishedAt,
                'updated_at' => $updatedAt,
            ])->save();

            $canonical = url('/blog/'.$model->slug);

            $model->seo()->updateOrCreate([], [
                'title' => $post['seo_title'],
                'description' => $post['seo_description'],
                'keywords' => $post['keywords'],
                'focus_keyword' => $post['focus_keyword'],
                'canonical' => $canonical,
                'og_title' => $post['seo_title'],
                'og_description' => $post['seo_description'],
                'og_image' => $model->featured_image,
                'twitter_title' => $post['seo_title'],
                'twitter_description' => $post['seo_description'],
                'twitter_image' => $model->featured_image,
                'robots' => 'index,follow',
            ]);

            $tagIds = [];
            foreach ($post['tags'] as $tagName) {
                $tag = Tag::query()->updateOrCreate(
                    ['slug' => Str::slug($tagName)],
                    ['name' => $tagName]
                );
                $tagIds[] = $tag->id;
            }
            $model->tags()->sync($tagIds);
        }
    }

    /**
     * @param  list<string>  $fallback
     */
    protected function imageForSlug(string $slug, array $fallback, int $index): string
    {
        $map = [
            'grill' => '/images/packages/package-grill-deluxe.webp',
            'fingerfood' => '/images/packages/package-fingerfood-deluxe.webp',
            'frueh' => '/images/packages/package-fruehstueck-classic.webp',
            'brunch' => '/images/packages/package-brunch-deluxe.webp',
            'hochzeit' => '/images/gallery/Hochzeitscatering_Mannheim.webp',
            'messe' => '/images/gallery/Event_Catering_Mannheim.webp',
            'halal' => '/images/gallery/halal-catering-mannheim.webp',
            'meze' => '/images/dishes/oriental-mezze-platte.webp',
            'garten' => '/images/packages/package-grill-deluxe.webp',
            'weihnacht' => '/images/gallery/buffet-catering-mannheim.webp',
            'campus' => '/images/gallery/catering-in-mannheim.webp',
            'firma' => '/images/gallery/firmen-catering-mannheim.webp',
        ];

        foreach ($map as $needle => $path) {
            if (str_contains($slug, $needle)) {
                return $path;
            }
        }

        return $fallback[$index % count($fallback)];
    }
}
