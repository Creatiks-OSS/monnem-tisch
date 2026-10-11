@extends('admin.layouts.app')
@section('title',$customer->full_name)
@section('heading',$customer->full_name)
@section('content')
<div class="flex gap-3 mb-6"><a href="{{ route('admin.customers.edit',$customer) }}" class="px-4 py-2 rounded-lg border text-sm">Düzenle</a>
<form method="POST" action="{{ route('admin.customers.destroy',$customer) }}" onsubmit="return confirm('Silinsin mi?')">@csrf @method('DELETE')<button class="px-4 py-2 rounded-lg border border-red-200 text-red-700 text-sm">Sil</button></form></div>
<div class="grid lg:grid-cols-2 gap-6">
<div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm text-sm space-y-1">
<p>{{ $customer->email }} · {{ $customer->phone }}</p>
<p>{{ $customer->company }}</p>
@if($customer->segments->isNotEmpty())
    <p class="pt-2">
        @foreach($customer->segments as $segment)
            <span class="inline-block rounded-full bg-amber-100 px-2 py-1 text-xs text-amber-900">{{ $segment->name }}</span>
        @endforeach
    </p>
@endif
<p class="text-stone-500">{{ $customer->notes }}</p>
</div>
<div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
<h2 class="font-semibold mb-3">Siparişler</h2>
@php $placedOrders = $customer->orders->reject(fn ($o) => $o->isAbandoned()); $leftCarts = $customer->orders->filter(fn ($o) => $o->isAbandoned()); @endphp
@forelse($placedOrders as $o)<div class="text-sm py-1"><a class="text-[#c4a35a]" href="{{ route('admin.orders.show',$o) }}">{{ $o->order_number }}</a> · {{ number_format($o->total,2,',','.') }} €</div>
@empty<p class="text-sm text-stone-500">Sipariş yok.</p>@endforelse
@if($leftCarts->isNotEmpty())
    <h2 class="font-semibold mt-5 mb-3">Sepeti bırakanlar</h2>
    @foreach($leftCarts as $o)
        <div class="text-sm py-1"><a class="text-[#c4a35a]" href="{{ route('admin.orders.show',$o) }}">{{ $o->order_number }}</a> · {{ number_format($o->total,2,',','.') }} €</div>
    @endforeach
@endif
</div>
</div>
@endsection