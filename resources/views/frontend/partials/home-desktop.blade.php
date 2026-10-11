@php
    $reviewAvg = number_format((float) ($reviewStats['avg'] ?? 4.9), 1, ',', '');
    $reviewCount = (int) ($reviewStats['count'] ?? 0);
    $desktopPackages = ($packages ?? collect())->take(3);
    $occasions = [
        [
            'title' => 'Buffets',
            'text' => 'Buffet Catering ab 10 Personen – Festpreis, frei kombinierbar.',
            'url' => route('packages.index', 'buffets'),
            'image' => velora_photo('buffet'),
            'icon' => 'buffet',
        ],
        [
            'title' => 'Fingerfood',
            'text' => 'Elegante Häppchen für Empfänge, Messen und Stehparties – frei kombinierbar.',
            'url' => route('packages.index', 'fingerfood'),
            'image' => velora_photo('fingerfood'),
            'icon' => 'canape',
        ],
        [
            'title' => 'Frühstück',
            'text' => 'Frühstück Catering fürs Büro – frische Pakete, pünktliche Lieferung, Festpreis.',
            'url' => route('packages.index', 'fruehstueck'),
            'image' => velora_photo('breakfast'),
            'icon' => 'breakfast',
        ],
        [
            'title' => 'Grillcatering',
            'text' => 'Grillcatering für Firmenfeste und Events – live am Grill, frei kombinierbar.',
            'url' => route('packages.index', 'grillcatering'),
            'image' => velora_photo('grill'),
            'icon' => 'grill',
        ],
    ];
@endphp

