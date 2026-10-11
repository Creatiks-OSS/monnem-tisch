@extends('admin.layouts.app')
@section('title','Şehir')
@section('heading','Şehir oluştur')
@section('content')
@php $city = new \App\Models\City(["is_active"=>true]); @endphp
<form method="POST" action="{{ route('admin.cities.store') }}" enctype="multipart/form-data" class="max-w-2xl space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">@csrf
@include('admin.partials.errors')
<div class="grid sm:grid-cols-2 gap-4">
<div><label class="block text-sm mb-1">Ad</label><input name="name" value="{{ old('name', $city->name ?? '') }}" required class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">PLZ</label><input name="postal_code" value="{{ old('postal_code', $city->postal_code ?? '') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Lieferpreis</label><input type="number" step="0.01" name="delivery_price" value="{{ old('delivery_price', $city->delivery_price ?? '') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Bild</label><input type="file" name="image" accept="image/*" class="w-full text-sm"></div>
</div>
<div><label class="block text-sm mb-1">Inhalt</label><textarea name="content" rows="6" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">{{ old('content', $city->content ?? '') }}</textarea></div>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $city->is_active ?? true))> Aktif</label>
<button class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Kaydet</button></form>
@endsection