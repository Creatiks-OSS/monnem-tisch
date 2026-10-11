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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('quote_number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', [
                'neu',
                'kontaktiert',
                'angebot_erstellt',
                'verhandlung',
                'gewonnen',
                'verloren',
            ])->default('neu');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('company')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->foreignId('event_type_id')->nullable()->constrained('event_types')->nullOnDelete();
            $table->date('event_date')->nullable();
            $table->time('event_time')->nullable();
            $table->unsignedInteger('persons')->nullable();
            $table->string('location')->nullable();
            $table->decimal('budget', 10, 2)->nullable();
            $table->text('catering_wunsch')->nullable();
            $table->text('message')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->date('valid_until')->nullable();
            $table->string('source')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('quote_number');
            $table->index('status');
            $table->index('customer_id');
            $table->index('assigned_user_id');
            $table->index('event_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
