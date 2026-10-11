@props([
    'cartCount' => 0,
    'phone' => null,
    'transparent' => false,
])

@php
    try {
        $cart = app(\App\Services\CartService::class);
        $totals = $cart->totals();
        $cartCount = (int) ($totals['item_count'] ?? $cartCount);
    } catch (\Throwable $e) {
        $sessionCart = session('cart', []);
        $cartCount = is_array($sessionCart['items'] ?? null) ? count($sessionCart['items']) : (int) $cartCount;
    }

    $phone = $phone ?: ($sitePhone ?? setting('phone', '', 'contact'));
    $menuNav = collect(\App\Models\CateringPackage::MENUS)->map(fn (array $item, string $key) => [
        'label' => $item['label'],
        'url' => route('packages.index', $key),
        'key' => $key,
    ])->values();
    $desktopNav = $menuNav->whereIn('key', ['buffets', 'fingerfood', 'fruehstueck', 'grillcatering', 'vegetarisch', 'vegan'])->values()->all();
    $mobileNav = $menuNav->map(fn (array $item) => [
        'label' => $item['label'],
        'url' => $item['url'],
    ])->push(['label' => 'Kontakt', 'url' => route('contact.show')])->all();
@endphp

<header
    x-data="siteHeader"
    @keydown.escape.window="mobileOpen = false; searchOpen = false"
    class="sticky top-0 z-50 border-b border-beige/80 bg-white/95 backdrop-blur"
    :class="scrolled ? 'shadow-soft' : ''"
>
    <div class="container-velora site-header-bar">
        <a href="{{ route('home') }}" class="relative z-10 shrink-0 self-center leading-none" @click="closeMobile()">
            @if(site_logo_url())
                <img src="{{ site_logo_url() }}" alt="{{ setting('company_name', 'Monnemtisch Catering', 'general') }}" class="h-11 w-auto max-w-[196px] object-contain object-left sm:h-12 sm:max-w-[220px]">
            @else
                <span class="block text-[1.15rem] font-bold tracking-tight text-charcoal">Monnemtisch</span>
                <span class="block text-[11px] text-muted">Catering Mannheim</span>
            @endif
        </a>

        <nav class="site-header-nav" aria-label="Hauptnavigation">
            @foreach ($desktopNav as $item)
                <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
            @endforeach
        </nav>

        <div class="site-header-actions">
            <a href="{{ route('checkout.index') }}" class="ht-cart relative inline-flex items-center justify-center text-white" aria-label="Kasse">
                <svg class="h-5 w-5 sm:h-[1.35rem] sm:w-[1.35rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l3-8H6.4M7 13 5.4 5M7 13l-1.2 6h12.4M10 19a1 1 0 1 0 0 2 1 1 0 0 0 0-2Zm7 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z"/></svg>
                <span
                    class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-bronze px-1 text-[10px] font-medium text-navy"
                    x-data="{ count: {{ (int) $cartCount }} }"
                    @velora:cart-updated.window="count = $event.detail?.totals?.item_count ?? count"
                    x-text="count"
                    x-show="count > 0"
                    x-cloak
                ></span>
            </a>
            <a href="{{ route('quote.create') }}" class="btn-primary hidden sm:inline-flex">Angebot erhalten</a>
            <button type="button" class="inline-flex rounded-lg p-2 text-charcoal lg:hidden" @click="mobileOpen = !mobileOpen" :aria-expanded="mobileOpen.toString()" aria-controls="mobile-nav" aria-label="Menü">
                <svg x-show="!mobileOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7h16M4 12h16M4 17h16"/></svg>
                <svg x-show="mobileOpen" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </div>
    </div>

    <div id="mobile-nav" x-show="mobileOpen" x-cloak class="absolute inset-x-0 top-full border-b border-beige bg-white shadow-soft lg:hidden">
        <nav class="container-velora flex flex-col py-4" aria-label="Mobile Navigation">
            @foreach ($mobileNav as $item)
                <a href="{{ $item['url'] }}" class="py-2.5 text-base" @click="closeMobile()">{{ $item['label'] }}</a>
            @endforeach
            <a href="{{ route('quote.create') }}" class="btn-primary mt-3" @click="closeMobile()">Angebot erhalten</a>
        </nav>
    </div>
</header>

<style>
.site-header-nav { justify-content: center; gap: 1.15rem; flex-wrap: nowrap; }
.site-header-nav a {
    padding: .35rem 0;
    font-size: .78rem;
    font-weight: 600;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: #3a3a3a;
}
.site-header-nav a:hover { color: #c9a96e; }
.site-header-actions .btn-primary { display: none; }
.ht-cart {
    width: 46px;
    height: 46px;
    border-radius: 999px;
    background: #111;
    color: #fff;
}
.ht-cart:hover { background: #c9a96e; color: #111; }
.ht-cart span { background: #c9a96e !important; color: #111 !important; }
@media (max-width: 1023px) {
    .ht-cart { width: 42px; height: 42px; }
}
</style>
