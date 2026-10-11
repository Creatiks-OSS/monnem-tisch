<?php

namespace App\Services;

use App\Models\BlockedDate;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class AvailabilityService
{
    public function isDateAvailable(CarbonInterface|string $date): bool
    {
        $day = $this->normalizeDate($date);

        $blocked = BlockedDate::query()
            ->whereDate('date', $day->toDateString())
            ->exists();

        return ! $blocked;
    }

    /**
     * @return array<int, string>
     */
    public function getBlockedDates(?Carbon $from = null, ?Carbon $to = null): array
    {
        $query = BlockedDate::query()
            ->orderBy('date');

        if ($from) {
            $query->whereDate('date', '>=', $from->toDateString());
        }

        if ($to) {
            $query->whereDate('date', '<=', $to->toDateString());
        }

        return $query->pluck('date')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->values()
            ->all();
    }

    public function blockDate(string $date, string $type = 'blocked', ?string $note = null): BlockedDate
    {
        $day = $this->normalizeDate($date);

        return BlockedDate::query()->updateOrCreate(
            ['date' => $day->toDateString()],
            [
                'type' => in_array($type, ['blocked', 'booked', 'special'], true) ? $type : 'blocked',
                'note' => $note,
            ]
        );
    }

    public function unblockDate(string $date): void
    {
        $day = $this->normalizeDate($date);
        BlockedDate::query()->whereDate('date', $day->toDateString())->delete();
    }

    protected function normalizeDate(CarbonInterface|string $date): Carbon
    {
        return $date instanceof CarbonInterface
            ? Carbon::instance($date)->startOfDay()
            : Carbon::parse($date)->startOfDay();
    }
}
