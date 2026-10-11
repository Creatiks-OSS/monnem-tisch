@extends('admin.layouts.app')
@section('title','E-posta şablonları')
@section('heading','E-posta şablonları')
@section('content')
<div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm"><table class="min-w-full text-sm">
<thead class="bg-stone-50 text-xs uppercase text-stone-500 text-left"><tr><th class="px-4 py-3">Ad</th><th class="px-4 py-3">Key</th><th class="px-4 py-3">Aktif</th><th class="px-4 py-3"></th></tr></thead>
<tbody class="divide-y divide-stone-100">@forelse($templates as $t)
<tr><td class="px-4 py-3">{{ $t->name }}</td><td class="px-4 py-3 font-mono text-xs">{{ $t->key }}</td><td class="px-4 py-3">{{ $t->is_active?'Evet':'Hayır' }}</td>
<td class="px-4 py-3 text-right"><a href="{{ route('admin.email-templates.edit',$t) }}" class="text-[#c4a35a]">Düzenle</a></td></tr>
@empty<tr><td colspan="4" class="px-4 py-12 text-center text-stone-500">Şablon yok.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $templates->links() }}</div>
@endsection