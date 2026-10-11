<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_settings', function (Blueprint $table) {
            $table->id();
            $table->string('ga_id')->nullable();
            $table->string('gtm_id')->nullable();
            $table->boolean('debug_mode')->default(false);
            $table->boolean('tracking_enabled')->default(true);
            $table->json('conversion_events')->nullable();
            $table->timestamps();
        });

        Schema::table('contact_messages', function (Blueprint $table) {
            $table->string('gclid', 255)->nullable()->after('source');
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->string('gclid', 255)->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('gclid');
        });

        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropColumn('gclid');
        });

        Schema::dropIfExists('analytics_settings');
    }
};
