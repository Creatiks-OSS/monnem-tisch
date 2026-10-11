<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\KitchenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KitchenBoardController extends Controller
{
    public function __construct(
        protected KitchenService $kitchen
    ) {}

    public function index(): View
    {
        $orders = $this->kitchen->visibleQuery()
            ->with(['items.product', 'extras.extra', 'eventType'])
            ->orderByRaw('event_date is null')
            ->orderBy('event_date')
            ->orderBy('event_time')
            ->get();

        return view('kitchen.board', [
            'orders' => $orders,
            'kitchen' => $this->kitchen,
        ]);
    }

    public function show(Order $order): View
    {
        abort_unless($this->isVisible($order), 404);

        $order->load(['items.product', 'extras.extra', 'eventType', 'customer']);

        return view('kitchen.show', [
            'order' => $order,
            'kitchen' => $this->kitchen,
        ]);
    }

    public function complete(Order $order): RedirectResponse
    {
        abort_unless($this->isVisible($order), 404);

        $this->kitchen->complete($order);

        return redirect()->route('kitchen.board');
    }

    public function accept(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        abort_unless($this->isVisible($order), 404);

        $this->kitchen->markSeen($order);

        return $this->ackResponse($request);
    }

    public function acceptAll(Request $request): JsonResponse|RedirectResponse
    {
        $this->kitchen->acceptAll();

        return $this->ackResponse($request);
    }

    public function ping(): JsonResponse
    {
        $ids = $this->kitchen->visibleIds();

        return response()->json([
            'ids' => $ids,
            'unacked' => $this->kitchen->unackedIds(),
            'incoming' => $this->kitchen->incomingPayload(),
            'count' => count($ids),
        ]);
    }

    protected function ackResponse(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            $unacked = $this->kitchen->unackedIds();

            return response()->json([
                'ok' => true,
                'unacked' => $unacked,
                'incoming' => $this->kitchen->incomingPayload(),
                'count' => count($this->kitchen->visibleIds()),
            ]);
        }

        return redirect()->route('kitchen.board');
    }

    protected function isVisible(Order $order): bool
    {
        return $this->kitchen->visibleQuery()->whereKey($order->id)->exists();
    }
}
