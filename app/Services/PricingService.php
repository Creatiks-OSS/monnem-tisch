<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Extra;
use App\Models\Product;
use App\Models\Setting;

class PricingService
{
    public function __construct(
        protected DeliveryService $deliveryService
    ) {}

    public function calculateProductPrice(Product $product, int $persons, int $quantity = 1): float
    {
        $persons = max(1, $persons);
        $quantity = max(1, $quantity);

        $unitPrice = method_exists($product, 'getPriceForPersons')
            ? (float) $product->getPriceForPersons($persons)
            : $this->resolveProductUnitPrice($product, $persons);

        $line = match ($product->price_type ?? 'person') {
            'person' => $unitPrice * $persons * $quantity,
            'stück' => $unitPrice * $quantity,
            'fixed' => $unitPrice * $quantity,
            default => $unitPrice * $persons * $quantity,
        };

        return round($line, 2);
    }

    public function calculateExtraPrice(Extra $extra, int $quantity, int $persons): float
    {
        $quantity = max(1, $quantity);
        $persons = max(1, $persons);
        $unit = (float) $extra->price;

        $line = match ($extra->price_type ?? 'stück') {
            'person' => $unit * $persons * $quantity,
            'stück' => $unit * $quantity,
            'hour' => $unit * $quantity,
            'fixed' => $unit * $quantity,
            default => $unit * $quantity,
        };

        return round($line, 2);
    }

    public function calculateDeliveryFee(?string $postalCode, float $subtotal): float
    {
        if ($postalCode === null || trim($postalCode) === '') {
            return 0.0;
        }

        $result = $this->deliveryService->calculateFee($postalCode, $subtotal);

        return (float) ($result['fee'] ?? 0);
    }

    public function applyCoupon(Coupon $coupon, float $subtotal): float
    {
        if (method_exists($coupon, 'applyTo')) {
            $after = (float) $coupon->applyTo($subtotal);

            return round(max(0, $subtotal - $after), 2);
        }

        if (method_exists($coupon, 'isValid') && ! $coupon->isValid()) {
            return 0.0;
        }

        if ($coupon->minimum_order !== null && $subtotal < (float) $coupon->minimum_order) {
            return 0.0;
        }

        $discount = $coupon->type === 'percent'
            ? $subtotal * ((float) $coupon->value / 100)
            : (float) $coupon->value;

        return round(min($discount, $subtotal), 2);
    }

    public function calculateTax(float $net, float $rate): float
    {
        return round($net * ($rate / 100), 2);
    }

