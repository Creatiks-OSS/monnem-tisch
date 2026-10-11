<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('sent_to_kitchen_at')->nullable()->after('delivered_at');
            $table->timestamp('kitchen_seen_at')->nullable()->after('sent_to_kitchen_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['sent_to_kitchen_at', 'kitchen_seen_at']);
        });
    }
};
