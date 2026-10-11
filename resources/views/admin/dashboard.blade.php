@extends('admin.layouts.app')
@section('title', 'Özet')
@section('heading', 'Günaydın')
@section('subheading', 'Menüler, siparişler ve talepler – tek bakışta')
@section('content')
@php
    $menuColors = [
        'buffets' => 'from-[#c45c26] to-[#a0461c]',
        'fingerfood' => 'from-[#3f6f4e] to-[#2d5239]',
        'fruehstueck' => 'from-[#e3a117] to-[#c48910]',
        'grillcatering' => 'from-[#8e2f45] to-[#6e2234]',
        'vegetarisch' => 'from-[#c2413b] to-[#9a2f2a]',
        'vegan' => 'from-[#2f8f5b] to-[#246f46]',
    ];
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
    @foreach([
        ['Siparişler', number_format($stats['orders_count']), 'Toplam kayıt'],
        ['Ciro', number_format($stats['revenue'], 2, ',', '.').' €', 'Onaylı satış'],
        ['Talepler', number_format($stats['leads']), 'Teklif istekleri'],
        ['Yemekler', number_format($dishCount ?? 0), 'Paket seçenekleri'],
    ] as [$label, $value, $hint])
        <div class="admin-card p-5">
            <p class="text-[11px] uppercase tracking-[0.16em] text-[var(--admin-muted)]">{{ $label }}</p>
            <p class="mt-2 font-serif text-3xl tracking-tight">{{ $value }}</p>
            <p class="mt-1 text-xs text-[var(--admin-muted)]">{{ $hint }}</p>
        </div>
    @endforeach
</div>

<div class="mb-8">
    <div class="mb-4 flex items-end justify-between gap-3">
        <div>
            <h2 class="text-2xl">Menü katalogu</h2>
            <p class="text-sm text-[var(--admin-muted)]">Buffets, Fingerfood, Frühstück, Grill — paket menüleri.</p>
        </div>
        <a href="{{ route('admin.packages.index') }}" class="admin-btn admin-btn-gold">Paketleri yönet</a>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($menus as $menu)
            <a href="{{ route('admin.packages.index', ['menu' => $menu['key']]) }}" class="group relative overflow-hidden rounded-2xl bg-charcoal text-cream shadow-soft">
                <img src="{{ velora_photo($menu['photo']) }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-50 transition duration-500 group-hover:scale-105">
                <div class="absolute inset-0 bg-gradient-to-t {{ $menuColors[$menu['key']] ?? 'from-black' }} opacity-70"></div>
                <div class="relative flex min-h-[10.5rem] flex-col justify-end p-4">
                    <p class="font-serif text-2xl">{{ $menu['label'] }}</p>
                    <p class="mt-1 text-xs text-cream/80">
                        {{ $menu['active'] }} aktif paket · {{ $menu['count'] }} toplam
                    </p>
                </div>
            </a>
        @endforeach
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6 mb-8">
    <div class="admin-card p-5">
        <h2 class="text-xl mb-4">Aylık ciro</h2>
        <div class="space-y-2 max-h-72 overflow-y-auto">
            @foreach($stats['monthly_revenue'] as $row)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-[var(--admin-muted)]">{{ $row['label'] }}</span>
                    <span class="font-medium">{{ number_format($row['revenue'], 2, ',', '.') }} €</span>
                </div>
            @endforeach
        </div>
    </div>
    <div class="admin-card p-5">
        <h2 class="text-xl mb-4">Popüler paketler</h2>
        @forelse($stats['popular_products'] as $p)
            <div class="flex justify-between text-sm py-2 border-b border-[var(--admin-line)] last:border-0">
                <span>{{ $p['name'] }}</span>
                <span class="text-[var(--admin-muted)]">{{ $p['quantity'] }}× · {{ number_format($p['revenue'], 2, ',', '.') }} €</span>
            </div>
        @empty
            <p class="text-sm text-[var(--admin-muted)]">Henüz satış verisi yok. Paket siparişleri burada görünecek.</p>
        @endforelse
        <a href="{{ route('admin.products.index') }}" class="mt-4 inline-block text-sm text-[var(--admin-terracotta)]">Yemek listesine git →</a>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xl">Yeni siparişler</h2>
            <a href="{{ route('admin.orders.index') }}" class="text-sm text-[var(--admin-terracotta)]">Tümü</a>
        </div>
        <div class="admin-card overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-[#f7f1e8] text-left text-[11px] uppercase tracking-wide text-[var(--admin-muted)]"><tr>
                    <th class="px-4 py-3">No</th><th class="px-4 py-3">Müşteri</th><th class="px-4 py-3">Durum</th><th class="px-4 py-3">Toplam</th><th class="px-4 py-3"></th>
                </tr></thead>
                <tbody class="divide-y divide-[var(--admin-line)]">
                @forelse($recentOrders as $order)
                    <tr class="hover:bg-[#faf6f0]">
                        <td class="px-4 py-3"><a class="font-medium text-[var(--admin-terracotta)]" href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                        <td class="px-4 py-3">{{ $order->billing_first_name }} {{ $order->billing_last_name }}</td>
                        <td class="px-4 py-3"><span class="admin-chip bg-[#f3eee6]">{{ admin_label($order->status) }}</span></td>
                        <td class="px-4 py-3">{{ number_format($order->total, 2, ',', '.') }} €</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a class="text-[var(--admin-terracotta)]" href="{{ route('admin.orders.show', $order) }}">Aç</a>
                            @can('delete', $order)
                                <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" class="mt-1" onsubmit="return confirm('Sipariş {{ $order->order_number }} silinsin mi?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-700 hover:text-red-800">Sil</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-[var(--admin-muted)]">Sipariş yok</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xl">Yeni talepler</h2>
            <a href="{{ route('admin.quotes.index') }}" class="text-sm text-[var(--admin-terracotta)]">Tümü</a>
        </div>
        <div class="admin-card overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-[#f7f1e8] text-left text-[11px] uppercase tracking-wide text-[var(--admin-muted)]"><tr>
                    <th class="px-4 py-3">No</th><th class="px-4 py-3">Ad</th><th class="px-4 py-3">Durum</th>
                </tr></thead>
                <tbody class="divide-y divide-[var(--admin-line)]">
                @forelse($recentQuotes as $quote)
                    <tr class="hover:bg-[#faf6f0]">
                        <td class="px-4 py-3"><a class="font-medium text-[var(--admin-terracotta)]" href="{{ route('admin.quotes.show', $quote) }}">{{ $quote->quote_number }}</a></td>
                        <td class="px-4 py-3">{{ $quote->first_name }} {{ $quote->last_name }}</td>
                        <td class="px-4 py-3"><span class="admin-chip bg-[#f3eee6]">{{ admin_label($quote->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-[var(--admin-muted)]">Talep yok</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
