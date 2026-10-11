<?php

namespace Database\Seeders;

use App\Models\EventType;
use Illuminate\Database\Seeder;

class EventTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Hochzeit',
                'slug' => 'hochzeit',
                'description' => 'Exklusives Catering für Ihren Hochzeitstag – von Sektempfang bis Mitternachtssnack.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Firmenfeier',
                'slug' => 'firmenfeier',
                'description' => 'Repräsentatives Catering für Firmenfeiern, Jubiläen und Teamevents.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Geburtstag',
                'slug' => 'geburtstag',
                'description' => 'Individuelle Menüs für Geburtstagsfeiern – privat oder großzügig.',
                'sort_order' => 3,
            ],
            [
                'name' => 'Messe',
                'slug' => 'messe',
                'description' => 'Zuverlässiges Catering für Messestände, Lounges und VIP-Areas.',
                'sort_order' => 4,
            ],
            [
                'name' => 'Private Feier',
                'slug' => 'private-feier',
                'description' => 'Catering für private Anlässe – familiär, stilvoll und flexibel.',
                'sort_order' => 5,
            ],
            [
                'name' => 'Business Event',
                'slug' => 'business-event',
                'description' => 'Professionelles Catering für Meetings, Konferenzen und Networking.',
                'sort_order' => 6,
            ],
        ];

        foreach ($types as $data) {
            EventType::query()->updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, [
                    'image' => '/images/placeholders/category.svg',
                    'is_active' => true,
                    'seo_title' => $data['name'].' Catering | Monnemtisch',
                    'seo_description' => $data['description'],
                ])
            );
        }
    }
}
