@extends('layouts.frontend')

@section('title', $currentCategory?->name
    ? $currentCategory->name.' – Catering-Magazin Mannheim'
    : 'Catering-Magazin Mannheim – Tipps für Events')
@section('meta_description', $currentCategory?->description
    ?? 'Catering-Tipps für Mannheim: Firmenbuffets, Fingerfood, Hochzeiten und Messen. Praxisleitfäden von Monnemtisch Catering.')

@section('content')
<div class="container-velora py-10 md:py-14">
    <x-breadcrumb :items="array_filter([
        ['label' => __('frontend.nav.blog'), 'url' => $currentCategory ? route('blog.index') : null],
        ['label' => $currentCategory?->name ?? __('frontend.nav.blog')],
    ], fn ($i) => ! empty($i['label']))" />

    <x-section-heading
        eyebrow="Magazin"
        :title="$currentCategory?->name ?? 'Catering-Magazin'"
        :text="$currentCategory?->description ?? 'Praxisbeiträge aus der Küche: Mengen, Ablauf und Lieferung in Mannheim – nicht als täglicher Newsfeed, sondern wenn wir aus echten Events etwas weitergeben können.'"
        class="mb-10"
    />

    @if (($categories ?? collect())->isNotEmpty())
        <div class="mb-10 flex flex-wrap gap-2">
            <a href="{{ route('blog.index') }}" @class(['border px-4 py-2 text-xs uppercase tracking-[0.14em]', 'border-bronze text-bronze' => ! $currentCategory, 'border-beige' => $currentCategory])>Alle</a>
            @foreach ($categories as $cat)
                <a href="{{ route('blog.category', $cat->slug) }}" @class(['border px-4 py-2 text-xs uppercase tracking-[0.14em]', 'border-bronze text-bronze' => ($currentCategory->id ?? null) === $cat->id, 'border-beige' => ($currentCategory->id ?? null) !== $cat->id])>
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>
    @endif

    <form action="{{ route('blog.index') }}" method="GET" class="mb-10 max-w-md">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Artikel suchen…" class="input-velora">
    </form>

    <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($posts as $post)
            @php
                $img = $post->imageUrl() ?: asset('images/gallery/catering-mannheim.webp');
            @endphp
            <article class="group">
                <a href="{{ route('blog.show', $post->slug) }}" class="block overflow-hidden rounded-2xl bg-beige">
                    <img src="{{ $img }}" alt="{{ $post->imageCaption() }}" class="aspect-[16/10] w-full object-cover transition duration-700 group-hover:scale-[1.03]" loading="lazy">
                </a>
                <div class="pt-4">
                    @if ($post->category)
                        <p class="text-[0.7rem] uppercase tracking-[0.16em] text-bronze">{{ $post->category->name }}</p>
                    @endif
                    <h2 class="mt-1 text-2xl font-bold">
                        <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-bronze">{{ $post->title }}</a>
                    </h2>
                    @if ($post->excerpt)
                        <p class="mt-2 line-clamp-3 text-sm text-charcoal/70">{{ $post->excerpt }}</p>
                    @endif
                    <p class="mt-3 text-xs text-muted">
                        {{ $post->authorName() }}
                        @if ($post->published_at)
                            · {{ $post->published_at->translatedFormat('d. M Y') }}
                        @endif
                    </p>
                </div>
            </article>
        @empty
            <p class="col-span-full text-muted">Noch keine Beiträge veröffentlicht.</p>
        @endforelse
    </div>

    <x-pagination :paginator="$posts" />
</div>
@endsection
