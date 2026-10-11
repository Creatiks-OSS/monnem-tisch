<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\ProductCategory;
use App\Services\ActivityLogService;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        protected MediaService $media,
        protected ActivityLogService $activity
    ) {}

    public function index(Request $request): View
    {
        $categories = ProductCategory::query()
            ->with('parent')
            ->withCount('products')
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(40)
            ->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        $parents = ProductCategory::query()->orderBy('name')->get();

        return view('admin.categories.form', [
            'category' => new ProductCategory(['is_active' => true]),
            'parents' => $parents,
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        unset($data['image']);

        if ($request->hasFile('image')) {
            $media = $this->media->upload($request->file('image'), 'categories');
            $data['image'] = $media->path;
        }

        $category = ProductCategory::query()->create($data);
        $this->activity->log('Kategori oluşturuldu', $category);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori oluşturuldu.');
    }

    public function edit(ProductCategory $category): View
    {
        $parents = ProductCategory::query()->where('id', '!=', $category->id)->orderBy('name')->get();

        return view('admin.categories.form', compact('category', 'parents'));
    }

    public function update(CategoryRequest $request, ProductCategory $category): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        unset($data['image']);

        if ($request->hasFile('image')) {
            $media = $this->media->upload($request->file('image'), 'categories');
            $data['image'] = $media->path;
        }

        $category->update($data);
        $this->activity->log('Kategori güncellendi', $category);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori kaydedildi.');
    }

    public function destroy(ProductCategory $category): RedirectResponse
    {
        $category->delete();
        $this->activity->log('Kategori silindi', $category);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori silindi.');
    }

    public function show(ProductCategory $category): RedirectResponse
    {
        return redirect()->route('admin.categories.edit', $category);
    }
}