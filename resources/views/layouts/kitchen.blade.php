<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mutfak')</title>
    <x-favicon />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Cormorant+Garamond:ital,wght@0,600;0,700;1,500&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        :root {
            --k-bg: #f6f1e8;
            --k-paper: #fffdf8;
            --k-ink: #2c261f;
            --k-muted: #7a7166;
            --k-line: #e6ddd0;
            --k-sage: #5b7c6e;
            --k-sage-deep: #476357;
            --k-terracotta: #d4653a;
            --k-gold: #b0894f;
            --k-today-soft: #fde8e0;
            --k-sage-soft: #e4efe9;
            --k-gold-soft: #f4ead8;
        }
        body.kitchen-body {
            font-family: 'DM Sans', system-ui, sans-serif;
            background:
                radial-gradient(1200px 520px at 8% -10%, rgba(91, 124, 110, 0.14), transparent 55%),
                radial-gradient(900px 480px at 100% 0%, rgba(212, 101, 58, 0.10), transparent 50%),
                var(--k-bg);
            color: var(--k-ink);
            min-height: 100vh;
        }
        body.kitchen-lock { overflow: hidden; }
        .k-brand { font-family: 'Cormorant Garamond', Georgia, serif; }
        .k-complete,
        .k-accept {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            border: 0;
            border-radius: 999px;
            color: #fff;
            font-weight: 700;
            letter-spacing: 0.04em;
            cursor: pointer;
            transition: transform 0.15s ease, filter 0.15s ease;
        }
        .k-complete {
            background: linear-gradient(180deg, #6a8d7d 0%, var(--k-sage) 100%);
            font-size: 0.82rem;
            padding: 0.85rem 1.1rem;
            box-shadow: 0 10px 22px rgba(91, 124, 110, 0.22);
        }
        .k-accept {
            background: linear-gradient(180deg, #e07a52 0%, var(--k-terracotta) 100%);
            font-size: 0.95rem;
            padding: 0.95rem 1.1rem;
            box-shadow: 0 10px 22px rgba(212, 101, 58, 0.28);
        }
        .k-complete:hover,
        .k-accept:hover { filter: brightness(1.05); transform: translateY(-1px); }
        .k-complete:disabled,
        .k-accept:disabled { opacity: 0.6; transform: none; cursor: wait; }
        .k-overlay {
            position: fixed;
            inset: 0;
            z-index: 80;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
            background: #f6f1e8;
        }
        .k-dialog {
            width: 100%;
            max-width: 28rem;
            overflow: hidden;
            border: 1px solid var(--k-line);
            border-radius: 1.5rem;
            background: var(--k-paper);
            box-shadow: 0 24px 60px rgba(44, 38, 31, 0.14);
        }
        .k-dialog-head {
            background: var(--k-terracotta);
            color: #fff;
            padding: 1.2rem 1.5rem;
        }
        .k-dialog-head p { margin: 0; }
        .k-dialog-photos {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 2px;
            margin-bottom: 1rem;
            overflow: hidden;
            border-radius: 1rem;
            background: var(--k-line);
        }
        .k-dialog-photos img {
            display: block;
            width: 100%;
            aspect-ratio: 1;
            object-fit: cover;
            background: var(--k-gold-soft);
        }
        .k-dialog-body { padding: 1.35rem 1.5rem 1.5rem; }
    </style>
    @if(request()->routeIs('kitchen.board', 'kitchen.show'))
        <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('kitchenPreview', () => ({
                preview: null,
                open(src, name) {
                    this.preview = { src, name };
                },
            }));
            Alpine.data('kitchenAlarm', (initialIds, initialUnacked, initialIncoming, pingUrl, acceptAllUrl) => ({
                ids: (initialIds || []).map(Number),
                unacked: (initialUnacked || []).map(Number),
                incoming: initialIncoming || [],
                clock: '',
                ctx: null,
                loopTimer: null,
                unlocked: false,
                needsGesture: false,
                reloading: false,
                accepting: false,
                get current() {
                    return this.incoming[0] || null;
                },
                get currentPhotos() {
                    return ((this.current && this.current.photos) || []).slice(0, 4);
                },
                init() {
                    this.tick();
                    setInterval(() => this.tick(), 1000);
                    this.tryUnlock();
                    const unlock = () => {
                        this.unlock(true);
                        window.removeEventListener('pointerdown', unlock);
                        window.removeEventListener('keydown', unlock);
                    };
                    window.addEventListener('pointerdown', unlock);
                    window.addEventListener('keydown', unlock);
                    this.syncAlarm();
                    setInterval(() => this.poll(), 4000);
                },
                tick() {
                    this.clock = new Date().toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                },
                tryUnlock() {
                    try {
                        if (sessionStorage.getItem('kitchen_audio') === '1') this.unlock(false);
                    } catch (e) {}
                },
                unlock(fromGesture) {
                    try {
                        const AC = window.AudioContext || window.webkitAudioContext;
                        this.ctx = this.ctx || new AC();
                        this.ctx.resume();
                        try { sessionStorage.setItem('kitchen_audio', '1'); } catch (e) {}
                        this.unlocked = this.ctx.state === 'running' || fromGesture;
                        this.syncAlarm();
                    } catch (e) {
                        this.needsGesture = this.unacked.length > 0;
                    }
                },
                syncAlarm() {
                    if (this.unacked.length && this.unlocked) this.startLoop();
                    else this.stopLoop();
                    this.needsGesture = this.unacked.length > 0 && !this.unlocked && !this.incoming.length;
                    document.body.classList.toggle('kitchen-lock', this.incoming.length > 0);
                },
                startLoop() {
                    if (this.loopTimer) return;
                    this.chime();
                    this.loopTimer = setInterval(() => this.chime(), 1700);
                },
                stopLoop() {
                    if (this.loopTimer) {
                        clearInterval(this.loopTimer);
                        this.loopTimer = null;
                    }
                },
                applyAck(data) {
                    this.unacked = (data.unacked || []).map(Number);
                    this.incoming = data.incoming || [];
                    this.ids = (data.ids || this.ids).map(Number);
                    this.syncAlarm();
                    if (!this.incoming.length && !this.reloading) {
                        this.reloading = true;
                        window.location.reload();
                    }
                },
                async accept(order) {
                    if (!order || this.accepting) return;
                    this.unlock(true);
                    this.accepting = true;
                    try {
                        const data = await window.Velora.fetchJson(order.acceptUrl, { method: 'POST', body: {} });
                        this.applyAck(data);
                    } catch (e) {}
                    this.accepting = false;
                },
                async acceptAll() {
                    if (this.accepting) return;
                    this.unlock(true);
                    this.accepting = true;
                    try {
                        const data = await window.Velora.fetchJson(acceptAllUrl, { method: 'POST', body: {} });
                        this.applyAck(data);
                    } catch (e) {}
                    this.accepting = false;
                },
                async poll() {
                    try {
                        const data = await window.Velora.fetchJson(pingUrl);
                        const nextIds = (data.ids || []).map(Number);
                        this.unacked = (data.unacked || []).map(Number);
                        this.incoming = data.incoming || [];
                        this.syncAlarm();
                        if (nextIds.length !== this.ids.length && !this.incoming.length && !this.reloading) {
                            this.reloading = true;
                            window.location.reload();
                            return;
                        }
                        this.ids = nextIds;
                    } catch (e) {}
                },
                chime() {
                    try {
                        const ctx = this.ctx || new (window.AudioContext || window.webkitAudioContext)();
                        this.ctx = ctx;
                        if (ctx.state === 'suspended') ctx.resume();
                        const now = ctx.currentTime;
                        [[784, 0], [988, 0.13], [1175, 0.26], [1568, 0.4]].forEach(([freq, t]) => {
                            const o = ctx.createOscillator();
                            const g = ctx.createGain();
                            o.type = 'triangle';
                            o.frequency.value = freq;
                            g.gain.setValueAtTime(0.0001, now + t);
                            g.gain.exponentialRampToValueAtTime(0.22, now + t + 0.03);
                            g.gain.exponentialRampToValueAtTime(0.0001, now + t + 0.42);
                            o.connect(g);
                            g.connect(ctx.destination);
                            o.start(now + t);
                            o.stop(now + t + 0.45);
                        });
                    } catch (e) {}
                },
            }));
        });
        </script>
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
@php
    $kitchenLive = request()->routeIs('kitchen.board', 'kitchen.show');
    $kitchenService = app(\App\Services\KitchenService::class);
    $kitchenIds = $kitchenLive ? $kitchenService->visibleIds() : [];
    $kitchenUnacked = $kitchenLive ? $kitchenService->unackedIds() : [];
    $kitchenIncoming = $kitchenLive ? $kitchenService->incomingPayload() : [];
