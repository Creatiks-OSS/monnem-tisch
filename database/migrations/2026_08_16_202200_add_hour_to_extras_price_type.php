<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE extras MODIFY price_type ENUM('stück', 'person', 'fixed', 'hour') NOT NULL DEFAULT 'stück'");
        }

        DB::table('extras')
            ->where(function ($query) {
                $query->whereIn('name', ['Servicekräfte', 'Personal'])
                    ->orWhere('slug', 'like', 'servicekraft%')
                    ->orWhere('slug', 'personal');
            })
            ->update(['price_type' => 'hour']);
    }

    public function down(): void
    {
        DB::table('extras')
            ->where('price_type', 'hour')
            ->update(['price_type' => 'stück']);

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE extras MODIFY price_type ENUM('stück', 'person', 'fixed') NOT NULL DEFAULT 'stück'");
        }
    }
};
