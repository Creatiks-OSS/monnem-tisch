@extends('admin.layouts.app')
@section('title', 'Seçim grupları')
@section('heading', 'Seçim grupları')
@section('subheading', 'Paket kotalarının yemek havuzu. Örn. Vorspeisen, Fingerfood, Grillfleisch.')
@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <form method="GET">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Grup ara…" class="admin-input w-56">
    </form>
    <a href="{{ route('admin.categories.create') }}" class="admin-btn admin-btn-gold">Yeni grup</a>
</div>

<div class="admin-card overflow-hidden">
    <table class="min-w-full text-sm">
        <thead class="bg-[#f7f1e8] text-left text-[11px] uppercase tracking-wide text-[var(--admin-muted)]">
            <tr>
                <th class="px-4 py-3">Grup</th>
                <th class="px-4 py-3">Menü / üst grup</th>
                <th class="px-4 py-3">Yemek</th>
                <th class="px-4 py-3">Durum</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[var(--admin-line)]">
        @forelse($categories as $c)
            <tr class="hover:bg-[#faf6f0]">
                <td class="px-4 py-3 font-medium">{{ $c->name }}</td>
                <td class="px-4 py-3 text-[var(--admin-muted)]">{{ $c->parent?->name ?? 'Ana menü' }}</td>
                <td class="px-4 py-3">{{ $c->products_count }}</td>
                <td class="px-4 py-3">
                    <span class="admin-chip {{ $c->is_active ? 'bg-[#e4efe7] text-[var(--admin-sage)]' : 'bg-stone-100 text-stone-500' }}">
                        {{ $c->is_active ? 'Aktif' : 'Pasif' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.categories.edit', $c) }}" class="text-[var(--admin-terracotta)]">Düzenle</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-12 text-center text-[var(--admin-muted)]">Grup yok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $categories->links() }}</div>
@endsection
