<?php

namespace App\Models;

use App\Models\Concerns\HasSeo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class City extends Model
{
    use HasFactory, HasSeo, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'postal_code',
        'meta_title',
        'meta_description',
        'content',
        'delivery_price',
        'delivery_radius',
        'faq',
        'image',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'delivery_price' => 'decimal:2',
            'delivery_radius' => 'decimal:2',
            'faq' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (City $city) {
            if (empty($city->slug) && ! empty($city->name)) {
                $city->slug = Str::slug($city->name);
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
