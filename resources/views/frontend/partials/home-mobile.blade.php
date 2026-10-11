@php
    $heroImage = velora_photo('hero');
    $reviewAvg = number_format((float) ($reviewStats['avg'] ?? 4.9), 1, ',', '');
    $reviewCount = (int) ($reviewStats['count'] ?? 0);
    $freeFrom = 500;
    foreach ($deliveryZones ?? [] as $zone) {
        if ($zone->free_delivery_threshold) {
            $freeFrom = min($freeFrom, (float) $zone->free_delivery_threshold);
        }
    }
    $cats = [
        ['title' => 'Buffets', 'url' => route('packages.index', 'buffets'), 'photo' => 'buffet'],
        ['title' => 'Fingerfood', 'url' => route('packages.index', 'fingerfood'), 'photo' => 'fingerfood'],
        ['title' => 'Frühstück', 'url' => route('packages.index', 'fruehstueck'), 'photo' => 'breakfast'],
        ['title' => 'Grillcatering', 'url' => route('packages.index', 'grillcatering'), 'photo' => 'grill'],
        ['title' => 'Vegetarisch', 'url' => route('packages.index', 'vegetarisch'), 'photo' => 'vegetarisch'],
        ['title' => 'Vegan', 'url' => route('packages.index', 'vegan'), 'photo' => 'vegan'],
    ];
    $checkUrl = route('delivery.check');
    $deliveryQuoteUrl = route('checkout.delivery-distance');
    $deliveryCostPerKm = (float) config('delivery.cost_per_km', 1);
    $quoteUrl = route('quote.create');
@endphp

