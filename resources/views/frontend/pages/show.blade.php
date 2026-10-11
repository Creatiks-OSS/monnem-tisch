@extends('layouts.frontend')

@section('title', ($page->seo_title ?? $page->title).' – '.__('frontend.brand'))
@section('meta_description', $page->seo_description ?? $page->excerpt ?? $page->title)

@section('schema')
    @php app(\App\Services\SchemaService::class)->pushJson($page->seo?->schema_json); @endphp
@endsection

@section('content')
@php
    $sections = $sections ?? collect();
@endphp

<div class="container-velora py-10 md:py-14">
    <x-breadcrumb :items="[['label' => $page->title]]" />
    <article class="mx-auto max-w-3xl md:max-w-4xl">
        <h1 class="text-4xl font-bold md:text-5xl">{{ $page->title }}</h1>
        @if ($page->excerpt)
            <p class="mt-4 text-lg text-charcoal/70">{{ $page->excerpt }}</p>
        @endif

        @if ($page->content)
            <div class="prose-velora mt-10">
                {!! $page->content !!}
            </div>
        @elseif ($sections->isEmpty())
            <div class="prose-velora mt-10 text-charcoal/70">
                <p>Für Auskünfte zu dieser Seite stehen wir Ihnen gerne persönlich zur Verfügung.</p>
                <p class="mt-4"><a href="{{ route('contact.show') }}">{{ __('frontend.cta.contact') }}</a></p>
            </div>
        @endif

        @foreach ($sections as $section)
            <section class="mt-12">
                @if ($section->title)
                    <h2 class="text-3xl font-bold">{{ $section->title }}</h2>
                @endif
                @if ($section->subtitle)
                    <p class="mt-2 text-charcoal/70">{{ $section->subtitle }}</p>
                @endif
                @if ($section->content)
                    <div class="prose-velora mt-4">{!! $section->content !!}</div>
                @endif
                @if ($section->image)
                    @php
                        $img = str_starts_with($section->image, 'http') || str_starts_with($section->image, '/')
                            ? $section->image
                            : asset('storage/'.$section->image);
                    @endphp
                    <img src="{{ $img }}" alt="{{ $section->title ?: $page->title }}" class="mt-6 w-full object-cover" loading="lazy">
                @endif
            </section>
        @endforeach
    </article>
</div>
@endsection
