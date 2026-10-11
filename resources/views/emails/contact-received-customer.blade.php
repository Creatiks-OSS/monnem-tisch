@extends('emails.layout')
@section('body')
    <p style="margin:8px 0 0;font-size:15px;line-height:1.65;color:#2c3544;">
        Hallo {{ $contact->name }},
    </p>
    <p style="margin:10px 0 0;font-size:15px;line-height:1.65;color:#2c3544;">
        vielen Dank für Ihre Nachricht. Wir haben Ihre Anfrage erhalten und melden uns in Kürze bei Ihnen.
    </p>
    @if($contact->subject)
        <p style="margin:16px 0 0;font-size:13px;color:#6b7280;">Betreff: <strong style="color:#1b2430;">{{ $contact->subject }}</strong></p>
    @endif
    <p style="margin:22px 0 0;font-size:14px;line-height:1.65;color:#2c3544;">
        Ihr Monnemtisch Catering Team
    </p>
    @include('emails.partials.cta', ['href' => $homeUrl ?? url('/'), 'label' => 'Zur Website'])
@endsection
