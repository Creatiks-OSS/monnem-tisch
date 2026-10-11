<?php

namespace Database\Seeders;

use App\Models\DeliveryZone;
use Illuminate\Database\Seeder;

class DeliveryZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            [
                'name' => 'Mannheim',
                'city' => 'Mannheim',
                'postal_codes' => ['68159', '68161', '68163', '68165', '68167', '68169', '68199', '68219', '68229', '68239', '68259', '68305', '68307', '68309'],
                'delivery_fee' => 15.00,
                'minimum_order' => 150.00,
                'free_delivery_threshold' => 400.00,
                'radius_km' => 12,
                'sort_order' => 1,
            ],
            [
                'name' => 'Ludwigsburg',
                'city' => 'Ludwigsburg',
                'postal_codes' => ['71634', '71636', '71638', '71640', '71642'],
                'delivery_fee' => 25.00,
                'minimum_order' => 180.00,
                'free_delivery_threshold' => 500.00,
                'radius_km' => 18,
                'sort_order' => 2,
            ],
            [
                'name' => 'Esslingen',
                'city' => 'Esslingen',
                'postal_codes' => ['73728', '73730', '73732', '73733', '73734'],
                'delivery_fee' => 28.00,
                'minimum_order' => 200.00,
                'free_delivery_threshold' => 550.00,
                'radius_km' => 25,
                'sort_order' => 3,
            ],
            [
                'name' => 'Sindelfingen',
                'city' => 'Sindelfingen',
                'postal_codes' => ['71063', '71065', '71067', '71069'],
                'delivery_fee' => 22.00,
                'minimum_order' => 180.00,
                'free_delivery_threshold' => 480.00,
                'radius_km' => 16,
                'sort_order' => 4,
            ],
            [
                'name' => 'Böblingen',
                'city' => 'Böblingen',
                'postal_codes' => ['71032', '71034'],
                'delivery_fee' => 24.00,
                'minimum_order' => 180.00,
                'free_delivery_threshold' => 500.00,
                'radius_km' => 20,
                'sort_order' => 5,
            ],
        ];

        foreach ($zones as $zone) {
            DeliveryZone::query()->updateOrCreate(
                ['name' => $zone['name']],
                array_merge($zone, [
                    'price_per_km' => null,
                    'is_active' => true,
                ])
            );
        }
    }
}
