<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catering_packages', function (Blueprint $table) {
            $table->string('subtitle')->nullable()->after('name');
            $table->decimal('compare_price', 10, 2)->nullable()->after('price_per_person');
            $table->string('discount_label')->nullable()->after('compare_price');
            $table->string('badge')->nullable()->after('discount_label');
            $table->boolean('is_popular')->default(false)->after('is_featured');
            $table->json('highlights')->nullable()->after('description');
            $table->json('gallery')->nullable()->after('image');
            $table->string('cta_label')->nullable()->after('gallery');
            $table->string('trust_line')->nullable()->after('cta_label');
        });

        Schema::create('package_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('catering_packages')->cascadeOnDelete();
            $table->foreignId('product_category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('name');
            $table->unsignedInteger('required_count')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['package_id', 'sort_order']);
        });

        Schema::table('package_items', function (Blueprint $table) {
            $table->foreignId('slot_id')->nullable()->after('package_id')->constrained('package_slots')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('package_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('slot_id');
        });
        Schema::dropIfExists('package_slots');
        Schema::table('catering_packages', function (Blueprint $table) {
            $table->dropColumn([
                'subtitle',
                'compare_price',
                'discount_label',
                'badge',
                'is_popular',
                'highlights',
                'gallery',
                'cta_label',
                'trust_line',
            ]);
        });
    }
};
