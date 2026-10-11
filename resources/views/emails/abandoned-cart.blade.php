@php
    $name = trim(($order->billing_first_name ?? '').' '.($order->billing_last_name ?? '')) ?: 'Gäste';
@endphp
@extends('emails.layout-order')
@section('body')
    <h1 style="margin:0 0 12px;font-size:22px;font-weight:700;color:#1b2430;">Ihr Warenkorb wartet</h1>
    <p style="margin:0 0 18px;font-size:14px;line-height:1.6;color:#1b2430;">
        Hallo {{ $name }},<br>
        Sie haben die folgenden Speisen zusammengestellt, die Bestellung aber noch nicht abgeschlossen. Ihr Warenkorb ist noch für Sie da.
    </p>

    @include('emails.partials.order-lines', ['lines' => $lines])
    @include('emails.partials.order-totals', ['order' => $order])

    @if(!empty($resumeUrl))
        <p style="margin:8px 0 22px;">
            <a href="{{ $resumeUrl }}" style="display:inline-block;background:#c9a96e;color:#111111;text-decoration:none;font-weight:700;font-size:14px;padding:12px 18px;border-radius:999px;">Bestellung abschließen</a>
        </p>
    @endif

    <p style="margin:0;font-size:13px;line-height:1.6;color:#6b7280;">
        Wenn Sie bereits bezahlt haben, können Sie diese Nachricht ignorieren.
    </p>
@endsection
