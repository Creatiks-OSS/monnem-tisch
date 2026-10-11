@extends('layouts.frontend')

@section('title', ($category->seo_title ?? $category->name).' – '.__('frontend.brand'))
@section('meta_description', $category->seo_description ?? $category->short_description ?? $category->name)

@section('content')
@php
    $hero = $category->image
        ? (str_starts_with($category->image, 'http') || str_starts_with($category->image, '/') ? $category->image : asset('storage/'.$category->image))
        : asset('images/placeholders/category.svg');
@endphp

<section class="relative min-h-[320px] overflow-hidden bg-charcoal text-cream md:min-h-[380px]">
    <img src="{{ $hero }}" alt="{{ $category->name }}" class="absolute inset-0 h-full w-full object-cover opacity-60">
    <div class="absolute inset-0 bg-hero-overlay"></div>
    <div class="container-velora relative flex min-h-[320px] flex-col justify-end pb-10 pt-20 md:min-h-[380px]">
        <x-breadcrumb class="!mb-4 text-cream/70 [&_a]:text-cream/70 [&_span]:text-cream/90" :items="[
            ['label' => __('frontend.nav.catering'), 'url' => route('catering.index')],
            ['label' => $category->name],
        ]" />
        <h1 class="font-serif text-4xl md:text-5xl">{{ $category->name }}</h1>
        @if ($category->short_description)
            <p class="mt-3 max-w-2xl text-cream/80">{{ $category->short_description }}</p>
        @endif
    </div>
</section>

<div class="container-velora py-12 md:py-16">
    @if (($category->children ?? collect())->isNotEmpty())
        <div class="mb-10 flex flex-wrap gap-2">
            @foreach ($category->children as $child)
                <a href="{{ route('catering.show', $child->slug) }}" class="border border-beige bg-ivory px-4 py-2 text-xs uppercase tracking-[0.14em] transition hover:border-bronze hover:text-bronze">
                    {{ $child->name }}
                </a>
            @endforeach
        </div>
    @endif

    <div class="grid gap-10 lg:grid-cols-12">
        <aside class="lg:col-span-3">
            @include('frontend.partials.product-filters', ['filters' => $filters ?? []])
        </aside>
        <div class="lg:col-span-9">
            @if ($category->description)
                <div class="prose-velora mb-10 max-w-3xl">{!! $category->description !!}</div>
            @endif
            <div class="grid gap-8 sm:grid-cols-2 xl:grid-cols-3">
                @forelse ($products as $product)
                    <x-product-card :product="$product" />
                @empty
                    <p class="col-span-full text-muted">In dieser Kategorie sind derzeit keine Produkte verfügbar.</p>
                @endforelse
            </div>
            <x-pagination :paginator="$products" />
        </div>
    </div>
</div>
@endsection
