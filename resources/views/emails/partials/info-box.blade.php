<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:8px 0 20px;background:#f7f5f1;border-radius:12px;">
    <tr>
        <td style="padding:16px 18px;font-size:14px;line-height:1.85;color:#1b2430;">
            @foreach($rows as $row)
                @if(($row['value'] ?? '') !== '')
                    <strong>{{ $row['label'] }}:</strong> {{ $row['value'] }}<br>
                @endif
            @endforeach
        </td>
    </tr>
</table>
