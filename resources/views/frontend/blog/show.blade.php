@extends('layouts.frontend')

@php
    $img = $post->imageUrl();
    $menu = $post->relatedMenu();
    $published = $post->published_at;
    $updated = $post->updated_at;
    $showUpdated = $published && $updated && $updated->toDateString() !== $published->toDateString();
    app(\App\Services\SchemaService::class)->setPageMeta(
        ($post->seo?->title ?? $post->title).' – '.__('frontend.brand'),
        $post->seo?->description ?? $post->excerpt ?? $post->title,
        $post->imageAbsoluteUrl(),
        $post->seo?->canonical ?? url()->current(),
        optional($published)->toIso8601String(),
        optional($updated)->toIso8601String()
    );
@endphp

@section('title', ($post->seo?->title ?? $post->title).' – '.__('frontend.brand'))
@section('meta_description', $post->seo?->description ?? $post->excerpt ?? $post->title)
@section('canonical', $post->seo?->canonical ?? url()->current())
@section('og_type', 'article')
@section('og_image', $post->imageAbsoluteUrl() ?? '')

@section('seo')
    @if ($post->seo?->keywords)
        <meta name="keywords" content="{{ $post->seo->keywords }}">
    @endif
    @if (! empty($preview))
        <meta name="robots" content="noindex, nofollow">
    @elseif ($post->seo?->robots)
        <meta name="robots" content="{{ $post->seo->robots }}">
    @endif
    @if ($published)
        <meta property="article:published_time" content="{{ $published->toIso8601String() }}">
    @endif
    @if ($updated)
        <meta property="article:modified_time" content="{{ $updated->toIso8601String() }}">
    @endif
    <meta property="article:author" content="{{ $post->authorName() }}">
@endsection

@section('schema')
    @php
        app(\App\Services\SchemaService::class)->push(
            app(\App\Services\SchemaService::class)->article($post)
        );
        app(\App\Services\SchemaService::class)->pushJson($post->seo?->schema_json);
    @endphp
@endsection

@section('content')
@if (! empty($preview))
    <div class="bg-[#c9a96e] px-4 py-3 text-center text-sm font-semibold leading-snug text-[#132A3A]">
        <p>Ön izleme — bu görünüm ziyaretçilere açık değil.</p>
        @if ($post->exists)
            <a href="{{ route('admin.blog-posts.edit', $post) }}" class="mt-1 inline-block underline">Düzenlemeye dön</a>
        @endif
    </div>
