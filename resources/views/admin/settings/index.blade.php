@extends('admin.layouts.app')
@section('title','Ayarlar')
@section('heading','Ayarlar')
@section('content')
<div class="flex flex-wrap gap-2 mb-6">
@foreach($groups as $key => $label)
<a href="{{ route('admin.settings.group',$key) }}" class="px-3 py-1.5 rounded-lg text-sm {{ $group===$key ? 'bg-[var(--admin-sidebar)] text-white' : 'border border-stone-200 hover:bg-white' }}">{{ $label }}</a>
@endforeach
</div>
@include('admin.partials.errors')

@if($group === 'general')
<div class="mb-6 max-w-3xl overflow-hidden rounded-2xl border {{ $maintenanceEnabled ? 'border-amber-300 bg-amber-50' : 'border-stone-200 bg-white' }} shadow-sm">
    <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <p class="text-sm font-semibold">Yapım aşamasında</p>
            <p class="mt-1 text-sm text-[var(--admin-muted)]">
                Aktifken ziyaretçiler site yerine “yakında açılıyoruz” sayfasını görür. Admin paneli açık kalır.
            </p>
            <p class="mt-2 text-xs font-medium {{ $maintenanceEnabled ? 'text-amber-800' : 'text-stone-500' }}">
                Durum: {{ $maintenanceEnabled ? 'Aktif — ziyaretçiler bu sayfayı görüyor' : 'Pasif — site yayında' }}
            </p>
        </div>
        <form method="POST" action="{{ route('admin.settings.maintenance') }}" class="shrink-0">
            @csrf
            @method('PUT')
            <input type="hidden" name="enabled" value="{{ $maintenanceEnabled ? '0' : '1' }}">
            <button type="submit"
                    class="inline-flex items-center gap-3 rounded-full border px-2 py-1.5 transition {{ $maintenanceEnabled ? 'border-amber-400 bg-white' : 'border-stone-200 bg-stone-50' }}"
                    aria-pressed="{{ $maintenanceEnabled ? 'true' : 'false' }}"
                    title="{{ $maintenanceEnabled ? 'Pasif et' : 'Aktif et' }}">
                <span class="relative h-7 w-12 rounded-full transition {{ $maintenanceEnabled ? 'bg-[var(--admin-terracotta)]' : 'bg-stone-300' }}">
                    <span class="absolute top-0.5 h-6 w-6 rounded-full bg-white shadow transition {{ $maintenanceEnabled ? 'right-0.5' : 'left-0.5' }}"></span>
                </span>
                <span class="pr-2 text-sm font-semibold">{{ $maintenanceEnabled ? 'Pasif et' : 'Aktif et' }}</span>
            </button>
        </form>
    </div>
    <form method="POST" action="{{ route('admin.settings.maintenance.text') }}" class="space-y-3 border-t {{ $maintenanceEnabled ? 'border-amber-200' : 'border-stone-100' }} px-5 py-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-medium mb-1">Sayfa başlığı (Almanca)</label>
            <input type="text" name="title" value="{{ old('title', $maintenanceTitle) }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm" maxlength="160">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Sayfa metni (Almanca)</label>
            <textarea name="message" rows="3" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm" maxlength="500">{{ old('message', $maintenanceMessage) }}</textarea>
        </div>
        <button class="px-4 py-2 rounded-lg border border-stone-200 bg-white text-sm font-medium hover:border-[var(--admin-gold)]">Metni kaydet</button>
    </form>
</div>
@endif

@if($group === 'notifications')
    @include('admin.settings.notifications')
@elseif($group === 'mail')
    @include('admin.settings.mail')
@elseif($group === 'ai')
    @include('admin.settings.ai')
@elseif($group === 'kitchen')
    @include('admin.settings.kitchen')
