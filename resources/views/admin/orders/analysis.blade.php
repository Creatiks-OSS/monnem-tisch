@extends('admin.layouts.app')
@section('title', 'Analiz '.$order->order_number)
@section('heading', 'Malzeme analizi')
@section('subheading', $order->order_number.' · '.$order->persons.' kişi')
@push('styles')
<style>
@media print {
    body.admin-body { background: #fff !important; }
    header, aside, .no-print, .admin-notify-panel { display: none !important; }
    main { padding: 0 !important; }
    .print-only { display: block !important; }
    .print-grid { display: block !important; }
    .print-card { break-inside: avoid; box-shadow: none !important; border-color: #ddd !important; margin-bottom: 1.25rem; }
}
</style>
@endpush
@section('content')
@php
    $analysisStatus = $ingredientAnalysis?->status ?? 'missing';
    $canPrint = $ingredientAnalysis?->isReady();
@endphp
<div class="space-y-6" x-data="{
    analysis: '{{ $analysisStatus }}',
    poll() {
        if (this.analysis !== 'pending' && this.analysis !== 'processing') return;
        fetch('{{ route('admin.orders.ingredients.status', $order) }}', { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(d => {
                if (d.ready || d.failed) location.reload();
                else setTimeout(() => this.poll(), 3000);
            })
            .catch(() => setTimeout(() => this.poll(), 5000));
    }
}" x-init="poll()">
    <div class="no-print flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.orders.show', $order) }}" class="text-sm text-stone-500 hover:text-[#c4a35a]">← Siparişe dön</a>
        <div class="flex flex-wrap items-center gap-2">
            <form method="POST" action="{{ route('admin.orders.ingredients.analyze', $order) }}">
                @csrf
                <button class="rounded-lg border border-stone-200 px-3 py-1.5 text-sm hover:border-[#c4a35a]">Yeniden analiz et</button>
            </form>
            <button type="button" @if(! $canPrint) disabled @endif onclick="window.print()"
                class="rounded-lg bg-[#c4a35a] text-white px-3 py-1.5 text-sm disabled:opacity-40 disabled:cursor-not-allowed">Yazdır</button>
        </div>
    </div>

    <div class="print-only hidden mb-4">
        <h1 class="text-2xl font-semibold">Malzeme analizi</h1>
        <p class="text-sm text-stone-600">{{ $order->order_number }} · {{ $order->persons }} kişi
            @if($order->event_date) · {{ $order->event_date->format('d.m.Y') }}@endif
            @if($order->event_time) {{ $order->event_time }}@endif
        </p>
        <p class="text-sm text-stone-600">{{ $order->billing_first_name }} {{ $order->billing_last_name }}</p>
    </div>

    @if(! $aiConfigured)
        <p class="no-print rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            OpenAI anahtarı yok. <a class="underline" href="{{ route('admin.settings.group', 'ai') }}">Ayarlar → Yapay zekâ</a> bölümünden GPT-4o mini API anahtarını ekleyin.
        </p>
    @elseif($analysisStatus === 'pending' || $analysisStatus === 'processing')
        <p class="no-print rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-600">Yapay zekâ malzemeleri hesaplıyor… Bu sayfa otomatik yenilenir.</p>
    @elseif($analysisStatus === 'failed')
        <p class="no-print rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $ingredientAnalysis?->error_message ?: 'Malzeme analizi başarısız.' }}</p>
    @endif

    <div class="print-grid grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
            <div class="print-card rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="font-semibold mb-4">Ürün bazında malzemeler</h2>
                @if($ingredientAnalysis?->isReady() && !empty($ingredientAnalysis->items))
                    <div class="space-y-5">
                        @foreach($ingredientAnalysis->items as $dish)
                            <div class="rounded-xl border border-stone-100 p-4">
                                <p class="font-medium">{{ $dish['name'] ?? '' }}@if(!empty($dish['quantity_label'])) <span class="font-normal text-stone-400">· {{ $dish['quantity_label'] }}</span>@endif</p>
                                @if(!empty($dish['ingredients']))
                                    <ul class="mt-3 space-y-1 text-sm">
                                        @foreach($dish['ingredients'] as $ing)
                                            <li class="flex justify-between gap-4">
                                                <span>{{ $ing['name'] ?? '' }}</span>
                                                <span class="shrink-0 tabular-nums text-stone-600">{{ $ing['display'] ?? '' }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="mt-2 text-sm text-stone-500">Malzeme listesi üretilemedi.</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @elseif($aiConfigured && ! in_array($analysisStatus, ['pending', 'processing', 'failed'], true))
                    <p class="text-sm text-stone-500">Henüz analiz yok. “Yeniden analiz et” ile başlatın.</p>
                @endif
            </div>
        </div>

        <div>
            <div class="print-card rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="font-semibold mb-1">Toplam Malzeme Listesi</h2>
                <p class="mb-4 text-xs text-stone-500">Aynı malzemeler birleştirildi. Tuz, karabiber ve yağ dahil.</p>
                @if($ingredientAnalysis?->isReady() && !empty($ingredientAnalysis->totals))
                    <ul class="divide-y divide-stone-100 text-sm">
                        @foreach($ingredientAnalysis->totals as $row)
                            <li class="flex justify-between gap-4 py-2">
                                <span>{{ $row['name'] ?? '' }}</span>
                                <span class="tabular-nums font-medium">{{ $row['display'] ?? '' }}</span>
                            </li>
                        @endforeach
                    </ul>
                @elseif($ingredientAnalysis?->isReady())
                    <p class="text-sm text-stone-500">Toplam malzeme bulunamadı.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
