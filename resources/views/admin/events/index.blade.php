@extends('admin.layouts.app')
@section('title','Etkinlikler')
@section('heading','Etkinlikler')
@section('content')
<div class="flex flex-wrap justify-between gap-3 mb-6">
<form method="GET" class="flex gap-2"><input type="search" name="q" value="{{ request('q') }}" placeholder="Ara…" class="rounded-xl border border-stone-200 px-3 py-2 text-sm w-56"><button class="px-3 py-2 rounded-xl border text-sm">Filtrele</button></form>
<a href="{{ route('admin.events.create') }}" class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Yeni</a>
</div>
<div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm"><table class="min-w-full text-sm">
<thead class="bg-stone-50 text-xs uppercase text-stone-500 text-left"><tr><th class="px-4 py-3">Ad</th><th class="px-4 py-3">Aktif</th><th class="px-4 py-3"></th></tr></thead>
<tbody class="divide-y divide-stone-100">
@forelse($events as $item)
<tr><td class="px-4 py-3">{{ $item->name }}</td><td class="px-4 py-3">{{ $item->is_active?'Evet':'Hayır' }}</td><td class="px-4 py-3 text-right"><a href="{{ route('admin.events.edit',$item) }}" class="text-[#c4a35a]">Düzenle</a></td></tr>
@empty<tr><td colspan="10" class="px-4 py-12 text-center text-stone-500">Kayıt yok.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $events->links() }}</div>
@endsection