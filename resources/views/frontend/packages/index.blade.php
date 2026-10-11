@extends('layouts.frontend')

@section('title', $meta['seo_title'] ?? $meta['headline'] ?? 'Buffet Catering Mannheim')
@section('meta_description', $menuExcerpt ?? ($meta['intro'] ?? 'Catering in Mannheim – Festpreis, frei kombinierbar.'))
@section('og_image', velora_photo($meta['photo'] ?? 'buffet'))

@section('schema')
    @php
        $list = ($packages ?? collect())->map(fn ($package) => [
            'name' => $package->name,
            'url' => $package->publicUrl(),
            'image' => $package->imageUrl(),
        ])->all();
        app(\App\Services\SchemaService::class)->push(
            app(\App\Services\SchemaService::class)->itemList($meta['headline'] ?? 'Catering-Menüs', $list)
        );
        app(\App\Services\SchemaService::class)->pushJson(app(\App\Services\MenuPageContent::class)->schema($meta['key'] ?? ''));
    @endphp
@endsection

@section('content')
<section class="bg-cream py-10 md:py-16">
    <div class="container-velora">
        <x-breadcrumb :items="[['label' => $meta['label'] ?? 'Menüs']]" />
        <p class="text-sm font-semibold text-bronze">{{ $meta['label'] ?? 'Menüs' }}</p>
        <h1 class="mt-2 max-w-3xl text-3xl font-bold leading-tight md:text-5xl">{{ $meta['headline'] ?? 'Buffet Catering Mannheim ab 10 Personen' }}</h1>
        <p class="mt-4 max-w-xl text-muted">{{ $meta['intro'] ?? 'Wählen Sie ein Paket – der Preis pro Person bleibt transparent.' }}</p>
        <div class="mt-8 flex gap-2 overflow-x-auto pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            @foreach (\App\Models\CateringPackage::MENUS as $key => $item)
                <a href="{{ route('packages.index', $key) }}"
                   @class([
                       'shrink-0 rounded-lg px-4 py-2 text-sm font-semibold transition',
                       'bg-bronze text-white' => ($meta['key'] ?? '') === $key,
                       'bg-white text-charcoal/80 shadow-soft hover:text-bronze' => ($meta['key'] ?? '') !== $key,
                   ])>{{ $item['label'] }}</a>
            @endforeach
        </div>
    </div>
</section>

<section class="pb-20">
    <div class="container-velora">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-5 lg:grid-cols-3">
            @forelse ($packages as $package)
                <x-package-card :package="$package" />
            @empty
                <p class="col-span-full text-muted">Derzeit sind keine {{ $meta['label'] ?? 'Menü' }}-Pakete veröffentlicht.</p>
            @endforelse
        </div>

        @if (! empty($menuBody))
            <div class="mx-auto mt-16 max-w-3xl space-y-4 text-base leading-relaxed text-charcoal/80">
                @foreach ($menuBody as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
