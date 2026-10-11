@extends('layouts.frontend')

@section('title', 'Allergene & Zusatzstoffe – Monnemtisch Catering')
@section('meta_description', 'Allergenkennzeichnung bei Monnemtisch Catering: Gluten, Krebstiere, Eier, Fisch, Milch, Nüsse und weitere gesetzliche Hinweise zu unseren Speisen.')
@section('canonical', route('legal.allergene'))

@section('content')
<div class="container-velora py-10 md:py-14">
    <x-breadcrumb :items="[['label' => 'Allergene']]" />
    <article class="mx-auto max-w-3xl">
        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-bronze">Kennzeichnung</p>
        <h1 class="mt-2 text-4xl font-bold md:text-5xl">Allergene</h1>
        <p class="mt-4 text-lg leading-relaxed text-charcoal/70">
            Wir kennzeichnen unsere Speisen nach den gesetzlichen Hauptallergenen.
            Die Buchstaben stehen an jeder Speise, wenn Sie Ihr Menü zusammenstellen.
        </p>

        <div class="mt-10 overflow-hidden rounded-2xl border border-beige bg-white shadow-soft">
            <table class="w-full text-left text-sm">
                <thead class="bg-cream/80 text-[11px] uppercase tracking-[0.14em] text-muted">
                    <tr>
                        <th class="px-4 py-3 font-semibold sm:px-5">Code</th>
                        <th class="px-4 py-3 font-semibold sm:px-5">Allergen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-beige">
                    @foreach ($allergens as $code => $label)
                        <tr id="{{ $code }}">
                            <td class="px-4 py-3 align-top sm:px-5">
                                <span class="inline-flex min-w-8 items-center justify-center rounded-md bg-navy px-2 py-1 text-xs font-bold uppercase text-cream">{{ $code }}</span>
                            </td>
                            <td class="px-4 py-3 text-charcoal sm:px-5">{{ $label }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="prose-velora mt-10 text-charcoal/75">
            <p>
                Spuren anderer Allergene können trotz sorgfältiger Zubereitung nicht vollständig ausgeschlossen werden.
                Bitte nennen Sie uns Unverträglichkeiten bei der Bestellung – wir beraten Sie gerne.
            </p>
            <p class="mt-4">
                <a href="{{ route('contact.show') }}">Kontakt aufnehmen</a>
                oder
                <a href="{{ route('quote.create') }}">unverbindlich anfragen</a>.
            </p>
        </div>
    </article>
</div>
@endsection