@endif
<article class="bg-white">
    <header class="border-b border-beige bg-cream">
        <div class="container-velora py-10 md:py-14">
            <x-breadcrumb :items="[
                ['label' => __('frontend.nav.blog'), 'url' => route('blog.index')],
                ['label' => $post->category->name ?? null, 'url' => $post->category?->slug ? route('blog.category', $post->category->slug) : null],
                ['label' => $post->title],
            ]" />
            @if ($post->category)
                <p class="text-xs uppercase tracking-[0.18em] text-bronze">{{ $post->category->name }}</p>
            @endif
            <h1 class="mt-3 max-w-3xl text-4xl font-bold md:text-5xl">{{ $post->title }}</h1>
            <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-muted">
                <span>
                    <span class="font-semibold text-navy">{{ $post->authorName() }}</span>
                    <span class="text-muted"> · {{ $post->authorRole() }}</span>
                </span>
                @if ($published)
                    <time datetime="{{ $published->toIso8601String() }}">Veröffentlicht {{ $published->translatedFormat('d. F Y') }}</time>
                @endif
                @if ($showUpdated)
                    <time datetime="{{ $updated->toIso8601String() }}">Aktualisiert {{ $updated->translatedFormat('d. F Y') }}</time>
                @endif
            </div>
        </div>
        @if ($img)
            <figure class="container-velora pb-10">
                <img src="{{ $img }}" alt="{{ $post->imageCaption() }}" class="max-h-[520px] w-full rounded-2xl object-cover" width="1200" height="750">
                <figcaption class="mt-3 text-sm text-muted">{{ $post->imageCaption() }}</figcaption>
            </figure>
        @endif
    </header>

    <div class="container-velora py-12">
        <div class="prose-velora mx-auto max-w-3xl">
            {!! $post->content !!}
        </div>

        <aside class="mx-auto mt-12 max-w-3xl rounded-2xl bg-cream p-6 md:p-8">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-sage">Aus der Praxis</p>
            <p class="mt-3 text-[15px] leading-relaxed text-charcoal/80">
                Wir kochen in Mannheim (H1, 10) und liefern in die Quadratestadt, nach Ludwigshafen, Heidelberg und in den Rhein-Neckar-Kreis.
                Die Hinweise in diesem Beitrag stammen aus echten Events – Mengen, Zeitfenster und Allergene klären wir vor jeder Produktion mit Ihnen.
            </p>
        </aside>

        <div class="mx-auto mt-8 grid max-w-3xl gap-4 md:grid-cols-2">
            <a href="{{ $menu['url'] }}" class="rounded-2xl border border-beige bg-white p-6 transition hover:border-navy">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-bronze">Passendes Menü</p>
                <h2 class="mt-2 text-xl font-bold">{{ $menu['label'] }}</h2>
                <p class="mt-2 text-sm leading-relaxed text-muted">{{ $menu['intro'] }}</p>
                <span class="mt-4 inline-flex text-sm font-semibold text-navy">Menü ansehen →</span>
            </a>
            <div class="rounded-2xl bg-navy p-6 text-white">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-bronze">Unverbindlich</p>
                <h2 class="mt-2 text-xl font-bold text-white">Angebot für Ihr Event</h2>
                <p class="mt-2 text-sm leading-relaxed text-white/75">Anlass, Datum und Personen – wir antworten mit einem klaren Festpreis.</p>
                <a href="{{ route('quote.create') }}" class="btn-primary mt-5">Angebot anfragen</a>
            </div>
        </div>

        @if (($post->tags ?? collect())->isNotEmpty())
            <div class="mx-auto mt-10 flex max-w-3xl flex-wrap gap-2">
                @foreach ($post->tags as $tag)
                    <span class="border border-beige px-3 py-1 text-xs uppercase tracking-[0.12em] text-muted">{{ $tag->name }}</span>
                @endforeach
            </div>
        @endif
    </div>

    @if (($related ?? collect())->isNotEmpty())
        <section class="border-t border-beige bg-cream py-16">
            <div class="container-velora">
                <h2 class="text-3xl font-bold">Weitere Beiträge</h2>
                <div class="mt-8 grid gap-8 md:grid-cols-3">
                    @foreach ($related as $item)
                        @php
                            $relatedImg = $item->imageUrl() ?: asset('images/gallery/catering-mannheim.webp');
                        @endphp
                        <a href="{{ route('blog.show', $item->slug) }}" class="group block">
                            <span class="block overflow-hidden rounded-2xl bg-beige">
                                <img src="{{ $relatedImg }}" alt="{{ $item->imageCaption() }}" class="aspect-[16/10] w-full object-cover transition duration-700 group-hover:scale-[1.03]" loading="lazy">
                            </span>
                            @if ($item->category)
                                <p class="mt-4 text-[0.7rem] uppercase tracking-[0.16em] text-bronze">{{ $item->category->name }}</p>
                            @endif
                            <h3 class="mt-1 text-xl font-bold group-hover:text-bronze">{{ $item->title }}</h3>
                            <p class="mt-2 line-clamp-2 text-sm text-charcoal/70">{{ $item->excerpt }}</p>
                            @if ($item->published_at)
                                <p class="mt-3 text-xs text-muted">{{ $item->published_at->translatedFormat('d. M Y') }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</article>
@endsection
