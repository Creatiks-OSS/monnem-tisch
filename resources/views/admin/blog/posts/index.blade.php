@extends('admin.layouts.app')
@section('title','Blog')
@section('heading','Blog yazıları')
@section('content')
<div class="flex justify-between mb-6"><a href="{{ route('admin.blog-categories.index') }}" class="text-sm text-[#c4a35a]">Kategoriler</a>
<a href="{{ route('admin.blog-posts.create') }}" class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Yeni</a></div>
<div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm"><table class="min-w-full text-sm">
<thead class="bg-stone-50 text-xs uppercase text-stone-500 text-left"><tr><th class="px-4 py-3">Başlık</th><th class="px-4 py-3">Kategori</th><th class="px-4 py-3">Durum</th><th class="px-4 py-3"></th></tr></thead>
<tbody class="divide-y divide-stone-100">@forelse($posts as $p)
<tr><td class="px-4 py-3">{{ $p->title }}</td><td class="px-4 py-3">{{ $p->category?->name }}</td><td class="px-4 py-3">{{ $p->is_published?'Yayında':'Entwurf' }}</td>
<td class="px-4 py-3 text-right"><a href="{{ route('admin.blog-posts.preview-saved',$p) }}" target="_blank" class="mr-3 text-stone-500">Ön izleme</a><a href="{{ route('admin.blog-posts.edit',$p) }}" class="text-[#c4a35a]">Düzenle</a></td></tr>
@empty<tr><td colspan="4" class="px-4 py-12 text-center text-stone-500">Yazı yok.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $posts->links() }}</div>
@endsection