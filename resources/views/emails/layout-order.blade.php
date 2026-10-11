<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $headerTitle ?? ($company['name'] ?? 'Monnemtisch Catering') }}</title>
</head>
<body style="margin:0;padding:0;background:#ffffff;font-family:Arial,Helvetica,sans-serif;color:#1b2430;-webkit-text-size-adjust:100%;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;">{{ $preheader ?? '' }}</div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#ffffff;">
        <tr>
            <td style="padding:20px 24px 12px;">
                <a href="{{ $homeUrl ?? url('/') }}" style="text-decoration:none;">
                    @php $logoSrc = site_logo_mail_src($message ?? null); @endphp
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}" alt="{{ $company['name'] ?? 'Monnemtisch Catering' }}" width="160" style="display:block;max-width:160px;height:auto;border:0;">
                    @else
                        <span style="display:block;font-size:18px;font-weight:700;color:#1b2430;">Monnemtisch</span>
                        <span style="display:block;margin-top:2px;font-size:11px;color:#6b7280;">Catering Mannheim</span>
                    @endif
                </a>
            </td>
        </tr>
        <tr>
            <td style="background:#1b2430;padding:16px 24px;">
                <p style="margin:0;font-size:18px;font-weight:700;color:#ffffff;">{{ $barTitle ?? $headerTitle ?? 'Bestellung bestätigt' }}</p>
            </td>
        </tr>
        <tr>
            <td style="padding:28px 24px 36px;">
                @yield('body')
            </td>
        </tr>
        <tr>
            <td style="padding:0 24px 28px;font-size:12px;line-height:1.6;color:#6b7280;">
                {{ $company['name'] ?? 'Monnemtisch Catering' }}
                @if(!empty($company['city'])) · {{ $company['city'] }}@endif
                @if(!empty($company['email']))<br><a href="mailto:{{ $company['email'] }}" style="color:#D89B2B;text-decoration:none;">{{ $company['email'] }}</a>@endif
            </td>
        </tr>
    </table>
</body>
</html>
