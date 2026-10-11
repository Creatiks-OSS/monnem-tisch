@extends('admin.layouts.app')
@section('title','Etkinliği düzenle')
@section('heading','Etkinliği düzenle')
@section('content')
<form method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data" class="max-w-2xl space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">@csrf @method('PUT')
@include('admin.partials.errors')
<div><label class="block text-sm mb-1">Ad</label><input name="name" value="{{ old('name', $event->name ?? '') }}" required class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Slug</label><input name="slug" value="{{ old('slug', $event->slug ?? '') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Açıklama</label><textarea name="description" rows="4" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">{{ old('description', $event->description ?? '') }}</textarea></div>
<div><label class="block text-sm mb-1">Bild</label><input type="file" name="image" accept="image/*" class="w-full text-sm"></div>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $event->is_active ?? true))> Aktif</label>
<button class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Kaydet</button></form>
@endsection