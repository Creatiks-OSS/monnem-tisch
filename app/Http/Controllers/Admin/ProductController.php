<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\ProductOption;
use App\Models\ProductPriceTier;
use App\Services\ActivityLogService;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        protected MediaService $media,
        protected ActivityLogService $activity
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::query()
            ->with(['category.parent', 'images'])
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->when($request->input('status') === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->input('status') === 'active', fn ($q) => $q->where('is_active', true))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        $categories = ProductCategory::query()
            ->with('parent')
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);
        $categories = ProductCategory::query()->active()->with('parent')->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.products.form', [
            'product' => new Product(['is_active' => true, 'price' => 0, 'price_type' => 'person']),
            'categories' => $categories,
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $product = DB::transaction(function () use ($request) {
            $data = $request->safe()->except(['image', 'gallery_images', 'tiers', 'options', 'allergens']);
            $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
            $data['allergens'] = $request->input('allergens', []);

            if ($request->hasFile('image')) {
                $media = $this->media->upload($request->file('image'), 'products');
                $data['image'] = $media->path;
            }

            $product = Product::query()->create($data);
            $this->syncRelations($product, $request);

            return $product;
        });

        $this->activity->log('Yemek oluşturuldu', $product);

        return redirect()->route('admin.products.edit', $product)->with('success', 'Yemek oluşturuldu. Paket kotalarında seçenek olarak görünür.');
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);
        $product->load(['images', 'priceTiers', 'options', 'category']);
        $categories = ProductCategory::query()->active()->with('parent')->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.products.form', compact('product', 'categories'));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        DB::transaction(function () use ($request, $product) {
            $data = $request->safe()->except(['image', 'gallery_images', 'tiers', 'options', 'allergens']);
            $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
            $data['allergens'] = $request->input('allergens', []);

            if ($request->hasFile('image')) {
                $media = $this->media->upload($request->file('image'), 'products');
                $data['image'] = $media->path;
            }

            $product->update($data);
            $this->syncRelations($product, $request);
        });

        $this->activity->log('Yemek güncellendi', $product);

        return redirect()->route('admin.products.edit', $product)->with('success', 'Yemek kaydedildi.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);
        $product->delete();
        $this->activity->log('Yemek silindi', $product);

        return redirect()->route('admin.products.index')->with('success', 'Yemek silindi.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $this->authorize('deleteAny', Product::class);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:products,id'],
        ], [
            'ids.required' => 'Silinecek yemek seçin.',
            'ids.min' => 'Silinecek yemek seçin.',
        ]);

        $products = Product::query()->whereIn('id', $data['ids'])->get();

        foreach ($products as $product) {
            $this->authorize('delete', $product);
            $product->delete();
        }

        $count = $products->count();
        $this->activity->log($count.' yemek silindi', null, ['ids' => $products->pluck('id')->all()]);

        return redirect()
            ->route('admin.products.index', $request->only(['q', 'category_id', 'status']))
            ->with('success', $count === 1 ? '1 yemek silindi.' : $count.' yemek silindi.');
    }

    protected function syncRelations(Product $product, ProductRequest $request): void
    {
        if ($request->hasFile('gallery_images')) {
            $firstGalleryPath = null;
            $sortBase = (int) $product->images()->max('sort_order');
            foreach ($request->file('gallery_images') as $i => $file) {
                if (! $file) {
                    continue;
                }
                $media = $this->media->upload($file, 'products');
                $firstGalleryPath ??= $media->path;
                ProductImage::query()->create([
                    'product_id' => $product->id,
                    'path' => $media->path,
                    'alt' => $product->name,
                    'sort_order' => $sortBase + $i + 1,
                ]);
            }
            if ($firstGalleryPath && ! $request->hasFile('image')) {
                $product->update(['image' => $firstGalleryPath]);
            }
        }

        if ($request->has('tiers')) {
            $product->priceTiers()->delete();
            foreach ($request->input('tiers', []) as $tier) {
                if (empty($tier['min_persons']) || ! isset($tier['price'])) {
                    continue;
                }
                ProductPriceTier::query()->create([
                    'product_id' => $product->id,
                    'min_persons' => (int) $tier['min_persons'],
                    'max_persons' => $tier['max_persons'] !== null && $tier['max_persons'] !== '' ? (int) $tier['max_persons'] : null,
                    'price' => (float) $tier['price'],
                ]);
            }
        }

        if ($request->has('options')) {
            $product->options()->delete();
            foreach ($request->input('options', []) as $i => $opt) {
                if (empty($opt['name'])) {
                    continue;
                }
                ProductOption::query()->create([
                    'product_id' => $product->id,
                    'name' => $opt['name'],
                    'type' => $opt['type'] ?? 'select',
                    'options' => isset($opt['options']) ? (is_array($opt['options']) ? $opt['options'] : array_filter(array_map('trim', explode(',', (string) $opt['options'])))) : null,
                    'price_modifier' => (float) ($opt['price_modifier'] ?? 0),
                    'is_required' => ! empty($opt['is_required']),
                    'sort_order' => $i,
                ]);
            }
        }
    }
}