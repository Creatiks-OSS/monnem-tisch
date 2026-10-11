<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessHour;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessHourController extends Controller
{
    public const DAYS = [
        0 => 'Sonntag',
        1 => 'Montag',
        2 => 'Dienstag',
        3 => 'Mittwoch',
        4 => 'Donnerstag',
        5 => 'Freitag',
        6 => 'Samstag',
    ];

    public function __construct(protected ActivityLogService $activity) {}

    public function index(): View
    {
        $hours = BusinessHour::query()->orderBy('day_of_week')->get()->keyBy('day_of_week');

        foreach (self::DAYS as $day => $label) {
            if (! $hours->has($day)) {
                $hours[$day] = BusinessHour::query()->create([
                    'day_of_week' => $day,
                    'is_open' => $day >= 1 && $day <= 5,
                    'open_time' => '09:00',
                    'close_time' => '18:00',
                ]);
            }
        }

        $days = self::DAYS;

        return view('admin.business-hours.index', [
            'hours' => $hours->sortKeys(),
            'days' => $days,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'hours' => ['required', 'array'],
            'hours.*.is_open' => ['sometimes', 'boolean'],
            'hours.*.open_time' => ['nullable', 'string'],
            'hours.*.close_time' => ['nullable', 'string'],
        ]);

        foreach ($data['hours'] as $day => $row) {
            BusinessHour::query()->updateOrCreate(
                ['day_of_week' => (int) $day],
                [
                    'is_open' => ! empty($row['is_open']),
                    'open_time' => $row['open_time'] ?? null,
                    'close_time' => $row['close_time'] ?? null,
                ]
            );
        }

        $this->activity->log('Çalışma saatleri güncellendi');

        return back()->with('success', 'Çalışma saatleri kaydedildi.');
    }
}