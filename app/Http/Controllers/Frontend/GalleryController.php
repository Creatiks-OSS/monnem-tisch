<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        $galleries = Gallery::query()
            ->active()
            ->withCount('images')
            ->with(['images' => fn ($q) => $q->limit(1)])
            ->orderBy('sort_order')
            ->get();

        return view('frontend.gallery.index', [
            'galleries' => $galleries,
        ]);
    }

    public function show(string $slug): View
    {
        $gallery = Gallery::query()
            ->active()
            ->where('slug', $slug)
            ->with(['images' => fn ($q) => $q->orderBy('sort_order')])
            ->firstOrFail();

        return view('frontend.gallery.show', [
            'gallery' => $gallery,
            'images' => $gallery->images,
        ]);
    }
}
