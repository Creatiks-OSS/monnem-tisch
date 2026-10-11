@extends('layouts.frontend')

@php
    $titles = [
        'hochzeiten' => 'Hochzeitscatering',
        'firmen-catering' => 'Firmen-Catering',
        'messe-catering' => 'Messe-Catering',
        'partyservice' => 'Partyservice',
    ];
    $pageTitle = $page->title ?? ($titles[$slug] ?? 'Catering');
    $intro = $page->excerpt
        ?? match ($slug) {
            'hochzeiten' => 'Kulinarische Eleganz für Ihren schönsten Tag – fein abgestimmt und unvergesslich serviert.',
            'firmen-catering' => 'Professionelles Catering für Meetings, Empfänge und Firmenfeiern.',
            'messe-catering' => 'Zuverlässiges Catering für Messestände, Lounges und Networking-Events.',
            'partyservice' => 'Festlicher Partyservice für private Feiern mit Charakter.',
            default => __('frontend.tagline'),
        };
@endphp

@section('title', $pageTitle.' – '.__('frontend.brand'))
@section('meta_description', $page->seo_description ?? $intro)

@section('content')
@php
    $hero = $page?->featured_image
        ?? optional(($sections ?? collect())->firstWhere('type', 'hero'))->image
        ?? asset('images/placeholders/hero.svg');
    if ($hero && ! str_starts_with($hero, 'http') && ! str_starts_with($hero, '/') && ! str_contains($hero, 'placeholders')) {
        $hero = asset('storage/'.$hero);
    }
@endphp

<section class="bg-cream">
    <div class="container-velora grid items-center gap-8 py-8 lg:grid-cols-2 lg:py-16">
        <div>
            <x-breadcrumb :items="[['label' => $pageTitle]]" />
            <p class="text-sm font-semibold text-bronze">Events</p>
            <h1 class="mt-2 max-w-xl text-3xl font-bold leading-tight md:text-5xl">{{ $pageTitle }}</h1>
            <p class="mt-4 max-w-xl text-muted">{{ $intro }}</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <x-button :href="route('quote.create')" variant="primary">Angebot erhalten</x-button>
                <x-button :href="route('packages.index', 'buffets')" variant="secondary">Menüs entdecken</x-button>
            </div>
        </div>
        <div class="order-first overflow-hidden rounded-2xl shadow-soft lg:order-none">
            <img src="{{ $hero }}" alt="{{ $pageTitle }}" class="aspect-[16/10] w-full object-cover">
        </div>
    </div>
</section>

@if (($sections ?? collect())->isNotEmpty())
    @foreach ($sections as $section)
        @if (($section->type ?? '') === 'hero') @continue @endif
        <section class="section-pad">
            <div class="container-velora max-w-3xl">
                @if ($section->title)
                    <h2 class="text-3xl font-bold">{{ $section->title }}</h2>
                @endif
                @if ($section->content)
                    <div class="prose-velora mt-4">{!! $section->content !!}</div>
                @endif
            </div>
        </section>
    @endforeach
@elseif ($page?->content)
    <section class="section-pad">
        <div class="container-velora prose-velora max-w-3xl">{!! $page->content !!}</div>
    </section>
@endif

@if (($packages ?? collect())->isNotEmpty())
    <section class="section-pad bg-cream">
        <div class="container-velora">
            <x-section-heading title="Passende Pakete" class="mb-10" />
            <div class="grid gap-5 lg:grid-cols-2">
                @foreach ($packages as $package)
                    <x-package-card :package="$package" />
                @endforeach
            </div>
        </div>
    </section>
@endif

@if (($products ?? collect())->isNotEmpty())
    <section class="section-pad">
        <div class="container-velora">
            <x-section-heading title="Empfehlungen" class="mb-10" />
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>
@endif

@if (($reviews ?? collect())->isNotEmpty())
    <section class="section-pad bg-ivory">
        <div class="container-velora grid gap-8 md:grid-cols-2">
            @foreach ($reviews as $review)
                <x-testimonial :review="$review" />
            @endforeach
        </div>
    </section>
@endif

@if (($galleries ?? collect())->isNotEmpty())
    <section class="section-pad">
        <div class="container-velora">
            <x-section-heading title="Impressionen" class="mb-8" />
            @php
                $allImages = collect();
                foreach ($galleries as $g) {
                    foreach ($g->images ?? [] as $img) {
                        $allImages->push($img);
                    }
                }
            @endphp
            <x-gallery-grid :images="$allImages->take(9)" />
        </div>
    </section>
@endif

@include('frontend.partials.faq-accordion', ['faqs' => $faqs ?? collect()])

<x-cta-banner />
@endsection
