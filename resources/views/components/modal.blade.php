@props([
    'name' => 'modal',
    'title' => null,
])

<div
    x-data="{ open: false }"
    x-on:open-modal-{{ $name }}.window="open = true"
    x-on:close-modal-{{ $name }}.window="open = false"
    @keydown.escape.window="open = false"
    {{ $attributes }}
>
    <div
        x-show="open"
        x-cloak
        class="fixed inset-0 z-[70] flex items-center justify-center p-4"
    >
        <div class="absolute inset-0 bg-charcoal/70" @click="open = false"></div>
        <div
            x-show="open"
            x-transition
            class="relative z-10 w-full max-w-lg rounded-2xl bg-white p-6 shadow-lift md:p-8"
            role="dialog"
            aria-modal="true"
            @if ($title) aria-label="{{ $title }}" @endif
        >
            <div class="mb-4 flex items-start justify-between gap-4">
                @if ($title)
                    <h2 class="text-2xl font-bold">{{ $title }}</h2>
                @endif
                <button type="button" class="text-muted hover:text-charcoal" @click="open = false" aria-label="{{ __('frontend.common.close') }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </div>
            {{ $slot }}
        </div>
    </div>
</div>
