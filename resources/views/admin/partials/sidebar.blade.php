@php
    $kitchenOnly = auth()->user()->hasRole('Kitchen') && !auth()->user()->hasAnyRole(['Super Admin','Admin','Manager','Editor','Sales']);
    $groups = [
        [
            'title' => 'Katalog',
            'items' => [
                ['label' => 'Menü paketleri', 'route' => 'admin.packages.index', 'match' => 'admin.packages.*', 'kitchen' => false, 'icon' => 'grid'],
                ['label' => 'Kategori metinleri', 'route' => 'admin.menu-pages.edit', 'match' => 'admin.menu-pages.*', 'kitchen' => false, 'icon' => 'page'],
                ['label' => 'Yemekler', 'route' => 'admin.products.index', 'match' => 'admin.products.*', 'kitchen' => false, 'icon' => 'dish'],
                ['label' => 'Seçim grupları', 'route' => 'admin.categories.index', 'match' => 'admin.categories.*', 'kitchen' => false, 'icon' => 'layers'],
                ['label' => 'Ekstra hizmetler', 'route' => 'admin.extras.index', 'match' => 'admin.extras.*', 'kitchen' => false, 'icon' => 'plus'],
            ],
        ],
        [
            'title' => 'Satış',
            'items' => [
                ['label' => 'Siparişler', 'route' => 'admin.orders.index', 'match' => 'admin.orders.*', 'kitchen' => true, 'icon' => 'bag'],
                ['label' => 'Sepeti bırakanlar', 'route' => 'admin.abandoned-carts.index', 'match' => 'admin.abandoned-carts.*', 'kitchen' => false, 'icon' => 'tag'],
                ['label' => 'Teklif talepleri', 'route' => 'admin.quotes.index', 'match' => 'admin.quotes.*', 'kitchen' => false, 'icon' => 'file'],
                ['label' => 'Müşteriler', 'route' => 'admin.customers.index', 'match' => 'admin.customers.*', 'kitchen' => false, 'icon' => 'users'],
                ['label' => 'Faturalar', 'route' => 'admin.invoices.index', 'match' => 'admin.invoices.*', 'kitchen' => false, 'icon' => 'receipt'],
                ['label' => 'Ödeme sistemleri', 'route' => 'admin.payments.index', 'match' => 'admin.payments.*', 'kitchen' => false, 'icon' => 'card'],
                ['label' => 'Kuponlar', 'route' => 'admin.coupons.index', 'match' => 'admin.coupons.*', 'kitchen' => false, 'icon' => 'tag'],
            ],
        ],
        [
            'title' => 'İçerik',
            'items' => [
                ['label' => 'Anasayfa', 'route' => 'admin.homepage.edit', 'match' => 'admin.homepage.*', 'kitchen' => false, 'icon' => 'home'],
                ['label' => 'Galeri', 'route' => 'admin.galleries.index', 'match' => 'admin.galleries.*', 'kitchen' => false, 'icon' => 'image'],
                ['label' => 'Blog', 'route' => 'admin.blog-posts.index', 'match' => 'admin.blog-*', 'kitchen' => false, 'icon' => 'pen'],
                ['label' => 'Sayfalar', 'route' => 'admin.pages.index', 'match' => 'admin.pages.*', 'kitchen' => false, 'icon' => 'page'],
                ['label' => 'Yorumlar', 'route' => 'admin.reviews.index', 'match' => 'admin.reviews.*', 'kitchen' => false, 'icon' => 'star'],
                ['label' => 'SSS', 'route' => 'admin.faqs.index', 'match' => 'admin.faqs.*', 'kitchen' => false, 'icon' => 'help'],
            ],
        ],
        [
            'title' => 'Operasyon',
            'items' => [
                ['label' => 'Teslimat bölgeleri', 'route' => 'admin.delivery-zones.index', 'match' => 'admin.delivery-zones.*', 'kitchen' => false, 'icon' => 'map'],
                ['label' => 'Şehirler / SEO', 'route' => 'admin.cities.index', 'match' => 'admin.cities.*', 'kitchen' => false, 'icon' => 'pin'],
                ['label' => 'Takvim / dolu günler', 'route' => 'admin.blocked-dates.index', 'match' => 'admin.blocked-dates.*', 'kitchen' => false, 'icon' => 'cal'],
                ['label' => 'Mutfak paneli', 'href' => url('/mutfak'), 'external' => true, 'match' => 'kitchen.*', 'kitchen' => true, 'icon' => 'dish'],
                ['label' => 'Çalışma saatleri', 'route' => 'admin.business-hours.index', 'match' => 'admin.business-hours.*', 'kitchen' => false, 'icon' => 'clock'],
                ['label' => 'Etkinlik türleri', 'route' => 'admin.events.index', 'match' => 'admin.events.*', 'kitchen' => false, 'icon' => 'flag'],
            ],
        ],
        [
            'title' => 'Sistem',
            'items' => [
                ['label' => 'Ayarlar', 'route' => 'admin.settings.group', 'match' => 'admin.settings.*', 'kitchen' => false, 'icon' => 'cog'],
                ['label' => 'Analitik', 'route' => 'admin.analytics.edit', 'match' => 'admin.analytics.*', 'kitchen' => false, 'icon' => 'search'],
                ['label' => 'SEO', 'route' => 'admin.seo.index', 'match' => 'admin.seo.*', 'kitchen' => false, 'icon' => 'search'],
                ['label' => 'Medya', 'route' => 'admin.media.index', 'match' => 'admin.media.*', 'kitchen' => false, 'icon' => 'folder'],
                ['label' => 'Kullanıcılar', 'route' => 'admin.users.index', 'match' => 'admin.users.*', 'kitchen' => false, 'icon' => 'user'],
                ['label' => 'İletişim mesajları', 'route' => 'admin.contact-messages.index', 'match' => 'admin.contact-messages.*', 'kitchen' => false, 'icon' => 'mail'],
                ['label' => 'Bülten', 'route' => 'admin.newsletter.index', 'match' => 'admin.newsletter.*', 'kitchen' => false, 'icon' => 'send'],
                ['label' => 'E-posta şablonları', 'route' => 'admin.email-templates.index', 'match' => 'admin.email-templates.*', 'kitchen' => false, 'icon' => 'mail'],
                ['label' => 'Bildirimler', 'route' => 'admin.notifications.index', 'match' => 'admin.notifications.*', 'kitchen' => true, 'icon' => 'bell'],
                ['label' => 'İşlem kaydı', 'route' => 'admin.activity-logs.index', 'match' => 'admin.activity-logs.*', 'kitchen' => false, 'icon' => 'list'],
            ],
        ],
    ];
    $icons = [
        'home' => 'M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9.5Z',
        'grid' => 'M4 4h7v7H4V4Zm9 0h7v7h-7V4ZM4 13h7v7H4v-7Zm9 0h7v7h-7v-7Z',
        'dish' => 'M4 18h16M5 14c0-4 3-7 7-7s7 3 7 7M8 7V5m8 2V5',
        'layers' => 'M12 3 3 8l9 5 9-5-9-5Zm0 18 9-5-9-5-9 5 9 5Z',
        'plus' => 'M12 5v14M5 12h14',
        'bag' => 'M6 8h12l1 12H5L6 8Zm3 0V6a3 3 0 0 1 6 0v2',
        'file' => 'M7 3h7l5 5v13H7V3Zm7 0v5h5',
        'users' => 'M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M9.5 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm12.5 10v-2a4 4 0 0 0-3-3.87M16.5 3.13a4 4 0 0 1 0 7.75',
        'receipt' => 'M6 3h12v18l-2-1-2 1-2-1-2 1-2-1-2 1V3Zm4 6h4M10 11h4M10 15h2',
        'card' => 'M3 7h18v10H3V7Zm0 3h18M7 15h4',
        'tag' => 'M20 13 11 4H4v7l9 9 7-7ZM7.5 8.5h.01',
        'image' => 'M4 5h16v14H4V5Zm3 4a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Zm13 7-5-5-9 9',
        'pen' => 'M4 20h4L19 9l-4-4L4 16v4Zm11-15 4 4',
        'page' => 'M7 3h7l5 5v13H7V3Z',
        'star' => 'M12 3 14.5 9H21l-5 4 2 7-6-4-6 4 2-7-5-4h6.5L12 3Z',
        'help' => 'M12 17h.01M9.1 9a3 3 0 1 1 4.2 2.7c-.8.4-1.3 1-1.3 1.8V14M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z',
        'map' => 'M9 4 3 7v13l6-3 6 3 6-3V4l-6 3-6-3Zm0 0v13m6-10v13',
        'pin' => 'M12 21s7-6 7-11a7 7 0 1 0-14 0c0 5 7 11 7 11Zm0-8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        'cal' => 'M5 5h14v14H5V5Zm0 5h14M9 3v4m6-4v4',
        'clock' => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0-9 3 2M12 7v5',
        'flag' => 'M5 21V4h9l-1 4h6l-2 5H5',
        'cog' => 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm7.4-3a7.5 7.5 0 0 0-.1-1l2-1.5-2-3.5-2.4 1a7.6 7.6 0 0 0-1.7-1L12.8 2h-3.6L8.8 4.5a7.6 7.6 0 0 0-1.7 1l-2.4-1-2 3.5 2 1.5a7.5 7.5 0 0 0-.1 1l-2 1.5 2 3.5 2.4-1a7.6 7.6 0 0 0 1.7 1L9.2 22h3.6l.4-2.5a7.6 7.6 0 0 0 1.7-1l2.4 1 2-3.5-2-1.5c.1-.3.1-.7.1-1Z',
        'search' => 'M11 19a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm10 2-4.3-4.3',
        'folder' => 'M3 7h6l2 2h10v10H3V7Z',
        'user' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7 9v-1a5 5 0 0 0-10 0v1',
        'mail' => 'M4 6h16v12H4V6Zm0 0 8 7 8-7',
        'send' => 'M22 2 11 13M22 2l-7 20-4-9-9-4 20-7Z',
        'bell' => 'M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9Zm6 13a2 2 0 0 0 2-2H10a2 2 0 0 0 2 2Z',
        'list' => 'M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01',
    ];
