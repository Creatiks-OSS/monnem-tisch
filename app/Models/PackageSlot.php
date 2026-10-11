<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackageSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'product_category_id',
        'name',
        'required_count',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'required_count' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(CateringPackage::class, 'package_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PackageItem::class, 'slot_id');
    }

    public function selectableProducts()
    {
        $this->loadMissing('package');
        $menu = $this->package?->menu_group;

        $assigned = $this->items()->with('product.images')->get()->pluck('product')->filter();

        if ($assigned->isNotEmpty()) {
            return $assigned
                ->where('is_active', true)
                ->filter(fn ($product) => $this->matchesMenuDiet($product, $menu))
                ->values();
        }

        if (! $this->product_category_id) {
            return collect();
        }

        return Product::query()
            ->with('images')
            ->active()
            ->forMenuDiet($menu)
            ->where('category_id', $this->product_category_id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    protected function matchesMenuDiet($product, ?string $menu): bool
    {
        return match ($menu) {
            'vegan' => (bool) $product->is_vegan,
            'vegetarisch' => (bool) ($product->is_vegetarian || $product->is_vegan),
            default => true,
        };
    }
}
