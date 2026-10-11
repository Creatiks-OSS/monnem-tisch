@props(['title' => 'Kayıt yok', 'text' => 'Henüz veri eklenmedi.', 'action' => null, 'actionLabel' => 'Yeni ekle'])
<div class="rounded-2xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
    <h3 class="text-lg font-semibold">{{ $title }}</h3>
    <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ $text }}</p>
    @if($action)
        <a href="{{ $action }}" class="inline-flex mt-5 px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm hover:bg-black transition">{{ $actionLabel }}</a>
    @endif
</div>
