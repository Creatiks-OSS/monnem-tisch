<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catering_packages', function (Blueprint $table) {
            $table->string('menu_group', 40)->default('buffets')->after('slug');
            $table->index('menu_group');
        });
    }

    public function down(): void
    {
        Schema::table('catering_packages', function (Blueprint $table) {
            $table->dropIndex(['menu_group']);
            $table->dropColumn('menu_group');
        });
    }
};
