<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            SettingsSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            ProductCaloriesSeeder::class,
            ProductImageSeeder::class,
            ExtraSeeder::class,
            EventTypeSeeder::class,
            PackageSeeder::class,
            ReviewSeeder::class,
            FaqSeeder::class,
            BlogSeeder::class,
            GallerySeeder::class,
            DeliveryZoneSeeder::class,
            CitySeeder::class,
            PageSeeder::class,
            BusinessHourSeeder::class,
            EmailTemplateSeeder::class,
            CouponSeeder::class,
            BlockedDateSeeder::class,
        ]);
    }
}
