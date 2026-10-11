import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import intersect from '@alpinejs/intersect';

Alpine.plugin(collapse);
Alpine.plugin(intersect);

const COOKIE_KEY = 'velora_cookie_consent';
const FAVORITES_KEY = 'velora_favorites';

window.Velora = {
    getCsrf() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    },

    async fetchJson(url, options = {}) {
        const headers = {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': this.getCsrf(),
            ...(options.headers || {}),
        };

        if (options.body && !(options.body instanceof FormData)) {
            headers['Content-Type'] = 'application/json';
            if (typeof options.body === 'object') {
                options.body = JSON.stringify(options.body);
            }
        }

        const response = await fetch(url, { ...options, headers, credentials: 'same-origin' });
        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            const error = new Error(data.message || 'Anfrage fehlgeschlagen');
            error.status = response.status;
            error.data = data;
            throw error;
        }

        return data;
    },

    formatMoney(value, currency = 'EUR') {
        const amount = Number(value || 0);
        return new Intl.NumberFormat('de-DE', {
            style: 'currency',
            currency,
        }).format(amount);
    },

    getCookieConsent() {
        try {
            const raw = localStorage.getItem(COOKIE_KEY);
            return raw ? JSON.parse(raw) : null;
        } catch {
            return null;
        }
    },

    setCookieConsent(consent) {
        localStorage.setItem(
            COOKIE_KEY,
            JSON.stringify({
                ...consent,
                updated_at: new Date().toISOString(),
            })
        );
        window.dispatchEvent(new CustomEvent('velora:cookie-consent', { detail: consent }));
    },

    getFavorites() {
        try {
            const raw = localStorage.getItem(FAVORITES_KEY);
            const parsed = raw ? JSON.parse(raw) : [];
            return Array.isArray(parsed) ? parsed.map(String) : [];
        } catch {
            return [];
        }
    },

    isFavorite(id) {
        return this.getFavorites().includes(String(id));
    },

    toggleFavorite(id) {
        const key = String(id);
        let list = this.getFavorites();
        if (list.includes(key)) {
            list = list.filter((item) => item !== key);
        } else {
            list.push(key);
        }
        localStorage.setItem(FAVORITES_KEY, JSON.stringify(list));
        window.dispatchEvent(new CustomEvent('velora:favorites', { detail: list }));
        return list.includes(key);
    },

    smoothScrollTo(hash) {
        if (!hash || hash === '#') return;
        const el = document.querySelector(hash);
        if (!el) return;
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    },
};

document.addEventListener('click', (event) => {
    const link = event.target.closest('a[href^="#"]');
    if (!link) return;
    const href = link.getAttribute('href');
    if (!href || href === '#') return;
    const target = document.querySelector(href);
    if (!target) return;
    event.preventDefault();
    window.Velora.smoothScrollTo(href);
});

