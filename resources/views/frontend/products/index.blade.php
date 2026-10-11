@extends('layouts.frontend')

@section('title', 'Produkte – '.__('frontend.brand'))
@section('meta_description', 'Entdecken Sie unser gesamtes Produktangebot für Ihr Event.')

@section('content')
<div class="container-velora py-10 md:py-14">
    <x-breadcrumb :items="[['label' => __('frontend.nav.products')]]" />
    <x-section-heading
        eyebrow="Sortiment"
        title="Produkte"
        text="Filtern Sie nach Kategorie und Ernährungsform – und finden Sie das passende Gericht."
        class="mb-10"
    />

    <div class="grid gap-10 lg:grid-cols-12">
        <aside class="lg:col-span-3">
            @include('frontend.partials.product-filters', [
                'filters' => $filters ?? [],
                'categories' => $categories ?? collect(),
                'showSearch' => true,
                'showLactose' => true,
                'showNewest' => true,
            ])
        </aside>
        <div class="lg:col-span-9">
            <div class="grid gap-8 sm:grid-cols-2 xl:grid-cols-3">
                @forelse ($products as $product)
                    <x-product-card :product="$product" />
                @empty
                    <p class="col-span-full text-muted">Keine Produkte gefunden.</p>
                @endforelse
            </div>
            <x-pagination :paginator="$products" />
        </div>
    </div>
</div>
@endsection
