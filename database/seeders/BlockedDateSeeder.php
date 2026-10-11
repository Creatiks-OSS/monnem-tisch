<?php

namespace Database\Seeders;

use App\Models\BlockedDate;
use Illuminate\Database\Seeder;

class BlockedDateSeeder extends Seeder
{
    public function run(): void
    {
        $dates = [
            [
                'date' => now()->addWeeks(3)->toDateString(),
                'type' => 'blocked',
                'note' => 'Betriebsferien / Kapazität ausgeschöpft',
            ],
            [
                'date' => now()->addWeeks(6)->toDateString(),
                'type' => 'booked',
                'note' => 'Großevent – keine weiteren Buchungen',
            ],
            [
                'date' => now()->addMonths(2)->startOfMonth()->toDateString(),
                'type' => 'special',
                'note' => 'Feiertag – eingeschränkter Service',
            ],
        ];

        foreach ($dates as $row) {
            BlockedDate::query()->updateOrCreate(
                ['date' => $row['date']],
                [
                    'type' => $row['type'],
                    'note' => $row['note'],
                ]
            );
        }
    }
}
