@props([
    'label' => null,
    'name' => null,
    'required' => false,
    'placeholder' => null,
])

<div {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>
    @if ($label)
        <label @if ($name) for="{{ $name }}" @endif class="label-velora">
            {{ $label }}@if ($required) <span class="text-bronze">*</span>@endif
        </label>
    @endif
    <select
        @if ($name) name="{{ $name }}" id="{{ $name }}" @endif
        @if ($required) required @endif
        {{ $attributes->except('class')->merge(['class' => 'input-velora']) }}
    >
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        {{ $slot }}
    </select>
    @if ($name && $errors->has($name))
        <p class="mt-1 text-xs text-danger">{{ $errors->first($name) }}</p>
    @endif
</div>
