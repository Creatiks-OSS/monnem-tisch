<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CateringPackage;
use App\Models\Extra;
use App\Services\CartService;
use App\Services\MenuPageContent;
use App\Services\PackageBuilderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function __construct(
        protected PackageBuilderService $builder,
        protected CartService $cart,
        protected MenuPageContent $menuPages
    ) {}

    public function index(string $menu): View
    {
        abort_unless(CateringPackage::isMenu($menu), 404);

        $meta = CateringPackage::menuMetaFor($menu);

        $packages = CateringPackage::query()
            ->active()
            ->forMenu($menu)
            ->with(['slots.category'])
            ->orderBy('price_per_person')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $menuBody = $this->menuPages->paragraphs($menu);
        $menuExcerpt = $this->menuPages->excerpt($menu);

        return view('frontend.packages.index', compact('packages', 'meta', 'menuBody', 'menuExcerpt'));
    }

    public function redirectLegacyMenu(string $slug): RedirectResponse
    {
        return redirect()->route('packages.index', CateringPackage::menuKeyFromSlug($slug), 301);
    }

    public function show(string $menu, string $slug): View
    {
        abort_unless(CateringPackage::isMenu($menu), 404);

        $package = CateringPackage::query()
            ->active()
            ->forMenu($menu)
            ->where('slug', $slug)
            ->with(['slots.category', 'slots.items.product', 'seo'])
            ->firstOrFail();

        $meta = $package->menuMeta();
        $slots = $this->builder->slotsPayload($package);

        $extras = Extra::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Extra $extra) => [
                'id' => $extra->id,
                'name' => $extra->name,
                'price' => (float) $extra->price,
                'price_type' => $extra->price_type ?: 'stück',
                'price_label' => $extra->formattedPrice(),
                'image' => $extra->displayImage(),
            ])
            ->values();

        return view('frontend.packages.show', [
            'package' => $package,
            'meta' => $meta,
            'slots' => $slots,
            'extras' => $extras,
            'related' => CateringPackage::query()
                ->active()
                ->forMenu($menu)
                ->where('id', '!=', $package->id)
                ->orderBy('price_per_person')
                ->orderBy('sort_order')
                ->limit(3)
                ->get(),
        ]);
    }

    public function addToCart(Request $request, string $menu, string $slug): JsonResponse|RedirectResponse
    {
        abort_unless(CateringPackage::isMenu($menu), 404);

        $package = CateringPackage::query()
            ->active()
            ->forMenu($menu)
            ->where('slug', $slug)
            ->with('slots')
            ->firstOrFail();

        $validated = $request->validate([
            'persons' => ['required', 'integer', 'min:1', 'max:5000'],
            'selections' => ['required', 'array'],
            'selections.*.slot_id' => ['required', 'integer'],
            'selections.*.product_ids' => ['required', 'array'],
            'selections.*.product_ids.*' => ['integer', 'exists:products,id'],
            'extra_ids' => ['nullable', 'array'],
            'extra_ids.*' => ['integer', 'exists:extras,id'],
        ]);

        $result = $this->builder->assertValid(
            $package,
            $validated['selections'],
            (int) $validated['persons']
        );

        $persons = (int) $validated['persons'];

        $this->cart->addPackage(
            $package,
            $persons,
            $result['selected']
        );
        $this->cart->replaceExtras($validated['extra_ids'] ?? [], $persons);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Weiter zur Kasse.',
                'cart' => $this->cart->getCart(),
                'totals' => $this->cart->totals(),
                'redirect' => route('checkout.index'),
            ]);
        }

        return redirect()->route('checkout.index');
    }
}