<style>
.mshop{--ink:#132a3a;--orange:#d89b2b;--orange-dark:#c08a22;color:var(--ink);background:#f7f3eb;overflow-x:clip;max-width:100%}
.mshop h1,.mshop h2,.mshop h3,.mshop-headline{font-family:'Playfair Display',Georgia,serif;color:var(--ink);letter-spacing:-.02em}
.mshop-hero{position:relative;padding:1rem 1rem 0}
.mshop-hero-bg{position:absolute;inset:0 0 auto 0;height:13.5rem;overflow:hidden;border-radius:0 0 1.25rem 1.25rem}
.mshop-hero-bg img{width:100%;height:100%;object-fit:cover;filter:blur(1px) brightness(.78)}
.mshop-card{position:relative;margin-top:5.5rem;border-radius:1.15rem;background:#fff;padding:1.15rem 1.1rem 1.2rem;box-shadow:0 12px 32px rgba(20,53,44,.12)}
.mshop-card .mshop-headline{font-size:1.55rem;line-height:1.15;font-weight:700}
.mshop-card p.lead{margin-top:.4rem;font-size:.9rem;color:#5c675f}
.mshop-date{margin-top:.7rem;position:relative;z-index:2}
.mshop-date .label-velora{margin-bottom:.4rem;font-size:.62rem;letter-spacing:.12em}
.mshop-date .input-velora{border-color:#e4ddd4;padding:.75rem .9rem;font-size:.95rem;border-radius:.8rem}
.mshop-addr{margin-top:.85rem}
.mshop-addr .label-velora{margin-bottom:.4rem;font-size:.62rem;letter-spacing:.12em}
.mshop-field{display:flex;align-items:center;gap:.55rem;width:100%;border:1px solid #e4ddd4;border-radius:.8rem;background:#fff;padding:.85rem .95rem;min-height:3.15rem}
.mshop-field input{width:100%;border:0;background:transparent;font-size:.95rem;color:var(--ink);outline:none}
.mshop-field input::placeholder{color:#9aa39c}
.mshop-field svg{flex-shrink:0;width:1.15rem;height:1.15rem;color:#7a847c}
.mshop-btn{display:flex;width:100%;align-items:center;justify-content:center;margin-top:.85rem;border:0;border-radius:.8rem;background:var(--orange);padding:.9rem 1rem;font-size:.95rem;font-weight:700;color:var(--ink)}
.mshop-btn:active{background:var(--orange-dark)}
.mshop-msg{margin-top:.65rem;font-size:.8rem;line-height:1.4}
.mshop-msg.ok{color:#3f6f4e}
.mshop-msg.bad{color:#c2413b}
.mshop-hint{margin-top:.45rem;font-size:.75rem;line-height:1.4;color:#6b746e}
.mshop-quote{margin-top:.35rem;font-size:.82rem;line-height:1.4;font-weight:700;color:var(--ink)}
.mshop-quote.busy{font-weight:500;color:var(--orange)}
.mshop-quote.err{font-weight:500;color:#c2413b}
.mshop-cats{display:flex;gap:.85rem;overflow-x:auto;padding:1.15rem 1rem .4rem;scrollbar-width:none;-webkit-overflow-scrolling:touch}
.mshop-cats::-webkit-scrollbar{display:none}
.mshop-cat{flex:0 0 auto;width:4.6rem;text-align:center;text-decoration:none;color:var(--ink)}
.mshop-cat img{width:4.35rem;height:4.35rem;border-radius:999px;object-fit:cover;background:#e8e2d8}
.mshop-cat span{display:block;margin-top:.4rem;font-size:.72rem;font-weight:600}
.mshop-wrap{padding:0 1rem 1.2rem}
.mshop-section-title{margin:1rem 0 .85rem;font-size:1.28rem;font-weight:700}
.mshop-pkg{display:flex;align-items:stretch;gap:.65rem;margin-bottom:.85rem;border-radius:1rem;background:#fff;padding:.65rem;box-shadow:0 8px 22px rgba(20,53,44,.07);max-width:100%;min-width:0;box-sizing:border-box}
.mshop-pkg img{width:4.85rem;height:4.85rem;flex-shrink:0;border-radius:.75rem;object-fit:cover}
.mshop-pkg-body{display:flex;min-width:0;flex:1;flex-direction:column}
.mshop-pkg-top{display:flex;align-items:flex-start;justify-content:space-between;gap:.35rem;min-width:0}
.mshop-pkg-top h3{min-width:0;flex:1;font-size:.95rem;font-weight:700;line-height:1.2}
.mshop-price{flex-shrink:0;font-size:.74rem;font-weight:800;color:var(--orange);white-space:nowrap}
.mshop-stars{margin-top:.15rem;font-size:.68rem;color:#d89b2b}
.mshop-meta{margin-top:.15rem;font-size:.66rem;line-height:1.3;color:#6b746e}
.mshop-row{display:grid;grid-template-columns:auto minmax(0,1fr);align-items:center;gap:.35rem;width:100%;min-width:0;margin-top:.45rem}
.mshop-qty{display:flex;align-items:center;gap:0;border:1px solid #e4ddd4;border-radius:.55rem;padding:.08rem;background:#fff}
.mshop-qty button{display:flex;height:1.5rem;width:1.45rem;flex-shrink:0;align-items:center;justify-content:center;border:0;background:transparent;font-size:1rem;line-height:1;color:var(--ink)}
.mshop-qty span{display:block;min-width:1.35rem;padding:0 .1rem;text-align:center;font-size:.72rem;font-weight:700;font-variant-numeric:tabular-nums}
.mshop-add{display:flex;min-width:0;align-items:center;justify-content:center;border:0;border-radius:.5rem;background:var(--orange);padding:.38rem .4rem;font-size:clamp(.58rem,.28rem + 1.6vw,.68rem);font-weight:700;color:var(--ink);text-decoration:none;text-align:center;line-height:1.15;overflow:hidden}
@media (min-width:380px){
    .mshop-pkg img{width:5.4rem;height:5.4rem}
    .mshop-price{font-size:.8rem}
}
.mshop-banner{display:flex;align-items:center;gap:.75rem;margin:1.1rem 0;border-radius:.9rem;background:#132a3a;padding:.9rem 1rem;color:#fff}
.mshop-banner p{font-size:.88rem;font-weight:700;line-height:1.25}
.mshop-banner span{display:block;font-size:.72rem;font-weight:500;opacity:.82}
.mshop-steps{display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;text-align:center}
.mshop-step{padding:.2rem}
.mshop-step i{display:flex;height:2.4rem;width:2.4rem;align-items:center;justify-content:center;margin:0 auto .4rem;border-radius:999px;background:#fff;color:var(--orange);box-shadow:0 4px 12px rgba(20,53,44,.08)}
.mshop-step b{display:block;font-size:.78rem;font-family:'Playfair Display',Georgia,serif}
.mshop-step small{display:block;margin-top:.2rem;font-size:.62rem;line-height:1.3;color:#6b746e}
.mshop-trust{display:grid;grid-template-columns:repeat(3,1fr);gap:.45rem;margin:1.1rem 0;text-align:center}
.mshop-trust p{font-size:.62rem;line-height:1.25;font-weight:600;color:#3d4a43}
.mshop-b2b{position:relative;overflow:hidden;border-radius:1rem;min-height:8.5rem;margin-top:.4rem}
.mshop-b2b img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.mshop-b2b-inner{position:relative;z-index:1;max-width:62%;padding:1.1rem 1rem;color:#fff}
.mshop-b2b-inner h3{font-size:1.15rem;color:#fff}
.mshop-b2b-inner p{margin:.3rem 0 .7rem;font-size:.72rem;line-height:1.35;color:rgba(255,255,255,.88)}
.mshop-b2b-inner a{display:inline-flex;border-radius:.55rem;background:#d89b2b;padding:.5rem .7rem;font-size:.72rem;font-weight:700;color:#132a3a;text-decoration:none}
</style>

<div class="mshop md:hidden">
    <section class="mshop-hero">
        <div class="mshop-hero-bg">
            <img src="{{ $heroImage }}" alt="Festlich angerichtetes Catering-Buffet von Monnemtisch in Mannheim" fetchpriority="high" decoding="async">
        </div>
        <form
            class="mshop-card"
            x-data="homeAvailability"
            x-on:submit.prevent="check()"
            @date-selected="date = $event.detail"
        >
            <p class="mshop-headline">Catering online bestellen</p>
            <p class="lead">Frisch geliefert in Mannheim &amp; Rhein-Neckar</p>
            <div class="mshop-addr">
                <label class="label-velora" for="mshop-address">Adresse</label>
                <div class="mshop-field">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11Zm0-8.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/></svg>
                    <input id="mshop-address" type="text" name="address" placeholder="Straße, Nr, PLZ, Ort" x-model="address" autocomplete="street-address">
                </div>
                <p class="mshop-hint">Innerhalb Mannheims kostenlos. Außerhalb: {{ number_format($deliveryCostPerKm, 2, ',', '.') }} €/km.</p>
                <p class="mshop-quote busy" x-show="quoting" x-cloak>Entfernung wird berechnet…</p>
                <p class="mshop-quote" x-show="!quoting && deliveryLabel" x-cloak x-text="deliveryLabel"></p>
                <p class="mshop-quote err" x-show="!quoting && deliveryError" x-cloak x-text="deliveryError"></p>
            </div>
            <div class="mshop-date">
                <x-blocked-date-picker
                    label="Datum"
                    name="event_date"
                    :blocked="$blockedDates ?? []"
                    min="{{ now()->toDateString() }}"
                />
            </div>
            <button type="submit" class="mshop-btn" :disabled="loading" x-text="loading ? 'Prüfen …' : 'Verfügbarkeit prüfen'"></button>
            <p class="mshop-msg" :class="ok === true ? 'ok' : (ok === false ? 'bad' : '')" x-show="msg" x-text="msg"></p>
        </form>
    </section>

    <nav id="mshop-cats" class="mshop-cats" aria-label="Kategorien">
        @foreach ($cats as $cat)
            <a href="{{ $cat['url'] }}" class="mshop-cat">
                <img src="{{ velora_photo($cat['photo']) }}" alt="{{ $cat['title'] }}" loading="lazy">
                <span>{{ $cat['title'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="mshop-wrap">
        <h2 class="mshop-section-title">Beliebte Catering-Pakete</h2>
        @foreach (($packages ?? collect()) as $package)
            @php
                $min = max(1, (int) $package->min_persons);
                $diet = match ($package->menu_group) {
                    'vegan' => 'Vegan',
                    'vegetarisch' => 'Vegetarisch',
                    'fruehstueck', 'fingerfood', 'buffets' => 'Vegetarisch verfügbar',
                    default => null,
                };
            @endphp
            <article class="mshop-pkg" x-data="{ n: {{ $min }}, min: {{ $min }} }">
                <img src="{{ $package->imageUrl() }}" alt="{{ $package->name }}" loading="lazy">
                <div class="mshop-pkg-body">
                    <div class="mshop-pkg-top">
                        <h3>{{ $package->name }}</h3>
                        <p class="mshop-price">ab {{ number_format((float) $package->price_per_person, 2, ',', '.') }} € p. P.</p>
                    </div>
                    <p class="mshop-stars">★★★★★ @if ($reviewCount) <span style="color:#6b746e">({{ $reviewCount }})</span> @endif</p>
                    <p class="mshop-meta">Mind. {{ $min }} Personen @if ($diet) · {{ $diet }} @endif</p>
                    <div class="mshop-row">
                        <div class="mshop-qty">
                            <button type="button" x-on:click="n = Math.max(min, n - 1)" aria-label="Weniger Personen">−</button>
                            <span x-text="n" :aria-label="n + ' Personen'"></span>
                            <button type="button" x-on:click="n = n + 1" aria-label="Mehr Personen">+</button>
                        </div>
                        <a class="mshop-add" :href="'{{ $package->publicUrl() }}?personen=' + n">In den Warenkorb</a>
                    </div>
                </div>
            </article>
        @endforeach
        <a href="{{ route('packages.index', 'buffets') }}" class="mshop-btn" style="margin-top:.4rem;background:#132a3a;color:#fff">Alle Menüs entdecken</a>

        <div class="mshop-banner">
            <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 7h11l4 5v6H3V7Zm11 0v5h4M7 20a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Zm10 0a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z"/></svg>
            <p>Kostenlose Lieferung ab {{ number_format($freeFrom, 0, ',', '.') }} €<span>In Mannheim &amp; Rhein-Neckar</span></p>
        </div>

        <h2 class="mshop-section-title">So einfach geht’s</h2>
        <div class="mshop-steps">
            <div class="mshop-step">
                <i><svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 10h16M7 10V7a5 5 0 0 1 10 0v3M6 10h12l-.6 9H6.6L6 10Z"/></svg></i>
                <b>Auswählen</b>
                <small>Pakete entdecken &amp; zusammenstellen</small>
            </div>
            <div class="mshop-step">
                <i><svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 3v3m8-3v3M4 9h16M6 6h12a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"/></svg></i>
                <b>Termin festlegen</b>
                <small>Datum &amp; Personenzahl angeben</small>
            </div>
            <div class="mshop-step">
                <i><svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 10h18M5 6h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Zm2 8h4"/></svg></i>
                <b>Online bezahlen</b>
                <small>Sicher bezahlen &amp; genießen</small>
            </div>
        </div>

        <div class="mshop-trust">
            <p>Google {{ $reviewAvg }} ★<br>Aus {{ $reviewCount ?: (int) ($trust['review_count'] ?? 120) }} Bewertungen</p>
            <p>Sichere Zahlung<br>SSL-verschlüsselt</p>
            <p>Pünktliche Lieferung<br>Verlässlich &amp; flexibel</p>
        </div>

        <div class="mshop-b2b">
            <img src="{{ velora_photo('table') }}" alt="Gedeckter Tisch mit Catering von Monnemtisch für Firmen und Events in Mannheim" loading="lazy">
            <div class="mshop-b2b-inner" style="background:linear-gradient(90deg, rgba(20,53,44,.88) 0%, rgba(20,53,44,.35) 100%)">
                <h3>Für Firmen &amp; Events</h3>
                <p>Messe, Meeting oder Feier – individuelles Catering auf Anfrage.</p>
                <a href="{{ $quoteUrl }}">Individuelles Angebot</a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('homeAvailability', () => ({
        COST_PER_KM: {{ $deliveryCostPerKm }},
        checkUrl: @json($checkUrl),
        quoteUrl: @json($deliveryQuoteUrl),
        address: '',
        date: '',
        msg: '',
        ok: null,
        loading: false,
        quoting: false,
        deliveryLabel: '',
        deliveryError: '',
        quoteTimer: null,
        init() {
            this.$watch('address', () => this.scheduleQuote());
        },
        scheduleQuote() {
            clearTimeout(this.quoteTimer);
            this.quoteTimer = setTimeout(() => this.quoteDistance(), 1500);
        },
        fmtKm(v) {
            return Number(v || 0).toFixed(1).replace('.', ',');
        },
        fmtMoney(v) {
            return window.Velora.formatMoney(v);
        },
        isFreeCity(text, data) {
            const typed = String(text || '');
            return Boolean(data.isStuttgart ?? data.isHannover)
                || /\b(68159|6816[13579]|68199|68219|68229|68239|68259|6830[579])\b/.test(typed)
                || typed.toLowerCase().includes('mannheim');
        },
        applyQuote(data) {
            const typed = this.address;
            const isFree = this.isFreeCity(typed, data);
            const km = Number(data.distanceKm || 0);
            if (!data.ok) {
                if (isFree) {
                    this.deliveryError = '';
                    this.deliveryLabel = 'Lieferung innerhalb Mannheims (Kostenlos)';
                    return;
                }
                this.deliveryLabel = '';
                this.deliveryError = data.error || 'Die Entfernung konnte nicht ermittelt werden.';
                return;
            }
            this.deliveryError = '';
            if (isFree) {
                this.deliveryLabel = 'Lieferung innerhalb Mannheims (Kostenlos)';
                return;
            }
            const cost = Math.round(km * this.COST_PER_KM * 100) / 100;
            this.deliveryLabel = 'Entfernung: ' + this.fmtKm(km) + ' km — Lieferkosten: ' + this.fmtMoney(cost);
        },
        async quoteDistance() {
            const text = String(this.address || '').trim();
            if (text.length < 3) {
                this.deliveryLabel = '';
                this.deliveryError = '';
                this.quoting = false;
                return;
            }
            this.quoting = true;
            this.deliveryError = '';
            try {
                const data = await window.Velora.fetchJson(this.quoteUrl, {
                    method: 'POST',
                    body: { address: text },
                });
                this.applyQuote(data);
            } catch (e) {
                this.applyQuote({
                    ok: false,
                    isStuttgart: false,
                    isHannover: false,
                    distanceKm: 0,
                    error: e.data?.message || e.message || 'Die Entfernung konnte nicht ermittelt werden.',
                });
            } finally {
                this.quoting = false;
            }
        },
        async check() {
            this.msg = '';
            this.ok = null;
            if (this.address.trim().length < 6) {
                this.ok = false;
                this.msg = 'Bitte Straße, Nr, PLZ und Ort angeben.';
                return;
            }
            this.date = this.$el.querySelector('[name=event_date]')?.value || this.date;
            this.loading = true;
            try {
                const data = await window.Velora.fetchJson(this.checkUrl, {
                    method: 'POST',
                    body: { address: this.address.trim(), date: this.date || null },
                });
                this.ok = !!data.ok;
                this.msg = data.message || '';
                if (data.ok && data.redirect) {
                    setTimeout(() => { window.location.href = data.redirect; }, 650);
                }
            } catch (e) {
                this.ok = false;
                this.msg = e.data?.message || e.message || 'Prüfung gerade nicht möglich. Bitte später erneut versuchen.';
            }
            this.loading = false;
        },
    }));
});
</script>
@endpush

