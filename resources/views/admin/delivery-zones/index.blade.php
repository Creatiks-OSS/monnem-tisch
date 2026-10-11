@extends('admin.layouts.app')
@section('title','Teslimat bölgeleri')
@section('heading','Teslimat bölgeleri')
@section('content')
<div class="flex justify-end mb-6"><a href="{{ route('admin.delivery-zones.create') }}" class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Yeni</a></div>
<div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm"><table class="min-w-full text-sm">
<thead class="bg-stone-50 text-xs uppercase text-stone-500 text-left"><tr><th class="px-4 py-3">Ad</th><th class="px-4 py-3">Şehir</th><th class="px-4 py-3">Ücret</th><th class="px-4 py-3"></th></tr></thead>
<tbody class="divide-y divide-stone-100">@forelse($zones as $z)
<tr><td class="px-4 py-3">{{ $z->name }}</td><td class="px-4 py-3">{{ $z->city }}</td><td class="px-4 py-3">{{ number_format($z->delivery_fee,2,',','.') }} €</td>
<td class="px-4 py-3 text-right"><a href="{{ route('admin.delivery-zones.edit',$z) }}" class="text-[#c4a35a]">Düzenle</a></td></tr>
@empty<tr><td colspan="4" class="px-4 py-12 text-center text-stone-500">Teslimat bölgesi yok.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $zones->links() }}</div>
@endsection