@extends('layouts.frontend')

@section('title', 'Über uns – Monnemtisch Catering Mannheim')
@section('meta_description', 'Monnemtisch Catering ist die Marke der Anatolian Gourmet GmbH in Mannheim. Buffets, Fingerfood, Frühstück und Grillcatering ab 10 Personen – frisch gekocht, fest im Preis.')
@section('canonical', route('about'))

@section('content')
@php
    $phone = $contact['phone'] ?? setting('phone', '', 'contact');
    $email = $contact['email'] ?? setting('email', 'info@monnemtisch.de', 'contact');
    $street = $contact['street'] ?? setting('street', 'H1, 10', 'contact');
    $postal = $contact['postal_code'] ?? setting('postal_code', '68159', 'contact');
    $city = $contact['city'] ?? setting('city', 'Mannheim', 'contact');
@endphp

<section class="bg-cream">
    <div class="container-velora py-10 md:py-16">
        <x-breadcrumb :items="[['label' => 'Über uns']]" />
        <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-14">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.16em] text-bronze">Anatolian Gourmet GmbH</p>
                <h1 class="mt-3 max-w-xl text-4xl font-bold leading-tight md:text-5xl">Catering, das Mannheim verbindet.</h1>
                <p class="mt-5 max-w-xl text-lg leading-relaxed text-charcoal/70">
                    Monnemtisch Catering ist unsere Marke für Events in Mannheim und der Region.
                    Wir kochen frisch, liefern pünktlich und halten den Preis pro Person transparent – ab 10 Personen.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <x-button :href="route('quote.create')" variant="primary">Angebot erhalten</x-button>
                    <x-button :href="route('packages.index', 'buffets')" variant="secondary">Menüs entdecken</x-button>
                </div>
            </div>
            <div class="overflow-hidden rounded-2xl shadow-soft">
                <img src="{{ velora_photo('kitchen') }}" alt="Küche von Monnemtisch Catering in Mannheim" class="aspect-[16/10] w-full object-cover">
            </div>
        </div>
    </div>
</section>

