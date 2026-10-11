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
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('subject')->nullable();
            $table->string('event_type')->nullable();
            $table->date('event_date')->nullable();
            $table->text('message');
            $table->string('source')->nullable();
            $table->boolean('is_read')->default(false);
            $table->enum('status', ['neu', 'bearbeitet', 'archiviert'])->default('neu');
            $table->boolean('gdpr_consent')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->index('is_read');
            $table->index('status');
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
