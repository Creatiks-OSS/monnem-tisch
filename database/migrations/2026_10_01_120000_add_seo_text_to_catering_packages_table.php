<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catering_packages', function (Blueprint $table) {
            $table->longText('seo_text')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('catering_packages', function (Blueprint $table) {
            $table->dropColumn('seo_text');
        });
    }
};
