@props([
    'cartCount' => 0,
    'cartTotal' => null,
])

<div
    class="fixed inset-x-0 bottom-0 z-40 border-t border-beige bg-cream/95 px-4 py-3 backdrop-blur md:hidden"
    x-data="{ count: {{ (int) $cartCount }}, total: {{ Js::from($cartTotal) }} }"
    @velora:cart-updated.window="count = $event.detail?.totals?.item_count ?? count; total = $event.detail?.totals?.total ?? total"
    x-show="count > 0"
    x-cloak
>
    <div class="flex items-center justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-[0.14em] text-muted">{{ __('frontend.nav.cart') }}</p>
            <p class="text-lg font-bold text-bronze">
                <span x-text="count"></span> ·
                <span x-text="total != null ? window.Velora.formatMoney(total) : '—'"></span>
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('checkout.index') }}" class="btn-secondary !px-3 !py-2 text-xs">Zur Kasse</a>
            <a href="{{ route('checkout.index') }}" class="btn-primary !px-3 !py-2 text-xs">{{ __('frontend.cta.checkout') }}</a>
        </div>
    </div>
</div>
