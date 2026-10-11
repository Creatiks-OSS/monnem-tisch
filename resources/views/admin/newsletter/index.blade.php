@extends('admin.layouts.app')
@section('title','Bülten')
@section('heading','Bülten')
@section('content')
<div class="flex justify-between mb-6"><form method="GET"><input type="search" name="q" value="{{ request('q') }}" class="rounded-xl border border-stone-200 px-3 py-2 text-sm" placeholder="Ara…"></form>
<a href="{{ route('admin.newsletter.export') }}" class="px-4 py-2 rounded-lg border text-sm">CSV Export</a></div>
<div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm"><table class="min-w-full text-sm">
<thead class="bg-stone-50 text-xs uppercase text-stone-500 text-left"><tr><th class="px-4 py-3">E-posta</th><th class="px-4 py-3">Ad</th><th class="px-4 py-3">Aktif</th><th class="px-4 py-3"></th></tr></thead>
<tbody class="divide-y divide-stone-100">@forelse($subscribers as $s)
<tr><td class="px-4 py-3">{{ $s->email }}</td><td class="px-4 py-3">{{ $s->name }}</td><td class="px-4 py-3">{{ $s->is_active?'Evet':'Hayır' }}</td>
<td class="px-4 py-3 text-right"><form method="POST" action="{{ route('admin.newsletter.destroy',$s) }}" onsubmit="return confirm('Silinsin mi?')">@csrf @method('DELETE')<button class="text-red-600">Sil</button></form></td></tr>
@empty<tr><td colspan="4" class="px-4 py-12 text-center text-stone-500">Abone yok.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $subscribers->links() }}</div>
@endsection