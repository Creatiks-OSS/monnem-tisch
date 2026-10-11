<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:8px 0 18px;">
@foreach($lines as $line)
    <tr>
        <td style="padding:0 0 18px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="56" valign="top" style="padding:0 12px 0 0;">
                        @php
                            $lineImage = mail_image_src($message ?? null, $line['image'] ?? null, 'line-'.$loop->index.'.jpg');
                        @endphp
                        @if($lineImage)
                            <img src="{{ $lineImage }}" alt="{{ $line['name'] }}" width="48" height="48" style="display:block;width:48px;height:48px;border:0;border-radius:6px;background:#eeeae3;">
                        @endif
                    </td>
                    <td valign="top">
                        <p style="margin:0;font-size:15px;font-weight:700;color:#1b2430;">{{ $line['name'] }}</p>
                    </td>
                    <td valign="top" align="right" style="white-space:nowrap;padding-left:12px;">
                        <p style="margin:0;font-size:15px;font-weight:700;color:#1b2430;">{{ $line['amount'] }}</p>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="2" style="padding-top:6px;font-size:13px;line-height:1.7;color:#6b7280;">
                        <strong style="color:#1b2430;">Menge:</strong> {{ $line['qty'] }}<br>
                        @forelse(($line['slots'] ?? []) as $slot)
                            @if($slot['label'] !== '')
                                <strong style="color:#1b2430;">{{ $slot['label'] }}:</strong> {{ $slot['items'] }}<br>
                            @else
                                {{ $slot['items'] }}<br>
                            @endif
                        @empty
                            @foreach($line['details'] ?? [] as $detail)
                                {{ $detail }}<br>
                            @endforeach
                        @endforelse
                    </td>
                </tr>
            </table>
        </td>
    </tr>
@endforeach
</table>
