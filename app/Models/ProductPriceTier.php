<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPriceTier extends Model
{
    use HasFactory;

    protected $table = 'product_price_tiers';

    protected $fillable = [
        'product_id',
        'min_persons',
        'max_persons',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'min_persons' => 'integer',
            'max_persons' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
