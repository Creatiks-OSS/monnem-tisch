<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CateringController extends Controller
{
    public function index(): View
    {
        $categories = ProductCategory::query()
            ->active()
            ->whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->active()->orderBy('sort_order')])
            ->withCount(['products' => fn ($q) => $q->active()])
            ->orderBy('sort_order')
            ->get();

        $featured = ProductCategory::query()
            ->active()
            ->featured()
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        return view('frontend.catering.index', [
            'categories' => $categories,
            'featured' => $featured,
        ]);
    }

    public function show(string $slug, Request $request): View
    {
        $category = ProductCategory::query()
            ->active()
            ->where('slug', $slug)
            ->with(['children' => fn ($q) => $q->active()->orderBy('sort_order'), 'parent'])
            ->firstOrFail();

        $query = Product::query()
            ->active()
            ->where(function ($q) use ($category) {
                $q->where('category_id', $category->id);

                $childIds = $category->children->pluck('id');
                if ($childIds->isNotEmpty()) {
                    $q->orWhereIn('category_id', $childIds);
                }
            })
            ->with(['images', 'priceTiers']);

        if ($request->boolean('vegan')) {
            $query->where('is_vegan', true);
        }
        if ($request->boolean('vegetarian')) {
            $query->where('is_vegetarian', true);
        }
        if ($request->boolean('gluten_free')) {
            $query->where('is_gluten_free', true);
        }
        if ($request->filled('sort')) {
            match ($request->string('sort')->toString()) {
                'price_asc' => $query->orderBy('price'),
                'price_desc' => $query->orderByDesc('price'),
                'name' => $query->orderBy('name'),
                default => $query->orderBy('sort_order'),
            };
        } else {
            $query->orderBy('sort_order');
        }

        $products = $query->paginate(12)->withQueryString();

        return view('frontend.catering.show', [
            'category' => $category,
            'products' => $products,
            'filters' => $request->only(['vegan', 'vegetarian', 'gluten_free', 'sort']),
        ]);
    }
}
