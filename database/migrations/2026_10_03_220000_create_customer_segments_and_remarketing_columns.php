<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_segments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_segment_customer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_segment_id')->constrained('customer_segments')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['customer_segment_id', 'customer_id'], 'segment_customer_unique');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('remarketing_sent_at')->nullable();
            $table->timestamp('coupon_redeemed_at')->nullable();
        });

        DB::table('customer_segments')->insert([
            'name' => 'Sepette ürün bırakanlar',
            'slug' => 'cart-left',
            'description' => 'Ödemeyi tamamlamadan sepetini bırakan müşteriler. Satın alma tamamlanınca listeden çıkar.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['remarketing_sent_at', 'coupon_redeemed_at']);
        });

        Schema::dropIfExists('customer_segment_customer');
        Schema::dropIfExists('customer_segments');
    }
};
