@extends('admin.layouts.app')
@section('title', 'Ekstra hizmetler')
@section('heading', 'Ekstra hizmetler')
@section('subheading', 'Personel, sofra takımı, içecekler — pakete eklenen hizmet ve ürünler.')
@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <form method="GET">
        <input type="search" name="q" value="{{ request('q') }}" class="admin-input w-56" placeholder="Ara…">
    </form>
    <a href="{{ route('admin.extras.create') }}" class="admin-btn admin-btn-gold">Yeni hizmet</a>
</div>
<div class="admin-card overflow-hidden">
    <table class="min-w-full text-sm">
        <thead class="bg-[#f7f1e8] text-left text-[11px] uppercase tracking-wide text-[var(--admin-muted)]">
            <tr>
                <th class="px-4 py-3">Ad</th>
                <th class="px-4 py-3">Fiyat</th>
                <th class="px-4 py-3">MwSt</th>
                <th class="px-4 py-3">Durum</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[var(--admin-line)]">
        @forelse($extras as $e)
            <tr class="hover:bg-[#faf6f0]">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <img
                            src="{{ $e->displayImage() }}"
                            alt=""
                            class="h-14 w-14 shrink-0 rounded-xl bg-[#f3ece3] object-cover"
                            referrerpolicy="no-referrer"
                        >
                        <div class="min-w-0">
                            <p class="font-medium leading-snug">{{ $e->name }}</p>
                            <p class="mt-0.5 text-xs text-[var(--admin-muted)]">{{ ($e->kind ?? 'service') === 'product' ? 'Ürün' : 'Hizmet' }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 tabular-nums">{{ $e->formattedPrice() }}</td>
                <td class="px-4 py-3 text-[var(--admin-muted)]">{{ $e->usesStandardTax() ? '19%' : '7%' }}</td>
                <td class="px-4 py-3">
                    <span class="admin-chip {{ $e->is_active ? 'bg-[#e4efe7] text-[var(--admin-sage)]' : 'bg-stone-100 text-stone-500' }}">{{ $e->is_active ? 'Aktif' : 'Pasif' }}</span>
                </td>
                <td class="px-4 py-3 text-right whitespace-nowrap">
                    <a href="{{ route('admin.extras.edit', $e) }}" class="text-[var(--admin-terracotta)]">Düzenle</a>
                    <form method="POST" action="{{ route('admin.extras.destroy', $e) }}" class="mt-1 inline-block sm:mt-0 sm:ml-3" onsubmit="return confirm({{ json_encode($e->name.' silinsin mi?') }})">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-700 hover:text-red-800">Sil</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-12 text-center text-[var(--admin-muted)]">Ekstra hizmet yok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $extras->links() }}</div>
@endsection
