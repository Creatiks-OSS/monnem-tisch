@extends('layouts.kitchen')
@section('title', 'Mutfak panosu')
@section('body')
<div class="min-h-screen">
    <header class="sticky top-0 z-20 border-b border-[var(--k-line)] bg-[var(--k-paper)]/90 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-5 py-4 lg:px-8">
            <div class="min-w-0">
                @if(site_logo_url())
                    <img src="{{ site_logo_url() }}" alt="Monnemtisch Catering" class="mb-1 h-10 w-auto max-w-[200px] object-contain object-left">
                @else
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[var(--k-sage)]">Monnemtisch</p>
                @endif
                <h1 class="k-brand text-[2rem] leading-none text-[var(--k-ink)]">Mutfak</h1>
            </div>
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="hidden text-right sm:block">
                    <p class="font-mono text-sm tabular-nums text-[var(--k-ink)]" x-text="clock"></p>
                    <p class="text-[11px] text-[var(--k-muted)]">{{ $orders->count() }} sipariş</p>
                </div>
                <form method="POST" action="{{ route('kitchen.logout') }}">
                    @csrf
                    <button class="rounded-full border border-[var(--k-line)] bg-white px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--k-muted)]">Çıkış</button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-5 py-8 lg:px-8">
        @if($orders->isEmpty())
            <div class="rounded-3xl border border-[var(--k-line)] bg-[var(--k-paper)] px-8 py-20 text-center shadow-sm">
                <p class="k-brand text-4xl text-[var(--k-ink)]">Hazır sipariş yok</p>
            </div>
        @else
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($orders as $order)
                    @php
                        $days = $kitchen->daysUntilEvent($order);
                        $urgent = $days !== null && $days <= 0;
                        $soon = $days === 1;
                        $menuLine = $order->items->first(fn ($item) => $kitchen->isKitchenLine($item));
                        $waiting = ! $order->kitchen_seen_at;
                        $accent = $urgent ? 'bg-[var(--k-terracotta)]' : ($soon ? 'bg-[var(--k-sage)]' : 'bg-[var(--k-gold)]');
                        $badge = $urgent
                            ? 'bg-[var(--k-today-soft)] text-[var(--k-terracotta)]'
                            : ($soon ? 'bg-[var(--k-sage-soft)] text-[var(--k-sage-deep)]' : 'bg-[var(--k-gold-soft)] text-[var(--k-gold)]');
                        $photos = $kitchen->dishPhotos($order);
                        $photoCount = count($photos);
                        $shownPhotos = array_slice($photos, 0, 8);
                        $morePhotos = max(0, $photoCount - 8);
                        $photoCols = $photoCount <= 1 ? 1 : ($photoCount === 2 ? 2 : ($photoCount === 3 ? 3 : 4));
                    @endphp
                    <article class="relative overflow-hidden rounded-3xl border border-[var(--k-line)] bg-[var(--k-paper)] shadow-[0_12px_30px_rgba(44,38,31,0.06)] {{ $waiting ? 'ring-2 ring-[var(--k-terracotta)]/35' : '' }}">
                        <div class="pointer-events-none absolute inset-y-0 left-0 z-10 w-1.5 {{ $accent }}"></div>
                        <a href="{{ route('kitchen.show', $order) }}" class="block">
                            @if($shownPhotos)
                                <div class="grid gap-px bg-[var(--k-line)] pl-1.5" style="grid-template-columns: repeat({{ $photoCols }}, minmax(0, 1fr));">
                                    @foreach($shownPhotos as $index => $photo)
                                        <div class="relative {{ $photoCount === 1 ? 'h-40' : 'aspect-square' }} overflow-hidden bg-[var(--k-gold-soft)]">
                                            <img src="{{ $photo['image'] }}" alt="{{ $photo['name'] }}" class="h-full w-full object-cover transition duration-200 hover:scale-[1.04]">
                                            @if($index === count($shownPhotos) - 1 && $morePhotos > 0)
                                                <span class="absolute inset-0 flex items-center justify-center bg-[var(--k-ink)]/45 text-sm font-semibold text-white">+{{ $morePhotos }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            <div class="p-5 pl-6">
                                <div class="flex items-start justify-between gap-3">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide {{ $badge }}">
                                        {{ $kitchen->urgencyLabel($order) }}
                                    </span>
                                    @if($waiting)
                                        <span class="rounded-full bg-[var(--k-today-soft)] px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-[var(--k-terracotta)]">Yeni</span>
                                    @endif
                                </div>
                                <p class="mt-4 font-mono text-xs text-[var(--k-gold)]">{{ $order->order_number }}</p>
                                <p class="mt-2 text-lg font-semibold leading-snug text-[var(--k-ink)]">{{ $menuLine->name ?? 'Menü' }}</p>
                                <p class="mt-1 text-sm text-[var(--k-muted)]">{{ $order->eventType?->name ?: 'Event' }}</p>
                                <div class="mt-4 flex items-end justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-medium text-[var(--k-ink)]">
                                            {{ optional($order->event_date)->format('d.m.Y') ?: '—' }}
                                            @if($order->event_time)
                                                <span class="text-[var(--k-sage)]">{{ \Illuminate\Support\Str::of($order->event_time)->substr(0, 5) }}</span>
                                            @endif
                                        </p>
                                        <p class="mt-0.5 text-[11px] text-[var(--k-muted)]">{{ $order->sent_to_kitchen_at ? 'Manuel gönderim' : 'Otomatik' }}</p>
                                    </div>
                                    <p class="text-3xl font-semibold tabular-nums leading-none text-[var(--k-sage)]">{{ $order->persons }}<span class="ml-1 text-sm font-medium text-[var(--k-muted)]">kişi</span></p>
                                </div>
                            </div>
                        </a>
                        <form method="POST" action="{{ route('kitchen.complete', $order) }}" class="px-5 pb-5 pl-6">
                            @csrf
                            <button type="submit" class="k-complete">Sipariş tamamlandı</button>
                        </form>
                    </article>
                @endforeach
            </div>
        @endif
    </main>
</div>
@endsection
