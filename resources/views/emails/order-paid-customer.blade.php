@php
    $name = trim(($order->billing_first_name ?? '').' '.($order->billing_last_name ?? '')) ?: 'Gäste';
    $shipStreet = trim(($order->shipping_street ?: $order->billing_street).' '.($order->shipping_house_number ?: $order->billing_house_number));
    $shipCity = trim(($order->shipping_postal_code ?: $order->billing_postal_code).' '.($order->shipping_city ?: $order->billing_city));
    $payment = strtoupper((string) ($order->payment_method ?: '—'));
@endphp
@extends('emails.layout-order')
@section('body')
    <h1 style="margin:0 0 12px;font-size:22px;font-weight:700;color:#1b2430;">Bestellung bestätigt</h1>
    <p style="margin:0 0 18px;font-size:14px;line-height:1.6;color:#1b2430;">
        Hallo {{ $name }},<br>
        wir haben Ihre Bestellung bestätigt. Ihre Zahlung war erfolgreich – hier ist Ihre Übersicht.
    </p>

    @include('emails.partials.info-box', ['rows' => [
        ['label' => 'Bestellung', 'value' => $order->order_number],
        ['label' => 'Kunde', 'value' => $name],
        ['label' => 'E-Mail', 'value' => $order->billing_email],
        ['label' => 'Telefon', 'value' => $order->billing_phone],
        ['label' => 'Event', 'value' => $order->event_date ? $order->event_date->format('d.m.Y').($order->event_time ? ' · '.$order->event_time : '') : ''],
        ['label' => 'Personen', 'value' => (string) ((int) $order->persons)],
        ['label' => 'Zahlung', 'value' => $payment.' · bezahlt'],
    ]])

    @include('emails.partials.order-lines', ['lines' => $lines])
    @include('emails.partials.order-totals', ['order' => $order])

    <p style="margin:0 0 6px;font-size:14px;font-weight:700;color:#1b2430;">Adresse</p>
    <p style="margin:0;font-size:14px;line-height:1.6;color:#1b2430;">
        {{ $shipStreet }}<br>{{ $shipCity }}
    </p>
@endsection