@endphp
<body class="kitchen-body"
      @if($kitchenLive)
          x-data="kitchenAlarm(@js($kitchenIds), @js($kitchenUnacked), @js($kitchenIncoming), @js(route('kitchen.ping')), @js(route('kitchen.accept-all')))"
      @endif
>
    @if($kitchenLive)
        <div class="k-overlay" x-show="needsGesture && !incoming.length" x-cloak style="display:flex" @click="unlock(true)">
            <button type="button" class="k-complete" style="max-width:16rem">Ses için dokunun</button>
        </div>

        <div class="k-overlay" x-show="incoming.length" x-cloak style="display:flex" role="dialog" aria-modal="true" aria-labelledby="kitchen-incoming-title">
            <div class="k-dialog" @click.stop>
                <div class="k-dialog-head">
                    <p style="font-size:11px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;opacity:.85">Yeni sipariş</p>
                    <p id="kitchen-incoming-title" class="k-brand" style="margin-top:.35rem;font-size:2rem;line-height:1">Sipariş geldi</p>
                    <p style="margin-top:.55rem;font-size:.9rem;opacity:.9" x-show="incoming.length > 1">
                        <span x-text="incoming.length"></span> bekleyen sipariş
                    </p>
                </div>
                <div class="k-dialog-body">
                    <div class="k-dialog-photos" x-show="currentPhotos.length" style="display:grid">
                        <template x-for="(photo, index) in currentPhotos" :key="index">
                            <img :src="photo.image" :alt="photo.name">
                        </template>
                    </div>
                    <p style="margin:0;font-family:ui-monospace,monospace;font-size:12px;color:var(--k-gold)" x-text="current && current.number"></p>
                    <p style="margin:.35rem 0 0;font-size:1.25rem;font-weight:650;color:var(--k-ink)" x-text="current && current.menu"></p>
                    <p style="margin:.25rem 0 0;font-size:.9rem;color:var(--k-muted)" x-text="current && (current.event || '')"></p>
                    <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:12px;margin-top:1rem">
                        <p style="margin:0;font-size:.9rem" x-text="current && current.when"></p>
                        <p style="margin:0;font-size:1.5rem;font-weight:650;color:var(--k-sage)">
                            <span x-text="current && current.persons"></span>
                            <span style="margin-left:.2rem;font-size:.85rem;font-weight:500;color:var(--k-muted)">kişi</span>
                        </p>
                    </div>
                    <button type="button" class="k-accept" style="margin-top:1.35rem" :disabled="accepting" @click="accept(current)">
                        Siparişi kabul et
                    </button>
                    <button type="button"
                            style="margin-top:.85rem;width:100%;border:0;background:transparent;color:var(--k-sage);font-size:.9rem;font-weight:600;cursor:pointer"
                            x-show="incoming.length > 1"
                            :disabled="accepting"
                            @click="acceptAll()">
                        Tümünü onayla
                    </button>
                </div>
            </div>
        </div>
    @endif

    @yield('body')
    @stack('scripts')
</body>
</html>
