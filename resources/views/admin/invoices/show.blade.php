@extends('admin.layouts.app')
@section('title',$invoice->invoice_number)
@section('heading','Fatura '.$invoice->invoice_number)
@section('content')
<div class="grid lg:grid-cols-3 gap-6">
<div class="lg:col-span-2 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm text-sm space-y-2">
<p>Müşteri: {{ $invoice->billingName() ?: $invoice->customer?->full_name }} · {{ $invoice->billing_email ?: $invoice->customer?->email }}</p>
@if($invoice->is_manual)
<p>{{ $invoice->billing_street }} {{ $invoice->billing_house_number }}, {{ $invoice->billing_postal_code }} {{ $invoice->billing_city }}</p>
<p>Ödeme: {{ \App\Models\Invoice::MANUAL_ACCOUNT_HOLDER }} · {{ \App\Models\Invoice::MANUAL_IBAN }}</p>
@endif
<p>Sipariş: @if($invoice->order)<a class="text-[#c4a35a]" href="{{ route('admin.orders.show',$invoice->order) }}">{{ $invoice->order->order_number }}</a>@else — @endif</p>
<p>Düzenlendi: {{ optional($invoice->issued_at)->format('d.m.Y') }}</p>
<div class="pt-4 font-semibold flex justify-between"><span>Toplam</span><span>{{ number_format($invoice->total,2,',','.') }} €</span></div>
</div>
<div class="space-y-3">
<form method="POST" action="{{ route('admin.invoices.update',$invoice) }}" class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm space-y-3">@csrf @method('PUT')
<select name="status" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">@foreach(['draft','sent','paid','cancelled'] as $s)<option value="{{ $s }}" @selected($invoice->status===$s)>{{ admin_label($s) }}</option>@endforeach</select>
<textarea name="notes" rows="3" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm" placeholder="Notlar">{{ $invoice->notes }}</textarea>
<button class="w-full rounded-lg bg-[var(--admin-sidebar)] text-white py-2 text-sm">Kaydet</button>
</form>
<a href="{{ route('admin.invoices.pdf',$invoice) }}" class="block text-center rounded-lg border border-[#c4a35a] text-[#c4a35a] py-2 text-sm">PDF indir</a>
<form method="POST" action="{{ route('admin.invoices.send',$invoice) }}">
    @csrf
    <button type="submit" class="w-full rounded-lg bg-[#c4a35a] text-white py-2 text-sm">Gönder</button>
</form>
</div>
</div>
@endsection