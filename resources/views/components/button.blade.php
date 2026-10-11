@props([
    'variant' => 'primary',
    'type' => 'button',
    'href' => null,
    'tag' => null,
])

@php
    $classes = match ($variant) {
        'secondary' => 'btn-secondary',
        'ghost' => 'btn-ghost',
        'gold' => 'btn-gold',
        'outline' => 'btn-outline',
        default => 'btn-primary',
    };
    $tag = $tag ?: ($href ? 'a' : 'button');
@endphp

@if ($tag === 'a')
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
