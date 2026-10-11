@extends('admin.layouts.app')
@section('title','Teslimat bölgesi')
@section('heading','Teslimat bölgesi oluştur')
@section('content')
@php $zone = new \App\Models\DeliveryZone(["is_active"=>true]); @endphp
<form method="POST" action="{{ route('admin.delivery-zones.store') }}" class="max-w-2xl space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">@csrf
@include('admin.partials.errors')
<div class="grid sm:grid-cols-2 gap-4">
<div><label class="block text-sm mb-1">Ad</label><input name="name" value="{{ old('name', $zone->name ?? '') }}" required class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Şehir</label><input name="city" value="{{ old('city', $zone->city ?? '') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div class="sm:col-span-2"><label class="block text-sm mb-1">PLZ (kommagetrennt)</label><input name="postal_codes" value="{{ old('postal_codes', isset($zone) ? implode(', ', $zone->postal_codes ?? []) : '') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Teslimat ücreti</label><input type="number" step="0.01" name="delivery_fee" value="{{ old('delivery_fee', $zone->delivery_fee ?? '') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Mindestbestellung</label><input type="number" step="0.01" name="minimum_order" value="{{ old('minimum_order', $zone->minimum_order ?? '') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Gratis ab</label><input type="number" step="0.01" name="free_delivery_threshold" value="{{ old('free_delivery_threshold', $zone->free_delivery_threshold ?? '') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Radius km</label><input type="number" step="0.01" name="radius_km" value="{{ old('radius_km', $zone->radius_km ?? '') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
</div>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $zone->is_active ?? true))> Aktif</label>
<button class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Kaydet</button></form>
@endsection