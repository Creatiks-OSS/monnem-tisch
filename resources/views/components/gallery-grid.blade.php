@props([
    'images' => [],
    'lightbox' => true,
])

@php
    $items = collect($images)->map(function ($img, $i) {
        if (is_string($img)) {
            return ['src' => $img, 'alt' => 'Galeriebild '.($i + 1), 'caption' => null];
        }
        $path = $img->path ?? $img->url ?? $img->image ?? $img['src'] ?? null;
        if ($path && ! str_starts_with($path, 'http') && ! str_starts_with($path, '/')) {
            $path = asset('storage/'.$path);
        }
        return [
            'src' => $path ?: asset('images/placeholders/food-'.(($i % 4) + 1).'.svg'),
            'alt' => $img->alt ?? $img->title ?? $img['alt'] ?? 'Galeriebild',
            'caption' => $img->caption ?? $img->title ?? null,
        ];
    })->values();
@endphp

<div
    {{ $attributes->merge(['class' => 'gallery-masonry']) }}
    @if ($lightbox)
        x-data="galleryLightbox({{ Js::from($items->pluck('src')->all()) }}, {{ Js::from($items->pluck('alt')->all()) }})"
        @keydown.escape.window="open && close()"
    @endif
>
    @forelse ($items as $i => $item)
        <button
            type="button"
            @if ($lightbox) @click="show({{ $i }})" @endif
            class="group relative block w-full overflow-hidden bg-beige text-left"
        >
            <img
                src="{{ $item['src'] }}"
                alt="{{ $item['alt'] }}"
                loading="lazy"
                class="w-full object-cover transition duration-700 group-hover:scale-[1.03]"
            >
            @if ($item['caption'])
                <span class="absolute inset-x-0 bottom-0 bg-charcoal/60 px-3 py-2 text-xs text-cream opacity-0 transition group-hover:opacity-100">
                    {{ $item['caption'] }}
                </span>
            @endif
        </button>
    @empty
        <p class="text-sm text-muted">Noch keine Bilder vorhanden.</p>
    @endforelse

    @if ($lightbox)
        <div
            x-show="open"
            x-cloak
            class="fixed inset-0 z-[80] flex items-center justify-center bg-charcoal/90 p-4"
            @click.self="close()"
        >
            <button type="button" class="absolute right-4 top-4 text-cream" @click="close()" aria-label="{{ __('frontend.common.close') }}">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
            <button type="button" class="absolute left-3 top-1/2 -translate-y-1/2 text-cream/80 hover:text-cream" @click.stop="prev()" aria-label="Zurück">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19 8 12l7-7"/></svg>
            </button>
            <img :src="images[index]" :alt="alts[index] || 'Galeriebild von Monnemtisch Catering'" class="max-h-[85vh] max-w-full object-contain">
            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-cream/80 hover:text-cream" @click.stop="next()" aria-label="Weiter">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m9 5 7 7-7 7"/></svg>
            </button>
        </div>
    @endif
</div>
