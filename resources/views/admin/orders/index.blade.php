@extends('admin.layouts.app')
@section('title', 'Siparişler')
@section('heading', 'Siparişler')
@section('content')
<div class="flex flex-wrap gap-3 mb-6">
    <form method="GET" class="flex flex-wrap gap-2">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Ara…" class="rounded-xl border border-stone-200 px-3 py-2 text-sm w-56">
        <select name="status" class="rounded-xl border border-stone-200 px-3 py-2 text-sm">
            <option value="">Tüm durumlar</option>
            @foreach(['neu','bestätigt','in_bearbeitung','vorbereitung','unterwegs','geliefert','abgeschlossen','storniert'] as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ admin_label($s) }}</option>
            @endforeach
        </select>
        <button class="px-3 py-2 rounded-xl border border-stone-200 text-sm">Filtrele</button>
    </form>
</div>
<div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
    <table class="min-w-full text-sm">
        <thead class="bg-stone-50 text-left text-xs uppercase text-stone-500"><tr>
            <th class="px-4 py-3">No</th><th class="px-4 py-3">Müşteri</th><th class="px-4 py-3">Tarih</th><th class="px-4 py-3">Durum</th><th class="px-4 py-3">Toplam</th><th class="px-4 py-3"></th>
        </tr></thead>
        <tbody class="divide-y divide-stone-100">
        @forelse($orders as $order)
            <tr class="hover:bg-stone-50">
                <td class="px-4 py-3 font-medium">{{ $order->order_number }}</td>
                <td class="px-4 py-3">{{ $order->billing_first_name }} {{ $order->billing_last_name }}</td>
                <td class="px-4 py-3">{{ optional($order->event_date)->format('d.m.Y') }}</td>
                        <td class="px-4 py-3"><span class="text-xs px-2 py-1 rounded-full bg-stone-100">{{ admin_label($order->status) }}</span></td>
                <td class="px-4 py-3">{{ number_format($order->total, 2, ',', '.') }} €</td>
                <td class="px-4 py-3 text-right whitespace-nowrap">
                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('admin.orders.show', $order) }}" class="text-[#c4a35a]">Aç</a>
                        @can('delete', $order)
                            <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" onsubmit="return confirm('Sipariş {{ $order->order_number }} silinsin mi?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-700 hover:text-red-800">Sil</button>
                            </form>
                        @endcan
                    </div>
                    @unless($order->sent_to_kitchen_at)
                        <form method="POST" action="{{ route('admin.orders.send-kitchen', $order, false) }}" class="mt-1">
                            @csrf
                            <button class="text-xs text-stone-500 hover:text-stone-800">Mutfağa gönder</button>
                        </form>
                    @else
                        <span class="mt-1 block text-[11px] text-emerald-700">Mutfakta</span>
                    @endunless
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-4 py-12 text-center text-stone-500">Sipariş bulunamadı.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $orders->links() }}</div>
@endsection