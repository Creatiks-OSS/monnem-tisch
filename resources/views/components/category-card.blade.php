@props([
    'category',
])

@php
    $name = $category->name ?? 'Kategorie';
    $slug = $category->slug ?? null;
    $url = $slug && \App\Models\CateringPackage::isMenu($slug)
        ? route('packages.index', $slug)
        : route('packages.index', \App\Models\CateringPackage::menuKeyFromSlug($slug));
    $image = null;
    if (! empty($category->image) && ! str_contains((string) $category->image, 'placeholders')) {
        $image = str_starts_with($category->image, 'http') || str_starts_with($category->image, '/')
            ? $category->image
            : asset('storage/'.$category->image);
    }
    $image = $image ?: velora_photo_for($name, (int) ($category->id ?? 0));
    $starting = $category->starting_price ?? null;
@endphp

<a href="{{ $url }}" {{ $attributes->merge(['class' => 'group block overflow-hidden rounded-2xl bg-white p-3 shadow-soft transition hover:-translate-y-0.5 hover:shadow-lift']) }}>
    <div class="overflow-hidden rounded-xl">
        <img
            src="{{ $image }}"
            alt="{{ $name }}"
            loading="lazy"
            class="aspect-[4/3] h-full w-full object-cover transition duration-700 group-hover:scale-105"
        >
    </div>
    <div class="px-2 pb-2 pt-4">
        <h3 class="text-xl font-bold">{{ $name }}</h3>
        @if ($category->short_description ?? null)
            <p class="mt-1.5 line-clamp-2 text-sm text-muted">{{ $category->short_description }}</p>
        @endif
        <p class="mt-3 text-sm font-bold text-charcoal">
            @if ($starting !== null)
                {{ __t('frontend.common.from', 'ab') }} {{ number_format((float) $starting, 2, ',', '.') }} €
            @else
                {{ __t('frontend.cta.discover', 'Entdecken') }}
            @endif
        </p>
    </div>
</a>
