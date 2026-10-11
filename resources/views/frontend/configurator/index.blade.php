@extends('layouts.frontend')

@section('title', __('frontend.configurator.title').' – '.__('frontend.brand'))
@section('meta_description', __('frontend.home.config_text'))

@section('content')
@php
    $productsPayload = [];
    foreach ($categories as $cat) {
        foreach ($cat->products ?? [] as $p) {
            $productsPayload[] = [
                'id' => $p->id,
                'name' => $p->name,
                'price' => (float) $p->price,
                'category' => $cat->name,
                'category_id' => $cat->id,
            ];
        }
    }
@endphp

<div
    class="container-velora py-10 md:py-14"
    x-data="configuratorWizard({
        persons: 20,
        calculateUrl: @js(route('configurator.calculate')),
        cartAddUrl: @js(route('cart.add')),
        blockedDates: @js(collect($blockedDates ?? [])->map(fn ($d) => \Illuminate\Support\Carbon::parse($d)->toDateString())->values()),
        totals: @js($totals ?? null),
    })"
>
    <x-breadcrumb :items="[['label' => __('frontend.configurator.title')]]" />
    <x-section-heading
        eyebrow="Planung"
        :title="__('frontend.configurator.title')"
        text="In sechs Schritten zu Ihrem individuellen Catering – mit live berechneter Preisübersicht."
        class="mb-10"
    />

    <div class="mb-8 flex flex-wrap gap-2">
        @foreach ([
            1 => __('frontend.configurator.step_event'),
            2 => __('frontend.configurator.step_persons'),
            3 => __('frontend.configurator.step_menu'),
            4 => __('frontend.configurator.step_extras'),
            5 => __('frontend.configurator.step_delivery'),
            6 => __('frontend.configurator.step_summary'),
        ] as $n => $label)
            <button
                type="button"
                class="border px-3 py-2 text-xs uppercase tracking-[0.12em] transition"
                :class="step === {{ $n }} ? 'border-bronze bg-bronze text-cream' : 'border-beige bg-ivory text-charcoal/70'"
                @click="go({{ $n }})"
            >{{ $n }}. {{ $label }}</button>
        @endforeach
    </div>

    <div class="grid gap-10 lg:grid-cols-12">
        <div class="lg:col-span-8">
            {{-- Step 1 Event --}}
            <div x-show="step === 1" class="border border-beige bg-ivory p-6 md:p-8">
                <h2 class="font-serif text-2xl">{{ __('frontend.configurator.step_event') }}</h2>
                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    @forelse ($eventTypes as $type)
                        <button
                            type="button"
                            class="border px-4 py-4 text-left transition"
                            :class="eventTypeId === {{ $type->id }} ? 'border-bronze bg-cream' : 'border-beige bg-cream'"
                            @click="eventTypeId = {{ $type->id }}"
                        >
                            <span class="font-serif text-xl">{{ $type->name }}</span>
                            @if ($type->description ?? null)
                                <span class="mt-1 block text-sm text-charcoal/65">{{ \Illuminate\Support\Str::limit(strip_tags($type->description), 80) }}</span>
                            @endif
                        </button>
                    @empty
                        <p class="text-muted">Keine Event-Arten verfügbar.</p>
                    @endforelse
                </div>
                @if (($packages ?? collect())->isNotEmpty())
                    <h3 class="mt-8 font-serif text-xl">Pakete</h3>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach ($packages as $package)
                            <button
                                type="button"
                                class="border px-4 py-4 text-left"
                                :class="packageId === {{ $package->id }} ? 'border-bronze' : 'border-beige'"
                                @click="packageId = packageId === {{ $package->id }} ? null : {{ $package->id }}; calculate()"
                            >
                                <span class="font-serif text-lg">{{ $package->name }}</span>
                                <span class="mt-1 block text-sm text-bronze">
                                    {{ number_format((float) $package->base_price, 2, ',', '.') }} €
                                    @if ($package->price_per_person)
                                        + {{ number_format((float) $package->price_per_person, 2, ',', '.') }} € / Pers.
                                    @endif
                                </span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Step 2 Persons --}}
            <div x-show="step === 2" x-cloak class="border border-beige bg-ivory p-6 md:p-8">
                <h2 class="font-serif text-2xl">{{ __('frontend.configurator.step_persons') }}</h2>
                <label class="label-velora mt-6">Personenanzahl</label>
                <input type="number" min="1" max="5000" class="input-velora max-w-xs" x-model.number="persons" @change="calculate()">
                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label-velora">Datum</label>
                        <input type="date" class="input-velora" x-model="date" :min="new Date().toISOString().slice(0,10)">
                    </div>
                    <div>
                        <label class="label-velora">Uhrzeit</label>
                        <input type="time" class="input-velora" x-model="time">
                    </div>
                </div>
            </div>

            {{-- Step 3 Menu --}}
            <div x-show="step === 3" x-cloak class="border border-beige bg-ivory p-6 md:p-8">
                <h2 class="font-serif text-2xl">{{ __('frontend.configurator.step_menu') }}</h2>
                <div class="mt-6 space-y-8">
                    @forelse ($categories as $category)
                        <div>
                            <h3 class="font-serif text-xl text-bronze">{{ $category->name }}</h3>
                            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                @foreach ($category->products ?? [] as $product)
                                    <button
                                        type="button"
                                        class="flex items-center justify-between border px-4 py-3 text-left text-sm transition"
                                        :class="selectedProducts['{{ $product->id }}'] ? 'border-bronze bg-cream' : 'border-beige bg-cream'"
                                        @click="toggleProduct({{ $product->id }})"
                                    >
                                        <span>
                                            {{ $product->name }}
                                            @if ($product->allergenCodes() !== [])
                                                <span class="mt-1 block"><x-allergen-badges :codes="$product->allergenCodes()" /></span>
                                            @endif
                                        </span>
                                        <span class="text-bronze">{{ number_format((float) $product->price, 2, ',', '.') }} €</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-muted">Keine Produkte verfügbar.</p>
                    @endforelse
                </div>
            </div>

            {{-- Step 4 Extras --}}
            <div x-show="step === 4" x-cloak class="border border-beige bg-ivory p-6 md:p-8">
                <h2 class="font-serif text-2xl">{{ __('frontend.configurator.step_extras') }}</h2>
                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    @forelse ($extras as $extra)
                        <button
                            type="button"
                            class="flex items-center justify-between border px-4 py-3 text-left text-sm"
                            :class="selectedExtras['{{ $extra->id }}'] ? 'border-bronze bg-cream' : 'border-beige bg-cream'"
                            @click="toggleExtra({{ $extra->id }})"
                        >
                            <span>{{ $extra->name }}</span>
                            <span class="text-bronze">{{ $extra->formattedPrice() }}</span>
                        </button>
                    @empty
                        <p class="text-muted">Keine Extras verfügbar.</p>
                    @endforelse
                </div>
            </div>

            {{-- Step 5 Delivery --}}
            <div x-show="step === 5" x-cloak class="border border-beige bg-ivory p-6 md:p-8">
                <h2 class="font-serif text-2xl">{{ __('frontend.configurator.step_delivery') }}</h2>
                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label-velora">PLZ</label>
                        <input type="text" class="input-velora" x-model="postalCode" @change="calculate()">
                    </div>
                    <div>
                        <label class="label-velora">Ort</label>
                        <input type="text" class="input-velora" x-model="city">
                    </div>
                    <div>
                        <label class="label-velora">Straße</label>
                        <input type="text" class="input-velora" x-model="street">
                    </div>
                    <div>
                        <label class="label-velora">Hausnummer</label>
                        <input type="text" class="input-velora" x-model="houseNumber">
                    </div>
                </div>
            </div>

            {{-- Step 6 Summary --}}
            <div x-show="step === 6" x-cloak class="border border-beige bg-ivory p-6 md:p-8">
                <h2 class="font-serif text-2xl">{{ __('frontend.configurator.step_summary') }}</h2>
                <p class="mt-4 text-sm text-charcoal/70">
                    Personen: <strong x-text="persons"></strong>
                    · Ausgewählte Produkte: <strong x-text="Object.keys(selectedProducts).length"></strong>
                    · Extras: <strong x-text="Object.keys(selectedExtras).length"></strong>
                </p>
                <p x-show="message" class="mt-4 text-sm text-success" x-text="message"></p>
                <p x-show="error" class="mt-4 text-sm text-danger" x-text="error"></p>
                <button type="button" class="btn-primary mt-6" @click="addSelectionsToCart()">In den Warenkorb übernehmen</button>
            </div>

            <div class="mt-6 flex justify-between">
                <button type="button" class="btn-secondary" @click="prev()" :disabled="step === 1">{{ __('frontend.cta.back') }}</button>
                <button type="button" class="btn-primary" @click="next()" x-show="step < maxStep">{{ __('frontend.cta.next') }}</button>
            </div>
        </div>

        <aside class="lg:col-span-4">
            <div class="sticky top-28 border border-beige bg-charcoal p-6 text-cream">
                <h3 class="font-serif text-2xl">{{ __('frontend.configurator.realtime') }}</h3>
                <p class="mt-2 text-xs uppercase tracking-[0.16em] text-bronze-light" x-show="calculating">{{ __('frontend.common.loading') }}</p>
                <dl class="mt-6 space-y-3 text-sm">
                    <div class="flex justify-between"><dt>{{ __('frontend.cart.subtotal') }}</dt><dd x-text="totals ? window.Velora.formatMoney(totals.subtotal) : '—'"></dd></div>
                    <div class="flex justify-between"><dt>{{ __('frontend.cart.delivery') }}</dt><dd x-text="totals ? window.Velora.formatMoney(totals.delivery_fee) : '—'"></dd></div>
                    <div class="flex justify-between"><dt>{{ __('frontend.cart.tax') }} ({{ format_mwst_percent($totals['tax_rate'] ?? 7) }}%)</dt><dd x-text="totals ? window.Velora.formatMoney(totals.tax_amount) : '—'"></dd></div>
                    <div class="flex justify-between" x-show="totals && Number(totals.tax_standard_amount || 0) > 0" x-cloak>
                        <dt>{{ __('frontend.cart.tax') }} ({{ format_mwst_percent($totals['tax_standard_rate'] ?? 19) }}%)</dt>
                        <dd x-text="totals ? window.Velora.formatMoney(totals.tax_standard_amount) : '—'"></dd>
                    </div>
                    <div class="flex justify-between border-t border-cream/15 pt-3 font-serif text-2xl">
                        <dt>{{ __('frontend.cart.total') }}</dt>
                        <dd class="text-bronze-light" x-text="totals ? window.Velora.formatMoney(totals.total) : '—'"></dd>
                    </div>
                </dl>
                <button type="button" class="btn-gold mt-6 w-full" @click="calculate()">Aktualisieren</button>
            </div>
        </aside>
    </div>
</div>
@endsection
