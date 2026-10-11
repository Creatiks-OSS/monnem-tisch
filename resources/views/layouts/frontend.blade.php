<!DOCTYPE html>
<html lang="de" class="notranslate" translate="no">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-analytics />
    <meta name="theme-color" content="#132A3A">
    <meta name="google" content="notranslate">

    @php
        $siteName = config('app.name', 'Monnemtisch Catering');
        $defaultTitle = __t('frontend.brand', 'Monnemtisch Catering').' – '.__t('frontend.tagline', 'Premium Catering für besondere Momente');
        $yieldedTitle = html_entity_decode(trim($__env->yieldContent('title')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $pageTitle = $yieldedTitle !== '' ? $yieldedTitle : $defaultTitle;
        $metaDescription = html_entity_decode(trim($__env->yieldContent('meta_description')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        app(\App\Services\SchemaService::class)->setPageMeta(
            $pageTitle,
            $metaDescription !== '' ? $metaDescription : __t('frontend.tagline', 'Premium Catering für besondere Momente'),
            trim($__env->yieldContent('og_image')) ?: velora_photo('hero'),
            trim($__env->yieldContent('canonical')) ?: url()->current()
        );
    @endphp

    <title>{{ $pageTitle }}</title>
    <x-favicon />

    <x-seo-meta
        :title="$pageTitle"
        :description="$metaDescription !== '' ? $metaDescription : __t('frontend.tagline', 'Premium Catering für besondere Momente')"
        :image="trim($__env->yieldContent('og_image')) ?: asset('images/placeholders/hero.svg')"
        :canonical="trim($__env->yieldContent('canonical')) ?: url()->current()"
        :type="trim($__env->yieldContent('og_type')) ?: 'website'"
    />

    @yield('seo')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,400;0,14..32,600;0,14..32,700;1,14..32,400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
    @yield('schema')
</head>
<body class="min-h-screen bg-cream text-charcoal font-sans antialiased" x-data>
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[100] focus:bg-cream focus:px-4 focus:py-2">
        Zum Inhalt springen
    </a>

    <x-header :cart-count="$cartCount ?? 0" :phone="$sitePhone ?? null" :transparent="$isHome ?? false" />

    @if (session('success') || session('error') || session('status'))
        <div class="container-velora pt-4">
            @if (session('success') || session('status'))
                <x-alert type="success">{{ session('success') ?: session('status') }}</x-alert>
            @endif
            @if (session('error'))
                <x-alert type="error">{{ session('error') }}</x-alert>
            @endif
        </div>
    @endif

    <main id="main">
        @yield('content')
    </main>

    <x-footer />

    <x-mobile-shop-bars :is-home="$isHome ?? false" />

    <x-scroll-top-button />
    <x-cookie-consent />

    @stack('scripts')
    @if (session('lead_tracked'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (!window.MonnemAnalytics) return;
                window.MonnemAnalytics.track('generate_lead', @json(session('lead_params', [])));
            });
        </script>
    @endif
    <x-json-ld :data="app(\App\Services\SchemaService::class)->document()" />
</body>
</html>
