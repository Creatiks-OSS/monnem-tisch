<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CateringPackage;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quote;
use App\Services\AnalyticsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected AnalyticsService $analytics
    ) {}

    public function index(): View
    {
        $stats = $this->analytics->dashboardStats();
        $chart = $this->analytics->chartMonthlyRevenue(12);

        $recentOrders = Order::query()
            ->placed()
            ->with('customer')
            ->latest()
            ->limit(8)
            ->get();

        $recentQuotes = Quote::query()
            ->latest()
            ->limit(8)
            ->get();

        $menus = collect(CateringPackage::MENUS)->map(function (array $meta, string $key) {
            return array_merge($meta, [
                'key' => $key,
                'count' => CateringPackage::query()->forMenu($key)->count(),
                'active' => CateringPackage::query()->active()->forMenu($key)->count(),
            ]);
        });

        $dishCount = Product::query()->where('is_active', true)->count();

        return view('admin.dashboard', compact('stats', 'chart', 'recentOrders', 'recentQuotes', 'menus', 'dishCount'));
    }
}
