<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $query = BlogPost::query()
            ->published()
            ->with(['category', 'author', 'tags'])
            ->orderByDesc('published_at');

        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->toString().'%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                    ->orWhere('excerpt', 'like', $term)
                    ->orWhere('content', 'like', $term);
            });
        }

        $posts = $query->paginate(6)->withQueryString();

        $categories = BlogCategory::query()
            ->active()
            ->withCount(['posts' => fn ($q) => $q->published()])
            ->orderBy('sort_order')
            ->get();

        return view('frontend.blog.index', [
            'posts' => $posts,
            'categories' => $categories,
            'currentCategory' => null,
        ]);
    }

    public function show(string $slug): View
    {
        $post = BlogPost::query()
            ->published()
            ->where('slug', $slug)
            ->with(['category', 'author', 'tags', 'seo'])
            ->firstOrFail();

        $post->increment('views');

        $related = BlogPost::query()
            ->published()
            ->where('id', '!=', $post->id)
            ->when($post->blog_category_id, fn ($q) => $q->where('blog_category_id', $post->blog_category_id))
            ->with('category')
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('frontend.blog.show', [
            'post' => $post,
            'related' => $related,
        ]);
    }

    public function category(string $slug): View
    {
        $category = BlogCategory::query()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        $posts = BlogPost::query()
            ->published()
            ->where('blog_category_id', $category->id)
            ->with(['category', 'author', 'tags'])
            ->orderByDesc('published_at')
            ->paginate(6);

        $categories = BlogCategory::query()
            ->active()
            ->withCount(['posts' => fn ($q) => $q->published()])
            ->orderBy('sort_order')
            ->get();

        return view('frontend.blog.index', [
            'posts' => $posts,
            'categories' => $categories,
            'currentCategory' => $category,
        ]);
    }
}
