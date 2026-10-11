<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ActivityLogService;
use App\Services\BusinessNotificationService;
use App\Services\CustomerSegmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AbandonedCartController extends Controller
{
    public function __construct(
        protected CustomerSegmentService $segments,
        protected BusinessNotificationService $notifications,
        protected ActivityLogService $activity
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Order::class);

        $segment = $this->segments->syncAbandonedMemberships();

        $orders = Order::query()
            ->abandoned()
            ->with(['customer', 'items', 'extras'])
            ->latest()
            ->paginate(20);

        $members = $segment->customers()
            ->orderBy('customer_segment_customer.updated_at', 'desc')
            ->limit(50)
            ->get();

        return view('admin.abandoned-carts.index', compact('orders', 'segment', 'members'));
    }

    public function sendMail(Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        if (! $order->isAbandoned()) {
            return back()->with('error', 'Bu kayıt artık sepeti bırakanlar listesinde değil.');
        }

        $email = trim((string) $order->billing_email);
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return back()->with('error', 'Bu kayıtta geçerli bir e-posta yok.');
        }

        try {
            $this->notifications->sendAbandonedCart($order);
        } catch (\Throwable) {
            return back()->with('error', 'Mail gönderilemedi. E-posta ayarlarını kontrol edin.');
        }

        $order->forceFill(['remarketing_sent_at' => now()])->save();
        $this->activity->log('Sepet hatırlatma maili gönderildi', $order, [
            'email' => $email,
        ]);

        return back()->with('success', 'Pazarlama maili gönderildi. Sepet içeriği e-postada.');
    }
}
