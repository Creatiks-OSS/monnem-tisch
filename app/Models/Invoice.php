<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    public const MANUAL_ACCOUNT_HOLDER = 'Anatolian Gourmet GmbH';

    public const MANUAL_IBAN = 'DE38240703680084378900';

    protected $fillable = [
        'invoice_number',
        'order_id',
        'quote_id',
        'customer_id',
        'is_manual',
        'billing_first_name',
        'billing_last_name',
        'billing_company',
        'billing_email',
        'billing_phone',
        'billing_street',
        'billing_house_number',
        'billing_postal_code',
        'billing_city',
        'status',
        'issued_at',
        'due_at',
        'paid_at',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'tax_standard_rate',
        'tax_standard_amount',
        'total',
        'pdf_path',
        'notes',
        'line_items',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'due_at' => 'datetime',
            'paid_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'tax_standard_rate' => 'decimal:2',
            'tax_standard_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'is_manual' => 'boolean',
            'line_items' => 'array',
        ];
    }

    public function billingName(): string
    {
        $name = trim(($this->billing_first_name ?? '').' '.($this->billing_last_name ?? ''));

        return $name !== '' ? $name : (string) ($this->customer?->full_name ?? '');
    }

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
        });
    }

    public static function generateInvoiceNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = "RE-{$date}-";

        do {
            $number = $prefix.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (static::withTrashed()->where('invoice_number', $number)->exists());

        return $number;
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
