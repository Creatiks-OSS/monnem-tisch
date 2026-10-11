@extends('layouts.frontend')

@section('title', ($gallery->title ?? $gallery->name).' – Galerie – '.__('frontend.brand'))
@section('meta_description', $gallery->description ?? ($gallery->title ?? 'Galerie'))

@section('content')
<div class="container-velora py-10 md:py-14">
    <x-breadcrumb :items="[
        ['label' => __('frontend.nav.gallery'), 'url' => route('gallery.index')],
        ['label' => $gallery->title ?? $gallery->name],
    ]" />

    <x-section-heading
        :title="$gallery->title ?? $gallery->name"
        :text="$gallery->description ?? $gallery->excerpt ?? null"
        class="mb-10"
    />

    <x-gallery-grid :images="$images ?? $gallery->images" />
</div>
@endsection
