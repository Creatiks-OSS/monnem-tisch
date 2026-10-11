<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Quote;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * @return array{
     *     orders_count: int,
     *     revenue: float,
     *     leads: int,
     *     customers: int,
     *     popular_products: array,
     *     monthly_revenue: array,
     *     orders_by_status: array,
     *     average_order_value: float
     * }
     */
    public function dashboardStats(): array
    {
        $completedStatuses = ['geliefert', 'abgeschlossen', 'bestätigt', 'in_bearbeitung', 'vorbereitung', 'unterwegs'];

        $ordersCount = Order::query()->placed()->whereNull('deleted_at')->count();
        $revenue = (float) Order::query()
            ->placed()
            ->whereNull('deleted_at')
            ->where('status', '!=', 'storniert')
            ->sum('total');

        $leads = Quote::query()->whereNull('deleted_at')->count();
        $customers = Customer::query()->whereNull('deleted_at')->count();

        $popularProducts = OrderItem::query()
            ->select('product_id', 'name', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(line_total) as revenue'))
            ->whereNotNull('product_id')
            ->groupBy('product_id', 'name')
            ->orderByDesc('qty')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'product_id' => $row->product_id,
                'name' => $row->name,
                'quantity' => (int) $row->qty,
                'revenue' => round((float) $row->revenue, 2),
            ])
            ->all();

        $monthlyRevenue = $this->monthlyRevenue(12);

        $ordersByStatus = Order::query()
            ->placed()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($v) => (int) $v)
            ->all();

        $paidOrders = Order::query()
            ->placed()
            ->whereNull('deleted_at')
            ->where('status', '!=', 'storniert')
            ->count();

        return [
            'orders_count' => $ordersCount,
            'revenue' => round($revenue, 2),
            'leads' => $leads,
            'customers' => $customers,
            'popular_products' => $popularProducts,
            'monthly_revenue' => $monthlyRevenue,
            'orders_by_status' => $ordersByStatus,
            'average_order_value' => $paidOrders > 0 ? round($revenue / $paidOrders, 2) : 0.0,
        ];
    }

    /**
     * Optional page-view tracker stub — stores nothing harmful / PII.
     */
    public function trackPageView(?string $path = null, array $meta = []): void
    {
        // Intentionally a no-op for privacy-safe default. Chart data comes from DB aggregates.
    }

    /**
     * @return array<int, array{month: string, label: string, revenue: float, orders: int}>
     */
    public function monthlyRevenue(int $months = 12): array
    {
        $start = now()->startOfMonth()->subMonths($months - 1);
        $orders = Order::query()
            ->placed()
            ->where('created_at', '>=', $start)
            ->where('status', '!=', 'storniert')
            ->get(['created_at', 'total']);

        $grouped = $orders->groupBy(fn ($order) => Carbon::parse($order->created_at)->format('Y-m'));

        $result = [];
        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');
            $bucket = $grouped->get($key, collect());
            $result[] = [
                'month' => $key,
                'label' => $month->translatedFormat('M Y'),
                'revenue' => round((float) $bucket->sum('total'), 2),
                'orders' => $bucket->count(),
            ];
        }

        return $result;
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, float>}
     */
    public function chartMonthlyRevenue(int $months = 12): array
    {
        $data = $this->monthlyRevenue($months);

        return [
            'labels' => array_column($data, 'label'),
            'values' => array_column($data, 'revenue'),
        ];
    }
}
