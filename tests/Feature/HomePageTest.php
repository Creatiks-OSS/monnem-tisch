<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_returns_ok(): void
    {
        $response = $this->get('/');

        $html = $response->getContent();

        $response->assertOk()
            ->assertSee('Alle Fotos ansehen')
            ->assertSeeInOrder(['Buffets', 'Fingerfood', 'Frühstück', 'Grillcatering', 'Vegetarisch', 'Vegan'], false)
            ->assertDontSee('>Business</span>', false)
            ->assertSee('<h1>Catering in Mannheim für <br><span class="ht-gold">Firmen, Hochzeiten &amp; Events</span></h1>', false)
            ->assertDontSee('<h1>Catering online bestellen</h1>', false)
            ->assertDontSee('homeAvailability', false);

        $this->assertSame(1, preg_match_all('/<h1\b/i', $html));
        $this->assertStringNotContainsString('alt=""', $html);
    }

    public function test_home_delivery_check_accepts_full_address(): void
    {
        \App\Models\DeliveryZone::query()->create([
            'name' => 'Mannheim',
            'city' => 'Mannheim',
            'postal_codes' => ['68159', '68161'],
            'delivery_fee' => 0,
            'minimum_order' => 0,
            'free_delivery_threshold' => 500,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->postJson(route('delivery.check'), [
            'address' => 'H1 10, 68159 Mannheim',
        ])->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertSame('68159', data_get(session('cart'), 'delivery.postal_code'));
    }

    public function test_home_delivery_check_accepts_address_outside_mannheim(): void
    {
        $this->partialMock(\App\Services\GoogleDistanceService::class, function ($mock) {
            $mock->shouldReceive('quoteLine')->andReturn([
                'ok' => true,
                'isStuttgart' => false,
                'isHannover' => false,
                'distanceKm' => 412.2,
                'formattedAddress' => 'Göttinger Straße 52A, 30449 Hannover',
                'error' => null,
            ]);
        });

        $response = $this->postJson(route('delivery.check'), [
            'address' => 'Gottinger Srt 52A 30449',
        ]);

        $response->assertOk()->assertJsonPath('ok', true);
        $this->assertStringNotContainsString('außerhalb unseres Liefergebiets', (string) $response->json('message'));
        $this->assertSame(412.2, (float) data_get(session('cart'), 'delivery.distance_km'));
        $this->assertSame(412.2, (float) data_get(session('cart'), 'delivery.cost'));
    }

    public function test_home_includes_google_analytics_when_id_is_configured(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('G-DV7MYZ21KK', false)
            ->assertSee('MonnemAnalytics', false)
            ->assertSee('MonnemAnalytics.track', false);

        \App\Models\AnalyticsSetting::query()->whereKey(1)->update(['ga_id' => 'G-TEST1234']);
        \App\Services\AnalyticsSettings::flush();

        $this->get('/')
            ->assertOk()
            ->assertSee('G-TEST1234', false);
    }

    public function test_delivery_distance_quote_returns_outside_city_distance(): void
    {
        $this->mock(\App\Services\GoogleDistanceService::class, function ($mock) {
            $mock->shouldReceive('quoteLine')
                ->once()
                ->andReturn([
                    'ok' => true,
                    'isStuttgart' => false,
                    'isHannover' => false,
                    'distanceKm' => 12.5,
                    'formattedAddress' => 'Hannover, Deutschland',
                    'error' => null,
                ]);
        });

        $this->postJson(route('checkout.delivery-distance'), [
            'address' => 'Königstraße 1, 30159 Hannover',
        ])->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('distanceKm', 12.5)
            ->assertJsonPath('isStuttgart', false);
    }
}
