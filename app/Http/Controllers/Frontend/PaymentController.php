<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PayPalPaymentService;
use App\Services\PaymentService;
use App\Services\StripePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use RuntimeException;

class PaymentController extends Controller
{
    public function stripeSuccess(Request $request, StripePaymentService $stripe): RedirectResponse
    {
        $sessionId = (string) $request->query('session_id', '');
        if ($sessionId === '') {
            return redirect()->route('cart.index')->with('error', 'Die Zahlung konnte nicht bestätigt werden.');
        }

        try {
            $order = $stripe->completeFromSession($sessionId);
        } catch (RuntimeException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        return redirect()
            ->route('checkout.success', $order->order_number)
            ->with('success', 'Vielen Dank! Ihre Zahlung wurde bestätigt.');
    }

    public function paypalReturn(Request $request, PayPalPaymentService $paypal): RedirectResponse
    {
        $token = (string) $request->query('token', '');
        if ($token === '') {
            return redirect()->route('cart.index')->with('error', 'Die PayPal-Zahlung wurde nicht abgeschlossen.');
        }

        try {
            $order = $paypal->capture($token);
        } catch (RuntimeException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        return redirect()
            ->route('checkout.success', $order->order_number)
            ->with('success', 'Vielen Dank! Ihre PayPal-Zahlung wurde bestätigt.');
    }

    public function resume(string $orderNumber): RedirectResponse
    {
        $order = Order::query()->where('order_number', $orderNumber)->firstOrFail();

        if ($order->payment_status === 'paid') {
            return redirect()
                ->route('checkout.success', $order->order_number)
                ->with('success', 'Diese Bestellung ist bereits bezahlt.');
        }

        if (! $order->isAbandoned()) {
            return redirect()
                ->route('home')
                ->with('error', 'Diese Bestellung kann nicht mehr fortgesetzt werden.');
        }

        try {
            $url = $order->payment_method === 'paypal'
                ? app(PayPalPaymentService::class)->createCheckoutUrl($order)
                : app(StripePaymentService::class)->createCheckoutUrl($order);
        } catch (RuntimeException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        return redirect()->away($url);
    }

    public function cancel(string $order, PaymentService $payments): View
    {
        $record = Order::query()->where('order_number', $order)->first();
        if ($record) {
            $payments->markFailed($record, 'cancelled');
        }

        return view('frontend.checkout.cancel', [
            'order' => $record,
        ]);
    }

    public function stripeWebhook(Request $request, StripePaymentService $stripe): Response
    {
        try {
            $stripe->handleWebhook($request);
        } catch (RuntimeException $e) {
            return response('invalid', 400);
        }

        return response('ok', 200);
    }

    public function paypalWebhook(Request $request, PayPalPaymentService $paypal): Response
    {
        try {
            $paypal->handleWebhook($request);
        } catch (RuntimeException $e) {
            return response('invalid', 400);
        }

        return response('ok', 200);
    }

    public function webhookInfo(): Response
    {
        return response('', 405, ['Allow' => 'POST']);
    }
}
