<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AnalyticsSettings;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsAdminController extends Controller
{
    public function edit(): View
    {
        return view('admin.analytics', [
            'settings' => AnalyticsSettings::get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->merge([
            'ga_id' => $request->filled('ga_id') ? strtoupper(trim((string) $request->input('ga_id'))) : null,
            'gtm_id' => $request->filled('gtm_id') ? strtoupper(trim((string) $request->input('gtm_id'))) : null,
            'debug_mode' => $request->boolean('debug_mode'),
            'tracking_enabled' => $request->boolean('tracking_enabled'),
        ]);

        $validated = $request->validate([
            'ga_id' => ['nullable', 'regex:/^G-[A-Z0-9]+$/'],
            'gtm_id' => ['nullable', 'regex:/^GTM-[A-Z0-9]+$/'],
            'debug_mode' => ['boolean'],
            'tracking_enabled' => ['boolean'],
            'conversion_events' => ['nullable', 'string', 'max:2000'],
        ]);

        $events = collect(preg_split('/\r\n|\r|\n/', (string) ($validated['conversion_events'] ?? '')))
            ->map(fn ($event) => trim($event))
            ->filter(fn ($event) => $event !== '')
            ->unique()
            ->values();

        if ($events->contains(fn ($event) => ! preg_match('/^[A-Za-z][A-Za-z0-9_]{0,40}$/', $event))) {
            return back()
                ->withInput()
                ->withErrors(['conversion_events' => 'Event adları harf ile başlamalı ve yalnızca harf, rakam veya alt çizgi içermeli.']);
        }

        if ($events->isEmpty()) {
            $events = collect(['generate_lead']);
        }

        AnalyticsSettings::get()->update([
            'ga_id' => $validated['ga_id'],
            'gtm_id' => $validated['gtm_id'],
            'debug_mode' => $validated['debug_mode'],
            'tracking_enabled' => $validated['tracking_enabled'],
            'conversion_events' => $events->all(),
        ]);

        Setting::set('google_analytics_id', (string) ($validated['ga_id'] ?? ''), 'analytics');
        Setting::set('google_tag_manager_id', (string) ($validated['gtm_id'] ?? ''), 'analytics');
        app(SettingService::class)->forgetCache('analytics');

        AnalyticsSettings::flush();

        return redirect()
            ->route('admin.analytics.edit')
            ->with('success', 'Analytics ayarları güncellendi.');
    }
}
