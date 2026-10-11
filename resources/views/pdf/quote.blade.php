<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Angebot {{ $quote->quote_number }}</title>
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
        .dish { font-size: 10px; color: #555; white-space: pre-line; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td width="58%">
                <div class="label">Angebotsempfänger</div>
                <div class="recipient">
                    <strong>{{ trim($quote->first_name.' '.$quote->last_name) }}</strong><br>
                    @if ($quote->company)
                        {{ $quote->company }}<br>
                    @endif
                    @if ($quote->location)
                        {{ $quote->location }}<br>
                    @endif
                    {{ $quote->email }}
                    @if ($quote->phone)
                        <br>{{ $quote->phone }}
                    @endif
                </div>
            </td>
            <td width="42%" class="right">
                @if (! empty($company['logo']))
                    <img src="{{ $company['logo'] }}" alt="" style="height:52px;max-width:200px;margin-bottom:10px;"><br>
                @endif
                <div class="company">
                    @if (! empty($company['name']))
                        <strong>{{ $company['name'] }}</strong><br>
                    @endif
                    @if (! empty($company['street']))
                        {{ $company['street'] }}<br>
                    @endif
                    {{ trim(($company['postal_code'] ?? '').' '.($company['city'] ?? '')) }}<br>
                    @if (! empty($company['phone']))
                        Tel: {{ $company['phone'] }}<br>
                    @endif
                    @if (! empty($company['email']))
                        {{ $company['email'] }}<br>
                    @endif
                    @if (! empty($company['tax_id']))
                        Steuernummer: {{ $company['tax_id'] }}
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table class="meta">
        <tr>
            <th>Angebotsnummer</th>
            <th>Angebotsdatum</th>
            <th>Gültig bis</th>
        </tr>
        <tr>
            <td><strong>{{ $quote->quote_number }}</strong></td>
            <td>{{ optional($quote->created_at)->format('d.m.Y') }}</td>
            <td>{{ optional($quote->valid_until)->format('d.m.Y') ?: '—' }}</td>
        </tr>
    </table>

    @if ($quote->event_date || $quote->persons || $quote->location || $quote->eventType)
        <table class="meta">
            <tr>
                <th>Event</th>
                <th>Personen</th>
                <th>Ort</th>
            </tr>
            <tr>
                <td>
                    {{ $quote->eventType?->name }}
                    @if ($quote->event_date)
                        {{ optional($quote->event_date)->format('d.m.Y') }}
                        @if ($quote->event_time)
                            {{ \Carbon\Carbon::parse($quote->event_time)->format('H:i') }} Uhr
                        @endif
                    @endif
                </td>
                <td>{{ $quote->persons ?: '—' }}</td>
                <td>{{ $quote->location ?: '—' }}</td>
            </tr>
        </table>
    @endif

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
            @forelse ($quote->items as $item)
                <tr>
                    <td>
                        @php
                            $parts = preg_split("/\n+/", (string) $item->name, 2);
                        @endphp
                        {{ $parts[0] ?? $item->name }}
                        @if (! empty($parts[1]))
                            <div class="dish">{{ $parts[1] }}</div>
                        @endif
                    </td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">{{ number_format((float) $item->unit_price, 2, ',', '.') }} €</td>
                    <td class="right">{{ number_format((float) $item->line_total, 2, ',', '.') }} €</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="muted">Keine Positionen hinterlegt.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Netto</td>
            <td class="right">{{ number_format((float) $quote->subtotal, 2, ',', '.') }} €</td>
        </tr>
        @if ((float) $quote->delivery_fee > 0)
            <tr>
                <td>Lieferkosten</td>
                <td class="right">{{ number_format((float) $quote->delivery_fee, 2, ',', '.') }} €</td>
            </tr>
        @else
            <tr>
                <td>Lieferkosten (Kostenlos)</td>
                <td class="right">0,00 €</td>
            </tr>
        @endif
        @if ((float) $quote->discount > 0)
            <tr>
                <td>Rabatt</td>
                <td class="right">-{{ number_format((float) $quote->discount, 2, ',', '.') }} €</td>
            </tr>
        @endif
        @foreach ($quote->taxLines() as $taxLine)
            <tr>
                <td>MwSt. ({{ format_mwst_percent($taxLine['rate']) }}%)</td>
                <td class="right">{{ number_format((float) $taxLine['amount'], 2, ',', '.') }} €</td>
            </tr>
        @endforeach
        <tr class="grand">
            <td>Gesamtbetrag</td>
            <td class="right">{{ number_format((float) $quote->total, 2, ',', '.') }} €</td>
        </tr>
    </table>

    @if (! empty($company['iban']))
        <h2>Zahlungsinformationen</h2>
        <p>
            Bitte überweisen Sie den Betrag unter Angabe der Angebotsnummer.<br>
            IBAN: {{ $company['iban'] }}
        </p>
    @endif

    @if ($quote->notes)
        <h2>Bemerkungen</h2>
        <p>{{ $quote->notes }}</p>
    @endif

    <div class="footer">
        Dieses Angebot ist unverbindlich. Alle Preise in EUR.
        @if (! empty($company['tax_id']))
            Steuernummer: {{ $company['tax_id'] }}
        @endif
    </div>
</body>
</html>
