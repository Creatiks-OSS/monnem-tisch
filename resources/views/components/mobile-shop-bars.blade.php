@php
    $count = (int) ($cartCount ?? 0);
    $total = (float) ($cartTotals['total'] ?? 0);
    $isHome = (bool) ($isHome ?? false);
    $isCats = request()->routeIs('packages.*');
    $isCart = request()->routeIs('cart.*', 'checkout.*');
    $isKonto = request()->routeIs('contact.*', 'quote.*');
@endphp

@if (! $isHome)
<div
    class="md:hidden"
    x-data="{ count: {{ $count }}, total: {{ $total }} }"
    @velora:cart-updated.window="count = $event.detail?.totals?.item_count ?? count; total = $event.detail?.totals?.total ?? total"
>
    <div
        class="bg-white"
        style="height: calc(5rem + env(safe-area-inset-bottom, 0px))"
        :style="'height: calc(' + (count > 0 ? '8.25rem' : '5rem') + ' + env(safe-area-inset-bottom, 0px))'"
        aria-hidden="true"
    ></div>
    <div
        class="fixed inset-x-3 z-40 overflow-hidden rounded-t-2xl bg-bronze text-navy shadow-lift"
        style="bottom: 4.15rem"
        x-show="count > 0"
        x-cloak
        x-transition.opacity.duration.150ms
    >
        <div class="flex items-center gap-3 px-3.5 py-2.5">
            <span class="relative inline-flex">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 3h2l.4 2M7 13h10l3-8H6.4M7 13 5.4 5M7 13l-1.2 6h12.4M10 19a1 1 0 1 0 0 2 1 1 0 0 0 0-2Zm7 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z"/></svg>
                <span class="absolute -right-1.5 -top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-navy px-1 text-[10px] font-bold text-bronze" x-text="count"></span>
            </span>
            <p class="min-w-0 flex-1 text-sm font-semibold leading-tight">
                <span x-text="count + ' Artikel · ' + Number(total).toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' €'"></span>
            </p>
            <a href="{{ route('checkout.index') }}" class="shrink-0 rounded-lg bg-navy px-3 py-1.5 text-xs font-bold text-white">Zur Kasse</a>
        </div>
    </div>

    <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-beige bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur" aria-label="Mobile Shop-Navigation">
        <div class="grid grid-cols-4 px-1 pt-1.5 pb-2 text-[10px] font-semibold text-muted">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-0.5 py-1 {{ $isHome ? 'text-bronze' : '' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 11.5 12 4l8 7.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1v-8.5Z"/></svg>
                Start
            </a>
            <a href="{{ $isHome ? '#mshop-cats' : route('packages.index', 'buffets') }}" class="flex flex-col items-center gap-0.5 py-1 {{ $isCats ? 'text-bronze' : '' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 4h7v7H4V4Zm9 0h7v7h-7V4ZM4 13h7v7H4v-7Zm9 0h7v7h-7v-7Z"/></svg>
                Kategorien
            </a>
            <a href="{{ route('checkout.index') }}" class="flex flex-col items-center gap-0.5 py-1 {{ $isCart ? 'text-bronze' : '' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m-9 0h10l.7 12H6.3L7 7Z"/></svg>
                Bestellungen
            </a>
            <a href="{{ route('contact.show') }}" class="flex flex-col items-center gap-0.5 py-1 {{ $isKonto ? 'text-bronze' : '' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm7 8a7 7 0 0 0-14 0"/></svg>
                Konto
            </a>
        </div>
    </nav>
</div>
@endif
