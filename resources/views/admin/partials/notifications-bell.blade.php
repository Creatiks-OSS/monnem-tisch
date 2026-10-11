@php
    $adminUnreadCount = $adminUnreadCount ?? 0;
    $adminNotifications = $adminNotifications ?? collect();
    $adminNotifyItems = $adminNotifications->map(fn ($n) => [
        'id' => $n->id,
        'title' => $n->title,
        'body' => $n->body,
        'label' => $n->typeLabel(),
        'url' => route('admin.notifications.open', $n),
        'read' => $n->read_at !== null,
        'time' => $n->created_at?->diffForHumans(),
    ])->values();
@endphp
<script type="application/json" id="admin-notify-data">@json($adminNotifyItems)</script>
<div class="relative shrink-0" x-data="{
    open: false,
    unread: {{ (int) $adminUnreadCount }},
    items: JSON.parse(document.getElementById('admin-notify-data').textContent || '[]'),
    async refresh() {
        try {
            const res = await fetch('{{ route('admin.notifications.feed') }}', { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            this.unread = data.unread || 0;
            this.items = data.items || [];
        } catch (e) {}
    },
    init() {
        setInterval(() => this.refresh(), 25000);
    }
}" @keydown.escape.window="open = false">
    <button type="button" class="relative p-2.5 rounded-xl hover:bg-stone-100 text-stone-600" @click="open = !open" title="Bildirimler" aria-label="Bildirimler">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        <span x-show="unread > 0" x-cloak class="absolute right-1.5 top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-[var(--admin-terracotta)] px-1 text-[10px] font-bold text-white" x-text="unread > 9 ? '9+' : unread"></span>
    </button>
    <div x-show="open" x-cloak x-transition.opacity.duration.150ms
         @click.outside="open = false"
         class="admin-notify-panel fixed left-3 right-3 top-[4.5rem] z-50 overflow-hidden rounded-2xl border border-[var(--admin-line)] bg-white shadow-lg lg:absolute lg:left-auto lg:right-0 lg:top-full lg:mt-2">
        <div class="flex items-center justify-between gap-2 border-b border-stone-100 px-3 py-3 sm:px-4">
            <p class="text-sm font-semibold">Bildirimler</p>
            <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                @csrf
                <button type="submit" class="shrink-0 text-[11px] text-[var(--admin-muted)] hover:text-[var(--admin-terracotta)]" x-show="unread > 0">Tümünü okundu işaretle</button>
            </form>
        </div>
        <div class="max-h-80 overflow-y-auto overscroll-contain sm:max-h-96">
            <template x-for="item in items" :key="item.id">
                <a :href="item.url" class="block border-b border-stone-50 px-3 py-3 hover:bg-stone-50 sm:px-4" :class="item.read ? 'opacity-60' : ''">
                    <div class="flex items-center gap-2">
                        <span class="rounded-full bg-stone-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-stone-500" x-text="item.label"></span>
                        <span class="ml-auto shrink-0 text-[10px] text-stone-400" x-text="item.time"></span>
                    </div>
                    <p class="mt-1 break-words text-sm font-medium text-stone-800" x-text="item.title"></p>
                    <p class="mt-0.5 line-clamp-2 break-words text-xs text-stone-500" x-text="item.body"></p>
                </a>
            </template>
            <p x-show="!items.length" class="px-4 py-8 text-center text-sm text-stone-400">Yeni bildirim yok.</p>
        </div>
        <a href="{{ route('admin.notifications.index') }}" class="block border-t border-stone-100 px-4 py-2.5 text-center text-xs font-medium text-[var(--admin-terracotta)] hover:bg-stone-50">Tümünü gör</a>
    </div>
</div>
