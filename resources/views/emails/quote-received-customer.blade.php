@php
    $name = trim(($quote->first_name ?? '').' '.($quote->last_name ?? '')) ?: 'Gäste';
@endphp
@extends('emails.layout')
@section('body')
    <p style="margin:8px 0 0;font-size:15px;line-height:1.65;color:#2c3544;">
        Hallo {{ $name }},
    </p>
    <p style="margin:10px 0 0;font-size:15px;line-height:1.65;color:#2c3544;">
        vielen Dank für Ihre Anfrage. Wir haben Ihre Angaben erhalten und melden uns zeitnah mit einem individuellen Angebot.
    </p>

    @include('emails.partials.meta-pills', ['items' => array_values(array_filter([
        ['label' => 'Anfrage', 'value' => $quote->quote_number],
        $quote->event_date ? ['label' => 'Event', 'value' => $quote->event_date->format('d.m.Y')] : null,
        $quote->persons ? ['label' => 'Personen', 'value' => (string) ((int) $quote->persons)] : null,
    ]))])

    @if($quote->message)
        <p style="margin:18px 0 6px;font-size:13px;font-weight:700;color:#1b2430;">Ihre Nachricht</p>
        <p style="margin:0;font-size:14px;line-height:1.65;color:#2c3544;white-space:pre-wrap;">{{ $quote->message }}</p>
    @endif

    <p style="margin:22px 0 0;font-size:14px;line-height:1.65;color:#2c3544;">
        Ihr Monnemtisch Catering Team
    </p>
    @include('emails.partials.cta', ['href' => $homeUrl ?? url('/'), 'label' => 'Zur Website'])
@endsection
