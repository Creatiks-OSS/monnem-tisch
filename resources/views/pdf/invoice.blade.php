<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Rechnung {{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a1a; margin: 0; padding: 32px; }
        h2 { font-size: 14px; margin: 28px 0 8px; color: #1a3a2a; border-bottom: 1px solid #c4a35a; padding-bottom: 4px; }
        .muted { color: #666; }
        .header { width: 100%; margin-bottom: 8px; }
        .header td { vertical-align: top; }
        .label { font-size: 10px; text-transform: uppercase; letter-spacing: .06em; color: #888; margin-bottom: 6px; }
        .recipient { font-size: 12px; line-height: 1.5; }
        .company { font-size: 11px; line-height: 1.5; color: #555; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.items th { text-align: left; background: #f3efe6; padding: 8px; border-bottom: 1px solid #ddd; font-size: 11px; text-transform: uppercase; }
        table.items td { padding: 8px; border-bottom: 1px solid #eee; vertical-align: top; }
        .right { text-align: right; }
        .totals { width: 280px; margin-left: auto; margin-top: 16px; }
        .totals td { padding: 4px 0; }
        .totals .grand { font-weight: bold; font-size: 14px; border-top: 2px solid #1a3a2a; padding-top: 8px; }
        .footer { margin-top: 40px; font-size: 10px; color: #777; border-top: 1px solid #eee; padding-top: 12px; }
        table.meta { width: 100%; border-collapse: collapse; margin: 24px 0 0; }
        table.meta th { text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: .04em; color: #888; font-weight: normal; padding: 0 12px 4px 0; }
        table.meta td { font-size: 12px; padding: 0 12px 0 0; }
    </style>
</head>
<body>
    @php
        $order = $invoice->order;
        $customer = $invoice->customer;
    @endphp

    <table class="header">
        <tr>
            <td width="58%">
                <div class="label">Rechnungsempfänger</div>
                <div class="recipient">
                    @if($order)
                        <strong>{{ trim(($order->billing_first_name ?? '').' '.($order->billing_last_name ?? '')) }}</strong><br>
                        @if($order->billing_company){{ $order->billing_company }}<br>@endif
                        {{ $order->billing_street }} {{ $order->billing_house_number }}<br>
                        {{ $order->billing_postal_code }} {{ $order->billing_city }}<br>
                        {{ $order->billing_email }}
                    @elseif($invoice->is_manual)
                        <strong>{{ $invoice->billingName() }}</strong><br>
                        @if($invoice->billing_company){{ $invoice->billing_company }}<br>@endif
                        {{ $invoice->billing_street }} {{ $invoice->billing_house_number }}<br>
                        {{ $invoice->billing_postal_code }} {{ $invoice->billing_city }}<br>
                        {{ $invoice->billing_email }}
                    @elseif($customer)
                        <strong>{{ $customer->first_name }} {{ $customer->last_name }}</strong><br>
                        @if($customer->company){{ $customer->company }}<br>@endif
                        {{ $customer->email }}
                    @else
                        <span class="muted">Kein Empfänger hinterlegt.</span>
                    @endif
                </div>
            </td>
            <td width="42%" class="right">
                @if(!empty($company['logo']))
                    <img src="{{ $company['logo'] }}" alt="" style="height:52px;max-width:200px;margin-bottom:10px;"><br>
                @endif
                <div class="company">
                    @if(!empty($company['name']))<strong>{{ $company['name'] }}</strong><br>@endif
                    @if(!empty($company['street'])){{ $company['street'] }}<br>@endif
                    {{ trim(($company['postal_code'] ?? '').' '.($company['city'] ?? '')) }}<br>
                    @if(!empty($company['phone']))Tel: {{ $company['phone'] }}<br>@endif
                    @if(!empty($company['email'])){{ $company['email'] }}<br>@endif
                    @if(!empty($company['tax_id']))Steuernummer: {{ $company['tax_id'] }}@endif
                </div>
            </td>
        </tr>
    </table>

    <table class="meta">
        <tr>
            <th>Rechnungsnummer</th>
            <th>Rechnungsdatum</th>
            @if($order)
                <th>Bestellung</th>
            @endif
        </tr>
        <tr>
            <td><strong>{{ $invoice->invoice_number }}</strong></td>
            <td>{{ optional($invoice->issued_at ?? $invoice->created_at)->format('d.m.Y') }}</td>
            @if($order)
                <td>{{ $order->order_number }}</td>
            @endif
        </tr>
    </table>

    <h2>Positionen</h2>
    <table class="items">
        <thead>
            <tr>
                <th>Bezeichnung</th>
                <th class="right">Menge</th>
                <th class="right">Einzelpreis</th>
                <th class="right">Summe</th>
            </tr>
        </thead>
        <tbody>
            @if($order)
                @foreach($order->items as $item)
                    @if(in_array($item->options['kind'] ?? $item->sku, ['delivery', 'tip', 'DELIVERY', 'TIP'], true))
                        @continue
                    @endif
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td class="right">{{ $item->quantity }}</td>
                        <td class="right">{{ number_format((float) $item->unit_price, 2, ',', '.') }} €</td>
                        <td class="right">{{ number_format((float) $item->line_total, 2, ',', '.') }} €</td>
                    </tr>
                @endforeach
                @foreach($order->extras as $extra)
                    <tr>
                        <td>{{ $extra->name }}</td>
                        <td class="right">{{ $extra->quantity }}</td>
                        <td class="right">{{ number_format((float) $extra->unit_price, 2, ',', '.') }} €</td>
                        <td class="right">{{ number_format((float) $extra->line_total, 2, ',', '.') }} €</td>
                    </tr>
                @endforeach
            @elseif($invoice->is_manual)
                @foreach($invoice->line_items ?? [] as $item)
                    <tr>
                        <td>{{ $item['name'] ?? '' }}</td>
                        <td class="right">{{ rtrim(rtrim(number_format((float) ($item['quantity'] ?? 0), 2, ',', '.'), '0'), ',') }}</td>
                        <td class="right">{{ number_format((float) ($item['unit_price'] ?? 0), 2, ',', '.') }} €</td>
                        <td class="right">{{ number_format((float) ($item['line_total'] ?? 0), 2, ',', '.') }} €</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" class="muted">Keine Positionen verfügbar.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Netto</td>
            <td class="right">{{ number_format((float) $invoice->subtotal, 2, ',', '.') }} €</td>
        </tr>
        @if($order && (float) $order->delivery_fee > 0)
        <tr>
            <td>Lieferkosten @if((float) ($order->delivery_km ?? 0) > 0)({{ number_format((float) $order->delivery_km, 1, ',', '.') }} km)@endif</td>
            <td class="right">{{ number_format((float) $order->delivery_fee, 2, ',', '.') }} €</td>
        </tr>
        @elseif($order)
        <tr>
            <td>Lieferkosten (Kostenlos)</td>
            <td class="right">0,00 €</td>
        </tr>
        @endif
        @if($order && (float) ($order->tip_amount ?? 0) > 0)
        <tr>
            <td>Trinkgeld</td>
            <td class="right">{{ number_format((float) $order->tip_amount, 2, ',', '.') }} €</td>
        </tr>
        @endif
        @foreach(tax_display_lines($order ?? $invoice) as $taxLine)
        <tr>
            <td>MwSt. ({{ format_mwst_percent($taxLine['rate']) }}%)</td>
            <td class="right">{{ number_format((float) $taxLine['amount'], 2, ',', '.') }} €</td>
        </tr>
        @endforeach
        <tr class="grand">
            <td>Gesamtbetrag</td>
            <td class="right">{{ number_format((float) $invoice->total, 2, ',', '.') }} €</td>
        </tr>
    </table>

    @php
        $paymentName = $invoice->is_manual ? \App\Models\Invoice::MANUAL_ACCOUNT_HOLDER : null;
        $paymentIban = $invoice->is_manual ? \App\Models\Invoice::MANUAL_IBAN : ($company['iban'] ?? '');
    @endphp
    @if(!empty($paymentIban))
        <h2>Zahlungsinformationen</h2>
        <p>
            Bitte überweisen Sie den Betrag unter Angabe der Rechnungsnummer.<br>
            @if($paymentName)Kontoinhaber: {{ $paymentName }}<br>@endif
            IBAN: {{ $paymentIban }}
        </p>
    @endif

    @if($invoice->notes)
        <h2>Bemerkungen</h2>
        <p>{{ $invoice->notes }}</p>
    @endif

    <div class="footer">
        Vielen Dank für Ihr Vertrauen.
        @if(!empty($company['tax_id'])) Steuernummer: {{ $company['tax_id'] }}@endif
    </div>
</body>
</html>
