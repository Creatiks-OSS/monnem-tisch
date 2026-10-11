@extends('layouts.frontend')

@section('title', ($page?->seo?->title ?? $page?->seo_title ?? null) ?: 'Catering Mannheim – Buffets & Fingerfood | Monnemtisch')
@section('meta_description', $page?->seo?->description ?? $page?->excerpt ?? 'Catering in Mannheim: Buffets, Fingerfood, Frühstück und Grillcatering ab 10 Personen. Festpreis, frei kombinierbar – unverbindlich anfragen.')

@section('schema')
    @php
        app(\App\Services\SchemaService::class)->pushMany(
            app(\App\Services\SchemaService::class)->homeGraph($menus ?? [], $packages ?? [])
        );
        app(\App\Services\SchemaService::class)->pushJson($page?->seo?->schema_json ?: setting('schema_json', '', 'homepage'));
    @endphp
@endsection

@push('head')
    <link rel="preload" as="image" href="{{ velora_photo('hero') }}" fetchpriority="high">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
@endpush

@section('content')
@include('frontend.partials.home-hansetisch')

@include('frontend.partials.faq-accordion', ['faqs' => $faqs ?? collect(), 'title' => 'Häufige Fragen'])
@endsection
