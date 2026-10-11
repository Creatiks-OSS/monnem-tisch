@extends('admin.layouts.app')
@section('title','Şehirler')
@section('heading','Şehirler')
@section('content')
<div class="flex justify-between mb-6"><form method="GET"><input type="search" name="q" value="{{ request('q') }}" class="rounded-xl border border-stone-200 px-3 py-2 text-sm" placeholder="Ara…"></form>
<a href="{{ route('admin.cities.create') }}" class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Yeni</a></div>
<div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm"><table class="min-w-full text-sm">
<thead class="bg-stone-50 text-xs uppercase text-stone-500 text-left"><tr><th class="px-4 py-3">Ad</th><th class="px-4 py-3">PLZ</th><th class="px-4 py-3">Aktif</th><th class="px-4 py-3"></th></tr></thead>
<tbody class="divide-y divide-stone-100">@forelse($cities as $c)
<tr><td class="px-4 py-3">{{ $c->name }}</td><td class="px-4 py-3">{{ $c->postal_code }}</td><td class="px-4 py-3">{{ $c->is_active?'Evet':'Hayır' }}</td>
<td class="px-4 py-3 text-right"><a href="{{ route('admin.cities.edit',$c) }}" class="text-[#c4a35a]">Düzenle</a></td></tr>
@empty<tr><td colspan="4" class="px-4 py-12 text-center text-stone-500">Şehir yok.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $cities->links() }}</div>
@endsection