<section class="section-pad">
    <div class="container-velora grid gap-10 lg:grid-cols-12">
        <div class="lg:col-span-7">
            <h2 class="text-3xl font-bold md:text-4xl">Wer wir sind</h2>
            <div class="prose-velora mt-5 max-w-2xl text-charcoal/75">
                <p>
                    Hinter Monnemtisch steht die <strong>Anatolian Gourmet GmbH</strong> mit Sitz in der
                    {{ $street }}, {{ $postal }} {{ $city }}. Von hier aus planen, kochen und liefern wir
                    Buffets, Fingerfood, Frühstück und Grillcatering für Unternehmen, Hochzeiten und private Feiern.
                </p>
                <p>
                    Der Name ist Programm: Monnemtisch – ein Tisch für Mannheim. Wir verbinden orientalische und
                    internationale Küche mit dem, was Events hier brauchen: klare Pakete, freie Auswahl der Speisen
                    und eine Küche, die Allergene ernst nimmt.
                </p>
                <p>
                    Ob Büro-Frühstück, Messe-Empfang oder Familienfeier: Sie wählen das Menü, wir kümmern uns um
                    Produktion, Lieferung und den Ablauf vor Ort.
                </p>
            </div>
        </div>
        <aside class="lg:col-span-5">
            <div class="rounded-2xl border border-beige bg-cream p-6 sm:p-8">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-muted">Auf einen Blick</p>
                <dl class="mt-5 space-y-4 text-sm">
                    <div>
                        <dt class="font-semibold text-charcoal">Marke</dt>
                        <dd class="mt-0.5 text-charcoal/70">Monnemtisch Catering</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-charcoal">Unternehmen</dt>
                        <dd class="mt-0.5 text-charcoal/70">Anatolian Gourmet GmbH</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-charcoal">Standort</dt>
                        <dd class="mt-0.5 text-charcoal/70">{{ $street }}, {{ $postal }} {{ $city }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-charcoal">Mindestbestellung</dt>
                        <dd class="mt-0.5 text-charcoal/70">ab 10 Personen, Festpreis pro Person</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-charcoal">Lieferung</dt>
                        <dd class="mt-0.5 text-charcoal/70">in Mannheim kostenlos, außerhalb 1&nbsp;€ / km</dd>
                    </div>
                </dl>
            </div>
        </aside>
    </div>
</section>

<section class="section-pad bg-cream">
    <div class="container-velora">
        <h2 class="text-3xl font-bold md:text-4xl">Was wir kochen</h2>
        <p class="mt-3 max-w-2xl text-charcoal/70">Sechs Menüwelten, frei kombinierbar – Sie stellen das Paket zusammen, der Preis bleibt fest.</p>
        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach (\App\Models\CateringPackage::MENUS as $key => $menu)
                <a href="{{ route('packages.index', $key) }}" class="group rounded-2xl border border-beige bg-white p-5 shadow-soft transition hover:border-bronze/40">
                    <p class="text-lg font-bold text-charcoal group-hover:text-bronze">{{ $menu['label'] }}</p>
                    <p class="mt-2 text-sm leading-relaxed text-charcoal/65">{{ $menu['intro'] ?? '' }}</p>
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="section-pad">
    <div class="container-velora grid items-center gap-10 lg:grid-cols-2">
        <div class="overflow-hidden rounded-2xl shadow-soft">
            <img src="{{ velora_photo('table') }}" alt="Gedeckter Tisch von Monnemtisch Catering" class="aspect-[16/10] w-full object-cover">
        </div>
        <div>
            <h2 class="text-3xl font-bold md:text-4xl">So arbeiten wir</h2>
            <ul class="mt-6 space-y-4 text-charcoal/75">
                <li>
                    <strong class="text-charcoal">Frisch aus der Küche.</strong>
                    Speisen werden für Ihren Termin zubereitet – nicht auf Vorrat aufgewärmt.
                </li>
                <li>
                    <strong class="text-charcoal">Allergene gekennzeichnet.</strong>
                    Jede Speise trägt die gesetzlichen Codes. Die Übersicht finden Sie unter
                    <a href="{{ route('legal.allergene') }}" class="font-semibold text-bronze hover:underline">Allergene</a>.
                </li>
                <li>
                    <strong class="text-charcoal">Klarer Preis.</strong>
                    Pakete gelten pro Person. Lieferung in Mannheim ist inklusive, außerhalb rechnen wir die Kilometer.
                </li>
                <li>
                    <strong class="text-charcoal">Persönlich erreichbar.</strong>
                    Fragen zu Mengen, Diäten oder Aufbau klären wir vor der Produktion – nicht erst am Eventtag.
                </li>
            </ul>
        </div>
    </div>
</section>

<section class="section-pad bg-navy text-cream">
    <div class="container-velora grid gap-8 lg:grid-cols-2 lg:items-center">
        <div>
            <h2 class="text-3xl font-bold text-cream md:text-4xl">Sprechen Sie mit uns</h2>
            <p class="mt-4 max-w-xl text-cream/75">
                Für ein unverbindliches Angebot genügen Datum, Personenzahl und Anlass.
                Wir melden uns mit einem konkreten Vorschlag.
            </p>
            <div class="mt-6 space-y-2 text-sm text-cream/80">
                <p>{{ $street }}, {{ $postal }} {{ $city }}</p>
                @if ($email)
                    <p><a href="mailto:{{ $email }}" class="text-bronze hover:underline">{{ $email }}</a></p>
                @endif
                @if ($phone)
                    <p><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="text-bronze hover:underline">{{ $phone }}</a></p>
                @endif
            </div>
        </div>
        <div class="flex flex-wrap gap-3 lg:justify-end">
            <x-button :href="route('quote.create')" variant="primary">Angebot anfordern</x-button>
            <a href="{{ route('contact.show') }}" class="inline-flex items-center justify-center rounded-[0.7rem] border border-cream/30 px-[1.35rem] py-[0.85rem] text-[0.92rem] font-semibold text-cream hover:border-bronze hover:text-bronze">Kontakt</a>
        </div>
    </div>
</section>
@endsection
