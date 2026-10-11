<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedHome();
        $this->seedLegalPages();
        $this->seedAbout();
        $this->seedLandings();
    }

    protected function seedHome(): void
    {
        $page = Page::query()->updateOrCreate(
            ['slug' => 'home'],
            [
                'title' => 'Startseite',
                'type' => 'page',
                'template' => 'home',
                'excerpt' => 'Monnemtisch Catering – Premium Catering in Mannheim',
                'featured_image' => '/images/placeholders/hero.svg',
                'is_published' => true,
                'published_at' => now(),
                'sort_order' => 0,
                'content' => null,
            ]
        );

        $page->seo()->updateOrCreate([], [
            'title' => 'Catering Mannheim – Buffets & Fingerfood | Monnemtisch',
            'description' => 'Catering in Mannheim: Buffets, Fingerfood, Frühstück und Grillcatering ab 10 Personen. Festpreis, frei kombinierbar – unverbindlich anfragen.',
            'keywords' => 'Catering Mannheim, Buffet Catering Mannheim, Fingerfood Catering Mannheim, Frühstück Catering Mannheim, Grillcatering Mannheim',
            'og_title' => 'Catering Mannheim – Buffets & Fingerfood | Monnemtisch',
            'og_description' => 'Catering in Mannheim: Buffets, Fingerfood, Frühstück und Grillcatering ab 10 Personen. Festpreis, frei kombinierbar – unverbindlich anfragen.',
            'robots' => 'index,follow',
        ]);

        PageSection::query()->where('page_id', $page->id)->delete();

        $sections = [
            [
                'type' => 'hero',
                'title' => 'Exquisites Catering für Ihre Feier',
                'subtitle' => 'Von eleganten Buffets bis Fingerfood – Monnemtisch Catering bringt Genuss nach Mannheim.',
                'content' => null,
                'settings' => [
                    'cta_primary' => 'Angebot anfordern',
                    'cta_primary_url' => '/angebot',
                    'cta_secondary' => 'Menü entdecken',
                    'cta_secondary_url' => '/catering',
                ],
                'image' => '/images/placeholders/hero.svg',
                'sort_order' => 1,
            ],
            [
                'type' => 'trust_bar',
                'title' => 'Warum Monnemtisch',
                'subtitle' => null,
                'content' => null,
                'settings' => [
                    'items' => [
                        ['label' => '12+ Jahre Erfahrung', 'value' => '12+'],
                        ['label' => '850+ Events', 'value' => '850+'],
                        ['label' => '4,9 Sterne', 'value' => '4,9'],
                        ['label' => '240+ Bewertungen', 'value' => '240+'],
                    ],
                ],
                'image' => null,
                'sort_order' => 2,
            ],
            [
                'type' => 'categories',
                'title' => 'Unsere Kategorien',
                'subtitle' => 'Entdecken Sie Buffets, Fingerfood und mehr',
                'content' => null,
                'settings' => ['limit' => 8],
                'image' => null,
                'sort_order' => 3,
            ],
            [
                'type' => 'products',
                'title' => 'Beliebte Angebote',
                'subtitle' => 'Unsere Empfehlungen für Ihr Event',
                'content' => null,
                'settings' => ['featured_only' => true, 'limit' => 8],
                'image' => null,
                'sort_order' => 4,
            ],
            [
                'type' => 'why_us',
                'title' => 'Frisch. Flexibel. Zuverlässig.',
                'subtitle' => 'Catering mit Anspruch',
                'content' => 'Wir verbinden handwerkliche Qualität mit zuverlässiger Lieferung und persönlicher Betreuung.',
                'settings' => [
                    'points' => [
                        'Regionale Zutaten',
                        'Individuelle Menüs',
                        'Pünktliche Lieferung',
                        'Allergen-Kennzeichnung',
                    ],
                ],
                'image' => '/images/placeholders/food-1.svg',
                'sort_order' => 5,
            ],
            [
                'type' => 'testimonials',
                'title' => 'Das sagen unsere Gäste',
                'subtitle' => null,
                'content' => null,
                'settings' => ['limit' => 6],
                'image' => null,
                'sort_order' => 6,
            ],
            [
                'type' => 'gallery',
                'title' => 'Einblicke',
                'subtitle' => 'Impressionen unserer Events',
                'content' => null,
                'settings' => ['limit' => 4],
                'image' => null,
                'sort_order' => 7,
            ],
            [
                'type' => 'faq',
                'title' => 'Häufige Fragen',
                'subtitle' => null,
                'content' => null,
                'settings' => ['limit' => 6],
                'image' => null,
                'sort_order' => 8,
            ],
            [
                'type' => 'cta',
                'title' => 'Bereit für Ihr Event?',
                'subtitle' => 'Fordern Sie jetzt Ihr unverbindliches Angebot an.',
                'content' => null,
                'settings' => [
                    'button_label' => 'Angebot anfordern',
                    'button_url' => '/angebot',
                ],
                'image' => null,
                'sort_order' => 9,
            ],
            [
                'type' => 'delivery_areas',
                'title' => 'Liefergebiete',
                'subtitle' => 'Mannheim und Umgebung',
                'content' => null,
                'settings' => [],
                'image' => null,
                'sort_order' => 10,
            ],
        ];

        foreach ($sections as $section) {
            PageSection::query()->create(array_merge($section, [
                'page_id' => $page->id,
                'is_active' => true,
                'padding' => 'default',
            ]));
        }
    }

    public function seedLegalPages(): void
    {
        $pages = [
            [
                'slug' => 'impressum',
                'title' => 'Impressum',
                'excerpt' => 'Anbieterkennzeichnung der Anatolian Gourmet GmbH, Marke Monnemtisch Catering, Mannheim.',
                'file' => 'impressum.html',
            ],
            [
                'slug' => 'datenschutz',
                'title' => 'Datenschutz',
                'excerpt' => 'Informationen zur Verarbeitung personenbezogener Daten gemäß DSGVO.',
                'file' => 'datenschutz.html',
            ],
            [
                'slug' => 'agb',
                'title' => 'AGB',
                'excerpt' => 'Allgemeine Geschäftsbedingungen für Catering, Lieferung und Events.',
                'file' => 'agb.html',
            ],
            [
                'slug' => 'widerruf',
                'title' => 'Widerrufsbelehrung',
                'excerpt' => 'Widerrufsrecht für Verbraucher und Hinweise zu verderblichen Speisen.',
                'file' => 'widerruf.html',
            ],
        ];

        foreach ($pages as $index => $data) {
            $content = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'legal'.DIRECTORY_SEPARATOR.$data['file']);

            $page = Page::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'title' => $data['title'],
                    'type' => 'legal',
                    'template' => 'legal',
                    'content' => $content,
                    'excerpt' => $data['excerpt'],
                    'is_published' => true,
                    'published_at' => now(),
                    'sort_order' => 100 + $index,
                ]
            );

            $page->seo()->updateOrCreate([], [
                'title' => $data['title'].' | Monnemtisch Catering',
                'description' => $data['excerpt'],
                'robots' => 'index,follow',
            ]);
        }
    }

    protected function seedAbout(): void
    {
        $page = Page::query()->updateOrCreate(
            ['slug' => 'ueber-uns'],
            [
                'title' => 'Über uns',
                'type' => 'landing',
                'template' => 'about',
                'content' => '<p>Monnemtisch Catering ist die Marke der Anatolian Gourmet GmbH in Mannheim. Wir kochen Buffets, Fingerfood, Frühstück und Grillcatering ab 10 Personen – frisch, fest im Preis, frei kombinierbar.</p>',
                'excerpt' => 'Monnemtisch Catering – Küche der Anatolian Gourmet GmbH in Mannheim.',
                'featured_image' => '/images/placeholders/hero.svg',
                'is_published' => true,
                'published_at' => now(),
                'sort_order' => 20,
            ]
        );

        $page->seo()->updateOrCreate([], [
            'title' => 'Über uns | Monnemtisch Catering Mannheim',
            'description' => 'Wer wir sind: Premium Catering mit Herz und Handwerk aus Mannheim.',
            'robots' => 'index,follow',
        ]);
    }

    protected function seedLandings(): void
    {
        $landings = [
            [
                'slug' => 'hochzeiten',
                'title' => 'Hochzeitscatering',
                'excerpt' => 'Unvergessliches Catering für Ihren großen Tag',
                'content' => '<p>Vom Sektempfang bis zum Mitternachtssnack – Monnemtisch gestaltet Ihr Hochzeitsmenü individuell und stilvoll.</p>',
            ],
            [
                'slug' => 'firmen-catering',
                'title' => 'Firmen-Catering',
                'excerpt' => 'Professionelles Catering für Unternehmen',
                'content' => '<p>Meetings, Jubiläen und Teamevents – repräsentativ, pünktlich und flexibel.</p>',
            ],
            [
                'slug' => 'messe-catering',
                'title' => 'Messe-Catering',
                'excerpt' => 'Catering für Messestände und VIP-Areas',
                'content' => '<p>Snacks, Coffee Breaks und Lounge-Fingerfood für Ihre Messe in Mannheim.</p>',
            ],
            [
                'slug' => 'partyservice',
                'title' => 'Partyservice',
                'excerpt' => 'Partyservice Mannheim und Umgebung',
                'content' => '<p>Kompletter Partyservice inkl. Lieferung, Aufbau und optionalem Personal.</p>',
            ],
            [
                'slug' => 'buffets',
                'title' => 'Buffets',
                'excerpt' => 'Vielfältige Buffets für jeden Anlass',
                'content' => '<p>Klassisch, mediterran, asiatisch oder vegetarisch – unsere Buffets begeistern Gäste.</p>',
            ],
            [
                'slug' => 'fingerfood',
                'title' => 'Fingerfood',
                'excerpt' => 'Elegantes Fingerfood für Empfänge',
                'content' => '<p>Kleine Häppchen mit großer Wirkung – ideal für Stehempfänge und Networking.</p>',
            ],
        ];

        foreach ($landings as $index => $data) {
            $page = Page::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'title' => $data['title'],
                    'type' => 'landing',
                    'template' => 'landing',
                    'content' => $data['content'],
                    'excerpt' => $data['excerpt'],
                    'featured_image' => '/images/placeholders/food-'.(($index % 4) + 1).'.svg',
                    'is_published' => true,
                    'published_at' => now(),
                    'sort_order' => 30 + $index,
                ]
            );

            $page->seo()->updateOrCreate([], [
                'title' => $data['title'].' | Monnemtisch Catering Mannheim',
                'description' => $data['excerpt'].' – Monnemtisch Catering in Mannheim und Umgebung.',
                'keywords' => $data['title'].', Catering Mannheim',
                'og_title' => $data['title'].' | Monnemtisch Catering',
                'og_description' => $data['excerpt'],
                'robots' => 'index,follow',
            ]);

            if (PageSection::query()->where('page_id', $page->id)->doesntExist()) {
                PageSection::query()->create([
                    'page_id' => $page->id,
                    'type' => 'hero',
                    'title' => $data['title'],
                    'subtitle' => $data['excerpt'],
                    'content' => null,
                    'settings' => [
                        'cta_primary' => 'Angebot anfordern',
                        'cta_primary_url' => '/angebot',
                    ],
                    'image' => $page->featured_image,
                    'is_active' => true,
                    'sort_order' => 1,
                ]);

                PageSection::query()->create([
                    'page_id' => $page->id,
                    'type' => 'cta',
                    'title' => 'Jetzt unverbindlich anfragen',
                    'subtitle' => 'Wir melden uns zeitnah mit einem individuellen Angebot.',
                    'content' => null,
                    'settings' => [
                        'button_label' => 'Kontakt aufnehmen',
                        'button_url' => '/kontakt',
                    ],
                    'is_active' => true,
                    'sort_order' => 2,
                ]);
            }
        }
    }
}
