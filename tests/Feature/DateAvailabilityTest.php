<?php

namespace Tests\Feature;

use App\Models\BlockedDate;
use App\Services\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DateAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_blocked_date_is_not_available(): void
    {
        $date = now()->addDays(14)->toDateString();

        BlockedDate::query()->create([
            'date' => $date,
            'type' => 'blocked',
            'note' => 'Testsperre',
        ]);

        $service = app(AvailabilityService::class);

        $this->assertFalse($service->isDateAvailable($date));
        $this->assertTrue($service->isDateAvailable(now()->addDays(15)->toDateString()));
        $this->assertContains($date, $service->getBlockedDates(now(), now()->addMonth()));
    }

    public function test_booked_date_is_not_available(): void
    {
        $date = now()->addDays(20)->toDateString();

        app(AvailabilityService::class)->blockDate($date, 'booked', 'Voll ausgebucht');

        $this->assertFalse(app(AvailabilityService::class)->isDateAvailable($date));
    }

    public function test_special_date_is_not_available(): void
    {
        $date = now()->addDays(8)->toDateString();

        BlockedDate::query()->create([
            'date' => $date,
            'type' => 'special',
            'note' => 'Feiertag',
        ]);

        $this->assertFalse(app(AvailabilityService::class)->isDateAvailable($date));
        $this->assertContains($date, app(AvailabilityService::class)->getBlockedDates(now(), now()->addMonth()));
    }
}
