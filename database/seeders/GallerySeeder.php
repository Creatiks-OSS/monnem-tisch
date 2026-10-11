<?php

namespace Database\Seeders;

use App\Models\Gallery;
use App\Models\GalleryImage;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        /** @var list<array{keyword: string, path: string}> $keywordImages */
        $keywordImages = require __DIR__.DIRECTORY_SEPARATOR.'gallery_keyword_images.php';

        $toRows = function (array $items): array {
            return array_map(fn (array $item) => [
                'path' => $item['path'],
                'title' => $item['keyword'],
                'alt' => $item['keyword'],
                'description' => $item['keyword'].' – Monnemtisch Catering, Anatolian Gourmet GmbH',
            ], $items);
        };

        $byKeyword = [];
        foreach ($keywordImages as $item) {
            $byKeyword[$item['keyword']] = $item;
        }

        $pick = function (array $keywords) use ($byKeyword): array {
            $out = [];
            foreach ($keywords as $keyword) {
                if (isset($byKeyword[$keyword])) {
                    $out[] = $byKeyword[$keyword];
                }
            }

            return $out;
        };

        $albums = [
            [
                'name' => 'Hochzeiten & Empfänge',
                'slug' => 'hochzeiten-empfaenge',
                'description' => 'Hochzeitscatering Mannheim, Sektempfänge und Party Catering Mannheim – echte Buffets von Monnemtisch.',
                'category' => 'hochzeiten',
                'images' => $toRows($pick([
                    'Hochzeitscatering Mannheim',
                    'Hochzeit Catering Mannheim',
                    'Party Catering Mannheim',
                    'Event Catering Mannheim',
                    'Catering für Events Mannheim',
                    'Halal Catering Mannheim',
                ])),
            ],
            [
                'name' => 'Impressionen',
                'slug' => 'impressionen',
                'description' => 'Catering Mannheim: Buffets, Fingerfood, Lieferung und Events – Einblicke in Catering in Mannheim von Monnemtisch.',
                'category' => 'events',
                'images' => $toRows($keywordImages),
            ],
            [
                'name' => 'Business & Messe',
                'slug' => 'business-messe',
                'description' => 'Business Catering Mannheim, Firmen Catering und Catering Lieferung Mannheim für Meetings, Messen und Empfänge.',
                'category' => 'firmen-catering',
                'images' => $toRows($pick([
                    'Business Catering Mannheim',
                    'Firmen Catering Mannheim',
                    'Catering Firma Mannheim',
                    'Catering für Firmen Mannheim',
                    'Catering Service Mannheim',
                    'Catering Lieferung Mannheim',
                    'Catering Anbieter Mannheim',
                    'Catering Mannheim Preise',
                ])),
            ],
        ];

        foreach ($albums as $index => $album) {
            $gallery = Gallery::query()->updateOrCreate(
                ['slug' => $album['slug']],
                [
                    'name' => $album['name'],
                    'description' => $album['description'],
                    'cover_image' => $album['images'][0]['path'] ?? null,
                    'category' => $album['category'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );

            GalleryImage::query()->where('gallery_id', $gallery->id)->delete();

            foreach ($album['images'] as $imgIndex => $image) {
                GalleryImage::query()->create([
                    'gallery_id' => $gallery->id,
                    'path' => $image['path'],
                    'title' => $image['title'],
                    'description' => $image['description'],
                    'alt' => $image['alt'],
                    'sort_order' => $imgIndex + 1,
                ]);
            }
        }
    }
}
