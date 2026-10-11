@extends('admin.layouts.app')
@section('title','Medya')
@section('heading','Medya kütüphanesi')
@section('content')
<div class="grid lg:grid-cols-3 gap-6 mb-6">
<form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="lg:col-span-1 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm space-y-3">@csrf
<label class="block text-sm font-medium">Datei hochladen</label>
<input type="file" name="file" accept="image/*" required class="w-full text-sm">
<input type="text" name="folder" value="uploads" placeholder="Ordner" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
<button class="w-full rounded-lg bg-[var(--admin-sidebar)] text-white py-2 text-sm">Hochladen</button>
</form>
<form method="GET" class="lg:col-span-2 flex gap-2 items-start">
<input type="search" name="q" value="{{ request('q') }}" placeholder="Ara…" class="rounded-xl border border-stone-200 px-3 py-2 text-sm flex-1">
<select name="folder" class="rounded-xl border border-stone-200 px-3 py-2 text-sm"><option value="">Alle Ordner</option>@foreach($folders as $f)<option value="{{ $f }}" @selected(request('folder')===$f)>{{ $f }}</option>@endforeach</select>
<button class="px-3 py-2 rounded-xl border text-sm">Filtrele</button>
</form>
</div>
@if($media->isEmpty())
<div class="rounded-2xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center text-stone-500">Medya yok.</div>
@else
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
@foreach($media as $m)
<div class="rounded-xl border border-stone-200 bg-white overflow-hidden shadow-sm group relative">
<img src="{{ $m->url }}" alt="{{ $m->alt }}" class="aspect-square object-cover w-full">
<form method="POST" action="{{ route('admin.media.destroy',$m) }}" class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center">@csrf @method('DELETE')
<button class="text-white text-sm">Sil</button></form>
<p class="p-2 text-[11px] truncate text-stone-500">{{ $m->filename }}</p>
</div>
@endforeach
</div>
@endif
<div class="mt-4">{{ $media->links() }}</div>
@endsection