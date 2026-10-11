@extends('admin.layouts.app')
@section('title', $order->order_number)
@section('heading', 'Sipariş '.$order->order_number)
@section('content')
@if($order->isAbandoned())
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
        <p>Bu kayıt henüz satın alma değil. Sepeti bırakanlar listesinde ve “Sepette ürün bırakanlar” segmentinde duruyor.</p>
        @can('update', $order)
            <form method="POST" action="{{ route('admin.abandoned-carts.mail', $order) }}">
                @csrf
                <button class="rounded-full bg-[var(--admin-sidebar)] px-4 py-2 text-xs text-white">Pazarlama maili gönder</button>
            </form>
        @endcan
    </div>
@endif
<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="font-semibold mb-4">Kalemler</h2>
            <div class="space-y-4 text-sm">
                @foreach($order->items as $item)
                    @if(in_array($item->options['kind'] ?? $item->sku, ['delivery', 'tip', 'DELIVERY', 'TIP'], true))
                        @continue
                    @endif
                    @php
                        $packageImage = velora_media_url($item->options['package_image'] ?? $item->options['image'] ?? null, $item->name, (int) $item->id);
                    @endphp
                    <div class="rounded-xl border border-stone-100 p-3">
                        <div class="flex items-start gap-3">
                            <img src="{{ $packageImage }}" alt="{{ $item->name }}" class="h-14 w-14 shrink-0 rounded-xl object-cover">
                            <div class="min-w-0 flex-1">
                                <div class="flex justify-between gap-3">
                                    <span class="font-medium">{{ $item->quantity }}× {{ $item->name }}</span>
                                    <span class="shrink-0">{{ number_format($item->line_total, 2, ',', '.') }} €</span>
                                </div>
                                @if(!empty($item->options['selections']))
                                    <div class="mt-3 space-y-3">
                                        @foreach($item->options['selections'] as $sel)
                                            <div>
                                                <p class="text-[10px] uppercase tracking-wide text-stone-400">{{ $sel['slot'] ?? '' }}</p>
                                                <ul class="mt-1.5 space-y-1.5">
                                                    @foreach($sel['products'] ?? [] as $product)
                                                        <li class="flex items-center gap-2">
                                                            <img src="{{ velora_media_url($product['image'] ?? null, $product['name'] ?? '', (int) ($product['id'] ?? 0)) }}" alt="{{ $product['name'] ?? '' }}" class="h-8 w-8 rounded-lg object-cover">
                                                            <span>{{ $product['name'] ?? '' }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
                @foreach($order->extras as $extra)
                    <div class="flex items-center gap-3 rounded-xl border border-stone-100 p-3">
                        <img src="{{ $extra->extra?->displayImage() ?? velora_photo_for($extra->name, (int) $extra->id) }}" alt="{{ $extra->name }}" class="h-10 w-10 shrink-0 rounded-lg object-cover">
                        <span class="min-w-0 flex-1">{{ $extra->quantity }}× {{ $extra->name }}</span>
                        <span>{{ number_format($extra->line_total, 2, ',', '.') }} €</span>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 space-y-1 text-sm">
                <div class="flex justify-between text-stone-500"><span>Ara toplam</span><span>{{ number_format((float) $order->subtotal, 2, ',', '.') }} €</span></div>
                <div class="flex justify-between text-stone-500">
                    <span>Teslimat @if((float) ($order->delivery_km ?? 0) > 0)({{ number_format((float) $order->delivery_km, 1, ',', '.') }} km)@elseif((float) $order->delivery_fee <= 0)(ücretsiz)@endif</span>
                    <span>{{ number_format((float) $order->delivery_fee, 2, ',', '.') }} €</span>
                </div>
                @if((float) ($order->tip_amount ?? 0) > 0)
                    <div class="flex justify-between text-stone-500"><span>Bahşiş</span><span>{{ number_format((float) $order->tip_amount, 2, ',', '.') }} €</span></div>
                @endif
                @foreach(tax_display_lines($order) as $taxLine)
                    <div class="flex justify-between text-stone-500"><span>KDV ({{ format_mwst_percent($taxLine['rate']) }}%)</span><span>{{ number_format((float) $taxLine['amount'], 2, ',', '.') }} €</span></div>
                @endforeach
                <div class="flex justify-between font-semibold"><span>Toplam</span><span>{{ number_format($order->total, 2, ',', '.') }} €</span></div>
            </div>
        </div>
        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm text-sm space-y-1">
            <h2 class="font-semibold mb-3 text-base">Müşteri ve etkinlik</h2>
            <p>{{ $order->billing_first_name }} {{ $order->billing_last_name }} · {{ $order->billing_email }} · {{ $order->billing_phone }}</p>
            <p>Etkinlik: {{ optional($order->event_date)->format('d.m.Y') }} {{ $order->event_time }} · {{ $order->persons }} Kişi</p>
            <p>{{ $order->shipping_street }} {{ $order->shipping_house_number }}, {{ $order->shipping_postal_code }} {{ $order->shipping_city }}</p>
            @if($order->notes)<p class="pt-2 text-stone-500">{{ $order->notes }}</p>@endif
        </div>
    </div>
    <div class="space-y-4">
        <form method="POST" action="{{ route('admin.orders.update-status', $order) }}" class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            @csrf @method('PATCH')
            <h2 class="font-semibold mb-3">Durum</h2>
            <select name="status" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm mb-3">
                @foreach(['neu','bestätigt','in_bearbeitung','vorbereitung','unterwegs','geliefert','abgeschlossen','storniert'] as $s)
                    <option value="{{ $s }}" @selected($order->status === $s)>{{ admin_label($s) }}</option>
                @endforeach
            </select>
            <button type="submit" class="w-full rounded-lg bg-[var(--admin-sidebar)] text-white py-2 text-sm">Durumu kaydet</button>
        </form>
        <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm text-sm space-y-2">
            <h2 class="font-semibold mb-3 text-base">Ödeme</h2>
            <p>Yöntem: <strong>{{ $order->payment_method }}</strong></p>
            <p>Durum: <strong>{{ admin_label($order->payment_status) }}</strong></p>
            @forelse($order->payments as $payment)
                <p class="text-xs text-stone-500">{{ admin_label($payment->status) }} · {{ number_format((float) $payment->amount, 2, ',', '.') }} € · {{ $payment->transaction_id ?: 'işlem no yok' }}</p>
            @empty
                <p class="text-xs text-stone-500">Henüz ödeme kaydı yok.</p>
            @endforelse
            @if($order->payment_status !== 'paid')
                <form method="POST" action="{{ route('admin.orders.mark-paid', $order) }}" class="pt-2">
                    @csrf @method('PATCH')
                    <button class="w-full rounded-lg border border-stone-200 py-2 text-sm">Ödendi işaretle</button>
                </form>
            @endif
        </div>
        <a href="{{ route('admin.orders.edit', $order) }}" class="block text-center rounded-lg border border-stone-200 py-2 text-sm">Düzenle</a>
        @if(!$order->invoice)
        <form method="POST" action="{{ route('admin.invoices.store') }}">@csrf<input type="hidden" name="order_id" value="{{ $order->id }}"><button class="w-full rounded-lg border border-[#c4a35a] text-[#c4a35a] py-2 text-sm">Fatura oluştur</button></form>
        @else
        <a href="{{ route('admin.invoices.show', $order->invoice) }}" class="block text-center rounded-lg border border-[#c4a35a] text-[#c4a35a] py-2 text-sm">Faturayı gör</a>
        @endif
        <a href="{{ route('admin.orders.analysis', $order) }}" class="block text-center rounded-lg bg-[#c4a35a] text-white py-2 text-sm">Analiz</a>
        @if($order->sent_to_kitchen_at)
            <p class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-center text-sm text-emerald-800">
                Mutfakta · {{ $order->sent_to_kitchen_at->format('d.m.Y H:i') }}
            </p>
            <a href="{{ url('/mutfak') }}" target="_blank" rel="noopener" class="block text-center rounded-lg border border-stone-200 py-2 text-sm">Mutfak panelini aç</a>
        @else
            <form method="POST" action="{{ route('admin.orders.send-kitchen', $order, false) }}">
                @csrf
                <button class="w-full rounded-lg bg-[#161412] py-2.5 text-sm font-semibold text-white">Mutfağa gönder</button>
            </form>
            <p class="text-center text-[11px] text-stone-500">Etkinliğe 2 gün kala zaten otomatik düşer. Şimdi göndermek için basın.</p>
        @endif
        @can('delete', $order)
            <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" onsubmit="return confirm('Sipariş {{ $order->order_number }} silinsin mi?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full rounded-lg border border-red-200 py-2 text-sm text-red-700 hover:bg-red-50">Siparişi sil</button>
            </form>
        @endcan
    </div>
</div>
@endsection
