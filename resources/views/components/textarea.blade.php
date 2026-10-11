@props([
    'label' => null,
    'name' => null,
    'required' => false,
    'rows' => 5,
])

<div {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>
    @if ($label)
        <label @if ($name) for="{{ $name }}" @endif class="label-velora">
            {{ $label }}@if ($required) <span class="text-bronze">*</span>@endif
        </label>
    @endif
    <textarea
        @if ($name) name="{{ $name }}" id="{{ $name }}" @endif
        rows="{{ $rows }}"
        @if ($required) required @endif
        {{ $attributes->except('class')->merge(['class' => 'input-velora']) }}
    >{{ $slot }}</textarea>
    @if ($name && $errors->has($name))
        <p class="mt-1 text-xs text-danger">{{ $errors->first($name) }}</p>
    @endif
</div>
