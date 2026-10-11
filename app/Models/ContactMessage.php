<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'event_type',
        'event_date',
        'message',
        'source',
        'gclid',
        'is_read',
        'status',
        'gdpr_consent',
        'quote_id',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'is_read' => 'boolean',
            'gdpr_consent' => 'boolean',
        ];
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }
}
