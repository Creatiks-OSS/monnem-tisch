<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\CateringPackage;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request): View|JsonResponse
    {
        $q = trim($request->string('q')->toString());

        $packages = collect();
        $posts = collect();
        $pages = collect();

        if ($q !== '') {
            $term = '%'.$q.'%';

            $packages = CateringPackage::query()
                ->active()
                ->with('slots')
                ->where(function ($query) use ($term) {
                    $query->where('name', 'like', $term)
                        ->orWhere('subtitle', 'like', $term)
                        ->orWhere('description', 'like', $term);
                })
                ->orderBy('sort_order')
                ->limit(12)
                ->get();

            $posts = BlogPost::query()
                ->published()
                ->where(function ($query) use ($term) {
                    $query->where('title', 'like', $term)
                        ->orWhere('excerpt', 'like', $term);
                })
                ->orderByDesc('published_at')
                ->limit(6)
                ->get();

            $pages = Page::query()
                ->published()
                ->where(function ($query) use ($term) {
                    $query->where('title', 'like', $term)
                        ->orWhere('excerpt', 'like', $term);
                })
                ->orderBy('sort_order')
                ->limit(6)
                ->get();
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'q' => $q,
                'packages' => $packages->map(fn (CateringPackage $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'url' => $p->publicUrl(),
                    'price' => $p->price_per_person,
                    'image' => $p->imageUrl(),
                    'menu' => $p->menuMeta()['label'] ?? 'Buffets',
                ])->values(),
                'posts' => $posts->map(fn (BlogPost $p) => [
                    'id' => $p->id,
                    'title' => $p->title,
                    'slug' => $p->slug,
                    'url' => route('blog.show', $p->slug),
                    'excerpt' => $p->excerpt,
                ])->values(),
                'pages' => $pages->map(fn (Page $p) => [
                    'id' => $p->id,
                    'title' => $p->title,
                    'slug' => $p->slug,
                    'url' => route('pages.show', $p->slug),
                ])->values(),
            ]);
        }

        return view('frontend.search', [
            'q' => $q,
            'packages' => $packages,
            'posts' => $posts,
            'pages' => $pages,
        ]);
    }
}
