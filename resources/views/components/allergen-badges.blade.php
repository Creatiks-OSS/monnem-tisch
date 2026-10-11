@props([
    'codes' => [],
    'linked' => true,
])

@php
    $badges = app(\App\Services\AllergenCatalog::class)->badges($codes);
@endphp

@if ($badges !== [])
    <span {{ $attributes->merge(['class' => 'allergen-badges']) }}>
        @foreach ($badges as $badge)
            @if ($linked)
                <a href="{{ route('legal.allergene') }}#{{ $badge['code'] }}" class="allergen-badge" title="{{ $badge['code'] }} — {{ $badge['label'] }}">{{ $badge['code'] }}</a>
            @else
                <span class="allergen-badge" title="{{ $badge['code'] }} — {{ $badge['label'] }}">{{ $badge['code'] }}</span>
            @endif
        @endforeach
    </span>
@endif
