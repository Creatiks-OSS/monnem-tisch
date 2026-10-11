@props([
    'eyebrow' => null,
    'title',
    'text' => null,
    'align' => 'left',
    'html' => false,
    'eyebrowClass' => 'text-terracotta',
    'level' => 2,
])

@php
    $alignClass = $align === 'center' ? 'text-center mx-auto' : 'text-left';
    $tag = (int) $level === 1 ? 'h1' : 'h2';
@endphp

<div {{ $attributes->merge(['class' => "max-w-2xl {$alignClass}"]) }}>
    @if ($eyebrow)
        <p class="mb-3 text-xs font-semibold uppercase tracking-[0.22em] {{ $eyebrowClass }}">{{ $eyebrow }}</p>
    @endif
    <{{ $tag }} class="text-3xl leading-tight text-charcoal md:text-4xl">
        @if ($html)
            {!! $title !!}
        @else
            {{ $title }}
        @endif
    </{{ $tag }}>
    @if ($text)
        <p class="mt-5 text-base leading-relaxed text-charcoal/75 md:text-lg">{{ $text }}</p>
    @endif
</div>
