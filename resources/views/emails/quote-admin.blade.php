@php
    $name = trim(($quote->first_name ?? '').' '.($quote->last_name ?? '')) ?: 'Interessent';
@endphp
@extends('emails.layout')
@section('body')
    <p style="margin:8px 0 0;font-size:15px;line-height:1.65;color:#2c3544;">
        Es ist eine neue Angebotsanfrage eingegangen.
    </p>

    @include('emails.partials.meta-pills', ['items' => array_values(array_filter([
        ['label' => 'Anfrage', 'value' => $quote->quote_number],
        ['label' => 'Name', 'value' => $name],
        $quote->event_date ? ['label' => 'Event', 'value' => $quote->event_date->format('d.m.Y')] : null,
        $quote->total > 0 ? ['label' => 'Summe', 'value' => format_price($quote->total)] : null,
    ]))])

    <p style="margin:16px 0 0;font-size:14px;line-height:1.65;color:#2c3544;">
        {{ $quote->email }}
        @if($quote->phone)<br>{{ $quote->phone }}@endif
        @if($quote->company)<br>{{ $quote->company }}@endif
        @if($quote->location)<br>{{ $quote->location }}@endif
    </p>

    @if($quote->message)
        <p style="margin:18px 0 6px;font-size:13px;font-weight:700;color:#1b2430;">Nachricht</p>
        <p style="margin:0;font-size:14px;line-height:1.65;color:#2c3544;white-space:pre-wrap;">{{ $quote->message }}</p>
    @endif

    @if($quote->items && $quote->items->isNotEmpty())
        <p style="margin:22px 0 8px;font-size:13px;font-weight:700;letter-spacing:0.04em;text-transform:uppercase;color:#6b7280;">Positionen</p>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e6e0d6;border-radius:14px;background:#ffffff;">
            @foreach($quote->items as $item)
                <tr>
                    <td style="padding:12px 16px;border-bottom:1px solid #eeeae3;font-size:14px;color:#1b2430;">{{ $item->name }}</td>
                    <td style="padding:12px 16px;border-bottom:1px solid #eeeae3;font-size:14px;font-weight:700;text-align:right;color:#D89B2B;">{{ format_price($item->line_total) }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    @include('emails.partials.cta', ['href' => $adminUrl ?? '', 'label' => 'Im Admin öffnen'])
@endsection
