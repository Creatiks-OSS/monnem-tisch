@extends('layouts.frontend')

@section('title', ($city->meta_title ?? ('Catering in '.$city->name)).' – '.__('frontend.brand'))
@section('meta_description', $city->meta_description ?? ('Premium Catering in '.$city->name.' von Monnemtisch Catering.'))

@section('schema')
    @php app(\App\Services\SchemaService::class)->pushJson($city->seo?->schema_json); @endphp
@endsection

@section('content')
<section class="bg-cream">
    <div class="container-velora py-12 md:py-16">
        <x-breadcrumb :items="[['label' => 'Catering in '.$city->name]]" />
        <p class="text-sm font-semibold text-bronze">Standort</p>
        <h1 class="mt-2 text-4xl font-bold md:text-5xl">Catering in {{ $city->name }}</h1>
        @if ($city->postal_code)
            <p class="mt-2 text-muted">PLZ {{ $city->postal_code }}</p>
        @endif
    </div>
</section>

<div class="container-velora py-12 md:py-16">
    @if ($city->content)
        <div class="prose-velora mx-auto max-w-3xl">{!! $city->content !!}</div>
    @else
        <p class="mx-auto max-w-3xl text-lg leading-relaxed text-charcoal/75">
            Monnemtisch Catering liefert Premium-Catering nach {{ $city->name }} – für Hochzeiten, Firmenfeiern und private Anlässe.
            @if ($city->delivery_price)
                Lieferpauschale ab {{ number_format((float) $city->delivery_price, 2, ',', '.') }} €.
            @endif
        </p>
    @endif

    @if (($menus ?? []) !== [])
        <section class="mt-16">
            <x-section-heading title="Menüs für {{ $city->name }}" class="mb-8" />
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($menus as $key => $menu)
                    <a href="{{ route('packages.index', $key) }}" class="rounded-2xl bg-white p-5 text-center shadow-soft transition hover:-translate-y-0.5 hover:shadow-lift">
                        <img src="{{ velora_photo($menu['photo']) }}" alt="{{ $menu['label'] }}" class="mx-auto h-16 w-16 rounded-full object-cover">
                        <h3 class="mt-3 text-base font-bold">{{ $menu['label'] }}</h3>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if (($packages ?? collect())->isNotEmpty())
        <section class="mt-16">
            <x-section-heading title="Empfehlungen für {{ $city->name }}" class="mb-8" />
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($packages as $package)
                    <x-package-card :package="$package" />
                @endforeach
            </div>
        </section>
    @endif

    @if (($reviews ?? collect())->isNotEmpty())
        <section class="mt-16 grid gap-8 md:grid-cols-2">
            @foreach ($reviews as $review)
                <x-testimonial :review="$review" />
            @endforeach
        </section>
    @endif

    @include('frontend.partials.faq-accordion', ['faqs' => $faqs ?? collect()])

    <div class="mt-12 flex flex-wrap gap-3">
        <x-button :href="route('quote.create')" variant="primary">{{ __('frontend.cta.quote') }}</x-button>
        <x-button :href="route('packages.index', 'buffets')" variant="secondary">{{ __('frontend.cta.configure') }}</x-button>
    </div>
</div>
@endsection
