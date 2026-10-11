@extends('admin.layouts.app')
@section('title', 'Menü paketleri')
@section('heading', 'Menü paketleri')
@section('subheading', 'Buffets, Fingerfood, Frühstück, Grill, Vegetarisch, Vegan — müşteri paket seçer')
@section('content')
<div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.packages.index') }}"
           class="admin-chip {{ ! request('menu') ? 'bg-[var(--admin-sidebar)] text-white' : 'bg-white border border-[#e4d9c8] text-stone-600' }} !px-3 !py-1.5">Tümü</a>
        @foreach ($menus as $key => $item)
            <a href="{{ route('admin.packages.index', ['menu' => $key]) }}"
               class="admin-chip {{ request('menu') === $key ? 'bg-[var(--admin-sidebar)] text-white' : 'bg-white border border-[#e4d9c8] text-stone-600' }} !px-3 !py-1.5">{{ $item['label'] }}</a>
        @endforeach
    </div>
    <div class="flex flex-wrap gap-2">
        <form method="GET" class="flex gap-2">
            @if(request('menu'))<input type="hidden" name="menu" value="{{ request('menu') }}">@endif
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Paket ara…" class="admin-input w-52">
            <button class="admin-btn-ghost admin-btn">Ara</button>
        </form>
        <a href="{{ route('admin.packages.create') }}" class="admin-btn admin-btn-gold">Yeni paket</a>
    </div>
</div>

<div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
    @forelse($packages as $item)
        @php $meta = $item->menuMeta(); @endphp
        <article class="admin-card overflow-hidden flex flex-col">
            <div class="relative h-40 overflow-hidden bg-charcoal">
                <img src="{{ $item->imageUrl() }}" alt="" class="h-full w-full object-cover">
                <div class="absolute left-3 top-3 flex gap-2">
                    <span class="admin-chip bg-white/90 text-charcoal">{{ $meta['label'] ?? $item->menu_group }}</span>
                    @if($item->is_popular)
                        <span class="admin-chip bg-[var(--admin-terracotta)] text-white">Popüler</span>
                    @endif
                    @unless($item->is_active)
                        <span class="admin-chip bg-stone-800 text-white">Pasif</span>
                    @endunless
                </div>
            </div>
            <div class="flex flex-1 flex-col p-5">
                <h2 class="text-2xl leading-tight">{{ $item->name }}</h2>
                <p class="mt-1 text-xs uppercase tracking-[0.14em] text-[var(--admin-muted)]">{{ $item->subtitle }}</p>
                <ul class="mt-3 space-y-1 text-sm text-stone-600">
                    @foreach($item->slots as $slot)
                        <li>{{ $slot->required_count }} {{ $slot->name }}</li>
                    @endforeach
                </ul>
                <p class="mt-4 font-serif text-3xl">{{ number_format($item->price_per_person, 2, ',', '.') }} €</p>
                <p class="text-xs text-[var(--admin-muted)]">kişi başı · min. {{ $item->min_persons }} kişi</p>
                <div class="mt-auto flex items-center gap-3 pt-5">
                    <a href="{{ route('admin.packages.edit', $item) }}" class="admin-btn flex-1">Düzenle</a>
                    <form method="POST" action="{{ route('admin.packages.destroy', $item) }}" onsubmit="return confirm('Bu paket silinsin mi?')">
                        @csrf @method('DELETE')
                        <button class="admin-btn-ghost admin-btn !px-3 text-red-700">Sil</button>
                    </form>
                </div>
            </div>
        </article>
    @empty
        <div class="admin-card col-span-full px-6 py-16 text-center">
            <p class="font-serif text-2xl">Henüz paket yok</p>
            <p class="mt-2 text-sm text-[var(--admin-muted)]">Yeni bir menü paketi oluşturun — müşteri yemekleri buradan seçecek.</p>
            <a href="{{ route('admin.packages.create') }}" class="admin-btn admin-btn-gold mt-5">İlk paketi oluştur</a>
        </div>
    @endforelse
</div>
<div class="mt-6">{{ $packages->links() }}</div>
@endsection
