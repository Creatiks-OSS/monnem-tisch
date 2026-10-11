<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Extra extends Model
{
    use HasFactory, SoftDeletes;

    public const PRICE_TYPES = [
        'stück' => 'Adet',
        'person' => 'Kişi başı',
        'fixed' => 'Sabit fiyat',
        'hour' => 'Saatlik',
    ];

    protected $fillable = [
        'name',
        'slug',
        'kind',
        'description',
        'image',
        'price',
        'price_type',
        'minimum_quantity',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'minimum_quantity' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Extra $extra) {
            if (empty($extra->slug) && ! empty($extra->name)) {
                $extra->slug = Str::slug($extra->name);
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeProducts(Builder $query): Builder
    {
        return $query->where('kind', 'product');
    }

    public function scopeServices(Builder $query): Builder
    {
        return $query->where('kind', '!=', 'product');
    }

    public function usesStandardTax(): bool
    {
        return ($this->kind ?? 'service') === 'product';
    }

    public function taxRate(): float
    {
        return $this->usesStandardTax() ? shop_standard_tax_rate() : shop_tax_rate();
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (empty($this->image)) {
                return null;
            }

            return public_media_url($this->image);
        });
    }

    public function priceTypeLabel(): string
    {
        return self::PRICE_TYPES[$this->price_type] ?? (string) $this->price_type;
    }

    public function priceTypeSuffix(): string
    {
        return extra_price_suffix($this->price_type);
    }

    public function formattedPrice(): string
    {
        $price = number_format((float) $this->price, 2, ',', '.').' €';
        $suffix = $this->priceTypeSuffix();

        return $suffix !== '' ? $price.' '.$suffix : $price;
    }

    public function displayImage(): string
    {
        $url = $this->image_url;
        $generic = velora_photo('cheers');
        $isGeneric = ! $url
            || str_contains((string) $url, 'placeholders')
            || $url === $generic;

        if (! $isGeneric) {
            return $url;
        }

        return velora_drink_photo($this->name)
            ?: velora_photo_for($this->name, (int) $this->id);
    }
}
