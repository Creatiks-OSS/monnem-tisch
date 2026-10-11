<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:4px 0 22px;">
    <tr>
        <td style="padding:4px 0;font-size:14px;color:#1b2430;">Zwischensumme</td>
        <td style="padding:4px 0;font-size:14px;text-align:right;color:#1b2430;">{{ format_price($order->subtotal) }}</td>
    </tr>
    @if((float) $order->delivery_fee > 0)
    <tr>
        <td style="padding:4px 0;font-size:14px;color:#1b2430;">Lieferkosten @if((float) ($order->delivery_km ?? 0) > 0)({{ number_format((float) $order->delivery_km, 1, ',', '.') }} km)@endif</td>
        <td style="padding:4px 0;font-size:14px;text-align:right;color:#1b2430;">{{ format_price($order->delivery_fee) }}</td>
    </tr>
    @else
    <tr>
        <td style="padding:4px 0;font-size:14px;color:#1b2430;">Lieferkosten (Kostenlos)</td>
        <td style="padding:4px 0;font-size:14px;text-align:right;color:#1b2430;">0,00 €</td>
    </tr>
    @endif
    @if((float) ($order->tip_amount ?? 0) > 0)
    <tr>
        <td style="padding:4px 0;font-size:14px;color:#1b2430;">Trinkgeld</td>
        <td style="padding:4px 0;font-size:14px;text-align:right;color:#1b2430;">{{ format_price($order->tip_amount) }}</td>
    </tr>
    @endif
    @if((float) $order->service_fee > 0)
    <tr>
        <td style="padding:4px 0;font-size:14px;color:#1b2430;">Service</td>
        <td style="padding:4px 0;font-size:14px;text-align:right;color:#1b2430;">{{ format_price($order->service_fee) }}</td>
    </tr>
    @endif
    @if((float) $order->discount > 0)
    <tr>
        <td style="padding:4px 0;font-size:14px;color:#1b2430;">Rabatt</td>
        <td style="padding:4px 0;font-size:14px;text-align:right;color:#1b2430;">− {{ format_price($order->discount) }}</td>
    </tr>
    @endif
    @foreach(tax_display_lines($order) as $taxLine)
    <tr>
        <td style="padding:4px 0;font-size:14px;color:#1b2430;">MwSt. ({{ format_mwst_percent($taxLine['rate']) }}%)</td>
        <td style="padding:4px 0;font-size:14px;text-align:right;color:#1b2430;">{{ format_price($taxLine['amount']) }}</td>
    </tr>
    @endforeach
    <tr>
        <td style="padding:10px 0 0;font-size:18px;font-weight:700;color:#1b2430;border-top:1px solid #1b2430;">Gesamt</td>
        <td style="padding:10px 0 0;font-size:18px;font-weight:700;text-align:right;color:#1b2430;border-top:1px solid #1b2430;">{{ format_price($order->total) }}</td>
    </tr>
</table>
