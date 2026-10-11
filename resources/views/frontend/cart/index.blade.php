@extends('layouts.frontend')

@section('title', __('frontend.cart.title').' – '.__('frontend.brand'))

@section('content')
@php
    $items = $cart['items'] ?? [];
    $extras = $cart['extras'] ?? [];
    $isEmpty = empty($items) && empty($extras);
@endphp

<div class="container-velora py-10 md:py-14" x-data="{
    cart: {{ Js::from($cart) }},
    totals: {{ Js::from($totals) }},
    extraImages: {{ Js::from($extraImages ?? ($availableExtras ?? collect())->mapWithKeys(fn ($e) => [(string) $e->id => $e->displayImage()])) }},
    coupon: '',
    message: '',
    error: '',
    async updateItem(key, quantity, persons) {
        try {
            const data = await window.Velora.fetchJson(`/warenkorb/item/${key}`, {
                method: 'PATCH',
                body: { quantity: Number(quantity), persons: persons ? Number(persons) : null },
            });
            this.cart = data.cart; this.totals = data.totals; this.message = data.message;
            window.dispatchEvent(new CustomEvent('velora:cart-updated', { detail: data }));
        } catch (e) { this.error = e.message; }
    },
    async removeItem(key) {
        try {
            const data = await window.Velora.fetchJson(`/warenkorb/item/${key}`, { method: 'DELETE' });
            this.cart = data.cart; this.totals = data.totals; this.message = data.message;
            window.dispatchEvent(new CustomEvent('velora:cart-updated', { detail: data }));
        } catch (e) { this.error = e.message; }
    },
    async addExtra(extraId) {
        try {
            const data = await window.Velora.fetchJson('/warenkorb/extras', { method: 'POST', body: { extra_id: Number(extraId), quantity: 1 } });
            this.cart = data.cart; this.totals = data.totals; this.message = data.message;
            window.dispatchEvent(new CustomEvent('velora:cart-updated', { detail: data }));
        } catch (e) { this.error = e.message; }
    },
    async removeExtra(id) {
        try {
            const data = await window.Velora.fetchJson(`/warenkorb/extras/${id}`, { method: 'DELETE' });
            this.cart = data.cart; this.totals = data.totals; this.message = data.message;
            window.dispatchEvent(new CustomEvent('velora:cart-updated', { detail: data }));
        } catch (e) { this.error = e.message; }
    },
    extraQtyLabel(extra) {
        const n = Number(extra?.quantity || 0);
        return extra?.price_type === 'hour' ? n + ' Std.' : n + '×';
    },
    async applyCoupon() {
        try {
            const data = await window.Velora.fetchJson('/warenkorb/coupon', { method: 'POST', body: { code: this.coupon } });
            this.cart = data.cart; this.totals = data.totals; this.message = data.message; this.error = ''; this.coupon = '';
        } catch (e) { this.error = e.data?.message || e.message; this.coupon = ''; this.message = ''; }
    },
    async removeCoupon() {
        const data = await window.Velora.fetchJson('/warenkorb/coupon', { method: 'DELETE' });
        this.cart = data.cart; this.totals = data.totals; this.message = data.message;
    },
    money(v) { return window.Velora.formatMoney(v); },
    hasExtras() { return this.cart.extras && Object.keys(this.cart.extras).length > 0; },
    isFirstItem(key) { return Object.keys(this.cart.items || {})[0] === String(key); },
    extraImage(extra, id) { return extra.image || this.extraImages[extra.extra_id] || this.extraImages[id] || ''; },
    photo(url) {
        if (!url) return '{{ asset('images/placeholders/product.svg') }}';
        if (url.startsWith('http') || url.startsWith('/')) return url;
        return '/storage/' + url;
    },
}">
    <x-breadcrumb :items="[['label' => __('frontend.cart.title')]]" />
    <h1 class="text-3xl font-bold md:text-5xl">{{ __('frontend.cart.title') }}</h1>

    <template x-if="(!cart.items || Object.keys(cart.items).length === 0) && (!cart.extras || Object.keys(cart.extras).length === 0)">
        <div class="mt-10 rounded-2xl bg-white p-10 text-center shadow-soft">
            <p class="text-charcoal/70">{{ __('frontend.cart.empty') }}</p>
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <x-button :href="route('packages.index', 'buffets')" variant="primary">{{ __('frontend.cta.discover') }}</x-button>
                <x-button :href="route('packages.index', 'fingerfood')" variant="secondary">Fingerfood</x-button>
            </div>
        </div>
    </template>

    <div class="mt-10 grid gap-10 lg:grid-cols-12" x-show="(cart.items && Object.keys(cart.items).length) || (cart.extras && Object.keys(cart.extras).length)">
        <div class="space-y-6 lg:col-span-8">
            <template x-for="(item, key) in cart.items" :key="key">
                <div class="rounded-2xl bg-white p-5 shadow-soft" x-data="{ menuOpen: false }">
                    <div class="flex items-start gap-4">
                        <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-beige sm:h-24 sm:w-24">
                            <img :src="photo(item.image)" :alt="item.name" class="h-full w-full object-cover" referrerpolicy="no-referrer">
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h2 class="text-xl font-bold" x-text="item.name"></h2>
                                    <p class="mt-1 text-sm text-bronze" x-text="money(item.unit_price) + (item.type === 'package' ? ' p.P.' : '')"></p>
                                </div>
                                <p class="shrink-0 text-xl font-bold tabular-nums" x-text="money(item.line_total)"></p>
                            </div>
                        </div>
                    </div>

                    <template x-if="item.type === 'package' && item.options && item.options.selections && item.options.selections.length">
                        <div class="mt-4">
                            <button type="button" class="flex w-full cursor-pointer items-center justify-between rounded-xl bg-cream px-3 py-2.5 text-left text-sm font-medium" @click="menuOpen = !menuOpen" :aria-expanded="menuOpen.toString()">
                                <span x-text="menuOpen ? @js(__('frontend.cart.menu_hide')) : @js(__('frontend.cart.menu_show'))"></span>
                                <span class="text-bronze" x-text="menuOpen ? '−' : '+'"></span>
                            </button>
                            <div x-show="menuOpen" x-collapse x-cloak class="mt-3 space-y-2 pl-1 text-sm">
                                <template x-for="sel in item.options.selections" :key="sel.slot_id">
                                    <div>
                                        <p class="font-medium text-charcoal" x-text="sel.slot"></p>
                                        <p class="mt-0.5 text-muted" x-text="(sel.products || []).map(function (p) { return p.name; }).join(' · ')"></p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <div class="mt-4 flex flex-wrap items-end justify-end gap-3">
                        <label class="text-xs uppercase tracking-[0.12em] text-muted">
                            {{ __('frontend.cart.quantity') }}
                            <input type="number" min="1" class="input-velora mt-1 w-20" :value="item.quantity" @change="updateItem(item.key || key, $event.target.value, item.persons)">
                        </label>
                        <label class="text-xs uppercase tracking-[0.12em] text-muted">
                            {{ __('frontend.cart.persons') }}
                            <input type="number" min="1" class="input-velora mt-1 w-20" :value="item.persons" @change="updateItem(item.key || key, item.quantity, $event.target.value)">
                        </label>
                        <button type="button" class="mb-1 inline-flex cursor-pointer rounded-lg p-1.5 text-danger transition hover:bg-beige" @click="removeItem(item.key || key)" title="{{ __('frontend.cart.remove') }}" aria-label="{{ __('frontend.cart.remove') }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>

                    <div class="mt-4 border-t border-beige pt-4" x-show="isFirstItem(key) && hasExtras()" x-cloak>
                        <p class="mb-3 text-xs font-semibold uppercase tracking-[0.12em] text-muted">{{ __('frontend.cart.extras') }}</p>
                        <div class="space-y-2">
                            <template x-for="(extra, id) in cart.extras" :key="id">
                                <div class="flex items-center gap-3 rounded-xl bg-cream px-3 py-2">
                                    <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl bg-beige">
                                        <img :src="photo(extraImage(extra, id))" :alt="extra.name" class="h-full w-full object-cover" referrerpolicy="no-referrer">
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium" x-text="extra.name"></p>
                                        <p class="text-xs text-muted" x-text="extraQtyLabel(extra)"></p>
                                    </div>
                                    <p class="shrink-0 text-sm font-semibold text-bronze" x-text="money(extra.line_total)"></p>
                                    <button type="button" class="inline-flex shrink-0 rounded-lg p-1 text-danger transition hover:bg-white" @click="removeExtra(extra.extra_id || id)" title="{{ __('frontend.cart.remove') }}" aria-label="{{ __('frontend.cart.remove') }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="(!cart.items || Object.keys(cart.items).length === 0) && hasExtras()">
                <div class="space-y-2">
                    <template x-for="(extra, id) in cart.extras" :key="'solo-'+id">
                        <div class="flex items-center gap-3 rounded-2xl bg-white p-5 shadow-soft">
                            <div class="h-24 w-24 shrink-0 overflow-hidden rounded-xl bg-beige">
                                <img :src="photo(extraImage(extra, id))" :alt="extra.name" class="h-full w-full object-cover" referrerpolicy="no-referrer">
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="text-lg font-bold" x-text="extra.name"></h3>
                                <p class="text-sm text-muted">Extra · <span x-text="extraQtyLabel(extra)"></span></p>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <p class="text-xl font-bold" x-text="money(extra.line_total)"></p>
                                <button type="button" class="inline-flex rounded-lg p-1.5 text-danger transition hover:bg-beige" @click="removeExtra(extra.extra_id || id)" title="{{ __('frontend.cart.remove') }}" aria-label="{{ __('frontend.cart.remove') }}">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            @if (($availableExtras ?? collect())->isNotEmpty())
                <div class="rounded-2xl bg-white p-5 shadow-soft">
                    <h3 class="text-2xl font-bold">{{ __('frontend.cart.extras') }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted">{{ __('frontend.cart.extras_hint') }}</p>
                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                        @foreach ($availableExtras as $extra)
                            <button type="button" class="flex cursor-pointer items-center gap-3 rounded-xl border border-beige bg-cream px-3 py-2 text-left text-sm transition hover:border-bronze hover:bg-white md:hover:-translate-y-px" @click="addExtra({{ $extra->id }})">
                                <img src="{{ $extra->displayImage() }}" alt="{{ $extra->name }}" width="48" height="48" class="extra-thumb">
                                <span class="min-w-0 flex-1 truncate font-medium">{{ $extra->name }}</span>
                                <span class="shrink-0 text-bronze">{{ $extra->formattedPrice() }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <aside class="lg:col-span-4">
            <div class="rounded-2xl bg-white p-5 shadow-soft sm:p-6 lg:sticky lg:top-28">
                <h2 class="text-2xl font-bold">Summe</h2>

                <div class="mt-4 space-y-2" x-show="hasExtras()" x-cloak>
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-muted">{{ __('frontend.cart.extras') }}</p>
                    <template x-for="(extra, id) in cart.extras" :key="'sum-'+id">
                        <div class="flex items-center gap-2">
                            <div class="h-10 w-10 shrink-0 overflow-hidden rounded-lg bg-beige">
                                <img :src="photo(extraImage(extra, id))" :alt="extra.name" class="h-full w-full object-cover" referrerpolicy="no-referrer">
                            </div>
                            <span class="min-w-0 flex-1 truncate text-sm" x-text="extra.name"></span>
                            <span class="shrink-0 text-xs text-muted" x-text="extraQtyLabel(extra)"></span>
                            <span class="shrink-0 text-sm font-medium tabular-nums" x-text="money(extra.line_total)"></span>
                        </div>
                    </template>
                </div>

                <dl class="mt-5 space-y-3 text-sm">
                    <div class="flex justify-between"><dt>{{ __('frontend.cart.subtotal') }}</dt><dd x-text="money(totals.subtotal)"></dd></div>
                    <div class="flex justify-between"><dt>{{ __('frontend.cart.delivery') }}</dt><dd class="text-muted">im Checkout</dd></div>
                    <div class="flex justify-between text-success" x-show="totals.discount > 0"><dt>{{ __('frontend.cart.discount') }}</dt><dd x-text="'-' + money(totals.discount)"></dd></div>
                    <div class="flex justify-between"><dt>{{ __('frontend.cart.tax') }} ({{ format_mwst_percent($totals['tax_rate'] ?? 7) }}%)</dt><dd x-text="money(totals.tax_amount)"></dd></div>
                    <div class="flex justify-between" x-show="Number(totals.tax_standard_amount || 0) > 0" x-cloak>
                        <dt>{{ __('frontend.cart.tax') }} ({{ format_mwst_percent($totals['tax_standard_rate'] ?? 19) }}%)</dt>
                        <dd x-text="money(totals.tax_standard_amount)"></dd>
                    </div>
                    <div class="flex justify-between border-t border-beige pt-3 text-xl font-bold"><dt>{{ __('frontend.cart.total') }}</dt><dd class="text-bronze" x-text="money(totals.total)"></dd></div>
                </dl>

                <div class="mt-6 flex gap-2">
                    <input type="text" x-model="coupon" placeholder="{{ __('frontend.cart.coupon') }}" class="input-velora">
                    <button type="button" class="btn-secondary !px-3" @click="applyCoupon()">{{ __('frontend.cart.apply') }}</button>
                </div>
                <button type="button" class="mt-2 text-xs text-muted underline" x-show="cart.coupon" @click="removeCoupon()">Gutschein entfernen</button>

                <p x-show="message" class="mt-3 text-sm text-success" x-text="message"></p>
                <p x-show="error" class="mt-3 text-sm text-danger" x-text="error"></p>

                <a href="{{ route('checkout.index') }}" class="btn-primary mt-6 w-full">{{ __('frontend.cta.checkout') }}</a>
                <a href="{{ route('quote.create') }}" class="btn-secondary mt-3 w-full">{{ __('frontend.cta.quote') }}</a>
            </div>
        </aside>
    </div>
</div>
@endsection
