@extends('layouts.frontend')

@section('title', ($product->seo_title ?? $product->name).' – '.__('frontend.brand'))
@section('meta_description', $product->seo_description ?? $product->short_description ?? $product->name)

@section('schema')
    @php
        $schemas = app(\App\Services\SchemaService::class);
        $schemas->push($schemas->product($product));
        $recipe = $schemas->recipe($product);
        if ($recipe) {
            $schemas->push($recipe);
        }
    @endphp
@endsection

@section('content')
@php
    $images = collect();
    if ($product->image_url) {
        $images->push(['src' => $product->image_url, 'alt' => $product->name]);
    } elseif ($product->image) {
        $src = str_starts_with($product->image, 'http') || str_starts_with($product->image, '/')
            ? $product->image
            : asset('storage/'.$product->image);
        $images->push(['src' => $src, 'alt' => $product->name]);
    }
    foreach ($product->images ?? [] as $img) {
        $src = $img->url ?? null;
        if (! $src && $img->path) {
            $src = str_starts_with($img->path, 'http') || str_starts_with($img->path, '/')
                ? $img->path
                : asset('storage/'.$img->path);
        }
        if ($src) {
            $images->push(['src' => $src, 'alt' => $img->alt ?? $product->name]);
        }
    }
    if ($images->isEmpty()) {
        $images->push(['src' => asset('images/placeholders/product.svg'), 'alt' => $product->name]);
    }
    $minPersons = (int) ($product->minimum_persons ?? 1);
@endphp

<div class="container-velora py-10 md:py-14">
    <x-breadcrumb :items="[
        ['label' => __('frontend.nav.products'), 'url' => route('products.index')],
        ['label' => $product->category->name ?? __('frontend.nav.catering'), 'url' => $product->category?->slug ? route('catering.show', $product->category->slug) : route('catering.index')],
        ['label' => $product->name],
    ]" />

    <div class="grid gap-10 lg:grid-cols-2 lg:gap-16" x-data="{ active: 0, images: {{ Js::from($images->pluck('src')) }} }">
        <div>
            <div class="overflow-hidden bg-beige">
                <img :src="images[active]" alt="{{ $product->name }}" class="aspect-[4/5] w-full object-cover">
            </div>
            @if ($images->count() > 1)
                <div class="mt-3 grid grid-cols-4 gap-2">
                    @foreach ($images as $i => $image)
                        <button type="button" class="overflow-hidden border" :class="active === {{ $i }} ? 'border-bronze' : 'border-transparent'" @click="active = {{ $i }}">
                            <img src="{{ $image['src'] }}" alt="{{ $image['alt'] ?? $product->name }}" class="aspect-square w-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div
            x-data="addToCart({{ (int) $product->id }}, { persons: {{ $minPersons }}, quantity: 1 })"
        >
            @if ($product->category)
                <p class="text-xs uppercase tracking-[0.18em] text-bronze">{{ $product->category->name }}</p>
            @endif
            <h1 class="mt-2 font-serif text-4xl md:text-5xl">{{ $product->name }}</h1>

            <p class="mt-4 font-serif text-3xl text-bronze">
                {{ __('frontend.common.from') }} {{ number_format((float) $product->price, 2, ',', '.') }} €
                @if (($product->price_type ?? 'person') === 'person')
                    <span class="text-base font-sans text-muted">{{ __('frontend.common.per_person') }}</span>
                @endif
            </p>

            @if ($product->short_description)
                <p class="mt-5 text-base leading-relaxed text-charcoal/75">{{ $product->short_description }}</p>
            @endif

            <div class="mt-4 flex flex-wrap gap-2 text-xs uppercase tracking-[0.12em]">
                @if ($product->is_vegan) <span class="border border-beige px-2 py-1 text-[#2f8f5b]">Vegan</span> @endif
                @if ($product->is_vegetarian) <span class="border px-2 py-1 text-[#c2413b]" style="border-color:rgba(194,65,59,.25);background:#fdf6f5">Vegetarisch</span> @endif
                @if ($product->is_gluten_free) <span class="border border-beige px-2 py-1">Glutenfrei</span> @endif
                @if ($product->is_lactose_free) <span class="border border-beige px-2 py-1">Laktosefrei</span> @endif
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label-velora" for="persons">{{ __('frontend.common.persons') }}</label>
                    <input id="persons" type="number" min="{{ $minPersons }}" max="{{ $product->maximum_persons ?? 5000 }}" x-model.number="persons" class="input-velora">
                </div>
                <div>
                    <label class="label-velora" for="quantity">{{ __('frontend.cart.quantity') }}</label>
                    <input id="quantity" type="number" min="{{ $product->minimum_quantity ?? 1 }}" x-model.number="quantity" class="input-velora">
                </div>
            </div>

            @if (($product->options ?? collect())->isNotEmpty())
                <div class="mt-6 space-y-3">
                    <p class="label-velora">Optionen</p>
                    @foreach ($product->options as $option)
                        <div class="border border-beige bg-ivory px-4 py-3 text-sm">
                            <span class="font-medium">{{ $option->name }}</span>
                            @if ($option->price_modifier)
                                <span class="text-bronze">(+{{ number_format((float) $option->price_modifier, 2, ',', '.') }} €)</span>
                            @endif
                            @if ($option->description)
                                <p class="mt-1 text-charcoal/65">{{ $option->description }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mt-8 flex flex-wrap gap-3">
                <button type="button" class="btn-primary" @click="submit()" :disabled="loading">
                    <span x-show="!loading">{{ __('frontend.cta.add_cart') }}</span>
                    <span x-show="loading" x-cloak>{{ __('frontend.common.loading') }}</span>
                </button>
                <button type="button" class="btn-secondary" x-data="favoritesButton({{ (int) $product->id }})" @click="toggle()">
                    <span x-text="active ? 'Gespeichert' : 'Merken'"></span>
                </button>
            </div>

            <p x-show="message" x-cloak class="mt-3 text-sm text-success" x-text="message"></p>
            <p x-show="error" x-cloak class="mt-3 text-sm text-danger" x-text="error"></p>

            @if ($product->description)
                <div class="prose-velora mt-10 border-t border-beige pt-8">
                    {!! $product->description !!}
                </div>
            @endif

            @if ($product->allergenBadges() !== [])
                <div class="mt-6 text-sm text-muted">
                    <strong class="text-charcoal">Allergene:</strong>
                    <x-allergen-badges :codes="$product->allergenCodes()" class="ml-1 align-middle" />
                    <a href="{{ route('legal.allergene') }}" class="ml-2 text-bronze hover:underline">Legende</a>
                </div>
            @endif
        </div>
    </div>

    @if (($reviews ?? collect())->isNotEmpty())
        <section class="mt-20">
            <h2 class="font-serif text-3xl">Bewertungen</h2>
            <div class="mt-8 grid gap-8 md:grid-cols-2">
                @foreach ($reviews as $review)
                    <x-testimonial :review="$review" />
                @endforeach
            </div>
        </section>
    @endif

    @if (($related ?? collect())->isNotEmpty())
        <section class="mt-20">
            <h2 class="font-serif text-3xl">{{ __('frontend.common.related') }}</h2>
            <div class="mt-8 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($related as $item)
                    <x-product-card :product="$item" />
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
