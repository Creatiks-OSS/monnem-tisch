@extends('layouts.frontend')

@section('title', 'Galerie Catering Mannheim – '.__('frontend.brand'))
@section('meta_description', 'Galerie: Catering Mannheim, Hochzeitscatering, Business Catering und Buffets – echte Einblicke von Monnemtisch Catering.')

@section('content')
<div class="container-velora py-10 md:py-14">
    <x-breadcrumb :items="[['label' => __('frontend.nav.gallery')]]" />
    <x-section-heading
        eyebrow="Momente"
        title="Galerie"
        text="Buffets, Fingerfood und Events – Catering in Mannheim, fotografiert bei echten Aufträgen."
        class="mb-12"
    />

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($galleries as $gallery)
            @php
                $cover = $gallery->cover_image
                    ?? optional($gallery->images->first())->url
                    ?? optional($gallery->images->first())->path
                    ?? null;
                if ($cover && ! str_starts_with($cover, 'http') && ! str_starts_with($cover, '/')) {
                    $cover = asset('storage/'.$cover);
                }
                $cover = $cover ?: asset('images/placeholders/food-'.(($loop->index % 4) + 1).'.svg');
            @endphp
            <a href="{{ route('gallery.show', $gallery->slug) }}" class="group relative block overflow-hidden rounded-2xl bg-white shadow-soft">
                <img src="{{ $cover }}" alt="{{ $gallery->title ?? $gallery->name }}" class="aspect-[4/3] w-full object-cover transition duration-700 group-hover:scale-105" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-charcoal/70 to-transparent"></div>
                <div class="absolute inset-x-0 bottom-0 p-5">
                    <h2 class="text-xl font-bold" style="color:#fff">{{ $gallery->title ?? $gallery->name }}</h2>
                    <p class="mt-1 text-xs font-bold" style="color:#fff">{{ $gallery->images_count ?? $gallery->images->count() ?? 0 }} Bilder</p>
                </div>
            </a>
        @empty
            <p class="col-span-full text-muted">Aktuell sind keine Galerie-Alben veröffentlicht. Schauen Sie gerne später wieder vorbei oder kontaktieren Sie uns für Referenzen.</p>
        @endforelse
    </div>
</div>
@endsection
