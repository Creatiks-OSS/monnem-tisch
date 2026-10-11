@php
    $home = app(\App\Services\HomepageContent::class);
    $reviewAvg = number_format((float) ($reviewStats['avg'] ?? 4.9), 1, ',', '');
    $reviewCount = (int) ($reviewStats['count'] ?? 0);
    $categories = [
        ['title' => $home->text('cat_1_title'), 'url' => route('packages.index', 'buffets'), 'image' => $home->image('cat_1_image', velora_photo('buffet')), 'alt' => $home->text('cat_1_alt')],
        ['title' => $home->text('cat_2_title'), 'url' => route('packages.index', 'fingerfood'), 'image' => $home->image('cat_2_image', velora_photo('fingerfood')), 'alt' => $home->text('cat_2_alt')],
        ['title' => $home->text('cat_3_title'), 'url' => route('packages.index', 'fruehstueck'), 'image' => $home->image('cat_3_image', velora_photo('breakfast')), 'alt' => $home->text('cat_3_alt')],
        ['title' => $home->text('cat_4_title'), 'url' => route('packages.index', 'grillcatering'), 'image' => $home->image('cat_4_image', velora_photo('grill')), 'alt' => $home->text('cat_4_alt')],
        ['title' => $home->text('cat_5_title'), 'url' => route('packages.index', 'vegetarisch'), 'image' => $home->image('cat_5_image', velora_photo('vegetarisch')), 'alt' => $home->text('cat_5_alt')],
        ['title' => $home->text('cat_6_title'), 'url' => route('packages.index', 'vegan'), 'image' => $home->image('cat_6_image', velora_photo('vegan')), 'alt' => $home->text('cat_6_alt')],
    ];
    $concepts = [
        ['label' => 'Business & Firmen Catering', 'url' => route('quote.create')],
        ['label' => 'Partyservice Mannheim', 'url' => route('events.partyservice')],
        ['label' => 'Messe Catering Mannheim', 'url' => route('events.messe')],
        ['label' => 'Hochzeitscatering', 'url' => route('packages.index', 'buffets')],
        ['label' => 'Eventcatering', 'url' => route('quote.create')],
        ['label' => 'Catering ab 10 Personen', 'url' => route('packages.index', 'buffets')],
        ['label' => 'Fingerfood Mannheim', 'url' => route('packages.index', 'fingerfood')],
        ['label' => 'Grillcatering Mannheim', 'url' => route('packages.index', 'grillcatering')],
        ['label' => 'Frühstückscatering Mannheim', 'url' => route('packages.index', 'fruehstueck')],
        ['label' => 'Unser Liefergebiet', 'url' => route('about')],
    ];
    foreach ($cities ?? [] as $city) {
        if (empty($city->slug) || empty($city->name)) {
            continue;
        }
        $concepts[] = ['label' => $city->name, 'url' => route('cities.show', $city->slug)];
        if (count($concepts) >= 14) {
            break;
        }
    }
    $homeGalleryTiles = collect();
    foreach ($galleries ?? [] as $gallery) {
        foreach ($gallery->images ?? [] as $image) {
            $path = content_image_path($image->path ?: $image->url);
            if (! $path) {
                continue;
            }
            if (! str_starts_with($path, 'http') && ! str_starts_with($path, '/')) {
                $path = asset('storage/'.$path);
            }
            if ($homeGalleryTiles->contains(fn ($tile) => $tile['src'] === $path)) {
                continue;
            }
            $homeGalleryTiles->push([
                'src' => $path,
                'alt' => $image->alt ?: $image->title ?: ($gallery->name ?? 'Galerie'),
                'url' => route('gallery.show', $gallery->slug),
            ]);
            if ($homeGalleryTiles->count() >= 8) {
                break 2;
            }
        }
    }
    if ($homeGalleryTiles->count() < 4) {
        foreach ([
            ['src' => '/images/gallery/hochzeitscatering-mannheim.webp', 'alt' => 'Hochzeitscatering Mannheim'],
            ['src' => '/images/gallery/buffet-catering-mannheim.webp', 'alt' => 'Buffet Catering Mannheim'],
            ['src' => '/images/gallery/event-catering-mannheim.webp', 'alt' => 'Event Catering Mannheim'],
            ['src' => '/images/gallery/catering-fuer-firmen-mannheim.webp', 'alt' => 'Catering für Firmen Mannheim'],
        ] as $fallback) {
            if ($homeGalleryTiles->contains(fn ($tile) => $tile['src'] === $fallback['src'])) {
                continue;
            }
            $homeGalleryTiles->push([
                'src' => $fallback['src'],
                'alt' => $fallback['alt'],
                'url' => route('gallery.index'),
            ]);
            if ($homeGalleryTiles->count() >= 4) {
                break;
            }
        }
    }
    $reasons = [];
    for ($i = 1; $i <= 5; $i++) {
        $reasons[] = [
            'icon' => $home->text('reason_'.$i.'_icon'),
            'title' => $home->text('reason_'.$i.'_title'),
            'text' => $home->text('reason_'.$i.'_text'),
        ];
    }
