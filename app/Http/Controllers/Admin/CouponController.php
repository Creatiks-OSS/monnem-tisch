<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function __construct(protected ActivityLogService $activity) {}

    public function index(): View
    {
        $coupons = Coupon::query()->latest()->paginate(20);

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create(): View
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $coupon = Coupon::query()->create($this->validated($request));
        $this->activity->log('Kupon oluşturuldu', $coupon);

        return redirect()->route('admin.coupons.index')->with('success', 'Kupon oluşturuldu.');
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $coupon->update($this->validated($request, $coupon->id));
        $this->activity->log('Kupon güncellendi', $coupon);

        return redirect()->route('admin.coupons.index')->with('success', 'Kupon kaydedildi.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();
        $this->activity->log('Kupon silindi', $coupon);

        return redirect()->route('admin.coupons.index')->with('success', 'Kupon silindi.');
    }

    protected function validated(Request $request, ?int $id = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:coupons,code,'.($id ?? 'NULL')],
            'type' => ['required', 'in:fixed,percent'],
            'value' => ['required', 'numeric', 'min:0'],
            'minimum_order' => ['nullable', 'numeric', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['code'] = strtoupper($data['code']);

        return $data;
    }
}