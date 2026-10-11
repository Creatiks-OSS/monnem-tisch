<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'data',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->forceFill(['read_at' => now()])->save();
        }
    }

    public function targetUrl(): string
    {
        $data = $this->data ?? [];

        if (! empty($data['url'])) {
            return (string) $data['url'];
        }

        try {
            return match ($this->type) {
                'order.created' => ! empty($data['order_id'])
                    ? route('admin.orders.show', $data['order_id'])
                    : route('admin.orders.index'),
                'order.paid' => ! empty($data['order_id'])
                    ? route('admin.orders.show', $data['order_id'])
                    : route('admin.orders.index'),
                'quote.created' => ! empty($data['quote_id'])
                    ? route('admin.quotes.show', $data['quote_id'])
                    : route('admin.quotes.index'),
                'contact.created' => ! empty($data['contact_message_id'])
                    ? route('admin.contact-messages.show', $data['contact_message_id'])
                    : route('admin.contact-messages.index'),
                default => route('admin.notifications.index'),
            };
        } catch (\Throwable) {
            return route('admin.notifications.index');
        }
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'order.created' => 'Sipariş',
            'order.paid' => 'Ödeme',
            'quote.created' => 'Teklif',
            'contact.created' => 'İletişim',
            default => 'Bildirim',
        };
    }
}
