@extends('admin.layouts.app')
@section('title','Kupon')
@section('heading','Kuponu düzenle')
@section('content')
<form method="POST" action="{{ route('admin.coupons.update', $coupon) }}" class="max-w-2xl space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">@csrf @method('PUT')
@include('admin.partials.errors')
<div class="grid sm:grid-cols-2 gap-4">
<div><label class="block text-sm mb-1">Code</label><input name="code" value="{{ old('code', $coupon->code ?? '') }}" required class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm uppercase"></div>
<div><label class="block text-sm mb-1">Typ</label><select name="type" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"><option value="fixed" @selected(old('type',$coupon->type ?? '')==='fixed')>Festbetrag</option><option value="percent" @selected(old('type',$coupon->type ?? '')==='percent')>Prozent</option></select></div>
<div><label class="block text-sm mb-1">Wert</label><input type="number" step="0.01" name="value" value="{{ old('value', $coupon->value ?? '') }}" required class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Mindestbestellung</label><input type="number" step="0.01" name="minimum_order" value="{{ old('minimum_order', $coupon->minimum_order ?? '') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Max. Nutzungen</label><input type="number" name="max_uses" value="{{ old('max_uses', $coupon->max_uses ?? '') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Start</label><input type="datetime-local" name="starts_at" value="{{ old('starts_at', isset($coupon->starts_at) ? $coupon->starts_at->format('Y-m-d\\TH:i') : '') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
<div><label class="block text-sm mb-1">Ende</label><input type="datetime-local" name="expires_at" value="{{ old('expires_at', isset($coupon->expires_at) ? $coupon->expires_at->format('Y-m-d\\TH:i') : '') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
</div>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $coupon->is_active ?? true))> Aktif</label>
<button class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Kaydet</button></form>
@endsection