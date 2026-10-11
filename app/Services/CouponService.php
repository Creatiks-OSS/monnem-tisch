<?php

namespace App\Services;

use App\Models\Coupon;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function findByCode(string $code): ?Coupon
    {
        return Coupon::query()
            ->whereRaw('UPPER(code) = ?', [strtoupper(trim($code))])
            ->first();
    }

    /**
     * @return array{valid: bool, coupon: ?Coupon, discount: float, message: string}
     */
    public function validate(string $code, float $subtotal = 0.0): array
    {
        $coupon = $this->findByCode($code);

        if (! $coupon) {
            return [
                'valid' => false,
                'coupon' => null,
                'discount' => 0.0,
                'message' => 'Gutscheincode ungültig.',
            ];
        }

        if (! $coupon->is_active) {
            return [
                'valid' => false,
                'coupon' => $coupon,
                'discount' => 0.0,
                'message' => 'Dieser Gutschein ist nicht aktiv.',
            ];
        }

        if (method_exists($coupon, 'isValid') && ! $coupon->isValid()) {
            return [
                'valid' => false,
                'coupon' => $coupon,
                'discount' => 0.0,
                'message' => 'Dieser Gutschein ist abgelaufen oder aufgebraucht.',
            ];
        }

        if ($coupon->starts_at && now()->lt($coupon->starts_at)) {
            return [
                'valid' => false,
                'coupon' => $coupon,
                'discount' => 0.0,
                'message' => 'Dieser Gutschein ist noch nicht gültig.',
            ];
        }

        if ($coupon->expires_at && now()->gt($coupon->expires_at)) {
            return [
                'valid' => false,
                'coupon' => $coupon,
                'discount' => 0.0,
                'message' => 'Dieser Gutschein ist abgelaufen.',
            ];
        }

        if ($coupon->max_uses !== null && (int) $coupon->used_count >= (int) $coupon->max_uses) {
            return [
                'valid' => false,
                'coupon' => $coupon,
                'discount' => 0.0,
                'message' => 'Dieser Gutschein wurde bereits maximal genutzt.',
            ];
        }

        if ($coupon->minimum_order !== null && $subtotal < (float) $coupon->minimum_order) {
            return [
                'valid' => false,
                'coupon' => $coupon,
                'discount' => 0.0,
                'message' => 'Mindestbestellwert für diesen Gutschein nicht erreicht.',
            ];
        }

        $discount = method_exists($coupon, 'applyTo')
            ? max(0, $subtotal - (float) $coupon->applyTo($subtotal))
            : $this->calculateDiscount($coupon, $subtotal);

        return [
            'valid' => true,
            'coupon' => $coupon,
            'discount' => round($discount, 2),
            'message' => 'Gutschein erfolgreich angewendet.',
        ];
    }

    public function redeem(string $code, float $subtotal = 0.0): Coupon
    {
        $result = $this->validate($code, $subtotal);

        if (! $result['valid'] || ! $result['coupon']) {
            throw ValidationException::withMessages([
                'coupon' => $result['message'],
            ]);
        }

        /** @var Coupon $coupon */
        $coupon = $result['coupon'];
        $coupon->increment('used_count');

        return $coupon->fresh();
    }

    protected function calculateDiscount(Coupon $coupon, float $subtotal): float
    {
        $discount = $coupon->type === 'percent'
            ? $subtotal * ((float) $coupon->value / 100)
            : (float) $coupon->value;

        return min($discount, $subtotal);
    }
}
