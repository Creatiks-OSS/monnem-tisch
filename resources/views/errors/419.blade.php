@extends('layouts.frontend')

@section('title', __('frontend.errors.419_title').' – '.__('frontend.brand'))

@section('content')
<div class="container-velora flex min-h-[70vh] flex-col items-center justify-center py-20 text-center">
    <p class="font-serif text-7xl text-bronze md:text-8xl">419</p>
    <h1 class="mt-4 font-serif text-3xl md:text-4xl">{{ __('frontend.errors.419_title') }}</h1>
    <p class="mt-3 max-w-md text-charcoal/70">{{ __('frontend.errors.419_text') }}</p>
    <div class="mt-8 flex flex-wrap justify-center gap-3">
        <x-button href="javascript:location.reload()" variant="primary">Seite neu laden</x-button>
        <x-button :href="route('home')" variant="secondary">{{ __('frontend.errors.home') }}</x-button>
    </div>
</div>
@endsection
