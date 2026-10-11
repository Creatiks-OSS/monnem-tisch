<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            [
                'name' => 'Mannheim',
                'postal_code' => '68159',
                'delivery_price' => 0.00,
                'delivery_radius' => 12,
                'content' => '<p>Monnemtisch Catering beliefert Mannheim mit Premium-Buffets, Fingerfood und Event-Catering. Ob Hochzeit in den Quadraten oder Firmenevent – wir sind Ihr Partner vor Ort.</p><p>Innerhalb Mannheims ist die Lieferung kostenlos.</p>',
                'faq' => [
                    ['q' => 'Liefert ihr in ganz Mannheim?', 'a' => 'Ja, wir decken alle Stadtteile Mannheims ab. Die Lieferung innerhalb Mannheims ist kostenlos.'],
                    ['q' => 'Wie hoch ist die Liefergebühr?', 'a' => 'Innerhalb Mannheims kostenlos. Außerhalb 1,00 € je Kilometer ab H1, 10, 68159 Mannheim.'],
                ],
            ],
            [
                'name' => 'Ludwigsburg',
                'postal_code' => '71634',
                'delivery_price' => 25.00,
                'delivery_radius' => 18,
                'content' => '<p>Catering für Ludwigsburg und Umgebung: Von privaten Feiern bis Business-Lunch – Monnemtisch liefert pünktlich und stilvoll.</p>',
                'faq' => [
                    ['q' => 'Wie früh bestellen?', 'a' => 'Idealerweise 48 Stunden im Voraus.'],
                ],
            ],
            [
                'name' => 'Esslingen',
                'postal_code' => '73728',
                'delivery_price' => 28.00,
                'delivery_radius' => 25,
                'content' => '<p>Für Events in Esslingen bieten wir flexible Catering-Lösungen – Buffets, Fingerfood und Komplettpakete inklusive Lieferung.</p>',
                'faq' => [
                    ['q' => 'Gibt es Mindestbestellwerte?', 'a' => 'Ja, für Esslingen gilt ein Mindestbestellwert von 200 €.'],
                ],
            ],
            [
                'name' => 'Sindelfingen',
                'postal_code' => '71063',
                'delivery_price' => 22.00,
                'delivery_radius' => 16,
                'content' => '<p>Ob Firmenstandort oder private Feier in Sindelfingen: Monnemtisch Catering sorgt für reibungslose Versorgung Ihrer Gäste.</p>',
                'faq' => [
                    ['q' => 'Auch Messe-Catering?', 'a' => 'Ja, inklusive Snacks und Coffee Breaks.'],
                ],
            ],
            [
                'name' => 'Böblingen',
                'postal_code' => '71032',
                'delivery_price' => 24.00,
                'delivery_radius' => 20,
                'content' => '<p>Partyservice und Event-Catering für Böblingen – persönlich, frisch und zuverlässig von Monnemtisch Catering.</p>',
                'faq' => [
                    ['q' => 'Private Feiern möglich?', 'a' => 'Selbstverständlich – von Geburtstag bis Familienfeier.'],
                ],
            ],
        ];

        foreach ($cities as $city) {
            $model = City::query()->updateOrCreate(
                ['slug' => Str::slug($city['name'])],
                [
                    'name' => $city['name'],
                    'postal_code' => $city['postal_code'],
                    'meta_title' => 'Catering '.$city['name'].' | Monnemtisch Catering',
                    'meta_description' => 'Premium Catering und Partyservice in '.$city['name'].' – Buffets, Fingerfood und Events von Monnemtisch.',
                    'content' => $city['content'],
                    'delivery_price' => $city['delivery_price'],
                    'delivery_radius' => $city['delivery_radius'],
                    'faq' => $city['faq'],
                    'image' => '/images/placeholders/category.svg',
                    'is_active' => true,
                ]
            );

            $model->seo()->updateOrCreate([], [
                'title' => 'Catering '.$city['name'].' | Monnemtisch Catering',
                'description' => 'Premium Catering und Partyservice in '.$city['name'].'.',
                'keywords' => 'Catering '.$city['name'].', Partyservice '.$city['name'],
                'robots' => 'index,follow',
            ]);
        }
    }
}