@endphp

<style>
.ht{--gold:#c9a96e;--gold-light:#e2c992;--ink:#1a1a1a;--muted:#555;--off:#f8f7f4;color:var(--ink);background:#fff;font-family:Inter,ui-sans-serif,system-ui,sans-serif}
.ht h1,.ht h2,.ht .ht-display{font-family:'Playfair Display',Georgia,serif}
.ht a{text-decoration:none}
.ht-wrap{width:min(1180px,calc(100% - 40px));margin:0 auto}
.ht-hero{position:relative;display:flex;align-items:center;justify-content:flex-start;height:92vh;min-height:600px;max-height:900px;overflow:hidden;color:#fff}
.ht-hero-bg,.ht-hero-shade{position:absolute;inset:0}
.ht-hero-bg{z-index:1;background:#0a0a0a}
.ht-hero-bg img{width:100%;height:100%;object-fit:cover;object-position:center}
.ht-hero-shade{z-index:2;background:linear-gradient(105deg,rgba(10,10,10,.98) 0%,rgba(10,10,10,.95) 30%,rgba(10,10,10,.7) 55%,rgba(10,10,10,.3) 75%,rgba(10,10,10,0) 100%)}
.ht-hero-copy{position:relative;z-index:3;width:min(34rem,46vw);margin:0;margin-left:max(2.5rem,calc((100vw - 1180px) / 2));padding-right:1.5rem;text-align:left}
.ht-hero h1{color:#fff;max-width:11em;font-size:clamp(2.7rem,4.4vw,4.35rem);font-weight:800;line-height:1.02;letter-spacing:-.03em;text-align:left}
.ht-gold{color:var(--gold)}
.ht-hero p{margin-top:1.35rem;max-width:28rem;color:rgba(255,255,255,.75);font-size:1.12rem;line-height:1.65;text-align:left}
.ht-actions{display:flex;flex-wrap:wrap;justify-content:flex-start;gap:1rem;margin-top:2.1rem}
.ht-btn{display:inline-flex;align-items:center;gap:.5rem;border-radius:999px;padding:.8rem 1.75rem;font-size:.85rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase}
.ht-btn-gold{background:var(--gold);color:#0a0a0a}
.ht-btn-gold:hover{background:var(--gold-light)}
.ht-btn-ghost{border:1px solid rgba(255,255,255,.25);color:#fff}
.ht-btn-ghost:hover{border-color:#fff}
.ht-trust{background:#111;padding:1.85rem 0;border-bottom:1px solid rgba(255,255,255,.05)}
.ht-trust-row{display:flex;flex-wrap:wrap;justify-content:space-between;gap:1rem 1.5rem}
.ht-trust-row span{display:inline-flex;align-items:center;gap:.7rem;color:#fff;font-size:.85rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase}
.ht-trust-row i{color:var(--gold);font-style:normal}
.ht-section{padding:6.5rem 0;background:var(--off)}
.ht-section-head{margin-bottom:3.5rem;text-align:center}
.ht-section-head h2{font-size:clamp(2rem,3vw,2.5rem);letter-spacing:-.02em;color:#0a0a0a}
.ht-section-head p{margin:.9rem auto 0;max-width:38rem;color:var(--muted);font-size:1.15rem}
.ht-cats{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem}
.ht-cat{position:relative;display:flex;height:230px;align-items:flex-end;overflow:hidden;border-radius:16px;background:#0a0a0a;color:#fff}
.ht-cat img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;transition:transform .5s ease}
.ht-cat:hover img{transform:scale(1.04)}
.ht-cat::before{content:"";position:absolute;inset:0;z-index:1;background:linear-gradient(to top,rgba(0,0,0,.85) 0%,rgba(0,0,0,.3) 40%,rgba(0,0,0,.1) 100%)}
.ht-cat-body{position:relative;z-index:2;padding:1.15rem 1.2rem}
.ht-cat h3{margin-bottom:.55rem;font-size:1.25rem;font-weight:700;line-height:1.2;color:#fff}
.ht-cat .ht-btn{padding:.55rem 1rem;font-size:.72rem}
.ht-cat-clone{display:none}
.ht-split{display:flex;flex-wrap:wrap;width:100%}
.ht-split.reverse{flex-direction:row-reverse}
.ht-half{width:50%;min-height:600px;display:flex;flex-direction:column;justify-content:center}
.ht-photo{position:relative;overflow:hidden;background:#111}
.ht-photo img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.ht-copy{padding:8% 10%}
.ht-copy.light{background:#fff;color:#111;border-bottom:1px solid #eee}
.ht-copy.grey{background:#f4f4f4;color:#111}
.ht-copy.dark{background:#111;color:#fff}
.ht-kicker{margin-bottom:1.15rem;font-size:.85rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase;opacity:.7}
.ht-copy h2{margin-bottom:1.4rem;font-family:Inter,sans-serif;font-size:clamp(1.7rem,2.4vw,2.6rem);font-weight:900;line-height:1.15;letter-spacing:-.02em;text-transform:uppercase}
.ht-copy.light h2,.ht-copy.grey h2{color:#111}
.ht-copy.dark h2{color:#fff}
.ht-copy p{margin-bottom:1rem;font-size:1rem;line-height:1.8;color:#555}
.ht-copy.dark p{color:rgba(255,255,255,.78)}
.ht-reasons{display:grid;gap:1.15rem;margin:0;padding:0;list-style:none}
.ht-reasons li strong{display:block;margin-bottom:.25rem;font-size:.95rem;letter-spacing:.04em;text-transform:uppercase}
.ht-reasons li span{display:block;font-size:.95rem;line-height:1.6;color:#555}
.ht-service{position:relative;padding:7.5rem 0;background:#fff;text-align:center}
.ht-service::before{content:"";position:absolute;top:0;left:50%;width:60px;height:3px;border-radius:3px;background:var(--gold);transform:translateX(-50%)}
.ht-service h2{margin-bottom:4rem;font-size:clamp(1.8rem,3vw,2.5rem);color:#0a0a0a}
.ht-service-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:3rem}
.ht-service article{padding:2.2rem 1.6rem;border-radius:20px}
.ht-service article:hover{background:var(--off)}
.ht-service svg{width:56px;height:56px;margin:0 auto 1.4rem;stroke:var(--gold)}
.ht-service h3{margin-bottom:.8rem;font-size:1.3rem;color:#0a0a0a}
.ht-service p{color:var(--muted);font-size:.95rem;line-height:1.8}
.ht-gallery{padding:7rem 0;background:#fff}
.ht-gallery-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem}
.ht-gallery-grid a{display:block;overflow:hidden;border-radius:12px;aspect-ratio:1}
.ht-gallery-grid img{width:100%;height:100%;object-fit:cover;transition:transform .5s ease}
.ht-gallery-grid a:hover img{transform:scale(1.06)}
.ht-more{display:flex;justify-content:center;margin-top:2rem}
.ht-concepts{padding:6.5rem 0;background:var(--off)}
.ht-pills{display:flex;flex-wrap:wrap;justify-content:center;gap:.75rem;margin-top:2rem}
.ht-pills a{border-radius:999px;background:#fff;padding:.7rem 1.1rem;color:#1a1a1a;font-size:.92rem;font-weight:600;box-shadow:0 2px 8px rgba(0,0,0,.06)}
.ht-pills a:hover{color:#a88a4e}
.ht-seo{max-width:46rem;margin:3rem auto 0;text-align:left}
.ht-seo h3{margin-bottom:.8rem;font-size:1.35rem;color:#0a0a0a}
.ht-seo p{margin-bottom:1rem;color:var(--muted);line-height:1.8}
.ht-seo a{color:#a88a4e;font-weight:700}
.ht-reviews{padding:6rem 0;background:#fff;border-top:1px solid #f0f0f0}
.ht-reviews-head{display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:1.25rem;margin-bottom:3rem}
.ht-kicker-gold{margin:0 0 .4rem;color:#b8784e;font-size:.8rem;font-weight:800;letter-spacing:.16em;text-transform:uppercase}
.ht-reviews h2{margin:0;font-size:clamp(2rem,4vw,2.8rem);color:#111}
.ht-score{display:flex;align-items:center;gap:.9rem}
.ht-score b{font-size:2.2rem;font-weight:900;color:#111}
.ht-score small{display:block;color:#666;font-size:.85rem;font-weight:600}
.ht-stars{color:#fbbc05;letter-spacing:2px}
.ht-review-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.8rem}
.ht-review{display:flex;min-height:100%;flex-direction:column;border-radius:16px;background:#fafafa;padding:2.2rem}
.ht-review p{flex:1;color:#333;line-height:1.7}
.ht-review footer{margin-top:1.4rem;color:#666;font-size:.9rem;font-weight:700}
.ht-cta{position:relative;padding:6.25rem 0;background:#0a0a0a;text-align:center}
.ht-cta h2{color:#fff;font-size:clamp(1.8rem,3vw,2.8rem);letter-spacing:-.02em}
.ht-cta p{max-width:34rem;margin:1.1rem auto 2.2rem;color:rgba(255,255,255,.5);font-size:1.1rem;line-height:1.7}
@@media (max-width:991px){
    .ht-hero{align-items:flex-end;height:100vh;max-height:none;min-height:500px}
    .ht-trust-row{display:grid;grid-template-columns:1fr 1fr;gap:.85rem 1rem}
    .ht-trust-row span{font-size:.68rem;letter-spacing:.04em}
    .ht-hero-shade{background:linear-gradient(to top,rgba(10,10,10,.98) 0%,rgba(10,10,10,.8) 35%,rgba(10,10,10,.4) 60%,rgba(10,10,10,.1) 100%)}
    .ht-hero-copy{width:min(1180px,calc(100% - 2.5rem));max-width:none;margin-left:1.25rem;padding-right:0;padding-bottom:3.5rem;text-align:center}
    .ht-hero h1{max-width:none;text-align:center}
    .ht-actions{justify-content:center}
    .ht-service-grid,.ht-review-grid{grid-template-columns:1fr}
    .ht-gallery-grid{grid-template-columns:1fr 1fr}
    .ht-cats{display:flex;gap:.85rem;overflow-x:auto;scrollbar-width:none;-ms-overflow-style:none}
    .ht-cats::-webkit-scrollbar{display:none}
    .ht-cat{flex:0 0 82%;height:220px;border-radius:14px;scroll-snap-align:none}
    .ht-cat-clone{display:flex}
    .ht-cat h3{font-size:1.35rem}
    .ht-split,.ht-split.reverse{flex-direction:column}
    .ht-half{width:100%;min-height:0}
    .ht-photo{min-height:350px;order:1}
    .ht-copy{order:2;padding:3.5rem 1.5rem}
    .ht-service-grid{gap:1rem}
    .ht-service article{display:flex;gap:1.1rem;background:var(--off);padding:1.6rem 1.2rem;text-align:left}
    .ht-service svg{width:40px;height:40px;margin:0;flex-shrink:0}
}
@@media (max-width:480px){
    .ht-hero h1{font-size:1.8rem}
    .ht-hero p{font-size:.95rem}
    .ht-cat{flex-basis:86%;height:210px}
    .ht-cat h3{font-size:1.2rem}
}
@@media (prefers-reduced-motion:reduce){
    .ht-cat-clone{display:none}
}
</style>

<div class="ht">
    <section class="ht-hero">
        <div class="ht-hero-bg">
            <img src="{{ $home->image('hero_image', velora_photo('hero')) }}" alt="{{ $home->text('hero_alt') }}" fetchpriority="high" decoding="async">
        </div>
        <div class="ht-hero-shade"></div>
        <div class="ht-hero-copy">
            <h1>{{ $home->text('hero_line_1') }} <br><span class="ht-gold">{{ $home->text('hero_line_2') }}</span></h1>
            <p>{{ $home->text('hero_lead') }}</p>
            <div class="ht-actions">
                <a href="{{ route('packages.index', 'buffets') }}" class="ht-btn ht-btn-gold">{{ $home->text('hero_btn_primary') }} →</a>
                <a href="{{ route('contact.show') }}" class="ht-btn ht-btn-ghost">{{ $home->text('hero_btn_secondary') }}</a>
            </div>
        </div>
    </section>

    <div class="ht-trust">
        <div class="ht-wrap ht-trust-row">
            <span><i>✓</i> {{ $home->text('trust_1') }}</span>
            <span><i>✓</i> {{ $home->text('trust_2') }}</span>
            <span><i>✓</i> {{ $home->text('trust_3') }}</span>
            <span><i>✓</i> {{ $home->text('trust_4') }}</span>
        </div>
    </div>

    <section class="ht-section" id="kategorien">
        <div class="ht-wrap">
            <div class="ht-section-head">
                <h2>{{ $home->text('cats_title') }}</h2>
                <p>{{ $home->text('cats_lead') }}</p>
            </div>
            <div class="ht-cats" data-cat-marquee>
                @foreach ([false, true] as $clone)
                    @foreach ($categories as $category)
                        <a href="{{ $category['url'] }}" class="ht-cat{{ $clone ? ' ht-cat-clone' : '' }}" @if ($clone) aria-hidden="true" tabindex="-1" @endif>
                            <img src="{{ $category['image'] }}" alt="{{ $clone ? '' : $category['alt'] }}" loading="lazy">
                            <div class="ht-cat-body">
                                <h3>{{ $category['title'] }}</h3>
                                <span class="ht-btn ht-btn-gold">{{ $home->text('cat_btn') }} →</span>
                            </div>
                        </a>
                    @endforeach
                @endforeach
            </div>
        </div>
    </section>
    <script>
        (function () {
            var row = document.querySelector('[data-cat-marquee]');
            if (!row) return;
            var mq = window.matchMedia('(max-width: 991px)');
            var reduce = window.matchMedia('(prefers-reduced-motion: reduce)');
            var paused = false;
            var resumeTimer;
            function tick() {
                if (mq.matches && !paused && !reduce.matches && !document.hidden) {
                    row.scrollLeft += 0.7;
                    var half = row.scrollWidth / 2;
                    if (half > 0 && row.scrollLeft >= half) row.scrollLeft -= half;
                }
                requestAnimationFrame(tick);
            }
            function pause() {
                paused = true;
                clearTimeout(resumeTimer);
            }
            function resumeSoon() {
                clearTimeout(resumeTimer);
                resumeTimer = setTimeout(function () { paused = false; }, 2800);
            }
            row.addEventListener('pointerdown', pause);
            row.addEventListener('pointerup', resumeSoon);
            row.addEventListener('pointercancel', resumeSoon);
            row.addEventListener('mouseenter', pause);
            row.addEventListener('mouseleave', function () { paused = false; });
            mq.addEventListener('change', function () { row.scrollLeft = 0; });
            requestAnimationFrame(tick);
        })();
    </script>

    <div class="ht-split">
        <div class="ht-half ht-photo">
            <img src="{{ $home->image('split_1_image', velora_photo('table')) }}" alt="{{ $home->text('split_1_alt') }}" loading="lazy">
        </div>
        <div class="ht-half ht-copy light">
            <p class="ht-kicker">{{ $home->text('split_1_kicker') }}</p>
            <h2>{{ $home->text('split_1_title') }}</h2>
            @foreach ($home->paragraphs('split_1_body') as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
            <a href="{{ route('about') }}" class="ht-btn ht-btn-gold">{{ $home->text('split_1_btn') }}</a>
        </div>
    </div>

    <div class="ht-split reverse">
        <div class="ht-half ht-photo">
            <img src="{{ $home->image('split_2_image', velora_photo('kitchen')) }}" alt="{{ $home->text('split_2_alt') }}" loading="lazy">
        </div>
        <div class="ht-half ht-copy grey">
            <p class="ht-kicker">{{ $home->text('split_2_kicker') }}</p>
            <h2>{{ $home->text('split_2_title') }}</h2>
            @foreach ($home->paragraphs('split_2_body') as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
        </div>
    </div>

    <div class="ht-split">
        <div class="ht-half ht-photo">
            <img src="{{ $home->image('split_3_image', velora_photo('staff')) }}" alt="{{ $home->text('split_3_alt') }}" loading="lazy">
        </div>
        <div class="ht-half ht-copy light">
            <p class="ht-kicker">{{ $home->text('split_3_kicker') }}</p>
            <h2>{{ $home->text('split_3_title') }}</h2>
            @foreach ($home->paragraphs('split_3_body') as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
        </div>
    </div>

    <div class="ht-split reverse">
        <div class="ht-half ht-photo">
            <img src="{{ $home->image('split_4_image', velora_photo('buffet')) }}" alt="{{ $home->text('split_4_alt') }}" loading="lazy">
        </div>
        <div class="ht-half ht-copy grey">
            <h2>{{ $home->text('reasons_title') }}</h2>
            <ul class="ht-reasons">
                @foreach ($reasons as $reason)
                    <li>
                        <strong>{{ $reason['icon'] }} {{ $reason['title'] }}</strong>
                        <span>{{ $reason['text'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <section class="ht-service">
        <div class="ht-wrap">
            <h2 class="ht-display">{{ $home->text('service_title') }}</h2>
            <div class="ht-service-grid">
                <article>
                    <svg fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="M4 10h16M7 10V7a5 5 0 0 1 10 0v3M6 10h12l-.7 9H6.7L6 10Z"/></svg>
                    <div>
                        <h3 class="ht-display">{{ $home->text('service_1_title') }}</h3>
                        <p>{{ $home->text('service_1_text') }}</p>
                    </div>
                </article>
                <article>
                    <svg fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="M8 3v3m8-3v3M4 9h16M6 6h12a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"/></svg>
                    <div>
                        <h3 class="ht-display">{{ $home->text('service_2_title') }}</h3>
                        <p>{{ $home->text('service_2_text') }}</p>
                    </div>
                </article>
                <article>
                    <svg fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="M4 6h16v12H4V6Zm8 3v3l2 2"/></svg>
                    <div>
                        <h3 class="ht-display">{{ $home->text('service_3_title') }}</h3>
                        <p>{{ $home->text('service_3_text') }}</p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    @if ($homeGalleryTiles->isNotEmpty())
    <section class="ht-gallery" id="galerie" aria-labelledby="home-gallery-title">
        <div class="ht-wrap">
            <div class="ht-section-head">
                <h2 id="home-gallery-title">{{ $home->text('gallery_title') }}</h2>
                <p>{{ $home->text('gallery_lead') }}</p>
            </div>
            <div class="ht-gallery-grid">
                @foreach ($homeGalleryTiles as $tile)
                    <a href="{{ $tile['url'] }}">
                        <img src="{{ $tile['src'] }}" alt="{{ $tile['alt'] }}" loading="lazy" width="600" height="600">
                    </a>
                @endforeach
            </div>
            <div class="ht-more">
                <a href="{{ route('gallery.index') }}" class="ht-btn ht-btn-gold">{{ $home->text('gallery_btn') }}</a>
            </div>
        </div>
    </section>
    @endif

    <section class="ht-concepts">
        <div class="ht-wrap">
            <div class="ht-section-head">
                <h2>{{ $home->text('concepts_title') }}</h2>
                <p>{{ $home->text('concepts_lead') }}</p>
            </div>
            <div class="ht-pills">
                @foreach ($concepts as $concept)
                    <a href="{{ $concept['url'] }}">{{ $concept['label'] }}</a>
                @endforeach
            </div>
            <div class="ht-seo">
                <h3 class="ht-display">{{ $home->text('seo_title') }}</h3>
                <p>{{ $home->text('seo_p1') }}</p>
                <p>{{ $home->text('seo_p2') }}</p>
            </div>
        </div>
    </section>

    <section class="ht-reviews">
        <div class="ht-wrap">
            <div class="ht-reviews-head">
                <div>
                    <p class="ht-kicker-gold">{{ $home->text('reviews_kicker') }}</p>
                    <h2>{{ $home->text('reviews_title') }}</h2>
                </div>
                <div class="ht-score">
                    <b>{{ $reviewAvg }}</b>
                    <div>
                        <div class="ht-stars" aria-hidden="true">★★★★★</div>
                        <small>Aus {{ $reviewCount }}+ Bewertungen</small>
                    </div>
                </div>
            </div>
            <div class="ht-review-grid">
                @forelse ($reviews ?? [] as $review)
                    <article class="ht-review">
                        <div class="ht-stars" aria-label="{{ (int) $review->rating }} von 5 Sternen">{{ str_repeat('★', (int) $review->rating) }}{{ str_repeat('☆', max(0, 5 - (int) $review->rating)) }}</div>
                        <p>„{{ $review->body }}“</p>
                        <footer>
                            {{ $review->name }}
                            @if ($review->reviewed_at)
                                · {{ $review->reviewed_at->locale('de')->diffForHumans() }}
                            @endif
                        </footer>
                    </article>
                @empty
                    <article class="ht-review">
                        <div class="ht-stars" aria-hidden="true">★★★★★</div>
                        <p>„Frisch, pünktlich und genau so präsentiert, wie wir es uns für unsere Feier gewünscht haben.“</p>
                        <footer>Monnemtisch Kunden</footer>
                    </article>
                @endforelse
            </div>
            <div class="ht-more">
                <a href="{{ route('reviews.index') }}" class="ht-btn ht-btn-ghost" style="border-color:#dadce0;color:#3c4043">{{ $home->text('reviews_btn') }}</a>
            </div>
        </div>
    </section>

    <section class="ht-cta">
        <div class="ht-wrap">
            <h2>{{ $home->text('cta_title') }}</h2>
            <p>{{ $home->text('cta_lead') }}</p>
            <a href="{{ route('quote.create') }}" class="ht-btn ht-btn-gold">{{ $home->text('cta_btn') }} →</a>
        </div>
    </section>
</div>
