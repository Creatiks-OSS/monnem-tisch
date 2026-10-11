@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'required' => false,
])

<div {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>
    @if ($label)
        <label @if ($name) for="{{ $name }}" @endif class="label-velora">
            {{ $label }}@if ($required) <span class="text-bronze">*</span>@endif
        </label>
    @endif
    <input
        type="{{ $type }}"
        @if ($name) name="{{ $name }}" id="{{ $name }}" @endif
        @if ($required) required @endif
        {{ $attributes->except('class')->merge(['class' => 'input-velora']) }}
    >
    @if ($name && $errors->has($name))
        <p class="mt-1 text-xs text-danger">{{ $errors->first($name) }}</p>
    @endif
</div>
