<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\GalleryImage;
use App\Services\ActivityLogService;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function __construct(
        protected MediaService $media,
        protected ActivityLogService $activity
    ) {}

    public function index(): View
    {
        $galleries = Gallery::query()->withCount('images')->orderBy('sort_order')->paginate(20);

        return view('admin.galleries.index', compact('galleries'));
    }

    public function create(): View
    {
        return view('admin.galleries.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        if ($request->hasFile('cover_image')) {
            $media = $this->media->upload($request->file('cover_image'), 'galleries');
            $data['cover_image'] = $media->path;
        }
        $gallery = Gallery::query()->create($data);
        $this->activity->log('Galeri oluşturuldu', $gallery);

        return redirect()->route('admin.galleries.edit', $gallery)->with('success', 'Galeri oluşturuldu.');
    }

    public function edit(Gallery $gallery): View
    {
        $gallery->load('images');

        return view('admin.galleries.edit', compact('gallery'));
    }

    public function update(Request $request, Gallery $gallery): RedirectResponse
    {
        $data = $this->validated($request);
        if ($request->hasFile('cover_image')) {
            $media = $this->media->upload($request->file('cover_image'), 'galleries');
            $data['cover_image'] = $media->path;
        }
        $gallery->update($data);
        $this->activity->log('Galeri güncellendi', $gallery);

        return redirect()->route('admin.galleries.edit', $gallery)->with('success', 'Galeri kaydedildi.');
    }

    public function destroy(Gallery $gallery): RedirectResponse
    {
        $this->activity->log('Galeri silindi', $gallery);
        $gallery->delete();

        return redirect()->route('admin.galleries.index')->with('success', 'Galeri silindi.');
    }

    public function storeImage(Request $request, Gallery $gallery): RedirectResponse
    {
        $request->validate([
            'images' => ['required', 'array'],
            'images.*' => ['image', 'max:15360'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($request->file('images') as $i => $file) {
            $media = $this->media->upload($file, 'galleries');
            GalleryImage::query()->create([
                'gallery_id' => $gallery->id,
                'path' => $media->path,
                'title' => $request->input('title'),
                'alt' => $request->input('title') ?: $gallery->name,
                'sort_order' => ($gallery->images()->max('sort_order') ?? 0) + $i + 1,
            ]);
        }

        return back()->with('success', 'Görseller eklendi.');
    }

    public function destroyImage(Gallery $gallery, GalleryImage $image): RedirectResponse|JsonResponse
    {
        abort_unless($image->gallery_id === $gallery->id, 404);
        $image->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Görsel silindi.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'max:15360'],
            'category' => ['nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        unset($data['cover_image']);
        $data['is_active'] = $request->boolean('is_active');
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        return $data;
    }
}