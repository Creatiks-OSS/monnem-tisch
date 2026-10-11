@php
    $settings = \App\Services\AnalyticsSettings::get();
    $gaId = trim((string) $settings->ga_id);
    $gtmId = trim((string) $settings->gtm_id);
@endphp
<script>
window.dataLayer = window.dataLayer || [];
window.gtag = window.gtag || function () { window.dataLayer.push(arguments); };
window.gtag('consent', 'default', {
    analytics_storage: 'denied',
    ad_storage: 'denied',
    ad_user_data: 'denied',
    ad_personalization: 'denied',
    wait_for_update: 500
});
window.MonnemAnalytics = {
    gaId: @json($gaId),
    gtmId: @json($gtmId),
    debug: @json((bool) $settings->debug_mode),
    enabled: @json((bool) $settings->tracking_enabled),
    loaded: false,
    queue: [],
    apply(consent) {
        const analytics = !!(consent && consent.analytics);
        const marketing = !!(consent && consent.marketing);
        window.gtag('consent', 'update', {
            analytics_storage: analytics ? 'granted' : 'denied',
            ad_storage: marketing ? 'granted' : 'denied',
            ad_user_data: marketing ? 'granted' : 'denied',
            ad_personalization: marketing ? 'granted' : 'denied'
        });
        if (analytics && this.enabled) {
            this.load();
        }
    },
    load() {
        if (this.loaded || !this.enabled) {
            return;
        }
        if (!this.gtmId && !this.gaId) {
            return;
        }
        this.loaded = true;
        if (this.gtmId) {
            const s = document.createElement('script');
            s.async = true;
            s.src = 'https://www.googletagmanager.com/gtm.js?id=' + encodeURIComponent(this.gtmId);
            document.head.appendChild(s);
            window.dataLayer.push({ 'gtm.start': Date.now(), event: 'gtm.js' });
            return;
        }
        if (this.gaId) {
            const s = document.createElement('script');
            s.async = true;
            s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(this.gaId);
            document.head.appendChild(s);
            window.gtag('js', new Date());
            window.gtag('config', this.gaId, { debug_mode: this.debug });
        }
    },
    track(eventName, params) {
        if (!this.enabled) {
            return;
        }
        if (!this.loaded) {
            this.queue.push({ eventName: eventName, params: params });
            return;
        }
        window.gtag('event', eventName, params || {});
    },
    flushQueue() {
        const queued = this.queue;
        this.queue = [];
        queued.forEach(({ eventName, params }) => window.gtag('event', eventName, params || {}));
    }
};
document.addEventListener('velora:cookie-consent', function (event) {
    window.MonnemAnalytics.apply(event.detail || {});
    if (window.MonnemAnalytics.loaded) {
        window.MonnemAnalytics.flushQueue();
    }
});
document.addEventListener('DOMContentLoaded', function () {
    try {
        const raw = localStorage.getItem('velora_cookie_consent');
        if (raw) {
            window.MonnemAnalytics.apply(JSON.parse(raw));
            if (window.MonnemAnalytics.loaded) {
                window.MonnemAnalytics.flushQueue();
            }
        }
    } catch (e) {}

    document.querySelectorAll("a[href^='mailto:']").forEach(function (link) {
        link.addEventListener('click', function () {
            window.MonnemAnalytics.track('contact', { method: 'email' });
        });
    });
    document.querySelectorAll("a[href^='tel:']").forEach(function (link) {
        link.addEventListener('click', function () {
            window.MonnemAnalytics.track('contact', { method: 'phone' });
        });
    });
});
(function () {
    const gclid = new URLSearchParams(window.location.search).get('gclid');
    if (gclid) {
        try {
            localStorage.setItem('mt_gclid', JSON.stringify({ gclid: gclid, ts: Date.now() }));
        } catch (e) {}
    }
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form').forEach(function (form) {
            const input = form.querySelector('input[name="gclid"]');
            if (!input) {
                return;
            }
            form.addEventListener('submit', function () {
                try {
                    const raw = localStorage.getItem('mt_gclid');
                    if (raw) {
                        input.value = JSON.parse(raw).gclid || '';
                    }
                } catch (e) {}
            });
        });
    });
})();
</script>
