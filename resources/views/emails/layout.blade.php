<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $headerTitle ?? ($company['name'] ?? 'Monnemtisch Catering') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body style="margin:0;padding:0;background:#f7f3eb;font-family:Inter,Arial,Helvetica,sans-serif;color:#20262e;-webkit-text-size-adjust:100%;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;">{{ $preheader ?? '' }}</div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f7f3eb;">
        <tr>
            <td align="center" style="padding:28px 12px 40px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">
                    <tr>
                        <td style="padding:0 8px 20px;">
                            <a href="{{ $homeUrl ?? url('/') }}" style="text-decoration:none;">
                                @php $logoSrc = site_logo_mail_src($message ?? null); @endphp
                                @if($logoSrc)
                                    <img src="{{ $logoSrc }}" alt="{{ $company['name'] ?? 'Monnemtisch Catering' }}" width="160" style="display:block;max-width:160px;height:auto;border:0;">
                                @else
                                    <span style="display:block;font-size:20px;font-weight:700;letter-spacing:-0.02em;color:#132A3A;line-height:1.2;">Monnemtisch</span>
                                    <span style="display:block;margin-top:2px;font-size:11px;color:#6b7280;">Catering Mannheim</span>
                                @endif
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#fffcf8;border:1px solid #e6e0d6;border-radius:18px;overflow:hidden;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="height:6px;line-height:6px;font-size:0;background:#D89B2B;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="padding:28px 28px 8px;">
                                        @if(!empty($badge))
                                            <span style="display:inline-block;background:#f4e6c4;color:#132A3A;border-radius:999px;padding:5px 12px;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;">{{ $badge }}</span>
                                        @endif
                                        @if(!empty($headerTitle))
                                            <h1 style="margin:14px 0 0;font-size:26px;line-height:1.2;font-weight:700;letter-spacing:-0.03em;color:#132A3A;">{{ $headerTitle }}</h1>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 28px 32px;">
                                        @yield('body')
                                    </td>
                                </tr>
                                <tr>
                                    <td style="background:#f7f3eb;border-top:1px solid #e6e0d6;padding:22px 28px;">
                                        <p style="margin:0 0 4px;font-size:14px;font-weight:700;color:#132A3A;">{{ $company['name'] ?? 'Monnemtisch Catering' }}</p>
                                        <p style="margin:0 0 12px;font-size:12px;line-height:1.6;color:#6b7280;">
                                            {{ $company['city'] ?? 'Mannheim' }} &amp; Region
                                            @if(!empty($company['email'])) · <a href="mailto:{{ $company['email'] }}" style="color:#D89B2B;text-decoration:none;">{{ $company['email'] }}</a>@endif
                                            @if(!empty($company['phone'])) · {{ $company['phone'] }}@endif
                                        </p>
                                        <p style="margin:0;font-size:11px;color:#9ca3af;">
                                            <a href="{{ $homeUrl ?? url('/') }}" style="color:#6b7280;text-decoration:none;">Website</a>
                                            &nbsp;·&nbsp;
                                            <a href="{{ url('/impressum') }}" style="color:#6b7280;text-decoration:none;">Impressum</a>
                                            &nbsp;·&nbsp;
                                            <a href="{{ url('/datenschutz') }}" style="color:#6b7280;text-decoration:none;">Datenschutz</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
