<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CateringPackage;
use App\Models\EventType;
use App\Models\Extra;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\AvailabilityService;
use App\Services\CartService;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfiguratorController extends Controller
{
    public function __construct(
        protected PricingService $pricing,
        protected CartService $cart,
        protected AvailabilityService $availability
    ) {}

    public function index(): View
    {
        $eventTypes = EventType::query()
            ->active()
            ->orderBy('sort_order')
            ->get();

        $categories = ProductCategory::query()
            ->active()
            ->whereNull('parent_id')
            ->with(['products' => fn ($q) => $q->active()->orderBy('sort_order')->limit(20)])
            ->orderBy('sort_order')
            ->get();

        $packages = CateringPackage::query()
            ->active()
            ->with(['eventType', 'products'])
            ->orderBy('sort_order')
            ->get();

        $extras = Extra::query()
            ->active()
            ->orderBy('kind')
            ->orderBy('sort_order')
            ->get();

        $blockedDates = $this->availability->getBlockedDates(now(), now()->addMonths(6));

        return view('frontend.configurator.index', [
            'eventTypes' => $eventTypes,
            'categories' => $categories,
            'packages' => $packages,
            'extras' => $extras,
            'blockedDates' => $blockedDates,
            'cart' => $this->cart->getCart(),
            'totals' => $this->cart->totals(),
        ]);
    }

    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'persons' => ['required', 'integer', 'min:1', 'max:5000'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'items' => ['nullable', 'array'],
            'items.*.product_id' => ['required_with:items', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'items.*.persons' => ['nullable', 'integer', 'min:1'],
            'items.*.options' => ['nullable', 'array'],
            'extras' => ['nullable', 'array'],
            'extras.*.extra_id' => ['required_with:extras', 'integer', 'exists:extras,id'],
            'extras.*.quantity' => ['nullable', 'integer', 'min:1'],
            'package_id' => ['nullable', 'integer', 'exists:catering_packages,id'],
        ], [
            'required' => 'Das Feld :attribute ist erforderlich.',
            'exists' => 'Die ausgewählte :attribute ist ungültig.',
            'integer' => 'Das Feld :attribute muss eine ganze Zahl sein.',
            'min' => 'Das Feld :attribute muss mindestens :min sein.',
        ], [
            'persons' => 'Personenanzahl',
            'postal_code' => 'PLZ',
            'coupon_code' => 'Gutscheincode',
            'items' => 'Positionen',
            'extras' => 'Extras',
            'package_id' => 'Paket',
        ]);

        $persons = (int) $validated['persons'];
        $items = [];

        foreach ($validated['items'] ?? [] as $row) {
            $product = Product::query()->with('priceTiers')->find($row['product_id']);
            if (! $product) {
                continue;
            }

            $qty = max(1, (int) ($row['quantity'] ?? 1));
            $itemPersons = max(1, (int) ($row['persons'] ?? $persons));
            $lineTotal = $this->pricing->calculateProductPrice($product, $itemPersons, $qty);

            $optionModifier = 0.0;
            foreach ($row['options'] ?? [] as $option) {
                if (is_array($option) && isset($option['price_modifier'])) {
                    $optionModifier += (float) $option['price_modifier'];
                }
            }

            $items[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => $qty,
                'persons' => $itemPersons,
                'unit_price' => $product->getPriceForPersons($itemPersons),
                'line_total' => round($lineTotal + ($optionModifier * $qty), 2),
                'options' => $row['options'] ?? [],
            ];
        }

        if (! empty($validated['package_id'])) {
            $package = CateringPackage::query()
                ->active()
                ->with('products')
                ->find($validated['package_id']);

            if ($package) {
                $packageTotal = (float) $package->base_price + ((float) $package->price_per_person * $persons);
                $items[] = [
                    'product_id' => null,
                    'name' => $package->name,
                    'quantity' => 1,
                    'persons' => $persons,
                    'unit_price' => $packageTotal,
                    'line_total' => round($packageTotal, 2),
                    'options' => ['package_id' => $package->id],
                ];
            }
        }

        $extras = [];
        foreach ($validated['extras'] ?? [] as $row) {
            $extra = Extra::query()->find($row['extra_id']);
            if (! $extra) {
                continue;
            }
            $qty = max(1, (int) ($row['quantity'] ?? 1));
            $extras[] = [
                'extra_id' => $extra->id,
                'name' => $extra->name,
                'quantity' => $qty,
                'persons' => $persons,
                'unit_price' => (float) $extra->price,
                'line_total' => $this->pricing->calculateExtraPrice($extra, $qty, $persons),
            ];
        }

        $totals = $this->pricing->calculateOrderTotals(
            $items,
            $extras,
            $validated['postal_code'] ?? null,
            $validated['coupon_code'] ?? null
        );

        return response()->json([
            'success' => true,
            'persons' => $persons,
            'items' => $items,
            'extras' => $extras,
            'totals' => $totals,
        ]);
    }
}
