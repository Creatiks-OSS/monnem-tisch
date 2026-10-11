@extends('admin.layouts.app')
@section('title','Sayfalar')
@section('heading','Sayfalar')
@section('content')
<div class="flex justify-end mb-6"><a href="{{ route('admin.pages.create') }}" class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Yeni</a></div>
<div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm"><table class="min-w-full text-sm">
<thead class="bg-stone-50 text-xs uppercase text-stone-500 text-left"><tr><th class="px-4 py-3">Başlık</th><th class="px-4 py-3">Slug</th><th class="px-4 py-3">Durum</th><th class="px-4 py-3"></th></tr></thead>
<tbody class="divide-y divide-stone-100">@forelse($pages as $p)
<tr><td class="px-4 py-3">{{ $p->title }}</td><td class="px-4 py-3">{{ $p->slug }}</td><td class="px-4 py-3">{{ $p->is_published?'Live':'Entwurf' }}</td>
<td class="px-4 py-3 text-right"><a href="{{ route('admin.pages.edit',$p) }}" class="text-[#c4a35a]">Düzenle</a></td></tr>
@empty<tr><td colspan="4" class="px-4 py-12 text-center text-stone-500">Sayfa yok.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $pages->links() }}</div>
@endsection