<style>
.cc-root{position:fixed;inset:0;z-index:80;display:flex;align-items:flex-end;justify-content:center;padding:1rem;padding-bottom:calc(1rem + env(safe-area-inset-bottom,0px))}
@media (min-width:640px){.cc-root{align-items:center;padding:1.5rem}}
.cc-backdrop{position:absolute;inset:0;background:rgba(19,42,58,.46);backdrop-filter:blur(3px)}
.cc-panel{position:relative;width:100%;max-width:28.5rem;max-height:min(88dvh,40rem);overflow:auto;border-radius:1.25rem;background:#fff;box-shadow:0 24px 60px rgba(19,42,58,.22);padding:1.25rem 1.2rem 1.15rem;color:#132a3a}
@media (min-width:640px){.cc-panel{padding:1.5rem 1.45rem 1.35rem}}
.cc-kicker{margin:0;font-size:.68rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#d89b2b}
.cc-title{margin:.35rem 0 0;font-size:1.35rem;line-height:1.2;font-weight:700;color:#132a3a}
.cc-text{margin:.55rem 0 0;font-size:.875rem;line-height:1.55;color:#5c675f}
.cc-text a{color:#132a3a;font-weight:600;text-decoration:underline;text-underline-offset:2px}
.cc-text a:hover{color:#d89b2b}
.cc-actions{display:flex;flex-direction:column;gap:.55rem;margin-top:1.15rem}
.cc-btn{display:flex;width:100%;align-items:center;justify-content:center;min-height:2.75rem;border-radius:.75rem;padding:.7rem 1rem;font-size:.9rem;font-weight:700;line-height:1.2;cursor:pointer}
.cc-btn-gold{border:0;background:#d89b2b;color:#132a3a}
.cc-btn-gold:hover{background:#c08a22}
.cc-btn-navy{border:0;background:#132a3a;color:#fff}
.cc-btn-navy:hover{background:#1c3d50}
.cc-btn-ghost{border:0;background:transparent;color:#5c675f;font-weight:600;min-height:auto;padding:.35rem}
.cc-btn-ghost:hover{color:#132a3a}
.cc-details{margin-top:1rem;padding-top:1rem;border-top:1px solid #e4dcd0}
.cc-row{display:flex;align-items:flex-start;justify-content:space-between;gap:.85rem;padding:.7rem 0}
.cc-row + .cc-row{border-top:1px solid #f0ebe3}
.cc-row strong{display:block;font-size:.88rem;font-weight:700}
.cc-row span{display:block;margin-top:.15rem;font-size:.75rem;line-height:1.4;color:#6b746e}
.cc-switch{position:relative;flex-shrink:0;width:2.55rem;height:1.45rem;margin-top:.1rem}
.cc-switch input{position:absolute;inset:0;opacity:0;cursor:pointer}
.cc-switch i{display:block;height:100%;border-radius:999px;background:#d5cfc6;transition:background .2s ease}
.cc-switch i:after{content:"";position:absolute;top:.16rem;left:.16rem;width:1.12rem;height:1.12rem;border-radius:999px;background:#fff;box-shadow:0 1px 4px rgba(19,42,58,.2);transition:transform .2s ease}
.cc-switch input:checked + i{background:#d89b2b}
.cc-switch input:checked + i:after{transform:translateX(1.1rem)}
.cc-switch input:disabled{cursor:not-allowed}
.cc-switch input:disabled + i{background:#132a3a}
.cc-switch input:focus-visible + i{outline:2px solid #132a3a;outline-offset:2px}
</style>

<div
    x-data="cookieConsent"
    x-show="open"
    x-cloak
    x-transition.opacity.duration.200ms
    class="cc-root"
    role="dialog"
    aria-modal="true"
    aria-labelledby="cc-title"
>
    <div class="cc-backdrop" aria-hidden="true"></div>
    <div class="cc-panel" @keydown.escape.prevent>
        <p class="cc-kicker">Datenschutz</p>
        <p id="cc-title" class="cc-title">{{ __t('frontend.cookie.title', 'Cookie-Einstellungen') }}</p>
        <p class="cc-text">
            {{ __t('frontend.cookie.text', 'Wir verwenden Cookies, um die Website zu betreiben und – mit Ihrer Zustimmung – für Analyse und Marketing.') }}
            <a href="{{ route('legal.datenschutz') }}">{{ __t('frontend.cookie.privacy', 'Datenschutzerklärung') }}</a>
        </p>

        <div class="cc-actions">
            <button type="button" class="cc-btn cc-btn-gold" @click="acceptAll()">{{ __t('frontend.cookie.accept_all', 'Alle akzeptieren') }}</button>
            <button type="button" class="cc-btn cc-btn-navy" @click="acceptNecessary()">{{ __t('frontend.cookie.necessary_only', 'Nur notwendige') }}</button>
            <button type="button" class="cc-btn cc-btn-ghost" @click="showDetails = !showDetails">
                <span x-show="!showDetails">{{ __t('frontend.cookie.settings', 'Einstellungen anpassen') }}</span>
                <span x-show="showDetails">{{ __t('frontend.cookie.hide_settings', 'Einstellungen schließen') }}</span>
            </button>
        </div>

        <div class="cc-details" x-show="showDetails" x-collapse>
            <label class="cc-row">
                <p>
                    <strong>{{ __t('frontend.cookie.necessary', 'Notwendig') }}</strong>
                    <span>{{ __t('frontend.cookie.necessary_hint', 'Für den Betrieb der Website erforderlich und immer aktiv.') }}</span>
                </p>
                <span class="cc-switch">
                    <input type="checkbox" checked disabled>
                    <i aria-hidden="true"></i>
                </span>
            </label>
            <label class="cc-row">
                <p>
                    <strong>{{ __t('frontend.cookie.analytics', 'Analyse') }}</strong>
                    <span>{{ __t('frontend.cookie.analytics_hint', 'Hilft uns zu verstehen, wie die Website genutzt wird.') }}</span>
                </p>
                <span class="cc-switch">
                    <input type="checkbox" x-model="analytics">
                    <i aria-hidden="true"></i>
                </span>
            </label>
            <label class="cc-row">
                <p>
                    <strong>{{ __t('frontend.cookie.marketing', 'Marketing') }}</strong>
                    <span>{{ __t('frontend.cookie.marketing_hint', 'Für personalisierte Inhalte und Angebote.') }}</span>
                </p>
                <span class="cc-switch">
                    <input type="checkbox" x-model="marketing">
                    <i aria-hidden="true"></i>
                </span>
            </label>
            <button type="button" class="cc-btn cc-btn-navy" style="margin-top:.35rem" @click="saveCustom()">{{ __t('frontend.cookie.save', 'Auswahl speichern') }}</button>
        </div>
    </div>
</div>
