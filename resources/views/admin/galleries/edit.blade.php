@extends('admin.layouts.app')
@section('title','Galeriyi düzenle')
@section('heading','Galeriyi düzenle')
@section('content')
<form method="POST" action="{{ route('admin.galleries.update', $gallery) }}" enctype="multipart/form-data" class="max-w-3xl space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm mb-6">@csrf @method('PUT')
@include('admin.partials.errors')
<div><label class="block text-sm mb-1">Ad</label><input name="name" value="{{ old('name', $gallery->name ?? '') }}" required class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Kategori</label><input name="category" value="{{ old('category', $gallery->category ?? '') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Açıklama</label><textarea name="description" rows="3" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">{{ old('description', $gallery->description ?? '') }}</textarea></div>
<div><label class="block text-sm mb-1">Cover</label><input type="file" name="cover_image" accept="image/*" class="w-full text-sm"></div>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $gallery->is_active ?? true))> Aktif</label>
<div class="flex flex-wrap gap-2">
    <button class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Kaydet</button>
    <button type="submit" form="gallery-delete" class="px-4 py-2 rounded-lg border border-red-200 text-sm text-red-700">Sil</button>
</div>
</form>
<form id="gallery-delete" method="POST" action="{{ route('admin.galleries.destroy', $gallery) }}" onsubmit="return confirm('Galeri silinsin mi?')" class="hidden">@csrf @method('DELETE')</form>
<form method="POST" action="{{ route('admin.galleries.images.store', $gallery) }}" enctype="multipart/form-data" class="max-w-3xl rounded-2xl border border-stone-200 bg-white p-6 shadow-sm mb-6">@csrf
<label class="block text-sm mb-1">Görsel ekle</label>
<input type="file" name="images[]" accept="image/*" multiple required class="mb-3 text-sm">
<button class="px-4 py-2 rounded-lg border text-sm">Hochladen</button>
</form>
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 max-w-3xl">
@foreach($gallery->images as $image)
<div class="relative group rounded-xl overflow-hidden border border-stone-200">
<img src="{{ $image->url }}" class="aspect-square object-cover w-full" alt="">
<form method="POST" action="{{ route('admin.galleries.images.destroy', [$gallery, $image]) }}" class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center">@csrf @method('DELETE')
<button class="text-white text-sm">Sil</button></form>
</div>
@endforeach
</div>
@endsection