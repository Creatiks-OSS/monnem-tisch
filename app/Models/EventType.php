<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EventType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'is_active',
        'sort_order',
        'seo_title',
        'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (EventType $eventType) {
            if (empty($eventType->slug) && ! empty($eventType->name)) {
                $eventType->slug = Str::slug($eventType->name);
            }
        });
    }

    public function packages(): HasMany
    {
        return $this->hasMany(CateringPackage::class);
    }

    public function packageItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            PackageItem::class,
            CateringPackage::class,
            'event_type_id',
            'package_id',
            'id',
            'id'
        );
    }

    /**
     * Products included in packages for this event type.
     *
     * @return Builder<Product>
     */
    public function products(): Builder
    {
        return Product::query()
            ->whereHas('packages', function (Builder $query) {
                $query->where('event_type_id', $this->getKey());
            });
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
