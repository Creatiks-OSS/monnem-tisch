<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderStatusRequest;
use App\Models\Order;
use App\Services\ActivityLogService;
use App\Services\IngredientAnalysisService;
use App\Services\KitchenService;
use App\Services\OpenAiService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orders,
        protected ActivityLogService $activity
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Order::class);

        $orders = Order::query()
            ->placed()
            ->with(['customer', 'eventType'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('order_number', 'like', $term)
                        ->orWhere('billing_email', 'like', $term)
                        ->orWhere('billing_last_name', 'like', $term)
                        ->orWhere('billing_first_name', 'like', $term);
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);
        $order->load(['customer', 'items', 'extras.extra', 'eventType', 'invoice', 'payments']);

        return view('admin.orders.show', compact('order'));
    }

    public function analysis(Order $order, IngredientAnalysisService $analysis, OpenAiService $openai): View
    {
        $this->authorize('view', $order);
        $order->load(['customer', 'items', 'extras.extra', 'ingredientAnalysis']);

        $ingredientAnalysis = $order->ingredientAnalysis;
        if ($openai->isEnabled() && (! $ingredientAnalysis || $ingredientAnalysis->isBusy())) {
            $ingredientAnalysis = $analysis->queue($order);
        }

        return view('admin.orders.analysis', [
            'order' => $order,
            'ingredientAnalysis' => $ingredientAnalysis,
            'aiConfigured' => $openai->isEnabled(),
        ]);
    }

    public function ingredientStatus(Order $order): JsonResponse
    {
        $this->authorize('view', $order);
        $analysis = $order->ingredientAnalysis;

        return response()->json([
            'status' => $analysis?->status ?? 'missing',
            'ready' => $analysis?->status === 'completed',
            'failed' => $analysis?->status === 'failed',
        ]);
    }

    public function analyzeIngredients(Order $order, IngredientAnalysisService $analysis): RedirectResponse
    {
        $this->authorize('view', $order);
        $analysis->queue($order, true);

        return redirect()->route('admin.orders.analysis', $order)->with('success', 'Malzeme analizi başlatıldı.');
    }

    public function edit(Order $order): View
    {
        $this->authorize('update', $order);
        $order->load(['customer', 'items', 'extras', 'eventType']);

        return view('admin.orders.edit', compact('order'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        $data = $request->validate([
            'admin_notes' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'event_date' => ['nullable', 'date'],
            'event_time' => ['nullable', 'string', 'max:20'],
            'persons' => ['nullable', 'integer', 'min:1'],
            'payment_status' => ['nullable', 'string', 'max:50'],
        ]);

        $order->update($data);
        $this->activity->log('Sipariş güncellendi', $order);

        return redirect()->route('admin.orders.show', $order)->with('success', 'Sipariş kaydedildi.');
    }

    public function updateDurum(OrderStatusRequest $request, Order $order): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $order);

        $this->orders->updateDurum($order, $request->validated('status'));

        if ($request->filled('admin_notes')) {
            $order->update(['admin_notes' => $request->validated('admin_notes')]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $order->fresh()->status,
                'message' => 'Durum güncellendi.',
            ]);
        }

        return back()->with('success', 'Durum güncellendi.');
    }

    public function updateStatus(OrderStatusRequest $request, Order $order): JsonResponse|RedirectResponse
    {
        return $this->updateDurum($request, $order);
    }

    public function markPaid(Order $order, PaymentService $payments): RedirectResponse
    {
        $this->authorize('update', $order);
        $payments->markPaid($order, $order->payment_method ?: 'rechnung', 'manual-'.$order->id, ['source' => 'admin']);

        return back()->with('success', 'Ödeme alındı olarak işaretlendi.');
    }

    public function sendToKitchen(Order $order, KitchenService $kitchen): RedirectResponse
    {
        $this->authorize('update', $order);
        $kitchen->send($order);
        $this->activity->log('Sipariş mutfağa gönderildi', $order);

        return back()->with('success', 'Sipariş mutfak paneline düştü.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $this->authorize('delete', $order);
        $customer = $order->customer;
        $this->activity->log('Sipariş silindi', $order);
        $order->delete();

        if ($customer) {
            app(\App\Services\CustomerSegmentService::class)->release($customer);
        }

        return redirect()->route('admin.orders.index')->with('success', 'Sipariş silindi.');
    }
}