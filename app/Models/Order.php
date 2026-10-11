<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'customer_id',
        'user_id',
        'status',
        'payment_method',
        'payment_status',
        'event_type_id',
        'event_date',
        'event_time',
        'persons',
        'subtotal',
        'delivery_fee',
        'delivery_km',
        'service_fee',
        'tip_amount',
        'discount',
        'tax_rate',
        'tax_amount',
        'tax_standard_rate',
        'tax_standard_amount',
        'total',
        'currency',
        'coupon_code',
        'billing_company',
        'billing_first_name',
        'billing_last_name',
        'billing_email',
        'billing_phone',
        'billing_street',
        'billing_house_number',
        'billing_postal_code',
        'billing_city',
        'billing_country',
        'shipping_company',
        'shipping_first_name',
        'shipping_last_name',
        'shipping_phone',
        'shipping_street',
        'shipping_house_number',
        'shipping_postal_code',
        'shipping_city',
        'shipping_country',
        'notes',
        'admin_notes',
        'confirmed_at',
        'delivered_at',
        'sent_to_kitchen_at',
        'kitchen_seen_at',
        'kitchen_done_at',
        'remarketing_sent_at',
        'coupon_redeemed_at',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'persons' => 'integer',
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'delivery_km' => 'decimal:2',
            'service_fee' => 'decimal:2',
            'tip_amount' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'tax_standard_rate' => 'decimal:2',
            'tax_standard_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'confirmed_at' => 'datetime',
            'delivered_at' => 'datetime',
            'sent_to_kitchen_at' => 'datetime',
            'kitchen_seen_at' => 'datetime',
            'kitchen_done_at' => 'datetime',
            'remarketing_sent_at' => 'datetime',
            'coupon_redeemed_at' => 'datetime',
        ];
    }

    public function scopeAbandoned(Builder $query): Builder
    {
        return $query
            ->whereIn('payment_method', ['stripe', 'paypal'])
            ->whereIn('payment_status', ['pending', 'failed'])
            ->where('status', 'neu');
    }

    public function scopePlaced(Builder $query): Builder
    {
        return $query->where(function (Builder $placed) {
            $placed->whereNotIn('payment_method', ['stripe', 'paypal'])
                ->orWhereNotIn('payment_status', ['pending', 'failed'])
                ->orWhere('status', '!=', 'neu');
        });
    }

    public function isAbandoned(): bool
    {
        return in_array($this->payment_method, ['stripe', 'paypal'], true)
            && in_array($this->payment_status, ['pending', 'failed'], true)
            && $this->status === 'neu';
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = "VEL-{$date}-";

        do {
            $number = $prefix.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (static::withTrashed()->where('order_number', $number)->exists());

        return $number;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function extras(): HasMany
    {
        return $this->hasMany(OrderExtra::class);
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function ingredientAnalysis(): HasOne
    {
        return $this->hasOne(OrderIngredientAnalysis::class);
    }
}
