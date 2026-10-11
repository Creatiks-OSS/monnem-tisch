@extends('admin.layouts.app')
@php $editing = (bool) ($product?->exists); @endphp
@section('title', $editing ? 'Yemeği düzenle' : 'Yeni yemek')
@section('heading', $editing ? 'Yemeği düzenle' : 'Yeni yemek')
@section('subheading', 'Bu yemek sitede tek başına satılmaz; paket kotasında seçenek olur.')
@section('content')
<form method="POST" action="{{ $editing ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data" class="max-w-4xl space-y-6">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif
    @include('admin.partials.errors')

    <div class="admin-card p-6 space-y-5">
        <div>
            <p class="text-[11px] uppercase tracking-[0.2em] text-[var(--admin-terracotta)]">Yemek</p>
            <h2 class="mt-1 text-2xl">{{ $editing ? $product->name : 'Paket seçeneği' }}</h2>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="admin-label">Ad</label>
                <input name="name" value="{{ old('name', $product->name ?? '') }}" required class="admin-input" placeholder="Tomate-Mozzarella">
            </div>
            <div>
                <label class="admin-label">Seçim grubu</label>
                <select name="category_id" class="admin-select" required>
                    <option value="">Grup seçin</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id ?? '')==$cat->id)>
                            {{ $cat->parent?->name ? $cat->parent->name.' · ' : '' }}{{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-[11px] text-[var(--admin-muted)]">Buffets · Vorspeisen, Fingerfood gibi.</p>
            </div>
            <div>
                <label class="admin-label">Slug</label>
                <input name="slug" value="{{ old('slug', $product->slug ?? '') }}" class="admin-input">
            </div>
            <div>
                <label class="admin-label">Sıra</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $product->sort_order ?? 0) }}" class="admin-input">
            </div>
            <div>
                <label class="admin-label">Kalori (kcal / porsiyon)</label>
                <input type="number" name="calories" min="0" max="5000" value="{{ old('calories', $product->calories ?? '') }}" class="admin-input" placeholder="220">
            </div>
            <div>
                <label class="admin-label">Fotoğraf</label>
                <input type="file" name="image" accept="image/*" class="admin-input">
                @if ($editing && $product->displayImage())
                    <img src="{{ $product->displayImage() }}" alt="{{ $product->name }}" class="mt-2 h-16 w-16 rounded-xl object-cover">
                @endif
            </div>
            <div class="sm:col-span-2">
                <label class="admin-label">Kısa açıklama</label>
                <textarea name="short_description" rows="2" class="admin-textarea">{{ old('short_description', $product->short_description ?? '') }}</textarea>
            </div>
            <div class="sm:col-span-2">
                <label class="admin-label">Açıklama</label>
                <textarea name="description" rows="4" class="admin-textarea">{{ old('description', $product->description ?? '') }}</textarea>
            </div>
            <div class="sm:col-span-2">
                <label class="admin-label">Galeri</label>
                <input type="file" name="gallery_images[]" accept="image/*" multiple class="admin-input">
            </div>
        </div>

        <input type="hidden" name="price" value="0">
        <input type="hidden" name="price_type" value="person">

        <div class="flex flex-wrap gap-4 text-sm">
            @foreach([['is_active','Aktif — pakette görünür'],['is_vegan','Vegan'],['is_vegetarian','Vejetaryen'],['is_gluten_free','Glutensiz'],['is_lactose_free','Laktozsuz'],['is_spicy','Acılı']] as [$n,$l])
                <label class="flex items-center gap-2"><input type="checkbox" name="{{ $n }}" value="1" @checked(old($n, $product->$n ?? ($n === 'is_active')))> {{ $l }}</label>
            @endforeach
        </div>

        @php
            $allergenCatalog = app(\App\Services\AllergenCatalog::class)->all();
            $selectedAllergens = old('allergens', $product->exists ? $product->allergenCodes() : []);
        @endphp
        <div>
            <p class="admin-label">Allergene</p>
            <p class="mb-2 text-[11px] text-[var(--admin-muted)]">Buchstaben erscheinen neben der Speise. <a href="{{ route('legal.allergene') }}" target="_blank" class="underline">Legende</a></p>
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach ($allergenCatalog as $code => $label)
                    <label class="flex items-start gap-2 text-sm">
                        <input type="checkbox" name="allergens[]" value="{{ $code }}" class="mt-0.5" @checked(in_array($code, $selectedAllergens, true))>
                        <span><span class="font-bold uppercase">{{ $code }}</span> — {{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    @if($editing)
        @php
            $currentPhotos = collect();
            if ($product->displayImage()) {
                $currentPhotos->push($product->displayImage());
            }
            foreach ($product->images as $img) {
                if ($img->url) {
                    $currentPhotos->push($img->url);
                }
            }
            $currentPhotos = $currentPhotos->unique()->values();
        @endphp
        @if ($currentPhotos->isNotEmpty())
            <div class="admin-card p-6">
                <h2 class="text-xl mb-3">Mevcut görseller</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach ($currentPhotos as $photoUrl)
                        <img src="{{ $photoUrl }}" class="h-16 w-16 rounded-xl object-cover" alt="{{ $product->name }}">
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    <button class="admin-btn admin-btn-gold px-8">Kaydet</button>
</form>
@endsection
