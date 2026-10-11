<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') · Monnemtisch Catering</title>
    <x-favicon />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --admin-sidebar: #161412;
            --admin-gold: #c4a574;
            --admin-gold-soft: rgba(196, 165, 116, 0.16);
            --admin-bg: #f3eee6;
            --admin-card: #fffcf8;
            --admin-text: #161412;
            --admin-muted: #6f675c;
            --admin-line: rgba(22, 20, 18, 0.08);
            --admin-terracotta: #c45c26;
            --admin-sage: #3f6f4e;
        }
        body.admin-body {
            font-family: 'DM Sans', system-ui, sans-serif;
            background:
                radial-gradient(900px 420px at -10% -10%, rgba(196, 92, 38, 0.08), transparent 55%),
                radial-gradient(700px 380px at 110% 0%, rgba(63, 111, 78, 0.08), transparent 50%),
                var(--admin-bg);
            color: var(--admin-text);
        }
        body.admin-body h1, body.admin-body h2, body.admin-body h3 { font-family: 'Cormorant Garamond', Georgia, serif; }
        .admin-brand { font-family: 'Cormorant Garamond', Georgia, serif; }
        .admin-card { background: var(--admin-card); border: 1px solid var(--admin-line); border-radius: 1.25rem; box-shadow: 0 10px 30px rgba(22, 20, 18, 0.04); }
        .admin-input, .admin-select, .admin-textarea {
            width: 100%; border-radius: 0.9rem; border: 1px solid #e4d9c8; background: #fff; padding: 0.6rem 0.85rem; font-size: 0.875rem;
        }
        .admin-input:focus, .admin-select:focus, .admin-textarea:focus {
            outline: none; border-color: var(--admin-gold); box-shadow: 0 0 0 3px var(--admin-gold-soft);
        }
        .admin-label { display: block; margin-bottom: 0.35rem; font-size: 0.8rem; font-weight: 500; color: #3d3832; }
        .admin-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem;
            border-radius: 999px; padding: 0.6rem 1.15rem; font-size: 0.82rem; font-weight: 600; letter-spacing: 0.02em;
            background: var(--admin-sidebar); color: #fff; transition: 0.2s ease;
        }
        .admin-btn:hover { background: #2a2622; }
        .admin-btn-gold { background: var(--admin-gold); color: #161412; }
        .admin-btn-gold:hover { background: #d4b584; }
        .admin-btn-ghost { background: transparent; color: #3d3832; border: 1px solid #e4d9c8; }
        .admin-btn-ghost:hover { border-color: var(--admin-gold); color: var(--admin-terracotta); }
        .admin-chip { display: inline-flex; align-items: center; border-radius: 999px; padding: 0.2rem 0.65rem; font-size: 10px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; }
        [x-cloak] { display: none !important; }
        .admin-scroll::-webkit-scrollbar { width: 6px; }
        .admin-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 99px; }
        .rich-editor-btn {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 2rem; border-radius: 0.5rem; padding: 0.3rem 0.55rem;
            font-size: 0.75rem; font-weight: 600; color: #3d3832; background: #fff;
            border: 1px solid #e4d9c8;
        }
        .rich-editor-btn:hover { border-color: var(--admin-gold); color: var(--admin-terracotta); }
        .rich-editor-area h1 { font-size: 1.75rem; font-weight: 700; margin: 0.6rem 0 0.4rem; line-height: 1.2; }
        .rich-editor-area h2 { font-size: 1.4rem; font-weight: 700; margin: 0.55rem 0 0.35rem; line-height: 1.25; }
        .rich-editor-area h3 { font-size: 1.15rem; font-weight: 700; margin: 0.5rem 0 0.3rem; }
        .rich-editor-area p { margin: 0 0 0.75rem; }
        .rich-editor-area ul { list-style: disc; padding-left: 1.25rem; margin: 0 0 0.75rem; }
        .rich-editor-area ol { list-style: decimal; padding-left: 1.25rem; margin: 0 0 0.75rem; }
        .rich-editor-area blockquote { border-left: 3px solid var(--admin-gold); padding-left: 0.85rem; color: #6f675c; margin: 0 0 0.75rem; font-style: italic; }
        .rich-editor-area a { color: var(--admin-terracotta); text-decoration: underline; }
        .admin-notify-panel {
            max-height: min(28rem, calc(100dvh - 5.5rem));
        }
        @media (min-width: 1024px) {
            .admin-notify-panel {
                width: min(32rem, calc(100vw - 2.5rem));
            }
        }
    </style>
    @stack('styles')
</head>
<body class="admin-body antialiased" x-data="{ sidebarOpen: false }">
<div class="min-h-screen flex">
    @include('admin.partials.sidebar')

    <div class="flex-1 flex flex-col min-w-0">
        <header class="sticky top-0 z-30 border-b border-[var(--admin-line)] bg-[#fffcf8]/85 px-4 lg:px-8 h-[4.25rem] flex items-center justify-between gap-4 backdrop-blur-xl">
            <div class="flex items-center gap-3 min-w-0">
                <button type="button" class="lg:hidden p-2 rounded-xl hover:bg-stone-100" @click="sidebarOpen = true" aria-label="Menü">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="min-w-0">
                    <h1 class="text-[1.55rem] leading-none tracking-tight truncate">@yield('heading', 'Özet')</h1>
                    @hasSection('subheading')
                        <p class="mt-1 text-xs text-[var(--admin-muted)] truncate">@yield('subheading')</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2 sm:gap-3">
                <a href="{{ url('/') }}" target="_blank" class="hidden md:inline-flex admin-btn-ghost admin-btn !py-2 !px-3 text-xs">Siteyi gör</a>
                @include('admin.partials.notifications-bell')
                <div class="hidden sm:flex items-center gap-3 pl-2">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[var(--admin-gold-soft)] text-sm font-semibold text-[var(--admin-terracotta)]">
                        {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="hidden lg:flex flex-col items-start leading-tight">
                        <span class="text-sm font-medium">{{ auth()->user()->name }}</span>
                        <span class="text-[11px] text-[var(--admin-muted)]">{{ auth()->user()->roles->first()?->name }}</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="text-sm px-3 py-1.5 rounded-full border border-[#e4d9c8] hover:border-[var(--admin-gold)] hover:text-[var(--admin-terracotta)] transition">Çıkış</button>
                </form>
            </div>
        </header>

        <main class="flex-1 p-4 lg:p-8">
            @if(auth()->user()?->shouldPromptPasswordChange())
                <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    <strong>Güvenlik uyarısı:</strong> Hâlâ demo şifresini kullanıyorsunuz. Lütfen şifrenizi
                    <a href="{{ route('admin.users.edit', auth()->id()) }}" class="underline font-medium">kullanıcı profilinden</a> değiştirin.
                </div>
            @endif
            @include('admin.partials.flash')
            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
