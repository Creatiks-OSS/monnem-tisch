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
        Schema::create('page_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->enum('type', [
                'hero',
                'trust_bar',
                'text_image',
                'cards',
                'products',
                'categories',
                'testimonials',
                'gallery',
                'faq',
                'cta',
                'features',
                'stats',
                'logo_cloud',
                'contact',
                'map',
                'blog',
                'custom_html',
                'configurator',
                'delivery_areas',
                'why_us',
            ]);
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->longText('content')->nullable();
            $table->json('settings')->nullable();
            $table->string('image')->nullable();
            $table->string('background')->nullable();
            $table->string('padding')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('page_id');
            $table->index('type');
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_sections');
    }
};
