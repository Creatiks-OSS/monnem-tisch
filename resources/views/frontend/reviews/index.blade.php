@extends('layouts.frontend')

@section('title', 'Bewertungen – '.__('frontend.brand'))
@section('meta_description', 'Lesen Sie, was Gäste über Monnemtisch Catering sagen.')

@section('content')
<div class="container-velora py-10 md:py-14">
    <x-breadcrumb :items="[['label' => __('frontend.nav.reviews')]]" />

    <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
        <x-section-heading
            eyebrow="Stimmen"
            title="Bewertungen"
            text="Echte Rückmeldungen von Gastgebern und Gästen."
        />
        <div class="text-right">
            <p class="text-5xl font-bold text-bronze">{{ number_format((float) $average, 1, ',', '.') }}</p>
            <p class="mt-1 text-xs uppercase tracking-[0.16em] text-muted">{{ $count }} Bewertungen</p>
        </div>
    </div>

    @if (($featured ?? collect())->isNotEmpty())
        <section class="mt-14 grid gap-8 md:grid-cols-2">
            @foreach ($featured as $review)
                <x-testimonial :review="$review" />
            @endforeach
        </section>
    @endif

    <section class="mt-16 space-y-8">
        @forelse ($reviews as $review)
            <x-testimonial :review="$review" class="rounded-2xl bg-white p-6 shadow-soft" />
        @empty
            <p class="text-muted">Noch keine Bewertungen vorhanden.</p>
        @endforelse
    </section>

    <x-pagination :paginator="$reviews" />
</div>
@endsection
