@extends('admin.layouts.app')
@php $editing = (bool) ($category?->exists); @endphp
@section('title', $editing ? 'Grubu düzenle' : 'Yeni seçim grubu')
@section('heading', $editing ? 'Grubu düzenle' : 'Yeni seçim grubu')
@section('subheading', 'Ana menü (Buffets) veya alt kota grubu (Vorspeisen) olabilir.')
@section('content')
<form method="POST" action="{{ $editing ? route('admin.categories.update', $category) : route('admin.categories.store') }}" enctype="multipart/form-data" class="max-w-3xl space-y-6">
    @csrf
    @if($editing) @method('PUT') @endif
    @include('admin.partials.errors')
    <div class="admin-card p-6 space-y-4">
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="admin-label">Ad</label>
                <input name="name" value="{{ old('name', $category->name ?? '') }}" required class="admin-input" placeholder="Vorspeisen">
            </div>
            <div>
                <label class="admin-label">Slug</label>
                <input name="slug" value="{{ old('slug', $category->slug ?? '') }}" class="admin-input">
            </div>
            <div>
                <label class="admin-label">Üst menü</label>
                <select name="parent_id" class="admin-select">
                    <option value="">Ana menü (Buffets, Fingerfood…)</option>
                    @foreach($parents as $p)
                        <option value="{{ $p->id }}" @selected(old('parent_id', $category->parent_id ?? '')==$p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="admin-label">Görsel</label>
                <input type="file" name="image" accept="image/*" class="admin-input">
            </div>
            <div>
                <label class="admin-label">Sıra</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="admin-input">
            </div>
        </div>
        <div>
            <label class="admin-label">Kısa açıklama</label>
            <textarea name="short_description" rows="2" class="admin-textarea">{{ old('short_description', $category->short_description ?? '') }}</textarea>
        </div>
        <div>
            <label class="admin-label">Açıklama</label>
            <textarea name="description" rows="4" class="admin-textarea">{{ old('description', $category->description ?? '') }}</textarea>
        </div>
        <div class="flex flex-wrap gap-5 text-sm">
            <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))> Aktif</label>
            <label class="flex items-center gap-2"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $category->is_featured ?? false))> Öne çıkan</label>
        </div>
    </div>
    <button class="admin-btn admin-btn-gold px-8">Kaydet</button>
</form>
@endsection
