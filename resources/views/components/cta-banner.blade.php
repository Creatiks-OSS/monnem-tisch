@props([
    'title' => null,
    'text' => null,
    'primaryHref' => null,
    'primaryLabel' => null,
    'secondaryHref' => null,
    'secondaryLabel' => null,
])

<section {{ $attributes->merge(['class' => 'bg-navy text-cream']) }}>
    <div class="container-velora py-16 text-center md:py-20">
        <h2 class="mx-auto max-w-2xl text-3xl font-bold text-cream md:text-4xl">
            {!! $title ?? 'Lassen Sie uns gemeinsam etwas Besonderes schaffen' !!}
        </h2>
        <p class="mx-auto mt-4 max-w-xl text-cream/70">{{ $text ?? __t('frontend.home.cta_text', 'Erzählen Sie uns von Ihrem Event – wir melden uns mit einem maßgeschneiderten Vorschlag.') }}</p>
        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <x-button :href="$primaryHref ?? route('quote.create')" variant="primary">
                {{ $primaryLabel ?? 'Angebot erhalten' }}
            </x-button>
            <x-button :href="$secondaryHref ?? route('contact.show')" variant="secondary">
                {{ $secondaryLabel ?? __t('frontend.cta.contact', 'Beratung anfordern') }}
            </x-button>
        </div>
    </div>
</section>
