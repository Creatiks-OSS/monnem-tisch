@props([
    'product',
    'lazy' => true,
])

@php
    $name = $product->name ?? 'Produkt';
    $slug = $product->slug ?? null;
    $url = $slug ? route('products.show', $slug) : '#';
    $rawImage = $product->image ?? null;
    if (is_string($rawImage) && str_contains($rawImage, 'placeholders')) {
        $rawImage = null;
    }
    $image = $product->image_url
        ?? (isset($product->images) && $product->images->isNotEmpty()
            ? (str_starts_with((string) $product->images->first()->path, 'http') || str_starts_with((string) $product->images->first()->path, '/')
                ? $product->images->first()->path
                : asset('storage/'.$product->images->first()->path))
            : null)
        ?? ($rawImage ? (str_starts_with($rawImage, 'http') || str_starts_with($rawImage, '/') ? $rawImage : asset('storage/'.$rawImage)) : null);
    if (is_string($image) && str_contains($image, 'placeholders')) {
        $image = null;
    }
    $price = $product->price ?? null;
    $priceType = $product->price_type ?? 'person';
    $category = $product->category->name ?? null;
@endphp

<article {{ $attributes->merge(['class' => 'group']) }}>
    <a href="{{ $url }}" class="img-zoom block overflow-hidden rounded-2xl bg-beige/40">
        <div class="aspect-[4/5]">
            @if ($image)
                <img
                    src="{{ $image }}"
                    alt="{{ $name }}"
                    @if ($lazy) loading="lazy" @endif
                    class="h-full w-full object-cover"
                >
            @endif
        </div>
    </a>
    <div class="pt-4">
        @if ($category)
            <p class="text-[0.68rem] uppercase tracking-[0.2em] text-muted">{{ $category }}</p>
        @endif
        <h3 class="mt-1 text-[1.35rem] font-bold leading-snug text-charcoal">
            <a href="{{ $url }}" class="transition hover:text-terracotta">{{ $name }}</a>
        </h3>
        @if ($product->short_description ?? null)
            <p class="mt-2 line-clamp-2 text-sm text-charcoal/65">{{ $product->short_description }}</p>
        @endif
        @if (method_exists($product, 'allergenCodes') && $product->allergenCodes() !== [])
            <div class="mt-2">
                <x-allergen-badges :codes="$product->allergenCodes()" />
            </div>
        @endif
        <div class="mt-3 flex items-end justify-between gap-3">
            @if ($price !== null)
                <p class="text-xl font-bold text-terracotta">
                    {{ __t('frontend.common.from', 'ab') }} {{ number_format((float) $price, 2, ',', '.') }} €
                    @if ($priceType === 'person')
                        <span class="block font-sans text-[11px] uppercase tracking-wide text-muted">{{ __t('frontend.common.per_person', 'pro Person') }}</span>
                    @endif
                </p>
            @endif
            <a href="{{ $url }}" class="text-[11px] uppercase tracking-[0.16em] text-charcoal/55 transition hover:text-bronze">
                {{ __t('frontend.cta.learn_more', 'Mehr erfahren') }}
            </a>
        </div>
    </div>
</article>
