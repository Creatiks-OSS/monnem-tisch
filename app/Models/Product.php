<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'short_description',
        'description',
        'image',
        'price',
        'price_type',
        'minimum_quantity',
        'minimum_persons',
        'maximum_persons',
        'ingredients',
        'allergens',
        'nutritional_info',
        'calories',
        'is_vegan',
        'is_vegetarian',
        'is_gluten_free',
        'is_lactose_free',
        'is_spicy',
        'is_featured',
        'is_bestseller',
        'is_active',
        'sort_order',
        'seo_title',
        'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'minimum_quantity' => 'integer',
            'minimum_persons' => 'integer',
            'maximum_persons' => 'integer',
            'allergens' => 'array',
            'nutritional_info' => 'array',
            'calories' => 'integer',
            'is_vegan' => 'boolean',
            'is_vegetarian' => 'boolean',
            'is_gluten_free' => 'boolean',
            'is_lactose_free' => 'boolean',
            'is_spicy' => 'boolean',
            'is_featured' => 'boolean',
            'is_bestseller' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug) && ! empty($product->name)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function priceTiers(): HasMany
    {
        return $this->hasMany(ProductPriceTier::class)->orderBy('min_persons');
    }

    public function options(): HasMany
    {
        return $this->hasMany(ProductOption::class)->orderBy('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function packageItems(): HasMany
    {
        return $this->hasMany(PackageItem::class);
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(CateringPackage::class, 'package_items', 'product_id', 'package_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function getPriceForPersons(int $persons): float
    {
        $tier = $this->priceTiers
            ->first(function (ProductPriceTier $tier) use ($persons) {
                return $persons >= $tier->min_persons
                    && ($tier->max_persons === null || $persons <= $tier->max_persons);
            });

        if ($tier) {
            return (float) $tier->price;
        }

        return (float) $this->price;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForMenuDiet(Builder $query, ?string $menu): Builder
    {
        return match ($menu) {
            'vegan' => $query->where('is_vegan', true),
            'vegetarisch' => $query->where(function (Builder $q) {
                $q->where('is_vegetarian', true)->orWhere('is_vegan', true);
            }),
            default => $query,
        };
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeBestseller(Builder $query): Builder
    {
        return $query->where('is_bestseller', true);
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            $url = $this->displayImage();

            return $url !== '' ? $url : null;
        });
    }

    /**
     * @return list<string>
     */
    public function allergenCodes(): array
    {
        return app(\App\Services\AllergenCatalog::class)->forProduct($this);
    }

    /**
     * @return list<array{code: string, label: string}>
     */
    public function allergenBadges(): array
    {
        return app(\App\Services\AllergenCatalog::class)->badges($this->allergenCodes());
    }

    public function displayImage(): string
    {
        foreach ($this->imageCandidates() as $path) {
            if (public_media_file($path)) {
                return public_media_url($path) ?: '';
            }
        }

        $path = $this->imageCandidates()[0] ?? null;

        return $path ? (public_media_url($path) ?: '') : '';
    }

    /**
     * @return list<string>
     */
    public function imageCandidates(): array
    {
        $main = trim((string) ($this->image ?? ''));
        $gallery = $this->images
            ->pluck('path')
            ->map(fn ($path) => trim((string) $path))
            ->filter()
            ->all();

        $isUpload = static function (string $path): bool {
            $relative = ltrim(str_replace('\\', '/', $path), '/');

            return $relative !== ''
                && ! str_contains($relative, 'placeholders')
                && ! str_starts_with($relative, 'images/');
        };

        $out = [];
        if ($main !== '' && $isUpload($main)) {
            $out[] = $main;
        }
        foreach ($gallery as $path) {
            if ($path !== '' && ! str_contains($path, 'placeholders')) {
                $out[] = $path;
            }
        }
        if ($main !== '' && ! str_contains($main, 'placeholders')) {
            $out[] = $main;
        }

        return array_values(array_unique($out));
    }
}
