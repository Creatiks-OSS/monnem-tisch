<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CustomerSegment extends Model
{
    public const CART_LEFT = 'cart-left';

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'customer_segment_customer')->withTimestamps();
    }
}