@else
<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="max-w-3xl rounded-2xl border border-stone-200 bg-white p-6 shadow-sm space-y-4">
@csrf @method('PUT')
<input type="hidden" name="group" value="{{ $group }}">
@if($group === 'general')
@php $logoUrl = site_logo_url(); @endphp
<div>
    <label class="block text-sm font-medium mb-1">Logo</label>
    <p class="mb-2 text-xs text-stone-500">Sitenin üst menüsünde görünür. PNG, SVG veya JPG, en fazla 4 MB.</p>
    @if($logoUrl)
        <div class="mb-3 flex items-center gap-4 rounded-xl border border-stone-100 bg-[#f7f5f1] p-3">
            <img src="{{ $logoUrl }}" alt="Logo" class="h-12 w-auto max-w-[200px] object-contain">
            <label class="flex items-center gap-2 text-sm text-stone-600">
                <input type="checkbox" name="remove_logo" value="1" class="rounded border-stone-300">
                Logoyu kaldır
            </label>
        </div>
    @endif
    <input type="file" name="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml,image/gif" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
</div>
@endif
@php
$fields = match($group) {
    'general' => ['company_name'=>'Firma adı (marka)','legal_name'=>'Yasal unvan (GmbH)','tagline'=>'Slogan','locale'=>'Dil','currency'=>'Para birimi','tax_rate'=>'KDV % (MwSt.)'],
    'contact' => ['email'=>'E-posta','phone'=>'Telefon','street'=>'Sokak','postal_code'=>'Posta kodu','city'=>'Şehir','country'=>'Ülke','tax_id'=>'Steuernummer'],
    'social' => ['facebook'=>'Facebook','instagram'=>'Instagram','linkedin'=>'LinkedIn','youtube'=>'YouTube'],
    'seo' => ['meta_title'=>'Meta başlık','meta_description'=>'Meta açıklama','meta_keywords'=>'Anahtar kelimeler','og_image'=>'OG görsel URL'],
    'analytics' => [
        'google_analytics_id' => 'Google Analytics 4 ID (G-XXXXXXXX)',
        'google_tag_manager_id' => 'Google Tag Manager ID (GTM-XXXXXXX)',
        'facebook_pixel_id' => 'Facebook Pixel ID',
    ],
    'appearance' => ['primary'=>'Ana renk','secondary'=>'İkincil renk','accent'=>'Vurgu','background'=>'Arka plan','text'=>'Metin'],
    'whatsapp' => ['number'=>'WhatsApp numarası','message'=>'Varsayılan mesaj','enabled'=>'Aktif (1/0)'],
    default => [],
};
@endphp
@if($group === 'analytics')
    <p class="rounded-xl border border-stone-200 bg-[#f7f5f1] px-4 py-3 text-sm text-stone-600">
        Google Analytics 4 Measurement ID’yi yapıştırın (<code class="font-mono">G-XXXXXXXX</code>).
        Kod, ziyaretçi çerezlerde <strong>Analyse</strong> onayına basınca yüklenir.
        Tag Manager kullanıyorsanız Analytics’i GTM içinden ekleyin ve buraya yalnızca <code class="font-mono">GTM-XXXXXXX</code> yazın.
    </p>
@endif
@foreach($fields as $key => $label)
<div>
<label class="block text-sm font-medium mb-1">{{ $label }}</label>
@if(str_contains($key, 'description') || $key === 'message')
<textarea name="settings[{{ $key }}]" rows="3" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">{{ old('settings.'.$key, $values[$key] ?? '') }}</textarea>
@elseif($group === 'appearance')
<input type="color" name="settings[{{ $key }}]" value="{{ old('settings.'.$key, $values[$key] ?? '#000000') }}" class="h-10 w-20 rounded border border-stone-200">
@else
<input type="text" name="settings[{{ $key }}]" value="{{ old('settings.'.$key, $values[$key] ?? '') }}" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm" @if($group === 'analytics' && $key === 'google_analytics_id') placeholder="G-XXXXXXXX" @elseif($group === 'analytics' && $key === 'google_tag_manager_id') placeholder="GTM-XXXXXXX" @endif>
@endif
</div>
@endforeach
<button class="px-4 py-2 rounded-lg bg-[var(--admin-sidebar)] text-white text-sm">Kaydet</button>
</form>
@endif
@endsection