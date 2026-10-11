@extends('layouts.frontend')

@section('title', __('frontend.checkout.title').' – '.__('frontend.brand'))

@section('content')
@php
    $blocked = collect($blockedDates ?? [])->map(fn ($d) => \Illuminate\Support\Carbon::parse($d)->toDateString())->values();
    $deliveryCostPerKm = (float) ($deliveryCostPerKm ?? config('delivery.cost_per_km', 1.00));
@endphp

<div class="container-velora py-10 md:py-14" x-data="checkoutPage">
    <x-breadcrumb :items="[
        ['label' => __('frontend.checkout.title')],
    ]" />
    <h1 class="text-3xl font-bold md:text-5xl">{{ __('frontend.checkout.title') }}</h1>

    @if ($errors->any())
        <div class="mt-6">
            <x-alert type="error">
                <ul class="list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        </div>
    @endif

    <form id="clear-cart-form" action="{{ route('cart.clear') }}" method="POST" class="hidden" onsubmit="return confirm('Warenkorb wirklich leeren?')">
        @csrf
    </form>

    <form action="{{ route('checkout.store') }}" method="POST" class="mt-10 space-y-10">
        @csrf
        <input type="hidden" name="delivery_cost" :value="Number(deliveryCost || 0).toFixed(2)">
        <input type="hidden" name="delivery_km" :value="Number(deliveryKm || 0).toFixed(1)">
        <input type="hidden" name="tip_amount" :value="Number(tipAmount || 0).toFixed(2)">

        <div class="space-y-10">
            <div class="rounded-2xl bg-white p-5 shadow-soft sm:p-6">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-2xl font-bold">Ihre Auswahl</h2>
                    <button type="submit" form="clear-cart-form" class="shrink-0 text-sm font-medium text-muted underline decoration-beige underline-offset-4 hover:text-charcoal">Warenkorb leeren</button>
                </div>
                <ul class="mt-5 space-y-3 text-sm">
                    @foreach ($cart['items'] ?? [] as $item)
                        @php
                            $itemImage = velora_media_url(
                                $item['image'] ?? ($item['options']['package_image'] ?? null),
                                $item['name'] ?? '',
                                (int) ($item['package_id'] ?? 0)
                            );
                        @endphp
                        <li class="flex items-start gap-3 border-b border-beige pb-3">
                            <img src="{{ $itemImage }}" alt="{{ $item['name'] ?? '' }}" width="48" height="48" class="h-12 w-12 shrink-0 rounded-xl object-cover" referrerpolicy="no-referrer">
                            <span class="min-w-0 flex-1">
                                <span class="font-medium text-charcoal">{{ $item['name'] ?? 'Position' }} × {{ $item['quantity'] ?? 1 }}</span>
                                @if (($item['type'] ?? '') === 'package' && ! empty($item['options']['selections']))
                                    <span class="mt-2 block space-y-2">
                                        @foreach ($item['options']['selections'] as $sel)
                                            <span class="block">
                                                <span class="block text-[10px] font-semibold uppercase tracking-[0.12em] text-muted">{{ $sel['slot'] ?? '' }}</span>
                                                <span class="mt-1 block space-y-1">
                                                    @foreach ($sel['products'] ?? [] as $product)
                                                        @php
                                                            $productImage = velora_media_url(
                                                                $product['image'] ?? null,
                                                                $product['name'] ?? '',
                                                                (int) ($product['id'] ?? 0)
                                                            );
                                                        @endphp
                                                        <span class="flex items-center gap-2 text-xs text-muted">
                                                            <img src="{{ $productImage }}" alt="{{ $product['name'] ?? '' }}" width="36" height="36" class="h-9 w-9 shrink-0 rounded-lg object-cover" referrerpolicy="no-referrer">
                                                            <span>{{ $product['name'] ?? '' }}</span>
                                                        </span>
                                                    @endforeach
                                                </span>
                                            </span>
                                        @endforeach
                                    </span>
                                @endif
                            </span>
                            <span class="shrink-0 tabular-nums">{{ number_format((float) ($item['line_total'] ?? 0), 2, ',', '.') }} €</span>
                        </li>
                    @endforeach
                    @foreach ($cart['extras'] ?? [] as $extra)
                        @php
                            $extraImage = velora_drink_photo($extra['name'] ?? null)
                                ?: velora_media_url($extra['image'] ?? null, $extra['name'] ?? '', (int) ($extra['extra_id'] ?? 0));
                        @endphp
                        <li class="flex items-center gap-3 border-b border-beige pb-3">
                            <img src="{{ $extraImage }}" alt="{{ $extra['name'] ?? '' }}" width="40" height="40" class="h-10 w-10 shrink-0 rounded-lg object-cover" referrerpolicy="no-referrer">
                            <span class="min-w-0 flex-1">
                                {{ $extra['name'] ?? 'Extra' }}
                                @if (($extra['price_type'] ?? '') === 'hour')
                                    · {{ (int) ($extra['quantity'] ?? 0) }} Std.
                                @elseif (! empty($extra['quantity']))
                                    · {{ (int) $extra['quantity'] }}×
                                @endif
                                @if (($extra['kind'] ?? '') === 'product')
                                    <span class="text-xs text-muted">MwSt. 19%</span>
                                @endif
                            </span>
                            <span class="shrink-0 tabular-nums">{{ number_format((float) ($extra['line_total'] ?? 0), 2, ',', '.') }} €</span>
                        </li>
                    @endforeach
                </ul>
                <dl class="mt-5 space-y-2 text-sm">
                    <div class="flex justify-between"><dt>{{ __('frontend.cart.subtotal') }}</dt><dd x-text="fmtMoney(subtotal)"></dd></div>
                    <div class="flex justify-between">
                        <dt>{{ __('frontend.cart.delivery') }} <span class="font-normal text-muted" x-show="Number(deliveryCost) > 0" x-cloak>zzgl. MwSt. 7%</span></dt>
                        <dd x-text="Number(deliveryCost) > 0 ? fmtMoney(deliveryCost) : 'Kostenlos'"></dd>
                    </div>
                    <div class="flex justify-between" x-show="Number(tipAmount) > 0" x-cloak>
                        <dt>Trinkgeld <span class="font-normal text-muted">ohne MwSt.</span></dt>
                        <dd x-text="fmtMoney(tipAmount)"></dd>
                    </div>
                    @if ((float) ($totals['discount'] ?? 0) > 0)
                        <div class="flex justify-between text-success">
                            <dt>{{ __('frontend.cart.discount') }}@if($couponCode ?? '') ({{ $couponCode }})@endif</dt>
                            <dd>−{{ number_format((float) $totals['discount'], 2, ',', '.') }} €</dd>
                        </div>
                    @elseif (! empty($couponCode))
                        <div class="flex justify-between text-success">
                            <dt>{{ __('frontend.cart.coupon') }}</dt>
                            <dd>{{ $couponCode }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <dt>{{ __('frontend.cart.tax') }} ({{ format_mwst_percent($totals['tax_rate'] ?? 7) }}%)</dt>
                        <dd x-text="fmtMoney(taxAmountReduced())"></dd>
                    </div>
                    <div class="flex justify-between" x-show="taxAmountStandard() > 0" x-cloak>
                        <dt>{{ __('frontend.cart.tax') }} ({{ format_mwst_percent($totals['tax_standard_rate'] ?? 19) }}%)</dt>
                        <dd x-text="fmtMoney(taxAmountStandard())"></dd>
                    </div>
                    <div class="flex justify-between border-t border-beige pt-3 text-xl font-bold">
                        <dt>{{ __('frontend.cart.total') }}</dt>
                        <dd class="text-bronze" x-text="fmtMoney(grandTotal())"></dd>
                    </div>
                </dl>
            </div>

            <div>
                <h2 class="text-xl font-bold tracking-tight text-charcoal">Warum Monnemtisch?</h2>
                <ul class="mt-6 space-y-6">
                    <li class="flex items-start gap-4">
                        <span class="mt-0.5 inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-charcoal text-white" aria-hidden="true">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" focusable="false">
                                <path d="M5 12.5 9.8 17.2 19 7" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span>
                            <span class="block font-[Georgia,Cambria,'Times_New_Roman',Times,serif] text-[1.15rem] font-bold leading-snug text-charcoal">Kostenlose Stornierung</span>
                            <span class="mt-1 block text-sm leading-relaxed text-muted">Bis 48 Stunden vor Lieferung können Sie kostenfrei stornieren.</span>
                        </span>
                    </li>
                    <li class="flex items-start gap-4">
                        <span class="mt-0.5 inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-charcoal text-white" aria-hidden="true">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" focusable="false">
                                <path fill-rule="evenodd" d="M1.4 12c1.5-3.9 5.4-7.2 10.6-7.2S20.1 8.1 21.6 12c-1.5 3.9-5.4 7.2-10.6 7.2S2.9 15.9 1.4 12Zm10.6 3.6a3.6 3.6 0 1 0 0-7.2 3.6 3.6 0 0 0 0 7.2Z" clip-rule="evenodd"/>
                                <circle cx="12" cy="12" r="1.65"/>
                            </svg>
                        </span>
                        <span>
                            <span class="block font-[Georgia,Cambria,'Times_New_Roman',Times,serif] text-[1.15rem] font-bold leading-snug text-charcoal">Frisch &amp; Pünktlich</span>
                            <span class="mt-1 block text-sm leading-relaxed text-muted">Alles frisch zubereitet und pünktlich geliefert — garantiert.</span>
                        </span>
                    </li>
                    <li class="flex items-start gap-4">
                        <span class="mt-0.5 inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-charcoal text-white" aria-hidden="true">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" focusable="false">
                                <path d="M12 2.15 3.85 5.55v6.55c0 5.05 3.35 9.7 8.15 10.9 4.8-1.2 8.15-5.85 8.15-10.9V5.55L12 2.15Z"/>
                            </svg>
                        </span>
                        <span>
                            <span class="block font-[Georgia,Cambria,'Times_New_Roman',Times,serif] text-[1.15rem] font-bold leading-snug text-charcoal">Sichere Bezahlung</span>
                            <span class="mt-1 block text-sm leading-relaxed text-muted">256-Bit SSL-Verschlüsselung. Ihre Daten sind bei uns sicher.</span>
                        </span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="space-y-10">
            @php
                $fullNameValue = old('full_name');
                if ($fullNameValue === null) {
                    $fullNameValue = trim(implode(' ', array_filter([old('first_name'), old('last_name')])));
                }
                $fieldClass = 'block w-full min-h-14 rounded-xl border border-beige bg-white px-5 py-4 text-base text-charcoal placeholder:text-charcoal/45 transition focus:border-bronze focus:outline-none focus:ring-1 focus:ring-bronze';
            @endphp
            <section>
                <h2 class="text-2xl font-bold">Kontakt</h2>
                <div class="mt-5">
                    <input type="email" name="email" id="email" required autocomplete="email" placeholder="E-Mail Adresse *" value="{{ old('email') }}" class="{{ $fieldClass }}">
                    @error('email')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
            </section>

            <section class="border-t border-beige pt-8">
                <h2 class="text-2xl font-bold">Lieferadresse</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <input type="text" name="full_name" id="full_name" required autocomplete="name" placeholder="Vollständiger Name *" value="{{ $fullNameValue }}" class="{{ $fieldClass }}">
                        @error('full_name')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                        @error('first_name')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                        @error('last_name')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <input type="tel" name="phone" id="phone" required autocomplete="tel" placeholder="Telefon *" value="{{ old('phone') }}" class="{{ $fieldClass }}">
                        @error('phone')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="mt-4">
                    <input
                        type="text"
                        name="billing_address"
                        id="billing_address"
                        required
                        autocomplete="street-address"
                        placeholder="Straße, Nr, PLZ, Ort *"
                        x-model="billingAddress"
                        class="{{ $fieldClass }}"
                    >
                    @error('billing_address')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <p class="mt-2 flex items-start gap-1.5 text-sm text-muted">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" stroke-width="1.5"/>
                        <path stroke-linecap="round" stroke-width="1.5" d="M12 11v5M12 8h.01"/>
                    </svg>
                    <span>Innerhalb Mannheims kostenlos. Außerhalb: {{ number_format($deliveryCostPerKm, 2, ',', '.') }} €/km.</span>
                </p>
                <p class="mt-2 text-sm font-medium text-bronze" x-show="quoting" x-cloak>Entfernung wird berechnet…</p>
                <p class="mt-2 text-sm font-semibold text-charcoal" x-show="!quoting && deliveryLabel" x-text="deliveryLabel"></p>
                <p class="mt-2 text-sm text-danger" x-show="!quoting && deliveryError" x-text="deliveryError"></p>
                <input type="hidden" name="billing_country" value="DE">
                <input type="hidden" name="shipping_same_as_billing" value="1">
                <input type="hidden" name="persons" value="{{ old('persons', $cart['event']['persons'] ?? 20) }}">

                <div class="mt-6">
                    <x-blocked-date-picker
                        label="Datum"
                        name="event_date"
                        required
                        :value="old('event_date')"
                        :blocked="$blocked"
                        min="{{ now()->toDateString() }}"
                    />
                    <div class="mt-4">
                        <label for="event_time" class="label-velora">Uhrzeit</label>
                        <input type="time" name="event_time" id="event_time" value="{{ old('event_time') }}" class="{{ $fieldClass }}">
                        @error('event_time')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                    <p class="mt-2 text-sm text-muted">Mindestens 2 Tage Vorlauf empfohlen.</p>
                </div>
            </section>

            <section>
                <h2 class="text-2xl font-bold">Trinkgeld</h2>
                <p class="mt-2 text-sm text-muted">Freiwillig – vielen Dank, wenn Sie unser Team unterstützen möchten.</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <button type="button" class="rounded-lg px-3 py-2 text-sm font-semibold" :class="!tipCustom && tipPercent === 0 ? 'bg-charcoal text-white' : 'bg-white text-charcoal shadow-soft'" @click="setTipPercent(0)">Kein Trinkgeld</button>
                    <button type="button" class="rounded-lg px-3 py-2 text-sm font-semibold" :class="!tipCustom && tipPercent === 5 ? 'bg-charcoal text-white' : 'bg-white text-charcoal shadow-soft'" @click="setTipPercent(5)">5%</button>
                    <button type="button" class="rounded-lg px-3 py-2 text-sm font-semibold" :class="!tipCustom && tipPercent === 10 ? 'bg-charcoal text-white' : 'bg-white text-charcoal shadow-soft'" @click="setTipPercent(10)">10%</button>
                    <button type="button" class="rounded-lg px-3 py-2 text-sm font-semibold" :class="!tipCustom && tipPercent === 15 ? 'bg-charcoal text-white' : 'bg-white text-charcoal shadow-soft'" @click="setTipPercent(15)">15%</button>
                    <button type="button" class="rounded-lg px-3 py-2 text-sm font-semibold" :class="tipCustom ? 'bg-charcoal text-white' : 'bg-white text-charcoal shadow-soft'" @click="enableCustomTip()">Betrag</button>
                </div>
                <div class="mt-3 max-w-xs" x-show="tipCustom" x-cloak>
                    <label class="label-velora" for="tip_custom">Trinkgeldbetrag</label>
                    <input id="tip_custom" type="number" min="0" step="0.50" class="input-velora" x-model.number="tipAmount">
                </div>
            </section>

            <section>
                <h2 class="text-2xl font-bold">{{ __('frontend.checkout.payment') }}</h2>
                <p class="mt-2 text-sm text-muted">Wählen Sie, womit Sie bezahlen möchten. Sie werden zur sicheren Zahlung weitergeleitet.</p>
                @if (empty($paymentMethods))
                    <p class="mt-5 rounded-xl border border-beige bg-white px-4 py-3 text-sm text-muted">Derzeit ist keine Online-Zahlung verfügbar.</p>
                @else
                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        @foreach ($paymentMethods as $value => $label)
                            <label class="flex cursor-pointer items-center gap-4 rounded-xl border border-beige bg-white px-4 py-4 text-sm has-[:checked]:border-bronze has-[:checked]:bg-terracotta-soft/40">
                                <input type="radio" name="payment_method" value="{{ $value }}" @checked(old('payment_method', array_key_first($paymentMethods)) === $value) class="text-bronze focus:ring-bronze">
                                <span class="flex min-w-0 flex-1 items-center justify-between gap-3">
                                    <span>
                                        <span class="block font-semibold text-charcoal">{{ $label }}</span>
                                        <span class="mt-0.5 block text-xs text-muted">
                                            @if ($value === 'stripe')
                                                Visa, Mastercard, American Express
                                            @else
                                                PayPal-Konto oder Karte über PayPal
                                            @endif
                                        </span>
                                    </span>
                                    @if ($value === 'stripe')
                                        <span class="flex shrink-0 items-center gap-1">
                                            <span class="inline-flex h-7 min-w-[44px] items-center justify-center rounded border border-beige bg-white px-1.5" title="Visa">
                                                <span class="text-[11px] font-extrabold italic leading-none tracking-wide text-[#1A1F71]">VISA</span>
                                            </span>
                                            <span class="inline-flex h-7 w-11 items-center justify-center rounded border border-beige bg-white" title="Mastercard">
                                                <svg viewBox="0 0 38 24" class="h-5 w-8" aria-hidden="true">
                                                    <circle cx="15" cy="12" r="8" fill="#EB001B"/>
                                                    <circle cx="23" cy="12" r="8" fill="#F79E1B"/>
                                                </svg>
                                            </span>
                                            <span class="inline-flex h-7 min-w-[44px] items-center justify-center rounded border border-beige bg-[#1F72CD] px-1" title="American Express">
                                                <span class="text-[8px] font-bold tracking-wider text-white">AMEX</span>
                                            </span>
                                        </span>
                                    @else
                                        <span class="inline-flex h-7 items-center rounded border border-beige bg-white px-2 text-[11px] font-bold text-[#003087]">PayPal</span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </section>

            <section>
                <x-textarea label="{{ __('frontend.checkout.notes') }}" name="notes" rows="4">{{ old('notes') }}</x-textarea>
                <x-input class="mt-4" label="{{ __('frontend.cart.coupon') }}" name="coupon_code" value="{{ $couponCode ?? '' }}" />
                <div class="mt-4">
                    <x-checkbox name="gdpr_consent" value="1" required>
                        Ich stimme der Verarbeitung meiner Daten gemäß
                        <a href="{{ route('legal.datenschutz') }}" class="font-medium underline decoration-beige underline-offset-2 hover:text-bronze" target="_blank" rel="noopener noreferrer">Datenschutzerklärung</a>
                        zu.
                    </x-checkbox>
                </div>
            </section>

            <button type="submit" class="btn-primary" @disabled(empty($paymentMethods))>Jetzt bezahlen</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
@php
    $initialAddress = old('billing_address', trim(implode(' ', array_filter([
        $cart['delivery']['street'] ?? '',
        $cart['delivery']['house_number'] ?? '',
        $cart['delivery']['postal_code'] ?? '',
        $cart['delivery']['city'] ?? '',
    ]))));
@endphp
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('checkoutPage', () => ({
        DELIVERY_COST_PER_KM: {{ (float) $deliveryCostPerKm }},
        sameAddress: {{ old('shipping_same_as_billing', true) ? 'true' : 'false' }},
        billingAddress: @json($initialAddress),
        shippingAddress: @json(old('shipping_address', '')),
        subtotal: {{ (float) ($totals['subtotal'] ?? 0) }},
        foodSubtotal: {{ (float) ($totals['food_subtotal'] ?? $totals['subtotal'] ?? 0) }},
        drinkSubtotal: {{ (float) ($totals['drink_subtotal'] ?? 0) }},
        discount: {{ (float) ($totals['discount'] ?? 0) }},
        taxRate: {{ (float) ($totals['tax_rate'] ?? 7) }},
        taxStandardRate: {{ (float) ($totals['tax_standard_rate'] ?? 19) }},
        deliveryCost: {{ (float) old('delivery_cost', $totals['delivery_fee'] ?? 0) }},
        deliveryKm: {{ (float) old('delivery_km', $cart['delivery']['distance_km'] ?? 0) }},
        deliveryLabel: '',
        deliveryError: '',
        isFreeCity: false,
        quoting: false,
        quoteTimer: null,
        tipPercent: 0,
        tipCustom: false,
        tipAmount: {{ (float) old('tip_amount', $cart['tip']['amount'] ?? 0) }},
        init() {
            this.$watch('billingAddress', () => this.scheduleQuote());
            this.$watch('shippingAddress', () => this.scheduleQuote());
            this.$watch('sameAddress', () => this.scheduleQuote());
            this.$watch('deliveryCost', () => this.refreshTipFromPercent());
            if (this.deliveryAddressReady()) {
                this.quoteDistance();
            }
        },
        quoteUrl() {
            const prefix = window.location.pathname.replace(/\/checkout\/?.*$/, '');
            return prefix + '/checkout/delivery-distance';
        },
        addressText() {
            return this.sameAddress ? this.billingAddress : this.shippingAddress;
        },
        deliveryAddressReady() {
            const text = String(this.addressText() || '').trim();
            return text.length >= 3;
        },
        scheduleQuote() {
            clearTimeout(this.quoteTimer);
            this.quoteTimer = setTimeout(() => this.quoteDistance(), 1500);
        },
        fmtKm(v) {
            return Number(v || 0).toFixed(1).replace('.', ',');
        },
        fmtMoney(v) {
            return window.Velora.formatMoney(v);
        },
        refreshDeliveryLabel() {
            if (this.isFreeCity) {
                this.deliveryLabel = 'Lieferung innerhalb Mannheims (Kostenlos)';
                return;
            }
            this.deliveryLabel = 'Entfernung: ' + this.fmtKm(this.deliveryKm) + ' km — Lieferkosten: ' + this.fmtMoney(this.deliveryCost);
        },
        applyQuote(data) {
            const typed = this.addressText();
            const isFree = Boolean(data.isStuttgart ?? data.isHannover)
                || /\b(68159|6816[13579]|68199|68219|68229|68239|68259|6830[579])\b/.test(typed)
                || String(typed).toLowerCase().includes('mannheim');
            this.deliveryKm = Number(data.distanceKm || 0);
            if (!data.ok) {
                if (isFree) {
                    this.isFreeCity = true;
                    this.deliveryCost = 0;
                    this.deliveryError = '';
                    this.refreshDeliveryLabel();
                } else {
                    this.isFreeCity = false;
                    this.deliveryCost = 0;
                    this.deliveryError = data.error || 'Die Entfernung konnte nicht ermittelt werden.';
                    this.deliveryLabel = '';
                }
                return;
            }
            this.deliveryError = '';
            this.isFreeCity = isFree;
            this.deliveryCost = isFree ? 0 : Math.round(this.deliveryKm * this.DELIVERY_COST_PER_KM * 100) / 100;
            this.refreshDeliveryLabel();
        },
        async quoteDistance() {
            if (!this.deliveryAddressReady()) {
                this.deliveryLabel = '';
                this.deliveryError = '';
                return;
            }
            this.quoting = true;
            this.deliveryError = '';
            try {
                const data = await window.Velora.fetchJson(this.quoteUrl(), {
                    method: 'POST',
                    body: { address: this.addressText() },
                });
                this.applyQuote(data);
            } catch (e) {
                this.applyQuote({
                    ok: false,
                    isStuttgart: false,
                    isHannover: false,
                    distanceKm: 0,
                    formattedAddress: '',
                    error: e.data?.message || e.message || 'Die Entfernung konnte nicht ermittelt werden.',
                });
            } finally {
                this.quoting = false;
            }
        },
        setTipPercent(p) {
            this.tipCustom = false;
            this.tipPercent = p;
            this.refreshTipFromPercent();
        },
        enableCustomTip() {
            this.tipCustom = true;
            this.tipPercent = 0;
        },
        billTotal() {
            const net = Math.max(0, this.subtotal + Number(this.deliveryCost || 0) - this.discount);
            return Math.round((net + this.taxAmount()) * 100) / 100;
        },
        refreshTipFromPercent() {
            if (this.tipCustom) {
                return;
            }
            this.tipAmount = this.tipPercent <= 0
                ? 0
                : Math.round(this.billTotal() * (this.tipPercent / 100) * 100) / 100;
        },
        taxableBases() {
            const food = Math.max(0, Number(this.foodSubtotal || 0) + Number(this.deliveryCost || 0));
            const drink = Math.max(0, Number(this.drinkSubtotal || 0));
            const base = food + drink;
            let foodDisc = 0;
            let drinkDisc = 0;
            if (base > 0 && Number(this.discount || 0) > 0) {
                foodDisc = Math.round(this.discount * (food / base) * 100) / 100;
                drinkDisc = Math.round((this.discount - foodDisc) * 100) / 100;
            }
            return {
                food: Math.max(0, food - foodDisc),
                drink: Math.max(0, drink - drinkDisc),
            };
        },
        taxAmountReduced() {
            return Math.round(this.taxableBases().food * (this.taxRate / 100) * 100) / 100;
        },
        taxAmountStandard() {
            return Math.round(this.taxableBases().drink * (this.taxStandardRate / 100) * 100) / 100;
        },
        taxAmount() {
            return Math.round((this.taxAmountReduced() + this.taxAmountStandard()) * 100) / 100;
        },
        grandTotal() {
            return Math.round((this.billTotal() + Number(this.tipAmount || 0)) * 100) / 100;
        },
    }));
});
</script>
@endpush

