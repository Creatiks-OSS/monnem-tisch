@extends('layouts.frontend')

@section('title', 'Zahlung abgebrochen – Monnemtisch Catering')

@section('content')
<div class="container-velora py-16 md:py-24">
    <x-breadcrumb :items="[['label' => 'Zahlung abgebrochen']]" />
    <div class="mx-auto max-w-2xl rounded-2xl bg-white px-8 py-12 text-center shadow-soft md:px-12">
        <p class="text-sm font-semibold text-bronze">Zahlung</p>
        <h1 class="mt-3 text-4xl font-bold md:text-5xl">Zahlung abgebrochen</h1>
        <p class="mt-4 text-charcoal/70">Die Online-Zahlung wurde nicht abgeschlossen. Ihre Bestellung bleibt vorgemerkt – Sie können erneut zahlen oder uns kontaktieren.</p>
        @if($order)
            <p class="mt-6 text-sm text-muted">Bestellnummer: {{ $order->order_number }}</p>
        @endif
        <div class="mt-10 flex flex-wrap justify-center gap-3">
            <x-button :href="route('checkout.index')" variant="primary">Erneut versuchen</x-button>
            <x-button :href="route('contact.show')" variant="secondary">Kontakt</x-button>
        </div>
    </div>
</div>
@endsection
