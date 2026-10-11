@extends('layouts.frontend')

@section('title', 'Catering – '.__('frontend.brand'))
@section('meta_description', 'Entdecken Sie unsere Catering-Kategorien: Buffets, Fingerfood, Menüs und mehr.')

@section('content')
<div class="container-velora py-10 md:py-14">
    <x-breadcrumb :items="[['label' => __('frontend.nav.catering')]]" />

    <x-section-heading
        eyebrow="Monnemtisch"
        title="Catering"
        text="Kuratierte Kategorien für jeden Anlass – von feinem Fingerfood bis zum festlichen Buffet."
        class="mb-12"
    />

    @if (($featured ?? collect())->isNotEmpty())
        <div class="mb-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($featured as $category)
                <x-category-card :category="$category" />
            @endforeach
        </div>
    @endif

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($categories as $category)
            <x-category-card :category="$category" />
        @empty
            <p class="col-span-full text-muted">Aktuell sind keine Kategorien verfügbar.</p>
        @endforelse
    </div>
</div>
@endsection
