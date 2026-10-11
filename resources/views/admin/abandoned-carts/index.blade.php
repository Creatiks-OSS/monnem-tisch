@extends('admin.layouts.app')
@section('title', 'Sepeti bırakanlar')
@section('heading', 'Sepeti bırakanlar')
@section('subheading', 'Ödemeyi tamamlamayan sepetler. Satın alma olunca siparişlere geçer ve segmentten çıkar.')
@section('content')
<div class="mb-6 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
    <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Müşteri segmenti</p>
    <p class="mt-1 text-lg font-semibold">{{ $segment->name }}</p>
    <p class="mt-1 text-sm text-stone-500">{{ $segment->description }}</p>
    <p class="mt-3 text-sm"><span class="font-medium">{{ $segment->customers_count }}</span> kişi bu listede.</p>
    @if($members->isNotEmpty())
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach($members as $member)
                <a href="{{ route('admin.customers.show', $member) }}" class="rounded-full bg-stone-100 px-3 py-1 text-xs text-stone-700 hover:bg-stone-200">{{ $member->full_name }}</a>
            @endforeach
        </div>
    @endif
</div>

<div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
    <table class="min-w-full text-sm">
        <thead class="bg-stone-50 text-left text-xs uppercase text-stone-500"><tr>
            <th class="px-4 py-3">Müşteri</th>
            <th class="px-4 py-3">Sepet</th>
            <th class="px-4 py-3">Tutar</th>
            <th class="px-4 py-3">Mail</th>
            <th class="px-4 py-3"></th>
        </tr></thead>
        <tbody class="divide-y divide-stone-100">
        @forelse($orders as $order)
            <tr class="hover:bg-stone-50">
                <td class="px-4 py-3">
                    <div class="font-medium">{{ $order->billing_first_name }} {{ $order->billing_last_name }}</div>
                    <div class="text-xs text-stone-500">{{ $order->billing_email }}</div>
                </td>
                <td class="px-4 py-3 text-stone-600">
                    @foreach($order->items->reject(fn ($item) => in_array($item->options['kind'] ?? $item->sku, ['delivery', 'tip', 'DELIVERY', 'TIP'], true)) as $item)
                        <div>{{ $item->quantity }}× {{ $item->name }}</div>
                    @endforeach
                    @foreach($order->extras as $extra)
                        <div>{{ $extra->quantity }}× {{ $extra->name }}</div>
                    @endforeach
                </td>
                <td class="px-4 py-3">{{ number_format($order->total, 2, ',', '.') }} €</td>
                <td class="px-4 py-3 text-xs text-stone-500">
                    {{ $order->remarketing_sent_at ? $order->remarketing_sent_at->format('d.m.Y H:i') : 'Gönderilmedi' }}
                </td>
                <td class="px-4 py-3 text-right whitespace-nowrap">
                    <form method="POST" action="{{ route('admin.abandoned-carts.mail', $order) }}" class="inline">
                        @csrf
                        <button class="text-[#c4a35a]">Pazarlama maili</button>
                    </form>
                    <a href="{{ route('admin.orders.show', $order) }}" class="ml-3 text-stone-500">Aç</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-12 text-center text-stone-500">Sepetini bırakan müşteri yok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $orders->links() }}</div>
@endsection
