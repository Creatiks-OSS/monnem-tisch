@extends('admin.layouts.app')
@section('title','FAQ')
@section('heading','FAQ')
@section('content')
<div class="flex justify-end mb-6"><a href="{{ route('admin.faqs.create') }}" class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Yeni</a></div>
<div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm"><table class="min-w-full text-sm">
<thead class="bg-stone-50 text-xs uppercase text-stone-500 text-left"><tr><th class="px-4 py-3">Frage</th><th class="px-4 py-3">Kategori</th><th class="px-4 py-3">Aktif</th><th class="px-4 py-3"></th></tr></thead>
<tbody class="divide-y divide-stone-100">@forelse($faqs as $f)
<tr><td class="px-4 py-3">{{ Str::limit($f->question,60) }}</td><td class="px-4 py-3">{{ $f->category }}</td><td class="px-4 py-3">{{ $f->is_active?'Evet':'Hayır' }}</td>
<td class="px-4 py-3 text-right"><a href="{{ route('admin.faqs.edit',$f) }}" class="text-[#c4a35a]">Düzenle</a></td></tr>
@empty<tr><td colspan="4" class="px-4 py-12 text-center text-stone-500">SSS yok.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $faqs->links() }}</div>
@endsection