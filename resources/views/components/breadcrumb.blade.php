@props([
    'items' => [],
])

@php
    $crumbs = collect($items)->filter(fn ($item) => ! empty($item['label'] ?? null))->values();
@endphp

@if ($crumbs->isNotEmpty())
@php
    $schemaCrumbs = [['name' => __('frontend.nav.home'), 'url' => route('home')]];
    foreach ($crumbs as $crumb) {
        $schemaCrumbs[] = array_filter([
            'name' => $crumb['label'],
            'url' => $crumb['url'] ?? null,
        ]);
    }
@endphp
@php
    app(\App\Services\SchemaService::class)->push(
        app(\App\Services\SchemaService::class)->breadcrumb($schemaCrumbs)
    );
@endphp
<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => 'mb-5 md:mb-8']) }}>
    <ol class="flex flex-wrap items-center gap-2 text-xs uppercase tracking-[0.14em] text-muted">
        <li>
            <a href="{{ route('home') }}" class="transition hover:text-bronze">{{ __('frontend.nav.home') }}</a>
        </li>
        @foreach ($crumbs as $item)
            <li aria-hidden="true" class="text-beige-dark">/</li>
            <li>
                @if (! empty($item['url']) && ! $loop->last)
                    <a href="{{ $item['url'] }}" class="transition hover:text-bronze">{{ $item['label'] }}</a>
                @else
                    <span class="text-charcoal/70">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
@endif
