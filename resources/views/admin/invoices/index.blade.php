@extends('admin.layouts.app')
@section('title','Faturalar')
@section('heading','Faturalar')
@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
<form method="GET"><input type="search" name="q" value="{{ request('q') }}" placeholder="Ara…" class="rounded-xl border border-stone-200 px-3 py-2 text-sm w-64"></form>
<a href="{{ route('admin.invoices.manual') }}" class="rounded-lg bg-[var(--admin-sidebar)] px-4 py-2 text-sm text-white">Manuel fatura</a>
</div>
<div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm"><table class="min-w-full text-sm">
<thead class="bg-stone-50 text-xs uppercase text-stone-500 text-left"><tr><th class="px-4 py-3">No</th><th class="px-4 py-3">Müşteri</th><th class="px-4 py-3">Durum</th><th class="px-4 py-3">Toplam</th><th class="px-4 py-3"></th></tr></thead>
<tbody class="divide-y divide-stone-100">@forelse($invoices as $inv)
<tr><td class="px-4 py-3 font-medium">{{ $inv->invoice_number }}</td><td class="px-4 py-3">{{ $inv->billingName() ?: $inv->customer?->full_name }}</td><td class="px-4 py-3">{{ admin_label($inv->status) }}</td><td class="px-4 py-3">{{ number_format($inv->total,2,',','.') }} €</td>
<td class="px-4 py-3 text-right"><a href="{{ route('admin.invoices.show',$inv) }}" class="text-[#c4a35a]">Aç</a></td></tr>
@empty<tr><td colspan="5" class="px-4 py-12 text-center text-stone-500">Fatura yok.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $invoices->links() }}</div>
@endsection