<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsSetting extends Model
{
    protected $fillable = [
        'ga_id',
        'gtm_id',
        'debug_mode',
        'tracking_enabled',
        'conversion_events',
    ];

    protected function casts(): array
    {
        return [
            'debug_mode' => 'boolean',
            'tracking_enabled' => 'boolean',
            'conversion_events' => 'array',
        ];
    }
}
