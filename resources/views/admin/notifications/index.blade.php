@extends('admin.layouts.app')
@section('title','Bildirimler')
@section('heading','Bildirimler')
@section('content')
<div class="mb-6 flex flex-wrap gap-2">
    <form method="POST" action="{{ route('admin.notifications.read-all') }}">
        @csrf
        <button class="px-4 py-2 rounded-lg border text-sm">Tümünü okundu işaretle</button>
    </form>
    @if ($notifications->total() > 0)
        <form method="POST" action="{{ route('admin.notifications.destroy-all') }}" onsubmit="return confirm('Tüm bildirimler silinsin mi?')">
            @csrf
            @method('DELETE')
            <button class="px-4 py-2 rounded-lg border border-red-200 text-red-700 text-sm">Tümünü sil</button>
        </form>
    @endif
</div>
<div class="space-y-2">
@forelse($notifications as $n)
<div class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm flex items-start justify-between gap-4 {{ $n->read_at ? 'opacity-70' : 'border-[var(--admin-gold)]/40' }}">
    <a href="{{ route('admin.notifications.open', $n) }}" class="min-w-0 flex-1">
        <span class="inline-flex rounded-full bg-stone-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-stone-500">{{ $n->typeLabel() }}</span>
        <p class="font-medium text-sm mt-1">{{ $n->title }}</p>
        <p class="text-sm text-stone-500 mt-1">{{ $n->body }}</p>
        <p class="text-[11px] text-stone-400 mt-2">{{ $n->created_at->format('d.m.Y H:i') }}</p>
    </a>
    <div class="flex shrink-0 items-center gap-3">
        @if(!$n->read_at)
            <span class="h-2.5 w-2.5 rounded-full bg-[var(--admin-terracotta)]"></span>
        @endif
        <form method="POST" action="{{ route('admin.notifications.destroy', $n) }}" onsubmit="return confirm('Bu bildirim silinsin mi?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm text-red-600 hover:text-red-700">Sil</button>
        </form>
    </div>
</div>
@empty
<div class="rounded-2xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center text-stone-500">Bildirim yok.</div>
@endforelse
</div>
<div class="mt-4">{{ $notifications->links() }}</div>
@endsection
