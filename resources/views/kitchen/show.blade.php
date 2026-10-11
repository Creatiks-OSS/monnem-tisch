@extends('layouts.kitchen')
@section('title', $order->order_number)
@section('body')
@php
    $days = $kitchen->daysUntilEvent($order);
    $urgent = ($days ?? 9) <= 0;
    $soon = $days === 1;
    $badge = $urgent
        ? 'bg-[var(--k-today-soft)] text-[var(--k-terracotta)]'
        : ($soon ? 'bg-[var(--k-sage-soft)] text-[var(--k-sage-deep)]' : 'bg-[var(--k-gold-soft)] text-[var(--k-gold)]');
@endphp
<div class="min-h-screen pb-28" x-data="kitchenPreview()">
    <header class="border-b border-[var(--k-line)] bg-[var(--k-paper)]/90 backdrop-blur">
        <div class="mx-auto flex max-w-4xl items-center justify-between px-5 py-4 lg:px-8">
            <a href="{{ route('kitchen.board') }}" class="text-sm font-medium text-[var(--k-sage)] hover:text-[var(--k-sage-deep)]">← Pano</a>
            <span class="rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide {{ $badge }}">
                {{ $kitchen->urgencyLabel($order) }}
            </span>
        </div>
    </header>

    <main class="mx-auto max-w-4xl px-5 py-8 lg:px-8">
        <p class="font-mono text-xs text-[var(--k-gold)]">{{ $order->order_number }}</p>
        <div class="mt-2 flex flex-wrap items-end justify-between gap-4">
            <h1 class="k-brand text-5xl leading-none text-[var(--k-ink)]">{{ $order->persons }} <span class="text-2xl text-[var(--k-muted)]">kişi</span></h1>
            <p class="text-lg text-[var(--k-ink)]">
                {{ optional($order->event_date)->format('d.m.Y') }}
                @if($order->event_time)
                    <span class="text-[var(--k-sage)]">{{ \Illuminate\Support\Str::of($order->event_time)->substr(0, 5) }}</span>
                @endif
            </p>
        </div>
        @if($order->eventType)
            <p class="mt-2 text-sm text-[var(--k-muted)]">{{ $order->eventType->name }}</p>
        @endif

        <div class="mt-8 grid gap-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-[var(--k-line)] bg-[var(--k-paper)] p-5 text-sm shadow-sm">
                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-[var(--k-sage)]">Teslimat</p>
                <p class="mt-2 font-medium text-[var(--k-ink)]">{{ trim($order->shipping_street.' '.$order->shipping_house_number) ?: '—' }}</p>
                <p class="text-[var(--k-muted)]">{{ trim($order->shipping_postal_code.' '.$order->shipping_city) }}</p>
            </div>
            <div class="rounded-2xl border border-[var(--k-line)] bg-[var(--k-paper)] p-5 text-sm shadow-sm">
                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-[var(--k-sage)]">Not</p>
                <p class="mt-2 text-[var(--k-ink)]">{{ $order->notes ?: $order->admin_notes ?: '—' }}</p>
            </div>
        </div>

        <h2 class="mt-10 text-[11px] font-semibold uppercase tracking-[0.18em] text-[var(--k-muted)]">Speisen</h2>
        <div class="mt-3 space-y-4">
            @foreach($order->items as $item)
                @if(! $kitchen->isKitchenLine($item))
                    @continue
                @endif
                <article class="overflow-hidden rounded-2xl border border-[var(--k-line)] bg-[var(--k-paper)] shadow-sm">
                    <div class="flex items-center gap-4 p-4 sm:p-5">
                        @if ($kitchen->lineImage($item) !== '')
                            <button type="button" class="shrink-0" @click="open(@js($kitchen->lineImage($item)), @js($item->name))">
                                <img src="{{ $kitchen->lineImage($item) }}" alt="{{ $item->name }}" class="h-20 w-20 rounded-2xl object-cover sm:h-24 sm:w-24">
                            </button>
                        @endif
                        <div class="min-w-0">
                            <p class="text-lg font-semibold text-[var(--k-ink)]">{{ $item->name }}</p>
                            @if($item->persons)
                                <p class="mt-0.5 text-sm text-[var(--k-muted)]">{{ $item->persons }} kişi</p>
                            @endif
                        </div>
                    </div>
                    @if(!empty($item->options['selections']))
                        <div class="space-y-5 border-t border-[var(--k-line)] px-4 py-5 sm:px-5">
                            @foreach($item->options['selections'] as $sel)
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-[var(--k-terracotta)]">{{ $sel['slot'] ?? '' }}</p>
                                    <ul class="mt-3 grid gap-3 sm:grid-cols-2">
                                        @foreach($sel['products'] ?? [] as $product)
                                            <li class="flex items-center gap-3 rounded-xl bg-[var(--k-bg)] p-2.5">
                                                @if ($kitchen->productImage($product) !== '')
                                                    <button type="button" class="shrink-0" @click="open(@js($kitchen->productImage($product)), @js($product['name'] ?? ''))">
                                                        <img src="{{ $kitchen->productImage($product) }}" alt="{{ $product['name'] ?? '' }}" class="h-16 w-16 rounded-xl object-cover sm:h-[4.5rem] sm:w-[4.5rem]">
                                                    </button>
                                                @endif
                                                <span class="text-sm font-medium leading-snug text-[var(--k-ink)]">{{ $product['name'] ?? '' }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </article>
            @endforeach
        </div>

        @if($order->extras->isNotEmpty())
            <h2 class="mt-10 text-[11px] font-semibold uppercase tracking-[0.18em] text-[var(--k-muted)]">Extras</h2>
            <ul class="mt-3 grid gap-3 sm:grid-cols-2">
                @foreach($order->extras as $extra)
                    <li class="flex items-center gap-3 rounded-2xl border border-[var(--k-line)] bg-[var(--k-paper)] p-3 shadow-sm">
                        <button type="button" class="shrink-0" @click="open(@js($kitchen->extraImage($extra)), @js($extra->name))">
                            <img src="{{ $kitchen->extraImage($extra) }}" alt="{{ $extra->name }}" class="h-16 w-16 rounded-xl object-cover">
                        </button>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-[var(--k-ink)]">{{ $extra->name }}</p>
                            <p class="text-xs text-[var(--k-muted)]">{{ $extra->quantity }}×</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </main>

    <div class="fixed inset-x-0 bottom-0 z-30 border-t border-[var(--k-line)] bg-[var(--k-paper)]/95 px-5 py-4 backdrop-blur">
        <form method="POST" action="{{ route('kitchen.complete', $order) }}" class="mx-auto max-w-4xl">
            @csrf
            <button type="submit" class="k-complete">Sipariş tamamlandı</button>
        </form>
    </div>

    <div class="k-overlay" x-show="preview" x-cloak style="display:flex;background:rgba(44,38,31,.72)" @click="preview = null" @keydown.escape.window="preview = null">
        <div class="k-dialog" style="max-width:36rem" @click.stop x-show="preview">
            <img :src="preview && preview.src" :alt="preview && preview.name" style="display:block;width:100%;max-height:70vh;object-fit:cover;background:var(--k-gold-soft)">
            <div class="k-dialog-body">
                <p style="margin:0;font-size:1.15rem;font-weight:650" x-text="preview && preview.name"></p>
                <button type="button" class="k-complete" style="margin-top:1rem" @click="preview = null">Kapat</button>
            </div>
        </div>
    </div>
</div>
@endsection
