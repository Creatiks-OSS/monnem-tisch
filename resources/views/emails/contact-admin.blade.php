@extends('emails.layout')
@section('body')
    <p style="margin:8px 0 0;font-size:15px;line-height:1.65;color:#2c3544;">
        Über das Kontaktformular ist eine neue Nachricht eingegangen.
    </p>

    @include('emails.partials.meta-pills', ['items' => array_values(array_filter([
        ['label' => 'Name', 'value' => $contact->name],
        $contact->subject ? ['label' => 'Betreff', 'value' => $contact->subject] : null,
        $contact->event_date ? ['label' => 'Datum', 'value' => $contact->event_date->format('d.m.Y')] : null,
    ]))])

    <p style="margin:16px 0 0;font-size:14px;line-height:1.65;color:#2c3544;">
        {{ $contact->email }}
        @if($contact->phone)<br>{{ $contact->phone }}@endif
        @if($contact->event_type)<br>{{ $contact->event_type }}@endif
    </p>

    <p style="margin:18px 0 6px;font-size:13px;font-weight:700;color:#1b2430;">Nachricht</p>
    <p style="margin:0;padding:14px 16px;background:#f7f5f1;border-radius:12px;font-size:14px;line-height:1.65;color:#2c3544;white-space:pre-wrap;">{{ $contact->message }}</p>

    @include('emails.partials.cta', ['href' => $adminUrl ?? '', 'label' => 'Im Admin öffnen'])
@endsection
