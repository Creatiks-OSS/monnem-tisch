<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('tax_standard_rate', 10, 2)->default(19.00)->after('tax_amount');
            $table->decimal('tax_standard_amount', 10, 2)->default(0)->after('tax_standard_rate');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('tax_standard_rate', 10, 2)->default(19.00)->after('tax_amount');
            $table->decimal('tax_standard_amount', 10, 2)->default(0)->after('tax_standard_rate');
        });

        Schema::table('order_extras', function (Blueprint $table) {
            $table->string('kind', 20)->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['tax_standard_rate', 'tax_standard_amount']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['tax_standard_rate', 'tax_standard_amount']);
        });

        Schema::table('order_extras', function (Blueprint $table) {
            $table->dropColumn('kind');
        });
    }
};
