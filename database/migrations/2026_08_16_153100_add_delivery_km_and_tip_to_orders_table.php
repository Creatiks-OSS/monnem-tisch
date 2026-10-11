<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'delivery_km')) {
                $table->decimal('delivery_km', 10, 2)->default(0)->after('delivery_fee');
            }
            if (! Schema::hasColumn('orders', 'tip_amount')) {
                $table->decimal('tip_amount', 10, 2)->default(0)->after('service_fee');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'delivery_km')) {
                $table->dropColumn('delivery_km');
            }
            if (Schema::hasColumn('orders', 'tip_amount')) {
                $table->dropColumn('tip_amount');
            }
        });
    }
};
