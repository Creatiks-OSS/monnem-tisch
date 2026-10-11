<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogCategoryController extends Controller
{
    public function __construct(protected ActivityLogService $activity) {}

    public function index(): View
    {
        $categories = BlogCategory::query()->withCount('posts')->orderBy('name')->paginate(30);

        return view('admin.blog.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.blog.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $category = BlogCategory::query()->create($data);
        $this->activity->log('Blog kategorisi oluşturuldu', $category);

        return redirect()->route('admin.blog-categories.index')->with('success', 'Kategori oluşturuldu.');
    }

    public function edit(BlogCategory $blogCategory): View
    {
        return view('admin.blog.categories.edit', ['category' => $blogCategory]);
    }

    public function update(Request $request, BlogCategory $blogCategory): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $blogCategory->update($data);
        $this->activity->log('Blog kategorisi güncellendi', $blogCategory);

        return redirect()->route('admin.blog-categories.index')->with('success', 'Kategori kaydedildi.');
    }

    public function destroy(BlogCategory $blogCategory): RedirectResponse
    {
        $blogCategory->delete();
        $this->activity->log('Blog kategorisi silindi', $blogCategory);

        return redirect()->route('admin.blog-categories.index')->with('success', 'Kategori silindi.');
    }
}