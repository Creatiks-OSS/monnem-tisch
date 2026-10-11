<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OrderIngredientAnalysis extends Model
{
    protected $fillable = [
        'order_id',
        'status',
        'items',
        'totals',
        'warnings',
        'raw_response',
        'error_message',
        'analyzed_at',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'totals' => 'array',
            'warnings' => 'array',
            'analyzed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isReady(): bool
    {
        return $this->status === 'completed';
    }

    public function isBusy(): bool
    {
        return in_array($this->status, ['pending', 'processing'], true);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function itemFor(string $name): ?array
    {
        $needle = Str::lower(trim($name));

        foreach ($this->items ?? [] as $item) {
            if (Str::lower(trim((string) ($item['name'] ?? ''))) === $needle) {
                return $item;
            }
        }

        return null;
    }
}
