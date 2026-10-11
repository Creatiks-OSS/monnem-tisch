<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:16px 0 6px;background:#f7f5f1;border-radius:14px;">
    <tr>
        @foreach($items as $item)
            <td width="{{ (int) floor(100 / max(count($items), 1)) }}%" valign="top" style="padding:14px 12px;text-align:center;">
                <p style="margin:0;font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#6b7280;">{{ $item['label'] }}</p>
                <p style="margin:6px 0 0;font-size:14px;font-weight:700;color:#1b2430;line-height:1.3;">{{ $item['value'] }}</p>
            </td>
        @endforeach
    </tr>
</table>
