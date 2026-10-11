<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryZoneController extends Controller
{
    public function __construct(protected ActivityLogService $activity) {}

    public function index(): View
    {
        $zones = DeliveryZone::query()->orderBy('sort_order')->paginate(20);

        return view('admin.delivery-zones.index', compact('zones'));
    }

    public function create(): View
    {
        return view('admin.delivery-zones.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $zone = DeliveryZone::query()->create($this->validated($request));
        $this->activity->log('Teslimat bölgesi oluşturuldu', $zone);

        return redirect()->route('admin.delivery-zones.index')->with('success', 'Teslimat bölgesi oluşturuldu.');
    }

    public function edit(DeliveryZone $deliveryZone): View
    {
        return view('admin.delivery-zones.edit', ['zone' => $deliveryZone]);
    }

    public function update(Request $request, DeliveryZone $deliveryZone): RedirectResponse
    {
        $deliveryZone->update($this->validated($request));
        $this->activity->log('Teslimat bölgesi güncellendi', $deliveryZone);

        return redirect()->route('admin.delivery-zones.index')->with('success', 'Teslimat bölgesi kaydedildi.');
    }

    public function destroy(DeliveryZone $deliveryZone): RedirectResponse
    {
        $deliveryZone->delete();
        $this->activity->log('Teslimat bölgesi silindi', $deliveryZone);

        return redirect()->route('admin.delivery-zones.index')->with('success', 'Teslimat bölgesi silindi.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'postal_codes' => ['nullable', 'string'],
            'radius_km' => ['nullable', 'numeric', 'min:0'],
            'minimum_order' => ['nullable', 'numeric', 'min:0'],
            'delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'free_delivery_threshold' => ['nullable', 'numeric', 'min:0'],
            'price_per_km' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        if (isset($data['postal_codes']) && is_string($data['postal_codes'])) {
            $data['postal_codes'] = array_values(array_filter(array_map('trim', preg_split('/[\s,;]+/', $data['postal_codes']))));
        }

        return $data;
    }
}