@props([
    'type' => 'info',
    'dismissible' => true,
])

@php
    $styles = match ($type) {
        'success' => 'border-success/30 bg-success/10 text-success',
        'error', 'danger' => 'border-danger/30 bg-danger/10 text-danger',
        'warning' => 'border-warning/30 bg-warning/10 text-warning',
        default => 'border-bronze/30 bg-bronze/10 text-charcoal',
    };
@endphp

<div
    x-data="{ show: true }"
    x-show="show"
    {{ $attributes->merge(['class' => "mb-4 flex items-start justify-between gap-3 border px-4 py-3 text-sm {$styles}"]) }}
    role="alert"
>
    <div>{{ $slot }}</div>
    @if ($dismissible)
        <button type="button" class="shrink-0 opacity-70 hover:opacity-100" @click="show = false" aria-label="{{ __('frontend.common.close') }}">×</button>
    @endif
</div>
