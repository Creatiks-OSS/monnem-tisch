@extends('layouts.frontend')

@section('title', __('frontend.search.title').($q ? ': '.$q : '').' – '.__('frontend.brand'))

@section('content')
<div class="container-velora py-10 md:py-14">
    <x-breadcrumb :items="[['label' => __('frontend.search.title')]]" />
    <h1 class="text-4xl font-bold">{{ __('frontend.search.title') }}</h1>

    <form action="{{ route('search') }}" method="GET" class="mt-8 max-w-xl">
        <div class="flex gap-3">
            <input type="search" name="q" value="{{ $q }}" placeholder="{{ __('frontend.search.placeholder') }}" class="input-velora" autofocus>
            <button type="submit" class="btn-primary">{{ __('frontend.nav.search') }}</button>
        </div>
    </form>

    @if ($q === '')
        <p class="mt-10 text-charcoal/70">Geben Sie einen Suchbegriff ein.</p>
    @else
        @php
            $hasResults = $packages->isNotEmpty() || $posts->isNotEmpty() || $pages->isNotEmpty();
        @endphp

        @unless ($hasResults)
            <p class="mt-10 text-charcoal/70">{{ __('frontend.search.no_results') }}</p>
        @endunless

        @if ($packages->isNotEmpty())
            <section class="mt-12">
                <h2 class="text-2xl font-bold">Menüs</h2>
                <div class="mt-6 grid gap-8 lg:grid-cols-3">
                    @foreach ($packages as $package)
                        <x-package-card :package="$package" />
                    @endforeach
                </div>
            </section>
        @endif

        @if ($posts->isNotEmpty())
            <section class="mt-12">
                <h2 class="text-2xl font-bold">{{ __('frontend.search.posts') }}</h2>
                <ul class="mt-6 space-y-4">
                    @foreach ($posts as $post)
                        <li>
                            <a href="{{ route('blog.show', $post->slug) }}" class="text-xl font-bold hover:text-bronze">{{ $post->title }}</a>
                            @if ($post->excerpt)
                                <p class="mt-1 text-sm text-charcoal/70">{{ $post->excerpt }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($pages->isNotEmpty())
            <section class="mt-12">
                <h2 class="text-2xl font-bold">{{ __('frontend.search.pages') }}</h2>
                <ul class="mt-6 space-y-3">
                    @foreach ($pages as $page)
                        <li>
                            <a href="{{ route('pages.show', $page->slug) }}" class="hover:text-bronze">{{ $page->title }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    @endif
</div>
@endsection
