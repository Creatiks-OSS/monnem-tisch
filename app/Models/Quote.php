<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'quote_number',
        'customer_id',
        'assigned_user_id',
        'status',
        'first_name',
        'last_name',
        'company',
        'email',
        'phone',
        'event_type_id',
        'event_date',
        'event_time',
        'persons',
        'location',
        'budget',
        'catering_wunsch',
        'message',
        'subtotal',
        'delivery_fee',
        'discount',
        'tax_amount',
        'total',
        'valid_until',
        'source',
        'gclid',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'persons' => 'integer',
            'budget' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'valid_until' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Quote $quote) {
            if (empty($quote->quote_number)) {
                $quote->quote_number = static::generateQuoteNumber();
            }
        });
    }

    public static function generateQuoteNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = "ANG-{$date}-";

        do {
            $number = $prefix.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (static::withTrashed()->where('quote_number', $number)->exists());

        return $number;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function contactMessage(): HasOne
    {
        return $this->hasOne(ContactMessage::class);
    }

    /**
     * @return list<array{rate: float, amount: float}>
     */
    public function taxLines(): array
    {
        $this->loadMissing('items');

        $foodNet = 0.0;
        $drinkNet = 0.0;

        foreach ($this->items as $item) {
            $kind = (string) ($item->options['kind'] ?? '');
            $net = (float) $item->line_total;
            if ($net <= 0) {
                continue;
            }
            if ($kind === 'extra_product' || (float) ($item->options['tax_rate'] ?? 0) >= 15) {
                $drinkNet += $net;
            } else {
                $foodNet += $net;
            }
        }

        if ($foodNet <= 0 && $drinkNet <= 0) {
            return tax_display_lines($this);
        }

        $foodTax = round($foodNet * (shop_tax_rate() / 100), 2);
        $drinkTax = round($drinkNet * (shop_standard_tax_rate() / 100), 2);

        return tax_display_lines([
            'tax_rate' => shop_tax_rate(),
            'tax_amount' => $foodTax,
            'tax_standard_rate' => shop_standard_tax_rate(),
            'tax_standard_amount' => $drinkTax,
        ]);
    }
}
