<?php

namespace App\Services;

use App\Models\AnalyticsSetting;
use App\Models\Setting;

class AnalyticsSettings
{
    public static function get(): AnalyticsSetting
    {
        return cache()->remember('analytics_settings', 3600, function () {
            $gaId = trim((string) Setting::get('google_analytics_id', '', 'analytics'));
            $gtmId = trim((string) Setting::get('google_tag_manager_id', '', 'analytics'));

            if (str_starts_with(strtoupper($gaId), 'GTM-')) {
                $gtmId = $gaId;
                $gaId = '';
            }
            if (str_starts_with(strtoupper($gtmId), 'G-')) {
                $gaId = $gtmId;
                $gtmId = '';
            }

            return AnalyticsSetting::query()->firstOrCreate(['id' => 1], [
                'ga_id' => $gaId !== '' ? $gaId : 'G-DV7MYZ21KK',
                'gtm_id' => $gtmId !== '' ? $gtmId : null,
                'debug_mode' => false,
                'tracking_enabled' => true,
                'conversion_events' => ['generate_lead'],
            ]);
        });
    }

    public static function flush(): void
    {
        cache()->forget('analytics_settings');
    }
}
