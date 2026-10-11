@extends('admin.layouts.app')
@section('title','Galeri oluştur')
@section('heading','Galeri oluştur')
@section('content')
@php $gallery = new \App\Models\Gallery(["is_active"=>true]); @endphp
<form method="POST" action="{{ route('admin.galleries.store') }}" enctype="multipart/form-data" class="max-w-2xl space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">@csrf
@include('admin.partials.errors')
<div><label class="block text-sm mb-1">Ad</label><input name="name" value="{{ old('name', $gallery->name ?? '') }}" required class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Kategori</label><input name="category" value="{{ old('category', $gallery->category ?? '') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Açıklama</label><textarea name="description" rows="3" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">{{ old('description', $gallery->description ?? '') }}</textarea></div>
<div><label class="block text-sm mb-1">Cover</label><input type="file" name="cover_image" accept="image/*" class="w-full text-sm"></div>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $gallery->is_active ?? true))> Aktif</label>
<button class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Kaydet</button></form>
@endsection