@extends('admin.layouts.app')
@section('title', 'Talebi düzenle')
@section('heading', 'Talep '.$quote->quote_number)
@section('content')
@include('admin.partials.errors')
<form method="POST" action="{{ route('admin.quotes.update', $quote) }}" class="admin-card max-w-3xl space-y-4 p-6">
    @csrf
    @method('PUT')
    <div class="grid gap-4 sm:grid-cols-2">
        @foreach ([
            ['first_name', 'Ad'],
            ['last_name', 'Soyad'],
            ['company', 'Firma'],
            ['email', 'E-posta'],
            ['phone', 'Telefon'],
            ['persons', 'Kişi'],
            ['location', 'Konum'],
            ['budget', 'Bütçe'],
            ['subtotal', 'Ara toplam'],
            ['delivery_fee', 'Teslimat ücreti'],
            ['discount', 'İndirim'],
            ['tax_amount', 'KDV'],
            ['total', 'Toplam'],
        ] as [$n, $l])
            <div>
                <label class="admin-label">{{ $l }}</label>
                <input name="{{ $n }}" value="{{ old($n, $quote->$n) }}" class="admin-input">
            </div>
        @endforeach
        <div>
            <label class="admin-label">Etkinlik tarihi</label>
            <input type="date" name="event_date" value="{{ old('event_date', optional($quote->event_date)->format('Y-m-d')) }}" class="admin-input">
        </div>
        <div>
            <label class="admin-label">Saat</label>
            <input name="event_time" value="{{ old('event_time', $quote->event_time) }}" class="admin-input">
        </div>
        <div>
            <label class="admin-label">Geçerlilik</label>
            <input type="date" name="valid_until" value="{{ old('valid_until', optional($quote->valid_until)->format('Y-m-d')) }}" class="admin-input">
        </div>
        <div>
            <label class="admin-label">Atanan</label>
            <select name="assigned_user_id" class="admin-select">
                <option value="">—</option>
                @foreach ($users as $u)
                    <option value="{{ $u->id }}" @selected(old('assigned_user_id', $quote->assigned_user_id) == $u->id)>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div>
        <label class="admin-label">Mesaj</label>
        <textarea name="message" rows="3" class="admin-textarea">{{ old('message', $quote->message) }}</textarea>
    </div>
    <div>
        <label class="admin-label">Notlar</label>
        <textarea name="notes" rows="3" class="admin-textarea">{{ old('notes', $quote->notes) }}</textarea>
    </div>
    <button class="admin-btn admin-btn-gold px-8">Kaydet</button>
</form>
@endsection
