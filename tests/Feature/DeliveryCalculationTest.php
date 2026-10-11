<?php

namespace Tests\Feature;

use App\Models\DeliveryZone;
use App\Services\DeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_service_calculates_fee_for_hannover_plz(): void
    {
        DeliveryZone::query()->create([
            'name' => 'Hannover',
            'city' => 'Hannover',
            'postal_codes' => ['30159', '30161', '30449'],
            'delivery_fee' => 15.00,
            'minimum_order' => 150.00,
            'free_delivery_threshold' => 400.00,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $service = app(DeliveryService::class);

        $this->assertTrue($service->isDeliverable('30159'));

        $result = $service->calculateFee('30159', 200.00);
        $this->assertSame(15.0, $result['fee']);
        $this->assertTrue($result['meets_minimum']);
        $this->assertFalse($result['free_delivery']);
        $this->assertNotNull($result['zone']);

        $free = $service->calculateFee('30159', 450.00);
        $this->assertSame(0.0, $free['fee']);
        $this->assertTrue($free['free_delivery']);
    }

    public function test_cart_delivery_route_accepts_postal_code(): void
    {
        DeliveryZone::query()->create([
            'name' => 'Garbsen',
            'city' => 'Garbsen',
            'postal_codes' => ['30823'],
            'delivery_fee' => 25.00,
            'minimum_order' => 180.00,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->postJson(route('cart.delivery'), [
            'postal_code' => '30823',
            'city' => 'Garbsen',
        ]);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertSame('30823', $response->json('cart.delivery.postal_code'));
    }
}
