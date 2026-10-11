@extends('admin.layouts.app')
@section('title','İletişim mesajları')
@section('heading','İletişim mesajları')
@section('content')
<form method="GET" class="mb-6"><input type="search" name="q" value="{{ request('q') }}" placeholder="Ara…" class="rounded-xl border border-stone-200 px-3 py-2 text-sm w-64"></form>
<div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm"><table class="min-w-full text-sm">
<thead class="bg-stone-50 text-xs uppercase text-stone-500 text-left"><tr><th class="px-4 py-3">Ad</th><th class="px-4 py-3">Betreff</th><th class="px-4 py-3">Tarih</th><th class="px-4 py-3">Durum</th><th class="px-4 py-3">Teklif</th><th class="px-4 py-3"></th></tr></thead>
<tbody class="divide-y divide-stone-100">@forelse($messages as $m)
<tr class="{{ $m->is_read ? '' : 'bg-amber-50/40' }}">
    <td class="px-4 py-3">{{ $m->name }}</td>
    <td class="px-4 py-3">{{ $m->subject }}</td>
    <td class="px-4 py-3">{{ $m->created_at->format('d.m.Y H:i') }}</td>
    <td class="px-4 py-3">{{ $m->is_read ? 'Gelesen' : 'Neu' }}</td>
    <td class="px-4 py-3">
        @if ($m->quote)
            <a href="{{ route('admin.quotes.show', $m->quote) }}" class="text-[#c4a35a]">{{ $m->quote->quote_number }}</a>
        @else
            —
        @endif
    </td>
    <td class="px-4 py-3 text-right"><a href="{{ route('admin.contact-messages.show',$m) }}" class="text-[#c4a35a]">Aç</a></td>
</tr>
@empty<tr><td colspan="6" class="px-4 py-12 text-center text-stone-500">Mesaj yok.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $messages->links() }}</div>
@endsection
