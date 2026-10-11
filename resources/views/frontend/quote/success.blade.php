@extends('layouts.frontend')

@section('title', __('frontend.quote.success_title').' – '.__('frontend.brand'))

@section('content')
<div class="container-velora py-16 md:py-24">
    <x-breadcrumb :items="[['label' => __('frontend.quote.success_title')]]" />
    <div class="mx-auto max-w-2xl rounded-2xl bg-white px-8 py-12 text-center shadow-soft md:px-12">
        <p class="text-sm font-semibold text-bronze">Monnemtisch Catering</p>
        <h1 class="mt-3 text-4xl font-bold md:text-5xl">{{ __('frontend.quote.success_title') }}</h1>
        <p class="mt-4 text-charcoal/70">{{ __('frontend.quote.success_text') }}</p>
        <p class="mt-8 text-2xl font-bold text-bronze">
            {{ __('frontend.quote.quote_number') }}: {{ $quote->quote_number }}
        </p>
        <div class="mt-10 flex flex-wrap justify-center gap-3">
            <x-button :href="route('home')" variant="primary">{{ __('frontend.errors.home') }}</x-button>
            <x-button :href="route('packages.index', 'buffets')" variant="secondary">{{ __('frontend.cta.discover') }}</x-button>
        </div>
    </div>
</div>
@endsection
