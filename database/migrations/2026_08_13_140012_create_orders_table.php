<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', [
                'neu',
                'bestätigt',
                'in_bearbeitung',
                'vorbereitung',
                'unterwegs',
                'geliefert',
                'abgeschlossen',
                'storniert',
            ])->default('neu');
            $table->enum('payment_method', ['rechnung', 'anfrage', 'stripe'])->default('anfrage');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->foreignId('event_type_id')->nullable()->constrained('event_types')->nullOnDelete();
            $table->date('event_date')->nullable();
            $table->time('event_time')->nullable();
            $table->unsignedInteger('persons')->default(1);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax_rate', 10, 2)->default(19.00);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('currency', 3)->default('EUR');
            $table->string('coupon_code')->nullable();

            $table->string('billing_company')->nullable();
            $table->string('billing_first_name')->nullable();
            $table->string('billing_last_name')->nullable();
            $table->string('billing_email')->nullable();
            $table->string('billing_phone')->nullable();
            $table->string('billing_street')->nullable();
            $table->string('billing_house_number')->nullable();
            $table->string('billing_postal_code', 20)->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_country', 2)->default('DE');

            $table->string('shipping_company')->nullable();
            $table->string('shipping_first_name')->nullable();
            $table->string('shipping_last_name')->nullable();
            $table->string('shipping_phone')->nullable();
            $table->string('shipping_street')->nullable();
            $table->string('shipping_house_number')->nullable();
            $table->string('shipping_postal_code', 20)->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_country', 2)->default('DE');

            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('order_number');
            $table->index('event_date');
            $table->index('customer_id');
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
