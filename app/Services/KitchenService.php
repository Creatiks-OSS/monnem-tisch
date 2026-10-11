<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderExtra;
use App\Models\OrderItem;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class KitchenService
{
    public const SESSION_KEY = 'kitchen_authenticated';

    public const LEAD_DAYS = 2;

    public function __construct(
        protected SettingService $settings
    ) {}

    public function pinIsConfigured(): bool
    {
        return $this->pinHash() !== '';
    }

    public function setPin(string $pin): void
    {
        Setting::set('pin_hash', Hash::make($this->normalizePin($pin)), 'kitchen', 'string');
        $this->settings->forgetCache('kitchen');
    }

    public function verifyPin(string $pin): bool
    {
        $hash = $this->pinHash();
        if ($hash === '') {
            return false;
        }

        $normalized = $this->normalizePin($pin);

        return Hash::check($normalized, $hash);
    }

    public function visibleQuery(): Builder
    {
        $from = now()->toDateString();
        $until = now()->addDays(self::LEAD_DAYS)->toDateString();

        return Order::query()
            ->placed()
            ->whereNull('kitchen_done_at')
            ->whereNotIn('status', ['storniert', 'geliefert', 'abgeschlossen'])
            ->where(function (Builder $query) use ($from, $until) {
                $query->whereNotNull('sent_to_kitchen_at')
                    ->orWhere(function (Builder $auto) use ($from, $until) {
                        $auto->whereNotNull('event_date')
                            ->whereDate('event_date', '>=', $from)
                            ->whereDate('event_date', '<=', $until);
                    });
            });
    }

    public function visibleIds(): array
    {
        return $this->visibleQuery()
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function unackedIds(): array
    {
        return $this->visibleQuery()
            ->whereNull('kitchen_seen_at')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function send(Order $order): Order
    {
        if (! $order->sent_to_kitchen_at) {
            $order->forceFill(['sent_to_kitchen_at' => now()])->save();
        }

        return $order->fresh();
    }

    public function markSeen(Order $order): void
    {
        if ($order->kitchen_seen_at) {
            return;
        }

        $order->forceFill(['kitchen_seen_at' => now()])->save();
    }

    public function complete(Order $order): Order
    {
        $payload = [
            'kitchen_seen_at' => $order->kitchen_seen_at ?? now(),
            'kitchen_done_at' => $order->kitchen_done_at ?? now(),
        ];

        if ($order->status !== 'storniert') {
            $payload['status'] = 'abgeschlossen';
            if (! $order->delivered_at) {
                $payload['delivered_at'] = now();
            }
        }

        $order->forceFill($payload)->save();

        return $order->fresh();
    }

    public function daysUntilEvent(Order $order): ?int
    {
        if (! $order->event_date) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($order->event_date->copy()->startOfDay(), false);
    }

    public function urgencyLabel(Order $order): string
    {
        $days = $this->daysUntilEvent($order);
        if ($days === null) {
            return 'Tarih yok';
        }
        if ($days < 0) {
            return 'Geçti';
        }
        if ($days === 0) {
            return 'Bugün';
        }
        if ($days === 1) {
            return 'Yarın';
        }

        return $days.' gün';
    }

    public function incomingOrders()
    {
        return $this->visibleQuery()
            ->whereNull('kitchen_seen_at')
            ->with(['items.product', 'extras.extra', 'eventType'])
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return list<array{id: int, number: string, menu: string, event: ?string, persons: int, when: string, urgency: string, photos: list<array{name: string, image: string}>, acceptUrl: string, showUrl: string}>
     */
    public function incomingPayload(): array
    {
        return $this->incomingOrders()
            ->map(fn (Order $order) => $this->incomingCard($order))
            ->values()
            ->all();
    }

    /**
     * @return array{id: int, number: string, menu: string, event: ?string, persons: int, when: string, urgency: string, photos: list<array{name: string, image: string}>, acceptUrl: string, showUrl: string}
     */
    public function incomingCard(Order $order): array
    {
        $menu = $order->items->first(fn ($item) => $this->isKitchenLine($item));
        $time = $order->event_time
            ? \Illuminate\Support\Str::of((string) $order->event_time)->substr(0, 5)
            : '';

        return [
            'id' => (int) $order->id,
            'number' => (string) $order->order_number,
            'menu' => $menu?->name ?: 'Menü',
            'event' => $order->eventType?->name,
            'persons' => (int) $order->persons,
            'when' => trim((optional($order->event_date)->format('d.m.Y') ?: '').' '.$time),
            'urgency' => $this->urgencyLabel($order),
            'photos' => array_slice($this->dishPhotos($order), 0, 6),
            'acceptUrl' => route('kitchen.accept', $order),
            'showUrl' => route('kitchen.show', $order),
        ];
    }

    public function acceptAll(): int
    {
        $count = 0;
        foreach ($this->incomingOrders() as $order) {
            $this->markSeen($order);
            $count++;
        }

        return $count;
    }

    public function isKitchenLine(OrderItem $item): bool
    {
        return ! in_array($item->options['kind'] ?? $item->sku, ['delivery', 'tip', 'DELIVERY', 'TIP'], true);
    }

    public function lineImage(OrderItem $item): string
    {
        if ($item->product) {
            return $item->product->displayImage();
        }

        $path = $item->options['package_image'] ?? $item->options['image'] ?? null;
        if (! is_string($path) || trim($path) === '' || str_contains($path, 'placeholders')) {
            return '';
        }

        return velora_media_url($path, $item->name, (int) $item->id);
    }

    public function productImage(array $product): string
    {
        $path = $product['image'] ?? null;
        if (! is_string($path) || trim($path) === '' || str_contains($path, 'placeholders')) {
            return '';
        }

        return velora_media_url($path, $product['name'] ?? '', (int) ($product['id'] ?? 0));
    }

    public function extraImage(OrderExtra $extra): string
    {
        return $extra->extra?->displayImage()
            ?? velora_photo_for($extra->name, (int) ($extra->extra_id ?? $extra->id));
    }

    /**
     * @return list<array{name: string, image: string}>
     */
    public function dishPhotos(Order $order): array
    {
        $photos = [];
        $seen = [];

        foreach ($order->items as $item) {
            if (! $this->isKitchenLine($item)) {
                continue;
            }

            $hadProducts = false;
            foreach ($item->options['selections'] ?? [] as $sel) {
                foreach ($sel['products'] ?? [] as $product) {
                    $hadProducts = true;
                    $name = (string) ($product['name'] ?? '');
                    $image = $this->productImage($product);
                    if ($image === '') {
                        continue;
                    }
                    $key = mb_strtolower($name).'|'.$image;
                    if (isset($seen[$key])) {
                        continue;
                    }
                    $seen[$key] = true;
                    $photos[] = ['name' => $name, 'image' => $image];
                }
            }

            if (! $hadProducts) {
                $image = $this->lineImage($item);
                if ($image === '') {
                    continue;
                }
                $key = mb_strtolower($item->name).'|'.$image;
                if (! isset($seen[$key])) {
                    $seen[$key] = true;
                    $photos[] = ['name' => $item->name, 'image' => $image];
                }
            }
        }

        foreach ($order->extras as $extra) {
            $image = $this->extraImage($extra);
            $key = mb_strtolower((string) $extra->name).'|'.$image;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $photos[] = ['name' => $extra->name, 'image' => $image];
        }

        return $photos;
    }

    protected function pinHash(): string
    {
        return trim($this->settings->string('pin_hash', '', 'kitchen'));
    }

    protected function normalizePin(string $pin): string
    {
        return strtoupper(trim($pin));
    }
}
