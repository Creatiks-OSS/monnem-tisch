<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryZone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'city',
        'postal_codes',
        'radius_km',
        'minimum_order',
        'delivery_fee',
        'free_delivery_threshold',
        'price_per_km',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'postal_codes' => 'array',
            'radius_km' => 'decimal:2',
            'minimum_order' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'free_delivery_threshold' => 'decimal:2',
            'price_per_km' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function matchesPostalCode(string $plz): bool
    {
        $plz = trim($plz);
        $codes = $this->postal_codes ?? [];

        if ($codes === [] || $codes === null) {
            return false;
        }

        foreach ($codes as $code) {
            $code = trim((string) $code);

            if ($code === $plz) {
                return true;
            }

            if (str_contains($code, '*') && fnmatch($code, $plz)) {
                return true;
            }

            if (str_ends_with($code, '-') && str_starts_with($plz, rtrim($code, '-'))) {
                return true;
            }
        }

        return false;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
