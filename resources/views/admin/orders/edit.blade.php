@extends('admin.layouts.app')
@section('title', 'Siparişi düzenle')
@section('heading', 'Sipariş '.$order->order_number)
@section('content')
@include('admin.partials.errors')
<form method="POST" action="{{ route('admin.orders.update', $order) }}" class="max-w-2xl space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
    @csrf @method('PUT')
    <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="block text-sm mb-1">Etkinlik tarihi</label><input type="date" name="event_date" value="{{ old('event_date', optional($order->event_date)->format('Y-m-d')) }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
        <div><label class="block text-sm mb-1">Saat</label><input type="text" name="event_time" value="{{ old('event_time', $order->event_time) }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
        <div><label class="block text-sm mb-1">Kişi</label><input type="number" name="persons" value="{{ old('persons', $order->persons) }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
        <div><label class="block text-sm mb-1">Zahlungsstatus</label><input type="text" name="payment_status" value="{{ old('payment_status', $order->payment_status) }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm"></div>
    </div>
    <div><label class="block text-sm mb-1">Notlar</label><textarea name="notes" rows="3" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">{{ old('notes', $order->notes) }}</textarea></div>
    <div><label class="block text-sm mb-1">Yönetici notları</label><textarea name="admin_notes" rows="3" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">{{ old('admin_notes', $order->admin_notes) }}</textarea></div>
    <button class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Kaydet</button>
</form>
@endsection