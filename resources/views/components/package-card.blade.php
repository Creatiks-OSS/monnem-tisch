@props([
    'package',
])

@php
    $url = $package->publicUrl();
    $price = (float) $package->price_per_person;
    $meta = $package->menuMeta();
@endphp

<a href="{{ $url }}" {{ $attributes->merge(['class' => 'group flex h-full min-w-0 flex-col overflow-hidden rounded-2xl bg-white shadow-soft transition hover:-translate-y-0.5 hover:shadow-lift']) }}>
    <div class="aspect-[16/10] w-full shrink-0 overflow-hidden">
        <img src="{{ $package->imageUrl() }}" alt="{{ $package->name }}" class="h-full w-full object-cover" loading="lazy">
    </div>
    <div class="flex min-w-0 flex-1 flex-col p-4 sm:p-5">
        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-muted">Paket {{ $meta['label'] ?? '' }}</p>
        <h3 class="mt-1 text-lg font-bold leading-snug break-words text-charcoal sm:text-xl">{{ $package->name }}</h3>
        @if ($package->slots->isNotEmpty())
            <div class="mt-2 flex flex-wrap gap-1">
                @foreach ($package->slots as $slot)
                    <span class="rounded-md bg-cream px-1.5 py-0.5 text-[10px] font-semibold leading-tight text-charcoal/75">{{ $slot->required_count }} {{ $slot->name }}</span>
                @endforeach
            </div>
        @endif
        <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-muted">{{ $package->description ?: 'Freie Auswahl von der Speisekarte.' }}</p>
        <p class="mt-4 text-sm font-bold text-charcoal">ab {{ number_format($price, 2, ',', '.') }} € p.P.</p>
        <span class="btn-primary mt-4 w-full">Jetzt bestellen</span>
    </div>
</a>
