@extends('admin.layouts.app')
@section('title','Kuponlar')
@section('heading','Kuponlar')
@section('content')
<div class="flex justify-end mb-6"><a href="{{ route('admin.coupons.create') }}" class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Yeni</a></div>
<div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm"><table class="min-w-full text-sm">
<thead class="bg-stone-50 text-xs uppercase text-stone-500 text-left"><tr><th class="px-4 py-3">Code</th><th class="px-4 py-3">Typ</th><th class="px-4 py-3">Wert</th><th class="px-4 py-3">Genutzt</th><th class="px-4 py-3"></th></tr></thead>
<tbody class="divide-y divide-stone-100">@forelse($coupons as $c)
<tr><td class="px-4 py-3 font-mono">{{ $c->code }}</td><td class="px-4 py-3">{{ $c->type }}</td><td class="px-4 py-3">{{ $c->value }}</td><td class="px-4 py-3">{{ $c->used_count }}/{{ $c->max_uses ?? '∞' }}</td>
<td class="px-4 py-3 text-right"><a href="{{ route('admin.coupons.edit',$c) }}" class="text-[#c4a35a]">Düzenle</a></td></tr>
@empty<tr><td colspan="5" class="px-4 py-12 text-center text-stone-500">Kupon yok.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $coupons->links() }}</div>
@endsection