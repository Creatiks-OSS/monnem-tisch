@extends('admin.layouts.app')
@section('title', 'Analitik')
@section('heading', 'Google Analytics / Ads')
@section('content')
@include('admin.partials.errors')

<form method="POST" action="{{ route('admin.analytics.update') }}" class="max-w-2xl space-y-5 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
    @csrf
    <label class="block text-sm">
        <span class="mb-1 block font-medium">GA4 Measurement ID</span>
        <input type="text" name="ga_id" value="{{ old('ga_id', $settings->ga_id) }}" placeholder="G-XXXXXXXXXX" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
    </label>
    <label class="block text-sm">
        <span class="mb-1 block font-medium">GTM Container ID (opsiyonel)</span>
        <input type="text" name="gtm_id" value="{{ old('gtm_id', $settings->gtm_id) }}" placeholder="GTM-XXXXXXX" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">
        <span class="mt-1 block text-xs text-[var(--admin-muted)]">Doluysa site GTM yükler, GA4 etiketi GTM içinden gelmelidir.</span>
    </label>
    <label class="flex items-start gap-3 text-sm">
        <input type="hidden" name="debug_mode" value="0">
        <input type="checkbox" name="debug_mode" value="1" class="mt-1" @checked((string) old('debug_mode', $settings->debug_mode ? '1' : '0') === '1')>
        <span>
            <span class="font-medium">DebugView modu</span>
            <span class="mt-0.5 block text-xs text-[var(--admin-muted)]">Yalnızca test sırasında açın. GA4 DebugView’da olaylar görünür.</span>
        </span>
    </label>
    <label class="flex items-start gap-3 text-sm">
        <input type="hidden" name="tracking_enabled" value="0">
        <input type="checkbox" name="tracking_enabled" value="1" class="mt-1" @checked((string) old('tracking_enabled', $settings->tracking_enabled ? '1' : '0') === '1')>
        <span>
            <span class="font-medium">Tracking aktif</span>
            <span class="mt-0.5 block text-xs text-[var(--admin-muted)]">Kapatılırsa gtag / GTM scripti yüklenmez ve dönüşüm olayı gitmez.</span>
        </span>
    </label>
    <label class="block text-sm">
        <span class="mb-1 block font-medium">Dönüşüm event adları</span>
        <textarea name="conversion_events" rows="4" class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm">{{ old('conversion_events', implode("\n", $settings->conversion_events ?: ['generate_lead'])) }}</textarea>
        <span class="mt-1 block text-xs text-[var(--admin-muted)]">Her satıra bir event. Google Ads dönüşümü için <code>generate_lead</code> kalsın. GA4’te bunu Key Event yapıp Ads’e aktarın. <code>contact</code> yalnızca analiz içindir.</span>
    </label>
    <button type="submit" class="rounded-lg bg-[var(--admin-sidebar)] px-4 py-2 text-sm text-white">Kaydet</button>
</form>
@endsection
