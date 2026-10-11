@php
    $phone = '';
    $email = '';
    $facebook = '';
    $instagram = '';
    $linkedin = '';
    try {
        $settings = app(\App\Services\SettingService::class);
        $phone = $settings->string('phone', '', 'contact');
        $email = $settings->string('email', 'info@monnemtisch.de', 'contact');
        $facebook = $settings->string('facebook', '', 'social');
        $instagram = $settings->string('instagram', '', 'social');
        $linkedin = $settings->string('linkedin', '', 'social');
    } catch (\Throwable $e) {
        $email = 'info@monnemtisch.de';
    }
@endphp

<footer class="border-t border-beige bg-white">
    <div class="container-velora py-14">
        <div class="max-w-sm">
            @if(site_logo_url())
                <a href="{{ route('home') }}" class="inline-block">
                    <img src="{{ site_logo_url() }}" alt="{{ setting('company_name', 'Monnemtisch Catering', 'general') }}" class="h-12 w-auto max-w-[240px] object-contain object-left">
                </a>
            @endif
            <p class="mt-4 text-sm leading-relaxed text-muted">
                Monnemtisch ist Ihr Caterer für Firmenfeiern, Hochzeiten und Events in Mannheim. Frisch gekocht, pünktlich geliefert, zum Festpreis.
            </p>
            <ul class="mt-4 space-y-2 text-sm text-muted">
                @if ($phone)
                    <li><a class="hover:text-bronze" href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a></li>
                @endif
                @if ($email)
                    <li><a class="hover:text-bronze" href="mailto:{{ $email }}">{{ $email }}</a></li>
                @endif
                <li>{{ setting('city', 'Mannheim', 'contact') }} &amp; Region</li>
            </ul>
            <div class="mt-5 flex items-center gap-3">
                <a href="{{ $instagram ?: '#' }}" class="rounded-full p-1.5 hover:text-bronze" @if($instagram) target="_blank" rel="noopener noreferrer" @endif aria-label="Instagram">
                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5Zm10 2H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3Zm-5 3.5A4.5 4.5 0 1 1 7.5 12 4.5 4.5 0 0 1 12 7.5Zm0 2A2.5 2.5 0 1 0 14.5 12 2.5 2.5 0 0 0 12 9.5Zm5.25-3.25a1 1 0 1 1-1 1 1 1 0 0 1 1-1Z"/></svg>
                </a>
                <a href="{{ $facebook ?: '#' }}" class="rounded-full p-1.5 hover:text-bronze" @if($facebook) target="_blank" rel="noopener noreferrer" @endif aria-label="Facebook">
                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M13.5 9H16V6h-2.5C11.6 6 11 7.3 11 9v2H9v3h2v7h3v-7h2.2l.3-3H14V9.4c0-.3.2-.4.5-.4Z"/></svg>
                </a>
                <a href="{{ $linkedin ?: '#' }}" class="rounded-full p-1.5 hover:text-bronze" @if($linkedin) target="_blank" rel="noopener noreferrer" @endif aria-label="LinkedIn">
                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M6.5 9H3.8v12h2.7V9ZM5.15 3.5A1.65 1.65 0 1 0 5.2 6.8 1.65 1.65 0 0 0 5.15 3.5ZM20.2 13.2c0-3.1-1.7-4.5-3.9-4.5a3.4 3.4 0 0 0-3 1.6h-.1V9H10.6c0 1.8 0 12 0 12h2.7v-6.7c0-.4 0-.7.1-1 .3-.7.9-1.4 2-1.4 1.4 0 2 1.1 2 2.6V21H20.2v-7.8Z"/></svg>
                </a>
            </div>
        </div>
        <div class="mt-10 grid gap-10 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <p class="text-sm font-bold text-charcoal">Catering</p>
                <ul class="mt-4 space-y-2 text-sm text-muted">
                    @foreach (\App\Models\CateringPackage::MENUS as $key => $item)
                        <li><a class="hover:text-bronze" href="{{ route('packages.index', $key) }}">{{ $item['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <p class="text-sm font-bold text-charcoal">Unternehmen</p>
                <ul class="mt-4 space-y-2 text-sm text-muted">
                    <li><a class="hover:text-bronze" href="{{ route('about') }}">Über uns</a></li>
                    <li><a class="hover:text-bronze" href="{{ route('gallery.index') }}">Galerie</a></li>
                    <li><a class="hover:text-bronze" href="{{ route('legal.allergene') }}">Allergene</a></li>
                </ul>
            </div>
            <div>
                <p class="text-sm font-bold text-charcoal">Service</p>
                <ul class="mt-4 space-y-2 text-sm text-muted">
                    <li><a class="hover:text-bronze" href="{{ route('quote.create') }}">Angebot erhalten</a></li>
                    <li><a class="hover:text-bronze" href="{{ route('reviews.index') }}">Bewertungen</a></li>
                    <li><a class="hover:text-bronze" href="{{ route('blog.index') }}">Magazin</a></li>
                    <li><a class="hover:text-bronze" href="{{ route('contact.show') }}">Kontakt</a></li>
                </ul>
            </div>
        </div>
        <div class="mt-12 flex flex-col gap-4 border-t border-beige pt-6 text-xs text-muted sm:flex-row sm:items-center sm:justify-between">
            <p>
                &copy; {{ date('Y') }} Monnemtisch Catering. Alle Rechte vorbehalten.
                <span class="author mt-1 block">
                    <a rel="author" href="{{ route('about') }}" class="hover:text-bronze">Monnemtisch Küche</a>
                    · Herausgeber: <cite>Anatolian Gourmet GmbH</cite>
                </span>
            </p>
            <ul class="flex flex-wrap gap-x-4 gap-y-2">
                <li><a class="hover:text-bronze" href="{{ route('legal.impressum') }}">Impressum</a></li>
                <li><a class="hover:text-bronze" href="{{ route('legal.datenschutz') }}">Datenschutz</a></li>
                <li><a class="hover:text-bronze" href="{{ route('legal.agb') }}">AGB</a></li>
                <li>
                    <button type="button" class="hover:text-bronze" onclick="window.dispatchEvent(new CustomEvent('velora:open-cookie-settings'))">
                        Cookie-Einstellungen
                    </button>
                </li>
            </ul>
        </div>
    </div>
    <div class="border-t border-beige bg-white">
        <div class="container-velora flex flex-wrap items-center justify-center gap-2 py-3 sm:py-4 sm:justify-between">
            <p class="text-[11px] font-medium uppercase tracking-[0.14em] text-muted">Sichere Zahlung</p>
            <ul class="flex flex-wrap items-center justify-center gap-2" aria-label="Zahlungsmethoden">
                <li>
                    <span class="inline-flex h-8 w-[52px] items-center justify-center rounded-md border border-beige bg-white" title="Visa" aria-label="Visa">
                        <span class="text-[11px] font-extrabold italic tracking-wide text-[#1A1F71]">VISA</span>
                    </span>
                </li>
                <li>
                    <span class="inline-flex h-8 w-[52px] items-center justify-center rounded-md border border-beige bg-white" title="Mastercard" aria-label="Mastercard">
                        <svg viewBox="0 0 38 24" class="h-5 w-8" aria-hidden="true">
                            <circle cx="14" cy="12" r="8" fill="#EB001B"/>
                            <circle cx="24" cy="12" r="8" fill="#F79E1B"/>
                            <path fill="#FF5F00" d="M19 5.7a8 8 0 0 0 0 12.6 8 8 0 0 0 0-12.6Z"/>
                        </svg>
                    </span>
                </li>
                <li>
                    <span class="inline-flex h-8 w-[52px] items-center justify-center rounded-md border border-beige bg-[#1F72CD]" title="American Express" aria-label="American Express">
                        <span class="text-[9px] font-bold tracking-wider text-white">AMEX</span>
                    </span>
                </li>
                <li>
                    <span class="inline-flex h-8 w-[52px] items-center justify-center rounded-md border border-beige bg-white px-1" title="PayPal" aria-label="PayPal">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" aria-hidden="true">
                            <path fill="#003087" d="M7.1 20.5H4.4c-.3 0-.5-.3-.5-.6L6.3 3.8c.1-.4.4-.7.8-.7h6.7c2.2 0 3.8.5 4.6 1.6.4.5.6 1 .7 1.6.1.6 0 1.3-.2 2.1l-.1.4c-.5 2.3-2.1 3.9-4.5 4.2-.5.1-1 .1-1.7.1H10c-.4 0-.7.3-.8.6l-.8 5.1c0 .2-.2.4-.4.4H7.1Z"/>
                            <path fill="#009CDE" d="M9.4 12.2c.1-.4.4-.6.8-.6h2.6c.6 0 1.1 0 1.6-.1 2.1-.3 3.5-1.7 4-3.8l.1-.4c1.5.1 2.6.6 3.3 1.5.9 1.1 1.1 2.7.7 4.7-.4 2-1.6 3.5-3.5 4.1-1 .4-2.2.5-3.5.5h-1.9c-.4 0-.7.3-.8.6l-.9 5.5c0 .3-.3.5-.5.5H8.3c-.3 0-.5-.3-.4-.6l1.5-9.9Z"/>
                            <path fill="#012169" d="M8.9 8.3c0-.2.2-.4.4-.4h3.3c.5 0 1 0 1.4.1.8.1 1.5.3 2 .7-.2 1.3-.9 2.2-2.1 2.6-.5.1-1.1.2-1.8.2H9.7c-.4 0-.7.3-.8.6L8.3 15c0 .1 0 .1-.1.1H6.6L8.9 8.3Z"/>
                        </svg>
                    </span>
                </li>
                <li>
                    <span class="inline-flex h-8 min-w-[52px] items-center justify-center rounded-md border border-beige bg-white px-1.5" title="Stripe" aria-label="Stripe">
                        <span class="text-[11px] font-semibold tracking-tight text-[#635BFF]">stripe</span>
                    </span>
                </li>
            </ul>
        </div>
    </div>
</footer>