<style>
.home-desk{
    --forest:#132a3a;
    --forest-deep:#0e212d;
    --sand:#f7f3eb;
    --ink:#132a3a;
    --muted:#5e7568;
    --orange:#d89b2b;
    --orange-dark:#c08a22;
    color:#20262e;
    background:#fff;
    display:none;
}
@@media (min-width:768px){
    .home-desk{display:block}
}
.home-desk h1,.home-desk h2,.home-desk h3{
    font-family:'Playfair Display',Georgia,serif;
    color:var(--forest);
    letter-spacing:-.02em;
    font-weight:700;
}
.home-desk a{text-decoration:none}
.home-wrap{width:100%;max-width:1180px;margin:0 auto;padding:0 2rem}
.home-hero{padding:3.25rem 0 4.25rem;background:#fff}
.home-hero-grid{display:grid;align-items:center;gap:3.5rem}
@@media (min-width:1024px){
    .home-hero-grid{grid-template-columns:minmax(0,1fr) minmax(0,1.05fr);gap:4rem}
}
.home-hero h1{font-size:clamp(2.4rem,4.2vw,3.65rem);line-height:1.12;max-width:18ch}
.home-hero .lead{margin-top:1.1rem;font-size:1.12rem;line-height:1.55;color:var(--muted);max-width:36rem}
.home-hero-actions{display:flex;flex-wrap:wrap;gap:.85rem;margin-top:1.75rem}
.home-btn{
    display:inline-flex;align-items:center;justify-content:center;gap:.45rem;
    border-radius:.7rem;padding:.95rem 1.35rem;font-size:.95rem;font-weight:700;line-height:1;
    transition:background .2s ease,color .2s ease,border-color .2s ease,transform .2s ease;
}
.home-btn-primary{background:var(--orange);color:var(--ink);border:0;box-shadow:0 10px 22px rgba(216,155,43,.28)}
.home-btn-primary:hover{background:var(--orange-dark);color:var(--ink)}
.home-btn-ghost{background:#fff;color:var(--forest);border:1.5px solid #cfd6d1}
.home-btn-ghost:hover{border-color:var(--forest);color:var(--forest)}
.home-trust{display:flex;flex-wrap:wrap;gap:1.15rem 1.5rem;margin-top:2rem;padding-top:1.35rem;border-top:1px solid #eee8df}
.home-trust span{display:inline-flex;align-items:center;gap:.45rem;font-size:.78rem;font-weight:600;color:#4d5752}
.home-trust svg{width:1.05rem;height:1.05rem;flex-shrink:0}
.home-hero-photo{overflow:hidden;border-radius:1.35rem;box-shadow:0 22px 50px rgba(28,61,50,.16)}
.home-hero-photo img{display:block;width:100%;aspect-ratio:5/4;object-fit:cover}
.home-occasions{padding:1rem 0 4.5rem;background:#fff}
.home-occasions h2,.home-menus h2{text-align:center;font-size:clamp(1.85rem,2.6vw,2.45rem)}
.home-occ-grid{display:grid;gap:1.35rem;margin-top:2.35rem}
@@media (min-width:768px){.home-occ-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@@media (min-width:1100px){.home-occ-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
.home-occ{
    display:flex;flex-direction:column;background:#fff;border-radius:1.15rem;
    box-shadow:0 10px 28px rgba(28,61,50,.07);overflow:visible;
}
.home-occ-img{position:relative;margin:0 0 .15rem}
.home-occ-img img{display:block;width:100%;aspect-ratio:4/3.15;object-fit:cover;border-radius:1.05rem}
.home-occ-icon{
    position:absolute;left:.85rem;top:.85rem;z-index:1;
    display:flex;align-items:center;justify-content:center;
    width:2.35rem;height:2.35rem;border-radius:999px;background:var(--forest);color:#fff;
    box-shadow:0 6px 16px rgba(28,61,50,.25);
}
.home-occ-icon svg{width:1.05rem;height:1.05rem}
.home-occ-body{display:flex;flex-direction:column;flex:1;padding:1.05rem 1.05rem 1.2rem}
.home-occ-body h3{font-family:Inter,ui-sans-serif,system-ui,sans-serif;font-size:1.05rem;font-weight:700}
.home-occ-body p{margin-top:.45rem;font-size:.86rem;line-height:1.5;color:var(--muted)}
.home-occ-body .more{margin-top:auto;padding-top:.9rem;color:var(--forest)}
.home-occ-body .more svg{width:1.05rem;height:1.05rem;transition:transform .2s ease}
.home-occ:hover .more svg{transform:translateX(4px)}
.home-why{background:var(--sand);padding:4.25rem 0}
.home-why-grid{display:grid;align-items:center;gap:2.5rem}
@@media (min-width:960px){.home-why-grid{grid-template-columns:minmax(0,.95fr) minmax(0,1.05fr);gap:4rem}}
.home-why-photo{overflow:hidden;border-radius:1.25rem;box-shadow:0 18px 40px rgba(28,61,50,.12)}
.home-why-photo img{display:block;width:100%;aspect-ratio:4/3.2;object-fit:cover}
.home-why h2{font-size:clamp(1.9rem,2.8vw,2.55rem)}
.home-why .kicker{margin-top:.7rem;font-size:1.05rem;font-weight:700;color:var(--forest)}
.home-why .copy{margin-top:.85rem;font-size:.98rem;line-height:1.7;color:#5c665f;max-width:38rem}
.home-why-points{display:grid;gap:1.1rem;margin-top:1.75rem}
@@media (min-width:700px){.home-why-points{grid-template-columns:repeat(3,minmax(0,1fr));gap:1.25rem}}
.home-why-points article{display:flex;flex-direction:column;gap:.45rem}
.home-why-points span{
    display:flex;align-items:center;justify-content:center;
    width:2.35rem;height:2.35rem;border-radius:999px;background:#fff;color:var(--forest);
    box-shadow:0 4px 12px rgba(28,61,50,.08);
}
.home-why-points h3{font-family:Inter,ui-sans-serif,system-ui,sans-serif;font-size:.92rem;font-weight:700}
.home-why-points p{font-size:.8rem;line-height:1.45;color:var(--muted)}
.home-menus{padding:4.5rem 0;background:#fff}
.home-menu-grid{display:grid;gap:1.4rem;margin-top:2.35rem}
@@media (min-width:900px){.home-menu-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
.home-menu{
    display:flex;flex-direction:column;overflow:hidden;border-radius:1.15rem;background:#fff;
    box-shadow:0 12px 30px rgba(28,61,50,.08);color:inherit;
}
.home-menu:hover{transform:translateY(-3px);box-shadow:0 18px 36px rgba(28,61,50,.12);transition:transform .2s ease,box-shadow .2s ease}
.home-menu-img{position:relative}
.home-menu-img img{display:block;width:100%;aspect-ratio:16/10.5;object-fit:cover}
.home-menu-badge{
    position:absolute;right:.85rem;bottom:.85rem;
    border-radius:999px;background:var(--forest);color:#fff;
    padding:.38rem .7rem;font-size:.68rem;font-weight:700;
}
.home-menu-body{display:flex;flex-direction:column;flex:1;padding:1.2rem 1.25rem 1.3rem}
.home-menu-body h3{font-family:Inter,ui-sans-serif,system-ui,sans-serif;font-size:1.12rem;font-weight:700}
.home-menu-body p{margin-top:.45rem;font-size:.88rem;line-height:1.55;color:var(--muted)}
.home-menu-price{margin-top:auto;padding-top:1.1rem;font-size:.92rem;color:var(--orange);font-weight:600}
.home-menu-price strong{font-size:1.12rem}
.home-region{padding:4.25rem 0;background:#fff}
.home-region-grid{display:grid;align-items:center;gap:2.5rem}
@@media (min-width:960px){.home-region-grid{grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:3.5rem}}
.home-map{
    position:relative;overflow:hidden;border-radius:1.25rem;
    background:linear-gradient(180deg,#f4eee4 0%,#ebe3d6 100%);
    box-shadow:inset 0 0 0 1px rgba(28,61,50,.07);
}
.home-map svg{display:block;width:100%;height:auto}
.home-region h2{font-size:clamp(1.9rem,2.8vw,2.55rem)}
.home-region .kicker{margin-top:.65rem;font-size:1.05rem;font-weight:700}
.home-region .copy{margin-top:.85rem;font-size:.98rem;line-height:1.7;color:#5c665f;max-width:34rem}
.home-cities{display:flex;flex-wrap:wrap;gap:.85rem 1.35rem;margin-top:1.5rem}
.home-cities li{display:inline-flex;align-items:center;gap:.45rem;font-size:.9rem;font-weight:600}
.home-cities svg{width:1.05rem;height:1.05rem;color:var(--forest)}
.home-cta{display:grid;background:var(--forest);color:#fff}
@@media (min-width:900px){.home-cta{grid-template-columns:minmax(0,1.15fr) minmax(0,.85fr)}}
.home-cta-copy{
    padding:3.5rem 2rem;
}
@@media (min-width:1180px){
    .home-cta-copy{padding:4.25rem 3rem 4.25rem max(2rem, calc((100vw - 1180px) / 2 + 2rem))}
}
.home-cta-copy h2{color:#fff;font-size:clamp(1.85rem,2.8vw,2.55rem);max-width:16ch;line-height:1.18}
.home-cta-points{display:flex;flex-wrap:wrap;gap:1rem 1.5rem;margin-top:1.5rem}
.home-cta-points li{display:inline-flex;align-items:center;gap:.5rem;font-size:.88rem;font-weight:600;color:#e8efe9}
.home-cta-points svg{width:1.15rem;height:1.15rem;flex-shrink:0}
.home-cta-copy .home-btn{margin-top:1.75rem}
.home-cta-photo{min-height:16rem}
.home-cta-photo img{display:block;width:100%;height:100%;object-fit:cover}
</style>

<div class="home-desk">
    <section class="home-hero">
        <div class="home-wrap home-hero-grid">
            <div>
                <h1>Catering, das Mannheim verbindet.</h1>
                <p class="lead">Frisch zubereitet. Persönlich geplant. Pünktlich geliefert.</p>
                <div class="home-hero-actions">
                    <a href="{{ route('quote.create') }}" class="home-btn home-btn-primary">
                        Jetzt Angebot anfragen
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                    </a>
                    <a href="{{ route('packages.index', 'buffets') }}" class="home-btn home-btn-ghost">Menüs entdecken</a>
                </div>
                <div class="home-trust">
                    <span>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1Z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23Z"/><path fill="#FBBC05" d="M5.84 14.09A6.6 6.6 0 0 1 5.5 12c0-.72.13-1.43.34-2.09V7.07H2.18A11 11 0 0 0 1 12c0 1.77.42 3.45 1.18 4.93l3.66-2.84Z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53Z"/></svg>
                        {{ $reviewAvg }} ★ Google
                    </span>
                    <span>
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 14c3-7 7-10 7-10s1.8 1.4 3.4 4M5 14s2 6 7 6 7-6 7-6M12 4c2.4 3.8 4.2 7.6 4.4 10"/></svg>
                        Frische Zutaten
                    </span>
                    <span>
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7h11l5 5v6a1 1 0 0 1-1 1h-2a2 2 0 1 1-4 0H10a2 2 0 1 1-4 0H5a1 1 0 0 1-1-1V7Zm11 0v5h5"/></svg>
                        Pünktliche Lieferung
                    </span>
                    <span>
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3c.4 2.4 1.6 4.2 3.4 5.4C13.6 9.6 12.4 11.4 12 14c-.4-2.6-1.6-4.4-3.4-5.6C10.4 7.2 11.6 5.4 12 3Zm0 11c2.2 2.4 5 3.6 8 4-3 .8-5.4 2.4-8 5-2.6-2.6-5-4.2-8-5 3-.4 5.8-1.6 8-4Z"/></svg>
                        Vegetarisch &amp; vegan
                    </span>
                </div>
            </div>
            <div class="home-hero-photo">
                <img src="{{ velora_photo('hero') }}" alt="Festlich angerichtetes Catering-Buffet von Monnemtisch in Mannheim" width="1200" height="960" fetchpriority="high">
            </div>
        </div>
    </section>

    <section class="home-occasions" id="anlaesse">
        <div class="home-wrap">
            <h2>Catering für jeden Anlass</h2>
            <div class="home-occ-grid">
                @foreach ($occasions as $item)
                    <a href="{{ $item['url'] }}" class="home-occ">
                        <div class="home-occ-img">
                            <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" loading="lazy">
                            <span class="home-occ-icon" aria-hidden="true">
                                @if ($item['icon'] === 'buffet')
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 11h16M6 11c0-3.5 2.5-6 6-6s6 2.5 6 6M6 11v7h12v-7M9 18v2m6-2v2"/></svg>
                                @elseif ($item['icon'] === 'canape')
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 15c2.5-1 5-1.5 8-1.5s5.5.5 8 1.5M7 10c.8-2 2.6-3.5 5-3.5s4.2 1.5 5 3.5M12 6V3"/></svg>
                                @elseif ($item['icon'] === 'breakfast')
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 11h10a4 4 0 0 1 0 8H5V11Zm10 2h2.5A2.5 2.5 0 0 1 20 15.5 2.5 2.5 0 0 1 17.5 18H15M8 7V4m4 3V3m4 4V5"/></svg>
                                @else
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3s3 3.2 3 6.2A3 3 0 1 1 9 9.2C9 6.2 12 3 12 3Zm-5 12c1.5-2 3.4-3 5-3s3.5 1 5 3c.8 1.1.8 3-1 4.2-1.4.9-2.7.8-4 .8s-2.6.1-4-.8c-1.8-1.2-1.8-3.1-1-4.2Z"/></svg>
                                @endif
                            </span>
                        </div>
                        <div class="home-occ-body">
                            <h3>{{ $item['title'] }}</h3>
                            <p>{{ $item['text'] }}</p>
                            <span class="more inline-flex items-center">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="home-why">
        <div class="home-wrap home-why-grid">
            <div class="home-why-photo">
                <img src="{{ velora_photo('kitchen') }}" alt="Die Küche von Monnemtisch Catering – frisch zubereitete Speisen aus Mannheim" loading="lazy">
            </div>
            <div>
                <h2>Warum MonnemTisch?</h2>
                <p class="kicker">Regional. Flexibel. Mit Liebe gemacht.</p>
                <p class="copy">Wir kommen aus Mannheim und kochen für die Region: frische Zutaten, klare Festpreise und ein Team, das Ihr Event persönlich plant – vom kleinen Meeting bis zur großen Feier.</p>
                <div class="home-why-points">
                    <article>
                        <span aria-hidden="true">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11Zm0-8.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/></svg>
                        </span>
                        <h3>Aus der Region</h3>
                        <p>Zuhause in Mannheim, unterwegs im Rhein-Neckar-Kreis.</p>
                    </article>
                    <article>
                        <span aria-hidden="true">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21s-7-4.4-7-10a7 7 0 0 1 14 0c0 5.6-7 10-7 10Z"/></svg>
                        </span>
                        <h3>Persönlich für Sie da</h3>
                        <p>Beratung, Abstimmung und Lieferung aus einer Hand.</p>
                    </article>
                    <article>
                        <span aria-hidden="true">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 14c3-7 7-10 7-10s1.8 1.4 3.4 4M12 4c2.4 3.8 4.2 7.6 4.4 10"/></svg>
                        </span>
                        <h3>Nachhaltig gedacht</h3>
                        <p>Saisonale Küche, kurze Wege, wenig Verschwendung.</p>
                    </article>
                </div>
            </div>
        </div>
    </section>

    @if ($desktopPackages->isNotEmpty())
    <section class="home-menus" id="menues">
        <div class="home-wrap">
            <h2>Unsere beliebtesten Menüs</h2>
            <div class="home-menu-grid">
                @foreach ($desktopPackages as $package)
                    @php
                        $min = (int) ($package->min_persons ?: 10);
                        $bits = $package->slots->pluck('name')->filter()->take(4);
                        $line = $bits->isNotEmpty()
                            ? $bits->implode(', ')
                            : \Illuminate\Support\Str::limit(strip_tags((string) $package->description), 90);
                    @endphp
                    <a href="{{ $package->publicUrl() }}" class="home-menu">
                        <div class="home-menu-img">
                            <img src="{{ $package->imageUrl() }}" alt="{{ $package->name }}" loading="lazy">
                            <span class="home-menu-badge">Ab {{ $min }} Personen</span>
                        </div>
                        <div class="home-menu-body">
                            <h3>{{ $package->name }}</h3>
                            <p>{{ $line }}</p>
                            <p class="home-menu-price">ab <strong>{{ number_format((float) $package->price_per_person, 2, ',', '.') }} €</strong> pro Person</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <section class="home-region">
        <div class="home-wrap home-region-grid">
            <div class="home-map" role="img" aria-label="Liefergebiet im Rhein-Neckar-Raum: Mannheim, Ludwigshafen, Heidelberg und Umgebung">
                <svg viewBox="0 0 640 500" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="mapSky" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#f6f0e6"/>
                            <stop offset="100%" stop-color="#e8dfd0"/>
                        </linearGradient>
                        <linearGradient id="mapWater" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#8eaea4"/>
                            <stop offset="100%" stop-color="#6f9188"/>
                        </linearGradient>
                        <filter id="mapPinShadow" x="-50%" y="-20%" width="200%" height="180%">
                            <feDropShadow dx="0" dy="4" stdDeviation="3.5" flood-color="#132a3a" flood-opacity=".3"/>
                        </filter>
                    </defs>

                    <rect width="640" height="500" fill="url(#mapSky)"/>

                    <path fill="#e4d7c4" d="M36 58c78-36 168-22 238 18 42 24 58 62 48 108-12 58 18 96 6 148-14 58 4 104 28 148-48 28-118 22-176 2-72-24-154 4-198-42-28-30-22-84-8-124 16-46 4-92-10-132C-48 132 8 78 36 58Z"/>
                    <path fill="#d9ccb8" d="M300 48c86-24 176-4 248 46 52 36 86 92 80 148-8 68-36 114-22 172 18 52-8 86-48 104-62 28-132 8-196-18-58-24-128-4-168-48-28-32-14-86 6-124 28-52 12-104 24-158 10-44 38-86 76-122Z"/>
                    <ellipse cx="478" cy="198" rx="92" ry="48" fill="#cbbda8" opacity=".5"/>
                    <ellipse cx="524" cy="262" rx="74" ry="36" fill="#c4b49e" opacity=".42"/>
                    <ellipse cx="430" cy="292" rx="58" ry="26" fill="#cbbda8" opacity=".35"/>

                    <circle cx="278" cy="248" r="124" fill="#132a3a" opacity=".045"/>
                    <circle cx="278" cy="248" r="124" fill="none" stroke="#132a3a" stroke-width="1.5" stroke-dasharray="6 8" opacity=".32"/>
                    <circle cx="278" cy="248" r="82" fill="none" stroke="#132a3a" stroke-width="1" stroke-dasharray="3 7" opacity=".18"/>

                    <path d="M398 28c6 78-4 156-16 228 12 78 32 148 24 244" fill="none" stroke="#c2b29c" stroke-width="2.6" stroke-linecap="round" stroke-dasharray="9 9"/>
                    <path d="M28 268c90-10 168-20 250-12 96 10 178 30 262 22 48-4 78-16 100-8" fill="none" stroke="#c2b29c" stroke-width="2.6" stroke-linecap="round" stroke-dasharray="9 9"/>

                    <path d="M308 508c-10-72-14-128-20-176-6-52-28-86-62-112-26-20-44-42-38-70 6-30 28-48 46-70 14-18 20-44 16-74-4-34-6-68 0-90" fill="none" stroke="url(#mapWater)" stroke-width="26" stroke-linecap="round"/>
                    <path d="M308 508c-10-72-14-128-20-176-6-52-28-86-62-112-26-20-44-42-38-70 6-30 28-48 46-70 14-18 20-44 16-74-4-34-6-68 0-90" fill="none" stroke="#d7e6e0" stroke-width="9" stroke-linecap="round" opacity=".55"/>
                    <path d="M652 352c-70-10-128-18-186-26-52-8-100-12-140-28-36-14-72-38-108-52" fill="none" stroke="url(#mapWater)" stroke-width="16" stroke-linecap="round"/>
                    <path d="M652 352c-70-10-128-18-186-26-52-8-100-12-140-28-36-14-72-38-108-52" fill="none" stroke="#d7e6e0" stroke-width="6" stroke-linecap="round" opacity=".5"/>

                    <text transform="rotate(-82 250 128)" x="250" y="128" fill="#4f6861" font-size="12" font-family="Inter,sans-serif" font-weight="700" letter-spacing="2">RHEIN</text>
                    <text transform="rotate(-10 500 322)" x="470" y="326" fill="#4f6861" font-size="12" font-family="Inter,sans-serif" font-weight="700" letter-spacing="2">NECKAR</text>

                    <g font-family="Inter,sans-serif">
                        <circle cx="250" cy="88" r="5" fill="#5e6e66"/>
                        <rect x="262" y="76" width="56" height="22" rx="11" fill="#fff"/>
                        <text x="290" y="91" text-anchor="middle" fill="#3f4a44" font-size="12" font-weight="600">Worms</text>

                        <circle cx="412" cy="142" r="5" fill="#5e6e66"/>
                        <rect x="424" y="130" width="84" height="22" rx="11" fill="#fff"/>
                        <text x="466" y="145" text-anchor="middle" fill="#3f4a44" font-size="12" font-weight="600">Weinheim</text>

                        <circle cx="188" cy="250" r="5.5" fill="#5e6e66"/>
                        <rect x="34" y="238" width="142" height="24" rx="12" fill="#fff"/>
                        <text x="105" y="254" text-anchor="middle" fill="#3f4a44" font-size="13" font-weight="600">Ludwigshafen</text>

                        <circle cx="448" cy="332" r="5.5" fill="#5e6e66"/>
                        <rect x="460" y="320" width="108" height="24" rx="12" fill="#fff"/>
                        <text x="514" y="336" text-anchor="middle" fill="#3f4a44" font-size="13" font-weight="600">Heidelberg</text>

                        <circle cx="296" cy="412" r="5" fill="#5e6e66"/>
                        <rect x="308" y="400" width="62" height="22" rx="11" fill="#fff"/>
                        <text x="339" y="415" text-anchor="middle" fill="#3f4a44" font-size="12" font-weight="600">Speyer</text>
                    </g>

                    <g transform="translate(278 216)" filter="url(#mapPinShadow)">
                        <circle cy="32" r="7" fill="#d89b2b"/>
                        <path d="M0-44c-15.4 0-28 12.2-28 27.4C-28 6.4 0 32 0 32s28-25.6 28-48.6C28-31.8 15.4-44 0-44Z" fill="#132a3a"/>
                        <circle cy="-20" r="9" fill="#f7f3eb"/>
                        <circle cy="-20" r="4" fill="#d89b2b"/>
                    </g>
                    <rect x="224" y="268" width="108" height="28" rx="14" fill="#132a3a"/>
                    <text x="278" y="287" text-anchor="middle" fill="#fff" font-size="14" font-family="Playfair Display,Georgia,serif" font-weight="700">Mannheim</text>

                    <rect x="16" y="16" width="118" height="28" rx="14" fill="#fff"/>
                    <text x="75" y="35" text-anchor="middle" fill="#132a3a" font-size="11" font-family="Inter,sans-serif" font-weight="700" letter-spacing=".1em">LIEFERGEBIET</text>
                </svg>
            </div>
            <div>
                <h2>Mannheim &amp; Rhein-Neckar</h2>
                <p class="kicker">Zuhause in der Region, für Sie unterwegs.</p>
                <p class="copy">Wir liefern frisch zubereitetes Catering nach Mannheim, Ludwigshafen, Heidelberg und in viele Orte rund um den Rhein-Neckar-Kreis – pünktlich und gut erreichbar.</p>
                <ul class="home-cities">
                    @foreach (['Mannheim', 'Ludwigshafen', 'Heidelberg', 'und viele weitere Orte'] as $city)
                        <li>
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="m5 12 5 5L20 7"/></svg>
                            {{ $city }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    <section class="home-cta">
        <div class="home-cta-copy">
            <h2>In 2 Minuten zur unverbindlichen Anfrage</h2>
            <ul class="home-cta-points">
                <li>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7Z"/></svg>
                    Schnell &amp; unkompliziert
                </li>
                <li>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19a8 8 0 1 1 16 0M12 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/></svg>
                    Persönliche Beratung
                </li>
                <li>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 8h10M7 12h6m-8 8h14a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v14l4-3Z"/></svg>
                    Individuelles Angebot
                </li>
            </ul>
            <a href="{{ route('quote.create') }}" class="home-btn home-btn-primary">
                Anfrage starten
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-6-6 6 6-6 6"/></svg>
            </a>
        </div>
        <div class="home-cta-photo">
            <img src="{{ asset('images/dishes/garnelen-spiess.webp') }}" alt="Frisch zubereitete Garnelenspieße von Monnemtisch Catering" loading="lazy" decoding="async">
        </div>
    </section>
</div>
