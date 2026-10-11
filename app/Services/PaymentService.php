<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(protected ActivityLogService $activity) {}

    public function recordPending(Order $order, string $method, array $meta = []): Payment
    {
        return $order->payments()->create([
            'amount' => $order->total,
            'currency' => $order->currency ?: 'EUR',
            'method' => $method,
            'status' => 'pending',
            'meta' => $meta ?: null,
        ]);
    }

    public function markPaid(Order $order, string $method, ?string $transactionId = null, array $meta = []): Payment
    {
        $alreadyPaid = $order->payment_status === 'paid';

        $payment = $order->payments()->latest('id')->first();

        if (! $payment) {
            $payment = $this->recordPending($order, $method);
        }

        $payment->fill([
            'method' => $method ?: $payment->method,
            'status' => 'paid',
            'transaction_id' => $transactionId ?: $payment->transaction_id,
            'meta' => array_filter(array_merge($payment->meta ?? [], $meta)),
            'paid_at' => $payment->paid_at ?: Carbon::now(),
        ])->save();

        if (! $alreadyPaid && $order->coupon_code && ! $order->coupon_redeemed_at) {
            try {
                app(CouponService::class)->redeem($order->coupon_code, (float) $order->subtotal);
                $order->coupon_redeemed_at = now();
            } catch (\Throwable $e) {
                Log::info('Coupon redeem skipped after payment', [
                    'order_id' => $order->id,
                    'code' => $order->coupon_code,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $payload = [
            'payment_method' => $method ?: $order->payment_method,
            'payment_status' => 'paid',
        ];

        if ($order->status === 'neu') {
            $payload['status'] = 'bestätigt';
            $payload['confirmed_at'] = $order->confirmed_at ?: Carbon::now();
        }

        if ($order->coupon_redeemed_at) {
            $payload['coupon_redeemed_at'] = $order->coupon_redeemed_at;
        }

        $order->update($payload);

        try {
            app(CustomerSegmentService::class)->syncOrder($order->fresh());
        } catch (\Throwable $e) {
            Log::warning('Paid order segment release failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        if (! $alreadyPaid && ! $order->ingredientAnalysis()->exists()) {
            try {
                app(IngredientAnalysisService::class)->queue($order);
            } catch (\Throwable $e) {
                Log::warning('Paid order ingredient analysis queue failed', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->activity->log('Ödeme alındı', $order, [
            'method' => $payment->method,
            'transaction_id' => $payment->transaction_id,
        ]);

        if (! $alreadyPaid) {
            try {
                $fresh = $order->fresh(['items.product.category', 'extras.extra', 'eventType', 'payments', 'customer']);
                if ($fresh) {
                    app(BusinessNotificationService::class)->notifyOrderPaid($fresh);
                }
            } catch (\Throwable $e) {
                Log::warning('Order paid notifications failed', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $payment->fresh();
    }

    public function markFailed(Order $order, ?string $reason = null): ?Payment
    {
        $payment = $order->payments()->latest('id')->first();

        if ($payment) {
            $meta = $payment->meta ?? [];
            if ($reason) {
                $meta['error'] = $reason;
            }
            $payment->update([
                'status' => 'failed',
                'meta' => $meta,
            ]);
        }

        $order->update(['payment_status' => 'failed']);

        try {
            app(CustomerSegmentService::class)->syncOrder($order->fresh());
        } catch (\Throwable $e) {
            Log::warning('Failed payment segment sync failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $payment;
    }
}
