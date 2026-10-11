@extends('admin.layouts.app')
@section('title','Müşteriler')
@section('heading','Müşteriler')
@section('content')
<div class="flex justify-between mb-6"><form method="GET" class="flex gap-2"><input type="search" name="q" value="{{ request('q') }}" placeholder="Ara…" class="rounded-xl border border-stone-200 px-3 py-2 text-sm w-56"><button class="px-3 py-2 rounded-xl border text-sm">Filtrele</button></form>
<a href="{{ route('admin.customers.create') }}" class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Yeni</a></div>
<div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm"><table class="min-w-full text-sm">
<thead class="bg-stone-50 text-xs uppercase text-stone-500 text-left"><tr><th class="px-4 py-3">Ad</th><th class="px-4 py-3">E-posta</th><th class="px-4 py-3">Siparişler</th><th class="px-4 py-3"></th></tr></thead>
<tbody class="divide-y divide-stone-100">
@forelse($customers as $c)
<tr class="hover:bg-stone-50"><td class="px-4 py-3">{{ $c->full_name }}</td><td class="px-4 py-3">{{ $c->email }}</td><td class="px-4 py-3">{{ $c->orders_count }}</td><td class="px-4 py-3 text-right"><a href="{{ route('admin.customers.show',$c) }}" class="text-[#c4a35a]">Aç</a></td></tr>
@empty<tr><td colspan="4" class="px-4 py-12 text-center text-stone-500">Müşteri yok.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $customers->links() }}</div>
@endsection