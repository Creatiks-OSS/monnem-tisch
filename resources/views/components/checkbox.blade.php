@props([
    'label' => null,
    'name' => null,
    'value' => '1',
    'checked' => false,
])

<label {{ $attributes->only('class')->merge(['class' => 'flex items-start gap-3 text-sm text-charcoal/85']) }}>
    <input
        type="checkbox"
        @if ($name) name="{{ $name }}" @endif
        value="{{ $value }}"
        @checked(old($name, $checked))
        {{ $attributes->except('class')->merge(['class' => 'mt-1 h-4 w-4 rounded-sm border-beige text-bronze focus:ring-bronze']) }}
    >
    <span>@if ($label){{ $label }}@else{{ $slot }}@endif</span>
</label>
@if ($name && $errors->has($name))
    <p class="mt-1 text-xs text-danger">{{ $errors->first($name) }}</p>
@endif
