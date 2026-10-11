@extends('admin.layouts.app')
@section('title','Blog kategorileri')
@section('heading','Blog kategorileri')
@section('content')
<div class="flex justify-end mb-6"><a href="{{ route('admin.blog-categories.create') }}" class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Yeni</a></div>
<div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm"><table class="min-w-full text-sm">
<thead class="bg-stone-50 text-xs uppercase text-stone-500 text-left"><tr><th class="px-4 py-3">Ad</th><th class="px-4 py-3">Yazılar</th><th class="px-4 py-3"></th></tr></thead>
<tbody class="divide-y divide-stone-100">@forelse($categories as $c)
<tr><td class="px-4 py-3">{{ $c->name }}</td><td class="px-4 py-3">{{ $c->posts_count }}</td><td class="px-4 py-3 text-right"><a href="{{ route('admin.blog-categories.edit',$c) }}" class="text-[#c4a35a]">Düzenle</a></td></tr>
@empty<tr><td colspan="3" class="px-4 py-12 text-center text-stone-500">Kategori yok.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $categories->links() }}</div>
@endsection