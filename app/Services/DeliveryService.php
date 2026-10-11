<?php

namespace App\Services;

use App\Models\DeliveryZone;

class DeliveryService
{
    public function findZoneForPostalCode(string $plz): ?DeliveryZone
    {
        $plz = trim($plz);
        if ($plz === '') {
            return null;
        }

        $zones = DeliveryZone::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        foreach ($zones as $zone) {
            if (method_exists($zone, 'matchesPostalCode') && $zone->matchesPostalCode($plz)) {
                return $zone;
            }

            $codes = $zone->postal_codes ?? [];
            if (is_string($codes)) {
                $decoded = json_decode($codes, true);
                $codes = is_array($decoded) ? $decoded : [];
            }

            foreach ((array) $codes as $code) {
                $code = (string) $code;
                if ($code === $plz || ($code !== '' && str_starts_with($plz, $code))) {
                    return $zone;
                }
            }
        }

        return null;
    }

    /**
     * @return array{
     *     fee: float,
     *     zone: ?DeliveryZone,
     *     free_delivery: bool,
     *     meets_minimum: bool
     * }
     */
    public function calculateFee(string $plz, float $subtotal): array
    {
        $zone = $this->findZoneForPostalCode($plz);

        if (! $zone) {
            return [
                'fee' => 0.0,
                'zone' => null,
                'free_delivery' => false,
                'meets_minimum' => false,
            ];
        }

        $minimum = (float) ($zone->minimum_order ?? 0);
        $meetsMinimum = $subtotal >= $minimum;
        $threshold = $zone->free_delivery_threshold;
        $freeDelivery = $threshold !== null && $subtotal >= (float) $threshold;
        $fee = $freeDelivery ? 0.0 : (float) $zone->delivery_fee;

        return [
            'fee' => round($fee, 2),
            'zone' => $zone,
            'free_delivery' => $freeDelivery,
            'meets_minimum' => $meetsMinimum,
        ];
    }

    public function isDeliverable(string $plz): bool
    {
        return $this->findZoneForPostalCode($plz) !== null;
    }
}
