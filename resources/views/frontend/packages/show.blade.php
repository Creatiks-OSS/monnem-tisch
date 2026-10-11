@extends('layouts.frontend')

@section('title', $package->documentTitle())
@section('meta_description', filled($package->seo_text) ? \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', trim($package->seo_text)), 160) : ($package->subtitle ?: $package->description))
@section('og_image', str_starts_with($package->imageUrl(), 'http') ? $package->imageUrl() : url($package->imageUrl()))

@section('schema')
    @php
        app(\App\Services\SchemaService::class)->push(
            app(\App\Services\SchemaService::class)->menuPackage($package)
        );
        app(\App\Services\SchemaService::class)->pushJson($package->seo?->schema_json);
    @endphp
@endsection

@push('head')
<style>
#dish-zoom{
    position:fixed;z-index:99999;width:300px;height:300px;padding:0;margin:0;
    pointer-events:none;opacity:0;visibility:hidden;
    transition:opacity .15s ease,visibility .15s ease;
}
#dish-zoom.is-on{opacity:1;visibility:visible}
#dish-zoom img{
    display:block;width:300px;height:300px;max-width:300px;max-height:300px;
    border-radius:1rem;object-fit:cover;
    box-shadow:0 18px 50px rgba(27,36,48,.28);
    background:#f7f5f1;
}
.pkg-hero{
    background:
        linear-gradient(135deg, #f7f3eb 0%, #ffffff 42%, #eef3f0 100%);
    box-shadow: 0 14px 40px rgba(19, 42, 58, 0.08);
    border: 1px solid rgba(19, 42, 58, 0.08);
}
.pkg-hero-check{
    display:flex;align-items:center;justify-content:center;
    width:1.55rem;height:1.55rem;border-radius:999px;color:#fff;font-size:11px;font-weight:700;
    flex-shrink:0;
}
.pkg-hero-row{
    display:flex;align-items:flex-start;gap:.75rem;
    padding:.45rem .7rem;border-radius:.9rem;background:rgba(255,255,255,.72);
}
.pkg-hero-row span:last-child{min-width:0;overflow-wrap:anywhere;}
.pkg-hero-aktion{
    display:inline-flex;align-items:center;border-radius:999px;
    background:#d89b2b;color:#132a3a;padding:.4rem .9rem;
    font-size:12px;font-weight:700;letter-spacing:.04em;
    box-shadow:0 8px 18px rgba(216,155,43,.28);
}
.diet-chip{
    display:inline-flex;align-items:center;gap:.55rem;
    padding:.42rem .9rem .42rem .42rem;
    border-radius:999px;border:1px solid transparent;
    font-size:12px;font-weight:650;letter-spacing:.01em;line-height:1;
    box-shadow:0 8px 18px rgba(27,36,48,.07);
}
.diet-chip i{
    display:flex;align-items:center;justify-content:center;
    width:1.55rem;height:1.55rem;border-radius:999px;
}
.diet-chip i svg{width:.95rem;height:.95rem}
.diet-chip b{font-weight:700}
.diet-chip-vegan{
    color:#1f6b45;background:linear-gradient(180deg,#f7fdf9,#e6f6ee);
    border-color:rgba(47,143,91,.2);
}
.diet-chip-vegan i{background:#2f8f5b;color:#fff}
.diet-chip-veg{
    color:#c2413b;background:linear-gradient(180deg,#fdf6f5,#f8e8e6);
    border-color:rgba(194,65,59,.22);
}
.diet-chip-veg i{background:#c2413b;color:#fff}
.diet-flag{display:inline-flex;align-items:center;gap:.28rem;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase}
.diet-flag-vegan{color:#2f8f5b}
.diet-flag-veg{color:#c2413b}
.allergen-line{display:flex;flex-wrap:wrap;gap:.22rem;margin-top:.28rem}
.allergen-chip{display:inline-flex;min-width:1.2rem;align-items:center;justify-content:center;border-radius:.28rem;background:#132a3a;padding:.1rem .28rem;font-size:10px;font-weight:700;letter-spacing:.03em;line-height:1.15;color:#f7f3eb;text-transform:lowercase}
.pkg-aside-bar{height:6px;background:linear-gradient(90deg,#132a3a,#d89b2b 55%,#5e7568)}
.pkg-aside-head{background:linear-gradient(160deg,#f7f3eb,#ffffff)}
.pkg-mobile-bar{
    position:fixed;left:0;right:0;bottom:0;z-index:45;
    display:none;align-items:center;gap:.75rem;
    padding:.75rem 1rem calc(.75rem + env(safe-area-inset-bottom));
    background:rgba(255,255,255,.96);border-top:1px solid #e6e0d6;
    box-shadow:0 -10px 30px rgba(27,36,48,.08);
}
@media (max-width: 1023px){
    .pkg-mobile-bar{display:flex}
    .pkg-page{padding-bottom:7.5rem;overflow-x:hidden}
    .pkg-hero h1{font-size:1.55rem;line-height:1.15}
    .pkg-hero-row{padding:.4rem .55rem}
    a[aria-label="WhatsApp"]{bottom:7.5rem !important}
}
@media (max-width: 640px){
    #dish-zoom,#dish-zoom img{width:min(280px,calc(100vw - 24px));height:min(280px,calc(100vw - 24px));max-width:calc(100vw - 24px);max-height:calc(100vw - 24px)}
}
#dish-zoom.is-lightbox{
    inset:0;width:auto;height:auto;display:flex;align-items:center;justify-content:center;
    background:rgba(27,36,48,.55);pointer-events:auto;padding:16px;
    -webkit-tap-highlight-color:transparent;
}
#dish-zoom.is-lightbox img{
    width:min(88vw,420px);height:min(88vw,420px);max-width:88vw;max-height:70vh;
}
[data-dish-preview]{cursor:zoom-in}
</style>
@endpush

@section('content')
@php
    $slotsJson = $slots->values();
    $menuLabel = $meta['label'] ?? 'Buffets';
    $compare = $package->compare_price ? (float) $package->compare_price : null;
    $price = (float) $package->price_per_person;
@endphp

<section class="pkg-page bg-cream pb-24">
    <div class="container-velora pt-5 sm:pt-8">
        <x-breadcrumb :items="[
            ['label' => $menuLabel, 'url' => route('packages.index', $package->menu_group ?: 'buffets')],
            ['label' => $package->name],
        ]" />

        <div x-data="packageBuilder({{ Js::from([
            'slots' => $slotsJson,
            'min' => (int) $package->min_persons,
            'max' => $package->max_persons,
            'price' => $price,
            'compare' => $compare,
            'cartUrl' => $package->cartUrl(),
            'persons' => max((int) $package->min_persons, (int) request('personen', $package->min_persons)),
            'extras' => $extras ?? [],
        ]) }})">
            <div class="grid items-start gap-8 lg:grid-cols-12 lg:gap-12">
            <div class="lg:col-span-7">
                <div class="pkg-hero overflow-hidden rounded-2xl md:rounded-[1.5rem]">
                    <div class="grid md:grid-cols-2">
                        <div class="flex flex-col justify-center p-5 sm:p-7 md:p-10">
                            <h1 class="text-3xl font-bold leading-tight md:text-4xl">{{ $package->name }}</h1>
                            <p class="mt-3 text-sm text-charcoal/70">{{ $package->subtitle ?: 'Freie Auswahl von der Speisekarte' }}</p>
                            <ul class="mt-6 space-y-2 sm:mt-7">
                                @foreach ($package->slots as $slot)
                                    @php
                                        $tones = ['#5e7568', '#132a3a', '#d89b2b', '#4a7fd4'];
                                        $tone = $tones[$loop->index % count($tones)];
                                    @endphp
                                    <li class="pkg-hero-row text-sm text-charcoal/85">
                                        <span class="pkg-hero-check" style="background:{{ $tone }}">✓</span>
                                        <span><strong class="font-semibold text-charcoal">{{ $slot->required_count }}</strong> {{ $slot->name }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            @if ($package->hasDiscount())
                                <p class="pkg-hero-aktion mt-6 sm:mt-7">−{{ $package->discountPercent() }}% Aktion</p>
                            @endif
                        </div>
                        <div class="order-first p-3 md:order-none md:p-4">
                            <img src="{{ $package->imageUrl() }}" alt="{{ $package->name }}" class="h-48 w-full rounded-xl object-cover sm:h-56 md:h-full md:min-h-[240px]">
                        </div>
                    </div>
                </div>

                <p class="mt-5 text-xs leading-relaxed text-muted">
                    Allergene als Buchstaben an jeder Speise.
                    <a href="{{ route('legal.allergene') }}" class="font-semibold text-bronze hover:underline">Legende ansehen</a>
                </p>
                <div class="mt-6 flex flex-wrap items-center gap-2.5">
                    <span class="diet-chip diet-chip-vegan">
                        <i aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 21c8-2 12-8 14-16-7 1-12 6-14 16Z"/><path d="M5 21c3-7 8-11 14-13"/></svg>
                        </i>
                        <span><b>Vegan</b></span>
                    </span>
                    <span class="diet-chip diet-chip-veg">
                        <i aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21c-4.5-2.2-7-6.2-7-10.2C5 6.5 8.1 4 12 4s7 2.5 7 6.8c0 4-2.5 8-7 10.2Z"/><path d="M12 11V4"/></svg>
                        </i>
                        <span><b>Vegetarisch</b></span>
                    </span>
                </div>

                <ol class="mt-8 space-y-4">
                    <template x-for="(slot, index) in slots" :key="slot.id">
                        <li class="package-dish overflow-hidden rounded-[1.35rem] border bg-white shadow-soft transition"
                            :data-slot-id="slot.id"
                            :class="slotDone(slot) ? 'border-sage/35' : 'border-beige'">
                            <button type="button" class="flex w-full items-center gap-2.5 px-3 py-3 text-left sm:gap-4 sm:px-5 sm:py-4 md:px-6" @click="open = open === slot.id ? null : slot.id">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-[11px] font-semibold tracking-wide sm:h-9 sm:w-9"
                                      :class="slotDone(slot) ? 'bg-sage text-white' : 'bg-cream text-charcoal/55'"
                                      x-text="slotDone(slot) ? '✓' : String(index + 1).padStart(2, '0')"></span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[15px] font-bold leading-snug sm:text-xl" x-text="'Wähle ' + slot.name"></p>
                                    <div class="mt-2 h-1 overflow-hidden rounded-full bg-beige">
                                        <div class="h-full rounded-full transition-all duration-300" :class="slotDone(slot) ? 'bg-sage' : 'bg-terracotta'" :style="'width:' + slotPercent(slot) + '%'"></div>
                                    </div>
                                </div>
                                <span class="shrink-0 text-xs tabular-nums sm:text-sm"
                                      :class="slotDone(slot) ? 'text-sage' : 'text-muted'"
                                      x-text="(selected[slot.id] || []).length + ' / ' + slot.required"></span>
                            </button>
                            <div x-show="open === slot.id" x-collapse>
                                <div class="divide-y divide-beige/80 border-t border-beige">
                                    <template x-for="product in slot.products" :key="product.id">
                                        <button type="button"
                                                class="flex w-full items-start gap-2.5 px-3 py-3 text-left transition sm:items-center sm:gap-4 sm:px-5 sm:py-3.5 md:px-6"
                                                :class="isSelected(slot.id, product.id)
                                                    ? 'bg-sage-soft/70'
                                                    : (slotDone(slot) ? 'cursor-not-allowed opacity-45' : 'hover:bg-cream/70')"
                                                @click="toggle(slot, product.id)">
                                            <img x-show="product.image" data-dish-preview :src="product.image" :alt="product.name" class="h-11 w-11 shrink-0 rounded-xl object-cover sm:h-16 sm:w-16">
                                            <div class="min-w-0 flex-1">
                                                <p class="font-medium leading-snug break-words text-charcoal" x-text="product.name"></p>
                                                <p class="mt-0.5 hidden text-xs text-muted sm:line-clamp-1 sm:block" x-show="product.short_description" x-text="product.short_description"></p>
                                                <p class="allergen-line" x-show="product.allergens && product.allergens.length">
                                                    <template x-for="code in (product.allergens || [])" :key="code">
                                                        <span class="allergen-chip" x-text="code" :title="product.allergen_title || code"></span>
                                                    </template>
                                                </p>
                                                <p class="mt-1 flex flex-wrap items-center gap-1.5">
                                                    <span x-show="product.calories" class="text-[11px] font-semibold tabular-nums text-charcoal/55" x-text="'ca. ' + product.calories + ' kcal'"></span>
                                                    <span x-show="product.is_vegan" class="diet-flag diet-flag-vegan">
                                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 21c8-2 12-8 14-16-7 1-12 6-14 16Z"/><path d="M5 21c3-7 8-11 14-13"/></svg>
                                                        Vegan
                                                    </span>
                                                    <span x-show="product.is_vegetarian && !product.is_vegan" class="diet-flag diet-flag-veg">
                                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21c-4.5-2.2-7-6.2-7-10.2C5 6.5 8.1 4 12 4s7 2.5 7 6.8c0 4-2.5 8-7 10.2Z"/><path d="M12 11V4"/></svg>
                                                        Vegetarisch
                                                    </span>
                                                </p>
                                            </div>
                                            <span class="mt-1 flex h-6 w-6 shrink-0 items-center justify-center rounded-full border-2 text-[11px] transition sm:mt-0"
                                                  :class="isSelected(slot.id, product.id) ? 'border-sage bg-sage text-white' : 'border-beige text-transparent'">✓</span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </li>
                    </template>
                    <li x-show="extras.length" x-cloak
                        class="package-dish overflow-hidden rounded-[1.35rem] border bg-white shadow-soft transition"
                        :class="selectedExtras.length ? 'border-sage/35' : 'border-beige'">
                        <button type="button" class="flex w-full items-center gap-2.5 px-3 py-3 text-left sm:gap-4 sm:px-5 sm:py-4 md:px-6" @click="open = open === 'extras' ? null : 'extras'">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-[11px] font-semibold tracking-wide sm:h-9 sm:w-9"
                                  :class="selectedExtras.length ? 'bg-sage text-white' : 'bg-cream text-charcoal/55'"
                                  x-text="String(slots.length + 1).padStart(2, '0')"></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-[15px] font-bold leading-snug sm:text-xl">Extras</p>
                                <div class="mt-2 h-1 overflow-hidden rounded-full bg-beige">
                                    <div class="h-full rounded-full bg-sage transition-all duration-300" :style="'width:' + extrasPercent() + '%'"></div>
                                </div>
                            </div>
                            <span class="shrink-0 text-xs sm:text-sm"
                                  :class="selectedExtras.length ? 'text-sage' : 'text-muted'"
                                  x-text="selectedExtras.length ? selectedExtras.length + ' gewählt' : 'optional'"></span>
                        </button>
                        <div x-show="open === 'extras'" x-collapse>
                            <p class="border-t border-beige px-3 py-3 text-sm leading-relaxed text-muted sm:px-5 md:px-6">Optional. Der Preis richtet sich nach der Personenzahl: bei 10 Gästen wird ein Getränk 10-mal berechnet.</p>
                            <div class="divide-y divide-beige/80 border-t border-beige">
                                <template x-for="extra in extras" :key="extra.id">
                                    <button type="button"
                                            class="flex w-full cursor-pointer items-center gap-2.5 px-3 py-3 text-left transition sm:gap-4 sm:px-5 sm:py-3.5 md:px-6"
                                            :class="isExtra(extra.id) ? 'bg-sage-soft/70' : 'hover:bg-cream/70'"
                                            @click="toggleExtra(extra.id)">
                                        <img :src="extra.image" :alt="extra.name" width="48" height="48" class="extra-thumb h-11 w-11 shrink-0 rounded-xl object-cover sm:h-16 sm:w-16">
                                        <span class="min-w-0 flex-1">
                                            <span class="block font-medium leading-snug text-charcoal" x-text="extra.name"></span>
                                            <span class="mt-0.5 block text-xs text-muted" x-text="extra.price_label"></span>
                                        </span>
                                        <span class="shrink-0 text-right">
                                            <span class="block text-sm font-semibold text-bronze" x-text="formatMoney(extraLine(extra))"></span>
                                            <span class="mt-0.5 block text-[10px] uppercase tracking-wide text-muted" x-text="isExtra(extra.id) ? 'gewählt' : 'optional'"></span>
                                        </span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </li>
                </ol>
            </div>

            <aside class="hidden lg:col-span-5 lg:block">
                <div class="overflow-hidden rounded-2xl border border-bronze/15 bg-white shadow-lift lg:sticky lg:top-24 lg:rounded-[1.6rem]">
                    <div class="pkg-aside-bar"></div>
                    <div class="pkg-aside-head border-b border-beige px-5 py-5 sm:px-7 sm:py-6">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-muted">{{ $menuLabel }} · Festpreis</p>
                        <div class="mt-3 flex items-start gap-3">
                            <img src="{{ $package->imageUrl() }}" alt="{{ $package->name }}" class="h-14 w-14 shrink-0 rounded-xl object-cover">
                            <div class="min-w-0">
                                <h2 class="text-2xl font-bold leading-tight md:text-3xl">{{ $package->name }}</h2>
                                <div class="mt-2 flex flex-wrap items-end gap-3">
                                    <p class="text-2xl font-bold text-charcoal">{{ number_format($price, 2, ',', '.') }} € <span class="text-base font-medium text-muted">p.P.</span></p>
                                    @if ($compare && $compare > $price)
                                        <p class="pb-0.5 text-sm text-muted line-through">{{ number_format($compare, 2, ',', '.') }} €</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-muted">inkl. MwSt. · ab {{ $package->min_persons }} Personen</p>
                    </div>

                    <div class="px-5 py-5 sm:px-7 sm:py-6">
                        <p class="text-sm leading-relaxed text-charcoal/70">{{ $package->description ?: 'Stellen Sie in wenigen Schritten Ihr Wunschmenü zusammen – zum transparenten Festpreis.' }}</p>

                        <div class="mt-5 flex flex-wrap gap-2">
                            <span class="pill !py-1">Frisch zubereitet</span>
                            <span class="pill !py-1">Regional</span>
                            <span class="pill !py-1">Inkl. MwSt.</span>
                        </div>

                        <label class="mt-7 block text-[11px] font-semibold uppercase tracking-[0.18em] text-muted">Personen</label>
                        <div class="mt-2 flex items-center overflow-hidden rounded-xl border border-beige bg-ivory">
                            <button type="button" class="px-4 py-3 text-lg text-charcoal/60 hover:text-charcoal" @click="setPersons(persons - 1)">−</button>
                            <input type="number" min="{{ $package->min_persons }}" @if($package->max_persons) max="{{ $package->max_persons }}" @endif x-model.number="persons" inputmode="numeric" class="w-full border-0 bg-transparent py-3 text-center text-base font-medium focus:outline-none focus:ring-0">
                            <button type="button" class="px-4 py-3 text-lg text-charcoal/60 hover:text-charcoal" @click="setPersons(persons + 1)">+</button>
                        </div>

                        <div class="mt-6 flex items-end justify-between gap-3">
                            <div>
                                <p class="text-[11px] uppercase tracking-[0.16em] text-muted">Gesamt</p>
                                <p class="text-3xl font-bold leading-none sm:text-4xl" x-text="formatTotal()"></p>
                            </div>
                            <p class="pb-1 text-right text-xs text-muted" x-text="persons + ' × {{ number_format($price, 2, ',', '.') }} €'"></p>
                        </div>
                        <p class="mt-2 text-xs text-muted" x-show="extrasTotal() > 0" x-cloak x-text="'davon Extras ' + formatMoney(extrasTotal())"></p>

                        <p class="mt-3 text-sm text-danger" x-show="error" x-text="error" x-cloak></p>
                        <p class="mt-3 text-sm text-success" x-show="message" x-text="message" x-cloak></p>

                        <button type="button"
                                class="btn-primary mt-6 w-full"
                                :class="complete ? '' : 'cursor-not-allowed !bg-beige !text-muted !shadow-none'"
                                :disabled="!complete || loading"
                                @click="submit()">
                            <span x-show="!loading">Zur Kasse</span>
                            <span x-show="loading" x-cloak>Wird gespeichert…</span>
                        </button>
                        <p class="mt-3 text-center text-xs text-muted" x-show="!complete">Bitte alle Kategorien vollständig wählen.</p>

                        <div class="mt-7 border-t border-beige pt-5">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-muted">Ihre Auswahl <span class="tabular-nums" x-text="'(' + selectedCount + ')'"></span></p>
                            <ul class="mt-3 space-y-4 text-sm">
                                <template x-for="group in selectedGroups" :key="group.slot">
                                    <li>
                                        <p class="text-[10px] uppercase tracking-[0.14em] text-muted" x-text="group.slot"></p>
                                        <div class="mt-1.5 space-y-1.5">
                                            <template x-for="p in group.products" :key="p.id">
                                                <div class="flex items-center gap-2">
                                                    <img x-show="p.image" :src="p.image" :alt="p.name" width="32" height="32" class="h-8 w-8 shrink-0 rounded-lg object-cover">
                                                    <span class="min-w-0 truncate text-charcoal" x-text="p.name"></span>
                                                    <span x-show="p.calories" class="ml-auto shrink-0 text-[11px] tabular-nums text-muted" x-text="'ca. ' + p.calories + ' kcal'"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </li>
                                </template>
                                <li x-show="selectedCount === 0" class="text-muted">Noch keine Speisen gewählt</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </aside>
            </div>

            <div class="pkg-mobile-bar">
                <div class="flex items-center overflow-hidden rounded-xl border border-beige bg-ivory">
                    <button type="button" class="px-3 py-2 text-lg text-charcoal/60" @click="setPersons(persons - 1)" aria-label="Weniger Personen">−</button>
                    <span class="min-w-[2rem] text-center text-sm font-semibold tabular-nums" x-text="persons"></span>
                    <button type="button" class="px-3 py-2 text-lg text-charcoal/60" @click="setPersons(persons + 1)" aria-label="Mehr Personen">+</button>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-muted">Gesamt</p>
                    <p class="mt-0.5 text-lg font-bold leading-none tabular-nums" x-text="formatTotal()"></p>
                </div>
                <button type="button"
                        class="btn-primary !px-3.5 !py-3 shrink-0 text-sm"
                        :class="complete ? '' : 'cursor-not-allowed !bg-beige !text-muted !shadow-none'"
                        :disabled="!complete || loading"
                        @click="submit()">
                    <span x-show="!loading">Kasse</span>
                    <span x-show="loading" x-cloak>…</span>
                </button>
            </div>
        </div>
    </div>
</section>

@if (filled($package->seo_text))
<section class="bg-white px-4 py-14 md:py-20" aria-label="{{ $package->name }}">
    <div class="mx-auto max-w-3xl">
        <div class="space-y-4 text-base leading-relaxed text-charcoal/80">
            @foreach (preg_split("/\n\s*\n/", trim($package->seo_text)) as $paragraph)
                @if (trim($paragraph) !== '')
                    <p>{{ trim($paragraph) }}</p>
                @endif
            @endforeach
        </div>
    </div>
</section>
@endif

@if (($related ?? collect())->isNotEmpty())
<section class="section-pad bg-ivory">
    <div class="container-velora">
        <x-section-heading :title="'Weitere '.$menuLabel" class="mb-10" />
        <div class="grid gap-5 lg:grid-cols-3">
            @foreach ($related as $item)
                <x-package-card :package="$item" />
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('packageBuilder', (cfg) => ({
        slots: cfg.slots || [],
        extras: cfg.extras || [],
        selectedExtras: [],
        persons: cfg.persons || 10,
        min: cfg.min || 10,
        max: cfg.max || null,
        price: cfg.price || 0,
        cartUrl: cfg.cartUrl,
        selected: {},
        open: cfg.slots?.[0]?.id ?? null,
        loading: false,
        error: '',
        message: '',
        init() {
            this.slots.forEach((s) => { this.selected[s.id] = []; });
        },
        slotDone(slot) {
            return (this.selected[slot.id] || []).length === slot.required;
        },
        slotPercent(slot) {
            const n = (this.selected[slot.id] || []).length;
            return slot.required ? Math.min(100, Math.round((n / slot.required) * 100)) : 0;
        },
        isSelected(slotId, productId) {
            return (this.selected[slotId] || []).includes(productId);
        },
        toggle(slot, productId) {
            const current = [...(this.selected[slot.id] || [])];
            const idx = current.indexOf(productId);
            if (idx >= 0) {
                current.splice(idx, 1);
                this.selected = { ...this.selected, [slot.id]: current };
                this.error = '';
                return;
            }
            if (current.length >= slot.required) {
                return;
            }
            current.push(productId);
            this.selected = { ...this.selected, [slot.id]: current };
            this.error = '';
            if (current.length >= slot.required) {
                this.$nextTick(() => this.goToNextSlot(slot.id));
            }
        },
        goToNextSlot(currentId) {
            const index = this.slots.findIndex((s) => s.id === currentId);
            const next = this.slots.slice(index + 1).find((s) => !this.slotDone(s));
            if (next) {
                this.open = next.id;
                return;
            }
            this.open = this.extras.length ? 'extras' : null;
        },
        extrasPercent() {
            if (!this.extras.length) return 0;
            return Math.min(100, Math.round((this.selectedExtras.length / this.extras.length) * 100));
        },
        setPersons(n) {
            let v = Number(n) || this.min;
            if (v < this.min) v = this.min;
            if (this.max && v > this.max) v = this.max;
            this.persons = v;
        },
        get complete() {
            return this.slots.every((s) => (this.selected[s.id] || []).length === s.required)
                && this.persons >= this.min
                && (!this.max || this.persons <= this.max);
        },
        get selectedCount() {
            return Object.values(this.selected).reduce((n, ids) => n + ids.length, 0);
        },
        get selectedGroups() {
            return this.slots.map((slot) => {
                const products = (this.selected[slot.id] || []).map((id) => {
                    return slot.products.find((x) => x.id === id);
                }).filter(Boolean);
                return products.length ? { slot: slot.name, products } : null;
            }).filter(Boolean);
        },
        isExtra(id) {
            return this.selectedExtras.includes(id);
        },
        toggleExtra(id) {
            if (this.isExtra(id)) {
                this.selectedExtras = this.selectedExtras.filter((item) => item !== id);
                return;
            }
            this.selectedExtras = [...this.selectedExtras, id];
        },
        extraLine(extra) {
            const persons = Math.max(1, Number(this.persons) || 1);
            const unit = Number(extra.price) || 0;
            if (extra.price_type === 'stück' || extra.price_type === 'person') {
                return unit * persons;
            }
            return unit;
        },
        extrasTotal() {
            return this.extras
                .filter((extra) => this.isExtra(extra.id))
                .reduce((sum, extra) => sum + this.extraLine(extra), 0);
        },
        formatMoney(value) {
            return new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(value || 0);
        },
        formatTotal() {
            const total = this.price * (this.persons || 0) + this.extrasTotal();
            return this.formatMoney(total);
        },
        async submit() {
            if (!this.complete) {
                this.error = 'Bitte wählen Sie alle erforderlichen Speisen aus.';
                return;
            }
            this.loading = true;
            this.error = '';
            try {
                const data = await window.Velora.fetchJson(this.cartUrl, {
                    method: 'POST',
                    body: {
                        persons: this.persons,
                        selections: this.slots.map((s) => ({
                            slot_id: s.id,
                            product_ids: this.selected[s.id] || [],
                        })),
                        extra_ids: this.selectedExtras,
                    },
                });
                this.message = data.message;
                window.location.href = data.redirect || '/checkout';
            } catch (e) {
                this.error = e.data?.message || Object.values(e.data?.errors || {}).flat().join(' ') || e.message;
            } finally {
                this.loading = false;
            }
        },
    }));
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    var box = document.createElement('div');
    box.id = 'dish-zoom';
    box.innerHTML = '<img alt="Gericht von Monnemtisch Catering">';
    document.body.appendChild(box);
    var pic = box.querySelector('img');
    var size = 300;

    function hide() {
        box.classList.remove('is-on', 'is-lightbox');
        box.style.left = '';
        box.style.top = '';
        box.style.right = '';
        box.style.bottom = '';
    }

    function srcFrom(thumb) {
        return thumb.currentSrc || thumb.getAttribute('src') || '';
    }

    function showHover(thumb) {
        var src = srcFrom(thumb);
        if (!src) return;
        box.classList.remove('is-lightbox');
        pic.src = src;
        pic.alt = thumb.getAttribute('alt') || '';
        var r = thumb.getBoundingClientRect();
        var left = r.right + 16;
        if (left + size > window.innerWidth - 12) {
            left = r.left - size - 16;
        }
        if (left < 8) left = 8;
        var top = r.top + (r.height / 2) - (size / 2);
        top = Math.max(12, Math.min(top, window.innerHeight - size - 12));
        box.style.left = left + 'px';
        box.style.top = top + 'px';
        box.classList.add('is-on');
    }

    function showLightbox(thumb) {
        var src = srcFrom(thumb);
        if (!src) return;
        pic.src = src;
        pic.alt = thumb.getAttribute('alt') || '';
        box.style.left = '0';
        box.style.top = '0';
        box.classList.add('is-lightbox', 'is-on');
    }

    if (canHover) {
        document.addEventListener('mouseover', function (e) {
            var thumb = e.target.closest('[data-dish-preview]');
            if (!thumb) return;
            showHover(thumb);
        });
        document.addEventListener('mouseout', function (e) {
            var thumb = e.target.closest('[data-dish-preview]');
            if (!thumb) return;
            if (thumb.contains(e.relatedTarget)) return;
            hide();
        });
        return;
    }

    document.addEventListener('click', function (e) {
        var thumb = e.target.closest('[data-dish-preview]');
        if (box.classList.contains('is-on')) {
            e.preventDefault();
            e.stopPropagation();
            hide();
            return;
        }
        if (!thumb) return;
        e.preventDefault();
        e.stopPropagation();
        showLightbox(thumb);
    }, true);
});
</script>
@endpush
