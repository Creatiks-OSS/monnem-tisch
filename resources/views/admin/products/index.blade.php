@extends('admin.layouts.app')
@section('title', 'Yemekler')
@section('heading', 'Yemekler')
@section('subheading', 'Tek tek satılmaz. Paket kotalarında seçenek olarak görünür.')
@section('content')
<div class="mb-6 rounded-2xl border border-[#ead9c4] bg-gradient-to-r from-[#f6e6dc] to-[#f8ebc8] px-5 py-4 text-sm text-stone-700">
    Müşteri sitede yemek satın alamaz. Her yemek bir <strong>seçim grubuna</strong> (Vorspeisen, Fingerfood…) aittir ve menü paketinin kotasını doldurur.
</div>

<div
    x-data="{
        selected: [],
        pageIds: {{ $products->pluck('id')->map(fn ($id) => (string) $id)->values()->toJson() }},
        toggleAll() {
            this.selected = this.allSelected() ? [] : this.pageIds.slice();
        },
        allSelected() {
            return this.pageIds.length > 0 && this.selected.length === this.pageIds.length;
        },
        confirmBulk(event) {
            if (!this.selected.length) {
                event.preventDefault();
                return;
            }
            if (!confirm(this.selected.length === 1 ? 'Seçilen yemek silinsin mi?' : this.selected.length + ' yemek silinsin mi?')) {
                event.preventDefault();
            }
        }
    }"
>
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Yemek ara…" class="admin-input w-52">
            <select name="category_id" class="admin-select w-56">
                <option value="">Tüm gruplar</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category_id')==$cat->id)>
                        {{ $cat->parent?->name ? $cat->parent->name.' · ' : '' }}{{ $cat->name }}
                    </option>
                @endforeach
            </select>
            <select name="status" class="admin-select w-40">
                <option value="" @selected(! request('status'))>Tüm yemekler</option>
                <option value="active" @selected(request('status') === 'active')>Aktif</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Pasif (eski tekli)</option>
            </select>
            <button class="admin-btn-ghost admin-btn">Filtrele</button>
        </form>
        <div class="flex flex-wrap items-center gap-2">
            @if ($products->isNotEmpty())
                <button type="button" class="admin-btn-ghost admin-btn" @click="toggleAll()" x-text="allSelected() ? 'Seçimi kaldır' : 'Sayfadakileri seç'"></button>
                <form method="POST" action="{{ route('admin.products.bulk-destroy') }}" @submit="confirmBulk($event)">
                    @csrf
                    @method('DELETE')
                    @foreach (['q', 'category_id', 'status'] as $keep)
                        @if (request()->filled($keep))
                            <input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}">
                        @endif
                    @endforeach
                    <template x-for="id in selected" :key="id">
                        <input type="hidden" name="ids[]" :value="id">
                    </template>
                    <button
                        type="submit"
                        class="admin-btn-ghost admin-btn border-red-200 text-red-700"
                        :disabled="!selected.length"
                        :class="!selected.length ? 'opacity-40 cursor-not-allowed' : ''"
                    >
                        Seçilenleri sil <span x-show="selected.length" x-text="'(' + selected.length + ')'"></span>
                    </button>
                </form>
            @endif
            <a href="{{ route('admin.products.create') }}" class="admin-btn admin-btn-gold">Yeni yemek</a>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @forelse($products as $p)
            <article class="admin-card overflow-hidden flex flex-col" :class="selected.includes('{{ $p->id }}') ? 'ring-2 ring-[var(--admin-terracotta)]' : ''">
                <div class="relative h-36 bg-[#f3ece3]">
                    @if ($p->displayImage())
                        <img src="{{ $p->displayImage() }}" alt="" class="h-full w-full object-cover">
                    @endif
                    <label class="absolute right-3 top-3 z-10 flex h-7 w-7 cursor-pointer items-center justify-center rounded-lg bg-white/95 shadow-sm">
                        <input type="checkbox" class="h-4 w-4 accent-[var(--admin-terracotta)]" value="{{ $p->id }}" x-model="selected">
                    </label>
                    <div class="absolute left-3 top-3 flex flex-wrap gap-1 pr-10">
                        @if ($p->is_vegan)
                            <span class="admin-chip bg-[#e4efe7] text-[var(--admin-sage)]">Vegan</span>
                        @endif
                        @if ($p->is_vegetarian && ! $p->is_vegan)
                            <span class="admin-chip bg-[#f8e8e6] text-[#c2413b]">Vejetaryen</span>
                        @endif
                        @unless ($p->is_active)
                            <span class="admin-chip bg-stone-800 text-white">Pasif</span>
                        @endunless
                    </div>
                </div>
                <div class="flex flex-1 flex-col p-4">
                    <p class="text-[10px] uppercase tracking-[0.14em] text-[var(--admin-muted)]">
                        {{ $p->category?->parent?->name ? $p->category->parent->name.' · ' : '' }}{{ $p->category?->name ?? 'Grup yok' }}
                    </p>
                    <h2 class="mt-1 text-xl leading-tight">{{ $p->name }}</h2>
                    @if ($p->calories)
                        <p class="mt-1 text-xs font-semibold tabular-nums text-[var(--admin-muted)]">ca. {{ $p->calories }} kcal</p>
                    @endif
                    @if($p->short_description)
                        <p class="mt-1 line-clamp-2 text-xs text-[var(--admin-muted)]">{{ $p->short_description }}</p>
                    @endif
                    <div class="mt-auto flex items-center gap-2 pt-4">
                        <a href="{{ route('admin.products.edit', $p) }}" class="admin-btn flex-1 !py-2">Düzenle</a>
                        <form method="POST" action="{{ route('admin.products.destroy', $p) }}" onsubmit="return confirm('Yemek silinsin mi?')">
                            @csrf @method('DELETE')
                            <button class="admin-btn-ghost admin-btn !px-3 !py-2 text-red-700">Sil</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="admin-card col-span-full px-6 py-16 text-center">
                <p class="font-serif text-2xl">Yemek bulunamadı</p>
                <p class="mt-2 text-sm text-[var(--admin-muted)]">Paket kotalarında görünecek bir yemek ekleyin.</p>
                <a href="{{ route('admin.products.create') }}" class="admin-btn admin-btn-gold mt-5">Yemek ekle</a>
            </div>
        @endforelse
    </div>
</div>
<div class="mt-6">{{ $products->links() }}</div>
@endsection
