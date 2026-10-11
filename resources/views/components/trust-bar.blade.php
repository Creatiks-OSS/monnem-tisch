@props([
    'trust' => [],
])

<section {{ $attributes->merge(['class' => 'border-y border-beige bg-white']) }}>
    <div class="container-velora grid grid-cols-2 gap-6 py-8 lg:grid-cols-4">
        @foreach ([
            ['icon' => 'M3 7h13l5 5v7a1 1 0 0 1-1 1h-2a2 2 0 1 1-4 0H10a2 2 0 1 1-4 0H5a1 1 0 0 1-1-1V7Zm13 0v5h5', 'title' => 'Lieferung pünktlich'],
            ['icon' => 'M10 2c.4 3 2 5 4 6-2 1-3.6 3-4 6-.4-3-2-5-4-6 2-1 3.6-3 4-6Z', 'title' => 'Regionale Zutaten'],
            ['icon' => 'M10 1.5l2.4 4.86 5.36.78-3.88 3.78.92 5.34L10 13.9l-4.8 2.52.92-5.34L2.24 7.14l5.36-.78L10 1.5z', 'title' => 'Top Qualität & Service'],
            ['icon' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm13 10v-2a4 4 0 0 0-3-3.87M16.5 3.13a4 4 0 0 1 0 7.75', 'title' => 'Persönliche Beratung'],
        ] as $item)
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-sage-soft text-sage">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="{{ $item['icon'] }}"/></svg>
                </span>
                <p class="text-sm font-semibold text-charcoal">{{ $item['title'] }}</p>
            </div>
        @endforeach
    </div>
</section>
