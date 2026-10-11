<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::query()->updateOrCreate(
            ['code' => 'WELCOME10'],
            [
                'type' => 'percent',
                'value' => 10,
                'minimum_order' => 100,
                'max_uses' => 500,
                'used_count' => 0,
                'starts_at' => now()->subDay(),
                'expires_at' => now()->addYear(),
                'is_active' => true,
            ]
        );
    }
}
