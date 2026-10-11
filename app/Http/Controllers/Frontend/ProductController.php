<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query()
            ->active()
            ->with(['category', 'images']);

        if ($request->filled('category')) {
            $categorySlug = $request->string('category')->toString();
            $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->toString().'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('short_description', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        foreach (['is_vegan' => 'vegan', 'is_vegetarian' => 'vegetarian', 'is_gluten_free' => 'gluten_free', 'is_lactose_free' => 'lactose_free'] as $column => $param) {
            if ($request->boolean($param)) {
                $query->where($column, true);
            }
        }

        if ($request->boolean('featured')) {
            $query->featured();
        }

        $sort = $request->string('sort')->toString();
        match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'name' => $query->orderBy('name'),
            'newest' => $query->orderByDesc('created_at'),
            default => $query->orderBy('sort_order'),
        };

        $products = $query->paginate(16)->withQueryString();

        $categories = ProductCategory::query()
            ->active()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return view('frontend.products.index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['category', 'q', 'vegan', 'vegetarian', 'gluten_free', 'lactose_free', 'featured', 'sort']),
        ]);
    }

    public function show(string $slug): View
    {
        $product = Product::query()
            ->active()
            ->where('slug', $slug)
            ->with(['category', 'images', 'priceTiers', 'options'])
            ->firstOrFail();

        $related = Product::query()
            ->active()
            ->where('id', '!=', $product->id)
            ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
            ->with('images')
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        $reviews = Review::query()
            ->approved()
            ->where('product_id', $product->id)
            ->orderByDesc('reviewed_at')
            ->limit(10)
            ->get();

        return view('frontend.products.show', [
            'product' => $product,
            'related' => $related,
            'reviews' => $reviews,
        ]);
    }
}
