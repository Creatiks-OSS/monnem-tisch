<?php

namespace Database\Seeders;

use App\Models\BusinessHour;
use Illuminate\Database\Seeder;

class BusinessHourSeeder extends Seeder
{
    public function run(): void
    {
        $hours = [
            0 => ['is_open' => false, 'open_time' => null, 'close_time' => null], // Sonntag
            1 => ['is_open' => true, 'open_time' => '09:00', 'close_time' => '18:00'], // Montag
            2 => ['is_open' => true, 'open_time' => '09:00', 'close_time' => '18:00'],
            3 => ['is_open' => true, 'open_time' => '09:00', 'close_time' => '18:00'],
            4 => ['is_open' => true, 'open_time' => '09:00', 'close_time' => '18:00'],
            5 => ['is_open' => true, 'open_time' => '09:00', 'close_time' => '18:00'],
            6 => ['is_open' => true, 'open_time' => '10:00', 'close_time' => '14:00'], // Samstag limited
        ];

        foreach ($hours as $day => $row) {
            BusinessHour::query()->updateOrCreate(
                ['day_of_week' => $day],
                [
                    'is_open' => $row['is_open'],
                    'open_time' => $row['open_time'],
                    'close_time' => $row['close_time'],
                ]
            );
        }
    }
}
