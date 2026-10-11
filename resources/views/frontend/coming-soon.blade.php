<!DOCTYPE html>
<html lang="de" class="notranslate" translate="no">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#132A3A">
    <title>{{ $pageTitle }} – Monnemtisch Catering</title>
    <x-favicon />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;1,14..32,400&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-cream font-sans text-charcoal antialiased">
    <div class="relative flex min-h-screen flex-col">
        <div class="absolute inset-0 overflow-hidden">
            <img src="{{ velora_photo('hero') }}" alt="Festlich angerichtetes Catering-Buffet von Monnemtisch in Mannheim" class="h-full w-full object-cover" referrerpolicy="no-referrer">
            <div class="absolute inset-0 bg-charcoal/55"></div>
        </div>

        <main class="relative z-10 flex flex-1 flex-col items-center justify-center px-5 py-16 text-center text-white">
            @if(site_logo_url())
                <img src="{{ site_logo_url() }}" alt="{{ setting('company_name', 'Monnemtisch Catering', 'general') }}" class="mb-6 h-16 w-auto max-w-[280px] rounded-xl bg-white/95 px-3 py-2 object-contain">
            @else
                <p class="text-sm font-semibold tracking-[0.22em] uppercase text-white/80">Monnemtisch Catering</p>
            @endif
            <p class="mt-3 inline-flex items-center rounded-full bg-bronze px-3.5 py-1.5 text-[11px] font-semibold uppercase tracking-[0.16em]">
                In Vorbereitung
            </p>
            <h1 class="mt-6 max-w-2xl text-4xl font-bold leading-tight sm:text-5xl">{{ $pageTitle }}</h1>
            <p class="mt-5 max-w-lg text-base leading-relaxed text-white/80 sm:text-lg">{{ $pageMessage }}</p>

            <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
                @if ($email)
                    <a href="mailto:{{ $email }}" class="btn-primary !bg-white !text-charcoal !shadow-none hover:!bg-cream">{{ $email }}</a>
                @endif
                @if ($phone)
                    <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="btn-secondary !border-white/40 !bg-transparent !text-white hover:!bg-white/10">{{ $phone }}</a>
                @endif
            </div>
            <p class="mt-8 text-sm text-white/60">Mannheim &amp; Region</p>
        </main>
    </div>
</body>
</html>
