<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'day_of_week',
        'is_open',
        'open_time',
        'close_time',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_open' => 'boolean',
        ];
    }

    public function formattedOpen(): ?string
    {
        return $this->formatClock($this->open_time);
    }

    public function formattedClose(): ?string
    {
        return $this->formatClock($this->close_time);
    }

    public function hoursLabel(): string
    {
        if (! $this->is_open) {
            return 'Geschlossen';
        }

        $open = $this->formattedOpen();
        $close = $this->formattedClose();
        if ($open && $close) {
            return $open.' – '.$close;
        }

        return '–';
    }

    protected function formatClock(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_object($value) && method_exists($value, 'format')) {
            return $value->format('H:i');
        }

        $raw = substr((string) $value, 0, 5);

        return $raw !== '' ? $raw : null;
    }
}
