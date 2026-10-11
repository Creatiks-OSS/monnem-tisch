@extends('admin.layouts.app')
@section('title','Galeri')
@section('heading','Galeri')
@section('content')
<div class="flex justify-end mb-6"><a href="{{ route('admin.galleries.create') }}" class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Yeni</a></div>
<div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm"><table class="min-w-full text-sm">
<thead class="bg-stone-50 text-xs uppercase text-stone-500 text-left"><tr><th class="px-4 py-3">Ad</th><th class="px-4 py-3">Bilder</th><th class="px-4 py-3">Aktif</th><th class="px-4 py-3"></th></tr></thead>
<tbody class="divide-y divide-stone-100">@forelse($galleries as $g)
<tr><td class="px-4 py-3">{{ $g->name }}</td><td class="px-4 py-3">{{ $g->images_count }}</td><td class="px-4 py-3">{{ $g->is_active?'Evet':'Hayır' }}</td>
<td class="px-4 py-3 text-right whitespace-nowrap">
                    <a href="{{ route('admin.galleries.edit',$g) }}" class="text-[#c4a35a]">Düzenle</a>
                    <form method="POST" action="{{ route('admin.galleries.destroy', $g) }}" class="mt-1 inline-block" onsubmit="return confirm('Galeri silinsin mi?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-700 hover:text-red-800">Sil</button>
                    </form>
                </td></tr>
@empty<tr><td colspan="4" class="px-4 py-12 text-center text-stone-500">Galeri yok.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $galleries->links() }}</div>
@endsection