    /**
     * @param  array<int, array{product?: Product|null, product_id?: int, persons?: int, quantity?: int, unit_price?: float, line_total?: float, options?: array}>  $items
     * @param  array<int, array{extra?: Extra|null, extra_id?: int, quantity?: int, persons?: int, unit_price?: float, line_total?: float}>  $extras
     * @return array{
     *     subtotal: float,
     *     delivery_fee: float,
     *     service_fee: float,
     *     tip_amount: float,
     *     discount: float,
     *     tax_rate: float,
     *     tax_amount: float,
     *     tax_standard_rate: float,
     *     tax_standard_amount: float,
     *     food_subtotal: float,
     *     drink_subtotal: float,
     *     total: float,
     *     item_count: int
     * }
     */
    public function calculateOrderTotals(array $items, array $extras, ?string $postal, ?string $couponCode, ?float $deliveryOverride = null, float $tipAmount = 0): array
    {
        $foodSubtotal = 0.0;
        $drinkSubtotal = 0.0;
        $itemCount = 0;
        $personsFallback = 1;

        foreach ($items as $item) {
            $quantity = (int) ($item['quantity'] ?? 1);
            $persons = (int) ($item['persons'] ?? $personsFallback);
            $itemCount += $quantity;

            if (isset($item['line_total'])) {
                $foodSubtotal += (float) $item['line_total'];
                continue;
            }

            $product = $item['product'] ?? null;
            if (! $product && ! empty($item['product_id'])) {
                $product = Product::query()->with('priceTiers')->find($item['product_id']);
            }

            if ($product instanceof Product) {
                $line = $this->calculateProductPrice($product, $persons, $quantity);
                $optionModifier = $this->optionsModifier($item['options'] ?? []);
                $foodSubtotal += $line + ($optionModifier * $quantity);
            } elseif (isset($item['unit_price'])) {
                $foodSubtotal += (float) $item['unit_price'] * $quantity;
            }
        }

        $extraIds = collect($extras)->pluck('extra_id')->filter()->unique()->values();
        $extraModels = $extraIds->isEmpty()
            ? collect()
            : Extra::query()->whereIn('id', $extraIds)->get()->keyBy('id');

        foreach ($extras as $extraRow) {
            $quantity = (int) ($extraRow['quantity'] ?? 1);
            $persons = (int) ($extraRow['persons'] ?? $personsFallback);
            $itemCount += $quantity;

            $extra = $extraRow['extra'] ?? null;
            if (! $extra && ! empty($extraRow['extra_id'])) {
                $extra = $extraModels->get((int) $extraRow['extra_id']);
            }

            if (isset($extraRow['line_total'])) {
                $line = (float) $extraRow['line_total'];
            } elseif ($extra instanceof Extra) {
                $line = $this->calculateExtraPrice($extra, $quantity, $persons);
            } elseif (isset($extraRow['unit_price'])) {
                $line = (float) $extraRow['unit_price'] * $quantity;
            } else {
                $line = 0.0;
            }

            $kind = (string) ($extraRow['kind'] ?? ($extra instanceof Extra ? $extra->kind : 'service'));
            if ($kind === 'product') {
                $drinkSubtotal += $line;
            } else {
                $foodSubtotal += $line;
            }
        }

        $foodSubtotal = round($foodSubtotal, 2);
        $drinkSubtotal = round($drinkSubtotal, 2);
        $subtotal = round($foodSubtotal + $drinkSubtotal, 2);
        $deliveryFee = $deliveryOverride !== null
            ? round(max(0, $deliveryOverride), 2)
            : 0.0;
        $tipAmount = round(max(0, $tipAmount), 2);
        $serviceFee = (float) Setting::get('service_fee', 0, 'shop');

        $discount = 0.0;
        if ($couponCode) {
            $coupon = Coupon::query()
                ->whereRaw('UPPER(code) = ?', [strtoupper(trim($couponCode))])
                ->first();

            if ($coupon) {
                $discount = $this->applyCoupon($coupon, $subtotal);
            }
        }

        $reducedBase = round($foodSubtotal + $deliveryFee + $serviceFee, 2);
        $standardBase = round($drinkSubtotal, 2);
        $bases = $reducedBase + $standardBase;
        $discountReduced = 0.0;
        $discountStandard = 0.0;
        if ($discount > 0 && $bases > 0) {
            $discountReduced = round($discount * ($reducedBase / $bases), 2);
            $discountStandard = round($discount - $discountReduced, 2);
        }

        $taxRate = shop_tax_rate();
        $standardRate = shop_standard_tax_rate();
        $taxAmount = $this->calculateTax(max(0, $reducedBase - $discountReduced), $taxRate);
        $taxStandardAmount = $this->calculateTax(max(0, $standardBase - $discountStandard), $standardRate);

        $net = max(0, $subtotal + $deliveryFee + $serviceFee - $discount);
        $total = round($net + $taxAmount + $taxStandardAmount + $tipAmount, 2);

        return [
            'subtotal' => $subtotal,
            'food_subtotal' => $foodSubtotal,
            'drink_subtotal' => $drinkSubtotal,
            'delivery_fee' => round($deliveryFee, 2),
            'service_fee' => round($serviceFee, 2),
            'tip_amount' => $tipAmount,
            'discount' => round($discount, 2),
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'tax_standard_rate' => $standardRate,
            'tax_standard_amount' => $taxStandardAmount,
            'total' => $total,
            'item_count' => $itemCount,
        ];
    }

    protected function resolveProductUnitPrice(Product $product, int $persons): float
    {
        if ($product->relationLoaded('priceTiers') || method_exists($product, 'priceTiers')) {
            $tiers = $product->priceTiers ?? collect();
            $tier = $tiers
                ->filter(function ($t) use ($persons) {
                    $min = (int) $t->min_persons;
                    $max = $t->max_persons !== null ? (int) $t->max_persons : PHP_INT_MAX;

                    return $persons >= $min && $persons <= $max;
                })
                ->sortByDesc('min_persons')
                ->first();

            if ($tier) {
                return (float) $tier->price;
            }
        }

        return (float) $product->price;
    }

    protected function optionsModifier(array $options): float
    {
        $modifier = 0.0;
        foreach ($options as $option) {
            if (is_array($option) && isset($option['price_modifier'])) {
                $modifier += (float) $option['price_modifier'];
            }
        }

        return $modifier;
    }
}