@endphp

<div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 bg-black/50 lg:hidden" @click="sidebarOpen = false"></div>

<aside
    class="fixed lg:sticky top-0 left-0 z-50 h-screen w-[17.5rem] shrink-0 bg-[var(--admin-sidebar)] text-stone-300 flex flex-col transition-transform duration-200 lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
>
    <div class="px-5 py-5 border-b border-white/10">
        <a href="{{ $kitchenOnly ? route('admin.orders.index') : route('admin.dashboard') }}" class="block">
            @if(site_logo_url())
                <img src="{{ site_logo_url() }}" alt="Monnemtisch Catering" class="h-11 w-auto max-w-[210px] rounded-lg bg-white px-2 py-1.5 object-contain object-left">
            @else
                <span class="admin-brand text-[1.65rem] leading-none text-[var(--admin-gold)]">Monnemtisch</span>
            @endif
            <span class="mt-1.5 block text-[10px] uppercase tracking-[0.28em] text-white/45">Yönetim</span>
        </a>
    </div>
    <nav class="admin-scroll flex-1 overflow-y-auto py-4 px-3">
        @unless($kitchenOnly)
            @php $dashActive = request()->routeIs('admin.dashboard'); @endphp
            <a href="{{ route('admin.dashboard') }}"
               class="mb-3 flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm transition {{ $dashActive ? 'bg-[var(--admin-gold-soft)] text-[var(--admin-gold)]' : 'hover:bg-white/5 hover:text-white' }}">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="{{ $icons['home'] }}"/></svg>
                Özet
            </a>
        @endunless

        @foreach($groups as $group)
            @php
                $visible = collect($group['items'])->filter(fn ($item) => ! $kitchenOnly || ! empty($item['kitchen']));
            @endphp
            @continue($visible->isEmpty())
            <p class="mt-4 mb-1.5 px-3 text-[10px] font-semibold uppercase tracking-[0.2em] text-white/35">{{ $group['title'] }}</p>
            <div class="space-y-0.5">
                @foreach($visible as $item)
                    @php
                        $active = ! empty($item['match']) && request()->routeIs($item['match']);
                        $href = $item['href'] ?? route($item['route'], ($item['route'] ?? '') === 'admin.settings.group' ? ['group' => 'general'] : []);
                    @endphp
                    <a href="{{ $href }}"
                       @if(! empty($item['external'])) target="_blank" rel="noopener" @endif
                       class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-[13px] transition {{ $active ? 'bg-[var(--admin-gold-soft)] text-[var(--admin-gold)]' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                        <svg class="h-4 w-4 shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="{{ $icons[$item['icon']] ?? $icons['grid'] }}"/></svg>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>
    <div class="p-4 border-t border-white/10">
        <p class="text-[11px] text-white/35">Menüler · paketler · yemekler</p>
        <p class="mt-0.5 text-[11px] text-[var(--admin-gold)]">Monnemtisch Catering</p>
    </div>
</aside>
