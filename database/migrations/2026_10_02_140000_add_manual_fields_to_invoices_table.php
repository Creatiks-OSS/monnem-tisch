<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('is_manual')->default(false)->after('customer_id');
            $table->string('billing_first_name')->nullable()->after('is_manual');
            $table->string('billing_last_name')->nullable()->after('billing_first_name');
            $table->string('billing_company')->nullable()->after('billing_last_name');
            $table->string('billing_email')->nullable()->after('billing_company');
            $table->string('billing_phone')->nullable()->after('billing_email');
            $table->string('billing_street')->nullable()->after('billing_phone');
            $table->string('billing_house_number')->nullable()->after('billing_street');
            $table->string('billing_postal_code', 20)->nullable()->after('billing_house_number');
            $table->string('billing_city')->nullable()->after('billing_postal_code');
            $table->json('line_items')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'is_manual',
                'billing_first_name',
                'billing_last_name',
                'billing_company',
                'billing_email',
                'billing_phone',
                'billing_street',
                'billing_house_number',
                'billing_postal_code',
                'billing_city',
                'line_items',
            ]);
        });
    }
};
