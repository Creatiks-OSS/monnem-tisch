@extends('admin.layouts.app')
@section('title','Yorumlar')
@section('heading','Yorumlar')
@section('content')
<form method="GET" class="mb-6 flex gap-2"><select name="status" class="rounded-xl border border-stone-200 px-3 py-2 text-sm"><option value="">Tümü</option><option value="pending" @selected(request('status')==='pending')>Beklemede</option><option value="approved" @selected(request('status')==='approved')>Onaylı</option><option value="featured" @selected(request('status')==='featured')>Öne çıkan</option></select><button class="px-3 py-2 rounded-xl border text-sm">Filtrele</button></form>
<div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm"><table class="min-w-full text-sm">
<thead class="bg-stone-50 text-xs uppercase text-stone-500 text-left"><tr><th class="px-4 py-3">Ad</th><th class="px-4 py-3">Rating</th><th class="px-4 py-3">Durum</th><th class="px-4 py-3"></th></tr></thead>
<tbody class="divide-y divide-stone-100">@forelse($reviews as $r)
<tr><td class="px-4 py-3">{{ $r->name }}</td><td class="px-4 py-3">{{ $r->rating }}/5</td><td class="px-4 py-3">{{ $r->is_approved?'Onaylı':'Beklemede' }}{{ $r->is_featured?' · Öne çıkan':'' }}</td>
<td class="px-4 py-3 text-right space-x-2"><a href="{{ route('admin.reviews.edit',$r) }}" class="text-[#c4a35a]">Düzenle</a>
<form class="inline" method="POST" action="{{ route('admin.reviews.approve',$r) }}">@csrf<button class="text-emerald-700">Freigeben</button></form>
<form class="inline" method="POST" action="{{ route('admin.reviews.feature',$r) }}">@csrf<button class="text-amber-700">Öne çıkan</button></form></td></tr>
@empty<tr><td colspan="4" class="px-4 py-12 text-center text-stone-500">Yorum yok.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $reviews->links() }}</div>
@endsection