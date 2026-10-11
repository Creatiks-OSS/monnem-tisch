<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'type',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function scopeIsBlocked(Builder $query, CarbonInterface|string $date): Builder
    {
        return $query->whereDate('date', $date);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'booked' => 'Rezerve',
            'special' => 'Tatil',
            default => 'Kapalı',
        };
    }
}
