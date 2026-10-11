@php
    $name = trim(($order->billing_first_name ?? '').' '.($order->billing_last_name ?? '')) ?: 'Kunde';
    $shipStreet = trim(($order->shipping_street ?: $order->billing_street).' '.($order->shipping_house_number ?: $order->billing_house_number));
    $shipCity = trim(($order->shipping_postal_code ?: $order->billing_postal_code).' '.($order->shipping_city ?: $order->billing_city));
@endphp
@extends('emails.layout')
@section('body')
    <p style="margin:8px 0 0;font-size:15px;line-height:1.65;color:#2c3544;">
        {{ $intro ?? 'Es ist eine neue Bestellung eingegangen.' }}
    </p>

    @include('emails.partials.meta-pills', ['items' => array_values(array_filter([
        ['label' => 'Bestellung', 'value' => $order->order_number],
        ['label' => 'Kunde', 'value' => $name],
        $order->event_date ? ['label' => 'Event', 'value' => $order->event_date->format('d.m.Y')] : null,
        ['label' => 'Summe', 'value' => format_price($order->total)],
    ]))])

    <p style="margin:18px 0 0;font-size:14px;line-height:1.65;color:#2c3544;">
        <strong style="color:#1b2430;">{{ $name }}</strong><br>
        {{ $order->billing_email }}
        @if($order->billing_phone)<br>{{ $order->billing_phone }}@endif
        @if($shipStreet)<br>{{ $shipStreet }}, {{ $shipCity }}@endif
    </p>

    <p style="margin:22px 0 0;font-size:13px;font-weight:700;letter-spacing:0.04em;text-transform:uppercase;color:#6b7280;">Positionen</p>
    @include('emails.partials.order-lines', ['lines' => $lines])
    @include('emails.partials.order-totals', ['order' => $order])

    @include('emails.partials.cta', ['href' => $adminUrl ?? '', 'label' => 'Im Admin öffnen'])
@endsection
