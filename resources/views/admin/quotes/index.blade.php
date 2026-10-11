@extends('admin.layouts.app')
@section('title', 'Teklif talepleri')
@section('heading', 'Teklif talepleri')
@section('subheading', 'Angebot ve Kontakt formundan gelen talepler')
@section('content')
<form method="GET" class="mb-6 flex flex-wrap gap-2">
    <input type="search" name="q" value="{{ request('q') }}" placeholder="Ara…" class="admin-input w-56">
    <select name="status" class="admin-select w-48">
        <option value="">Tüm durumlar</option>
        @foreach (['neu', 'kontaktiert', 'angebot_erstellt', 'verhandlung', 'gewonnen', 'verloren'] as $s)
            <option value="{{ $s }}" @selected(request('status') === $s)>{{ admin_label($s) }}</option>
        @endforeach
    </select>
    <button class="admin-btn-ghost admin-btn">Filtrele</button>
</form>

<div class="admin-card overflow-hidden">
    <table class="min-w-full text-sm">
        <thead class="bg-[#f7f1e8] text-left text-[11px] uppercase tracking-wide text-[var(--admin-muted)]">
            <tr>
                <th class="px-4 py-3">No</th>
                <th class="px-4 py-3">Ad</th>
                <th class="px-4 py-3">Etkinlik</th>
                <th class="px-4 py-3">Durum</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[var(--admin-line)]">
            @forelse ($quotes as $quote)
                <tr class="hover:bg-[#faf6f0]">
                    <td class="px-4 py-3 font-medium">{{ $quote->quote_number }}</td>
                    <td class="px-4 py-3">{{ $quote->first_name }} {{ $quote->last_name }}</td>
                    <td class="px-4 py-3">{{ optional($quote->event_date)->format('d.m.Y') }}</td>
                    <td class="px-4 py-3">
                        <span class="admin-chip bg-stone-100 text-stone-700">{{ admin_label($quote->status) }}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.quotes.show', $quote) }}" class="text-[var(--admin-terracotta)]">Aç</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-12 text-center text-[var(--admin-muted)]">Talep yok.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $quotes->links() }}</div>
@endsection
