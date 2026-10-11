@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'canonical' => null,
    'robots' => null,
    'type' => 'website',
    'author' => null,
    'publisher' => null,
    'published' => null,
    'modified' => null,
])

@php
    $schema = app(\App\Services\SchemaService::class);
    $title = $title ?: __('frontend.brand');
    $description = $description ?: __('frontend.tagline');
    $image = $image ?: asset('images/placeholders/hero.svg');
    $canonical = $canonical ?: url()->current();
    $author = $author ?: $schema->authorName();
    $publisher = $publisher ?: $schema->publisherName();
    $published = $published ?: $schema->publishedAt();
    $modified = $modified ?: $schema->modifiedAt();
@endphp

<meta name="description" content="{{ Str::limit(strip_tags($description), 160) }}">
<meta name="author" content="{{ $author }}">
<meta name="publisher" content="{{ $publisher }}">
@if ($robots)
    <meta name="robots" content="{{ $robots }}">
@endif
<link rel="canonical" href="{{ $canonical }}">
<link rel="author" href="{{ route('about') }}">

<meta property="og:site_name" content="{{ __('frontend.brand') }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ Str::limit(strip_tags($description), 200) }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:locale" content="de_DE">
<meta property="article:author" content="{{ $author }}">
<meta property="article:publisher" content="{{ $publisher }}">
<meta property="article:published_time" content="{{ $published }}">
<meta property="article:modified_time" content="{{ $modified }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ Str::limit(strip_tags($description), 200) }}">
<meta name="twitter:image" content="{{ $image }}">