document.addEventListener('alpine:init', () => {
    Alpine.data('cookieConsent', () => ({
        open: false,
        showDetails: false,
        necessary: true,
        analytics: false,
        marketing: false,
        init() {
            const existing = window.Velora.getCookieConsent();
            if (!existing) {
                this.open = true;
            } else {
                this.analytics = !!existing.analytics;
                this.marketing = !!existing.marketing;
            }
            window.addEventListener('velora:open-cookie-settings', () => {
                const current = window.Velora.getCookieConsent();
                if (current) {
                    this.analytics = !!current.analytics;
                    this.marketing = !!current.marketing;
                }
                this.showDetails = true;
                this.open = true;
            });
            this.$watch('open', (value) => {
                document.documentElement.classList.toggle('overflow-hidden', !!value);
            });
        },
        acceptAll() {
            this.save({ necessary: true, analytics: true, marketing: true });
        },
        acceptNecessary() {
            this.save({ necessary: true, analytics: false, marketing: false });
        },
        saveCustom() {
            this.save({
                necessary: true,
                analytics: this.analytics,
                marketing: this.marketing,
            });
        },
        save(consent) {
            window.Velora.setCookieConsent(consent);
            this.open = false;
        },
    }));

    Alpine.data('siteHeader', () => ({
        scrolled: false,
        mobileOpen: false,
        searchOpen: false,
        init() {
            const onScroll = () => {
                this.scrolled = window.scrollY > 40;
            };
            onScroll();
            window.addEventListener('scroll', onScroll, { passive: true });
        },
        closeMobile() {
            this.mobileOpen = false;
        },
    }));

    Alpine.data('reveal', () => ({
        shown: false,
    }));

    Alpine.data('favoritesButton', (productId) => ({
        active: window.Velora.isFavorite(productId),
        toggle() {
            this.active = window.Velora.toggleFavorite(productId);
        },
    }));

    Alpine.data('addToCart', (productId, defaults = {}) => ({
        productId,
        quantity: defaults.quantity || 1,
        persons: defaults.persons || 1,
        loading: false,
        message: '',
        error: '',
        async submit() {
            this.loading = true;
            this.message = '';
            this.error = '';
            try {
                const data = await window.Velora.fetchJson('/warenkorb/add', {
                    method: 'POST',
                    body: {
                        product_id: this.productId,
                        quantity: Number(this.quantity) || 1,
                        persons: Number(this.persons) || 1,
                        options: defaults.options || [],
                    },
                });
                this.message = data.message || 'Hinzugefügt';
                window.dispatchEvent(
                    new CustomEvent('velora:cart-updated', {
                        detail: { cart: data.cart, totals: data.totals },
                    })
                );
            } catch (e) {
                this.error = e.data?.message || e.message || 'Fehler beim Hinzufügen';
            } finally {
                this.loading = false;
            }
        },
    }));

    Alpine.data('testimonialsSlider', (count = 1) => ({
        index: 0,
        count,
        timer: null,
        init() {
            if (this.count > 1) {
                this.timer = setInterval(() => this.next(), 6000);
            }
        },
        next() {
            this.index = (this.index + 1) % this.count;
        },
        prev() {
            this.index = (this.index - 1 + this.count) % this.count;
        },
        go(i) {
            this.index = i;
        },
        destroy() {
            if (this.timer) clearInterval(this.timer);
        },
    }));

    Alpine.data('galleryLightbox', (images = [], alts = []) => ({
        open: false,
        index: 0,
        images,
        alts,
        show(i) {
            this.index = i;
            this.open = true;
            document.body.classList.add('overflow-hidden');
        },
        close() {
            this.open = false;
            document.body.classList.remove('overflow-hidden');
        },
        next() {
            this.index = (this.index + 1) % this.images.length;
        },
        prev() {
            this.index = (this.index - 1 + this.images.length) % this.images.length;
        },
    }));

    Alpine.data('faqAccordion', () => ({
        openId: null,
        toggle(id) {
            this.openId = this.openId === id ? null : id;
        },
    }));

    Alpine.data('configuratorWizard', (payload = {}) => ({
        step: 1,
        maxStep: 6,
        eventTypeId: payload.eventTypeId || null,
        persons: payload.persons || 20,
        packageId: payload.packageId || null,
        selectedProducts: {},
        selectedExtras: {},
        postalCode: '',
        city: '',
        street: '',
        houseNumber: '',
        date: '',
        time: '',
        totals: payload.totals || null,
        calculating: false,
        message: '',
        error: '',
        blockedDates: payload.blockedDates || [],
        calculateUrl: payload.calculateUrl || '/konfigurator/calculate',
        cartAddUrl: payload.cartAddUrl || '/warenkorb/add',
        next() {
            if (this.step < this.maxStep) this.step += 1;
            if (this.step === this.maxStep) this.calculate();
        },
        prev() {
            if (this.step > 1) this.step -= 1;
        },
        go(step) {
            this.step = step;
        },
        toggleProduct(id) {
            const key = String(id);
            if (this.selectedProducts[key]) {
                delete this.selectedProducts[key];
            } else {
                this.selectedProducts[key] = { product_id: Number(id), quantity: 1, persons: this.persons };
            }
            this.selectedProducts = { ...this.selectedProducts };
            this.calculate();
        },
        toggleExtra(id) {
            const key = String(id);
            if (this.selectedExtras[key]) {
                delete this.selectedExtras[key];
            } else {
                this.selectedExtras[key] = { extra_id: Number(id), quantity: 1 };
            }
            this.selectedExtras = { ...this.selectedExtras };
            this.calculate();
        },
        payload() {
            return {
                persons: Number(this.persons) || 1,
                postal_code: this.postalCode || null,
                package_id: this.packageId || null,
                items: Object.values(this.selectedProducts),
                extras: Object.values(this.selectedExtras),
            };
        },
        async calculate() {
            this.calculating = true;
            this.error = '';
            try {
                const data = await window.Velora.fetchJson(this.calculateUrl, {
                    method: 'POST',
                    body: this.payload(),
                });
                this.totals = data.totals;
            } catch (e) {
                this.error = e.data?.message || e.message || 'Berechnung fehlgeschlagen';
            } finally {
                this.calculating = false;
            }
        },
        async addSelectionsToCart() {
            this.message = '';
            this.error = '';
            try {
                for (const item of Object.values(this.selectedProducts)) {
                    await window.Velora.fetchJson(this.cartAddUrl, {
                        method: 'POST',
                        body: {
                            product_id: item.product_id,
                            quantity: item.quantity || 1,
                            persons: this.persons,
                        },
                    });
                }
                for (const extra of Object.values(this.selectedExtras)) {
                    await window.Velora.fetchJson('/warenkorb/extras', {
                        method: 'POST',
                        body: {
                            extra_id: extra.extra_id,
                            quantity: extra.quantity || 1,
                        },
                    });
                }
                await window.Velora.fetchJson('/warenkorb/event', {
                    method: 'POST',
                    body: {
                        event_type_id: this.eventTypeId,
                        date: this.date || null,
                        time: this.time || null,
                        persons: this.persons,
                    },
                });
                if (this.postalCode) {
                    await window.Velora.fetchJson('/warenkorb/delivery', {
                        method: 'POST',
                        body: {
                            postal_code: this.postalCode,
                            city: this.city,
                            street: this.street,
                            house_number: this.houseNumber,
                        },
                    });
                }
                this.message = 'Auswahl wurde in den Warenkorb übernommen.';
                window.location.href = '/checkout';
            } catch (e) {
                this.error = e.data?.message || e.message || 'Übernahme fehlgeschlagen';
            }
        },
    }));
});

window.Alpine = Alpine;
Alpine.start();
