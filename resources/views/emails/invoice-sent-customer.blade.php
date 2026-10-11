@php
    $order = $invoice->order;
    $name = $invoice->billingName();
    if ($name === '' && $order) {
        $name = trim(($order->billing_first_name ?? '').' '.($order->billing_last_name ?? ''));
    }
    $name = $name !== '' ? $name : 'Gäste';
    $orderDate = $orderDateFormatted ?? optional($invoice->issued_at)->format('d.m.Y');
@endphp
@extends('emails.layout-order')
@section('body')
    <h1 style="margin:0 0 12px;font-size:22px;font-weight:700;color:#1b2430;">Ihre Rechnung</h1>
    <p style="margin:0 0 18px;font-size:14px;line-height:1.6;color:#1b2430;">
        Hallo {{ $name }},<br>
        @if($order)
            anbei erhalten Sie die Rechnung für Ihre Bestellung vom {{ $orderDate }}.
        @else
            anbei erhalten Sie Ihre Rechnung vom {{ $orderDate }}.
        @endif
        Die PDF-Datei finden Sie im Anhang dieser E-Mail.
    </p>

    @include('emails.partials.info-box', ['rows' => [
        ['label' => 'Rechnung', 'value' => $invoice->invoice_number],
        ['label' => 'Bestellung', 'value' => $order?->order_number],
        ['label' => 'Kunde', 'value' => $name],
        ['label' => 'Rechnungsdatum', 'value' => optional($invoice->issued_at)->format('d.m.Y')],
        ['label' => 'Event', 'value' => $order?->event_date ? $order->event_date->format('d.m.Y').($order->event_time ? ' · '.$order->event_time : '') : ''],
        ['label' => 'Gesamt', 'value' => format_price($invoice->total)],
    ]])

    @if(!empty($lines))
        @include('emails.partials.order-lines', ['lines' => $lines])
    @endif

    @if($order)
        @include('emails.partials.order-totals', ['order' => $order])
    @else
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:4px 0 22px;">
            <tr>
                <td style="padding:4px 0;font-size:14px;color:#1b2430;">Zwischensumme</td>
                <td style="padding:4px 0;font-size:14px;text-align:right;color:#1b2430;">{{ format_price($invoice->subtotal) }}</td>
            </tr>
            @foreach(tax_display_lines($invoice) as $taxLine)
            <tr>
                <td style="padding:4px 0;font-size:14px;color:#1b2430;">MwSt. ({{ format_mwst_percent($taxLine['rate']) }}%)</td>
                <td style="padding:4px 0;font-size:14px;text-align:right;color:#1b2430;">{{ format_price($taxLine['amount']) }}</td>
            </tr>
            @endforeach
            <tr>
                <td style="padding:10px 0 0;font-size:18px;font-weight:700;color:#1b2430;border-top:1px solid #1b2430;">Gesamt</td>
                <td style="padding:10px 0 0;font-size:18px;font-weight:700;text-align:right;color:#1b2430;border-top:1px solid #1b2430;">{{ format_price($invoice->total) }}</td>
            </tr>
        </table>
    @endif

    @if($invoice->is_manual)
        <p style="margin:0 0 18px;font-size:14px;line-height:1.6;color:#1b2430;">
            Bitte überweisen Sie den Betrag unter Angabe der Rechnungsnummer an:<br>
            <strong>{{ \App\Models\Invoice::MANUAL_ACCOUNT_HOLDER }}</strong><br>
            IBAN: {{ \App\Models\Invoice::MANUAL_IBAN }}
        </p>
    @endif

    <p style="margin:0;font-size:14px;line-height:1.6;color:#1b2430;">
        Bei Fragen antworten Sie einfach auf diese E-Mail.
    </p>
@endsection
