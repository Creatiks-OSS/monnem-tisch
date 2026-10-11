<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogPostRequest;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Tag;
use App\Services\ActivityLogService;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogPostController extends Controller
{
    public function __construct(
        protected MediaService $media,
        protected ActivityLogService $activity
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', BlogPost::class);

        $posts = BlogPost::query()
            ->with(['category', 'author'])
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%'.$request->q.'%'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.blog.posts.index', compact('posts'));
    }

    public function create(): View
    {
        $this->authorize('create', BlogPost::class);
        $categories = BlogCategory::query()->orderBy('name')->get();

        return view('admin.blog.posts.create', compact('categories'));
    }

    public function store(BlogPostRequest $request): RedirectResponse
    {
        $this->authorize('create', BlogPost::class);

        $data = $request->safe()->except(['featured_image', 'tags', 'schema_json']);
        $data['author_id'] = auth()->id();
        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        if (! empty($data['is_published']) && empty($data['published_at'])) {
            $data['published_at'] = now();
        }
        if ($request->hasFile('featured_image')) {
            $media = $this->media->upload($request->file('featured_image'), 'blog');
            $data['featured_image'] = $media->path;
        }

        $post = BlogPost::query()->create($data);
        $this->syncTags($post, $request->input('tags', []));
        app(\App\Services\SeoGeoService::class)->save($post, $request->input('schema_json'));
        $this->activity->log('Blogbeitrag oluşturuldu', $post);

        return redirect()->route('admin.blog-posts.edit', $post)->with('success', 'Yazı oluşturuldu.');
    }

    public function edit(BlogPost $blogPost): View
    {
        $this->authorize('update', $blogPost);
        $categories = BlogCategory::query()->orderBy('name')->get();
        $blogPost->load(['tags', 'seo']);

        return view('admin.blog.posts.edit', ['post' => $blogPost, 'categories' => $categories]);
    }

    public function update(BlogPostRequest $request, BlogPost $blogPost): RedirectResponse
    {
        $this->authorize('update', $blogPost);

        $data = $request->safe()->except(['featured_image', 'tags', 'schema_json']);
        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        if (! empty($data['is_published']) && empty($data['published_at']) && ! $blogPost->published_at) {
            $data['published_at'] = now();
        }
        if ($request->hasFile('featured_image')) {
            $media = $this->media->upload($request->file('featured_image'), 'blog');
            $data['featured_image'] = $media->path;
        }

        $blogPost->update($data);
        $this->syncTags($blogPost, $request->input('tags', []));
        app(\App\Services\SeoGeoService::class)->save($blogPost, $request->input('schema_json'));
        $this->activity->log('Blogbeitrag güncellendi', $blogPost);

        return redirect()->route('admin.blog-posts.edit', $blogPost)->with('success', 'Yazı kaydedildi.');
    }

    public function previewEntry(Request $request): RedirectResponse
    {
        $token = session('blog_preview_latest');
        $data = is_string($token) ? session('blog_preview.'.$token) : null;
        if (is_array($data) && (int) ($data['user_id'] ?? 0) === (int) $request->user()->id) {
            return redirect()->route('admin.blog-posts.preview-live', $token);
        }

        return redirect()->route('admin.blog-posts.index')
            ->with('error', 'Ön izleme için yazıyı açıp Ön izleme düğmesine basın.');
    }

    public function preview(Request $request): JsonResponse|RedirectResponse
    {
        $data = $this->previewData($request);

        if (! empty($data['post_id'])) {
            $this->authorize('update', BlogPost::query()->findOrFail($data['post_id']));
        } else {
            $this->authorize('create', BlogPost::class);
        }

        $token = Str::random(40);
        session()->put('blog_preview.'.$token, $data + ['user_id' => $request->user()->id]);
        session()->put('blog_preview_latest', $token);

        $url = route('admin.blog-posts.preview-live', $token);
        if ($request->expectsJson()) {
            return response()->json(['url' => $url]);
        }

        return redirect()->to($url);
    }

    public function previewLive(Request $request, string $token): Response
    {
        $data = session('blog_preview.'.$token);
        abort_unless(is_array($data) && (int) ($data['user_id'] ?? 0) === $request->user()->id, 404);

        return $this->renderPreview($this->postFromPreviewData($data, $request));
    }

    public function previewSaved(BlogPost $blogPost): Response
    {
        $this->authorize('view', $blogPost);
        $blogPost->load(['category', 'author', 'tags', 'seo']);

        return $this->renderPreview($blogPost);
    }

    public function destroy(BlogPost $blogPost): RedirectResponse
    {
        $this->authorize('delete', $blogPost);
        $blogPost->delete();
        $this->activity->log('Blogbeitrag silindi', $blogPost);

        return redirect()->route('admin.blog-posts.index')->with('success', 'Yazı silindi.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function previewData(Request $request): array
    {
        return $request->validate([
            'post_id' => ['nullable', 'integer', 'exists:blog_posts,id'],
            'blog_category_id' => ['nullable', 'exists:blog_categories,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'published_at' => ['nullable', 'date'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:100'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function postFromPreviewData(array $data, Request $request): BlogPost
    {
        if (! empty($data['post_id'])) {
            $post = BlogPost::query()->findOrFail($data['post_id']);
            $this->authorize('update', $post);
            $post->load(['author', 'seo']);
        } else {
            $this->authorize('create', BlogPost::class);
            $post = new BlogPost(['author_id' => $request->user()->id]);
            $post->setRelation('author', $request->user());
            $post->setRelation('seo', null);
        }

        $title = trim((string) ($data['title'] ?? ''));
        if ($title === '') {
            $title = 'Ohne Titel';
        }

        $post->fill([
            'blog_category_id' => $data['blog_category_id'] ?? null,
            'title' => $title,
            'slug' => ($data['slug'] ?? '') !== '' ? $data['slug'] : Str::slug($title),
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'] ?? '',
            'published_at' => $data['published_at'] ?? null,
        ]);

        $post->setRelation(
            'category',
            $post->blog_category_id ? BlogCategory::query()->find($post->blog_category_id) : null
        );
        $post->setRelation('tags', $this->previewTags($data['tags'] ?? []));

        return $post;
    }

    protected function renderPreview(BlogPost $post): Response
    {
        $related = BlogPost::query()
            ->published()
            ->when($post->exists, fn ($q) => $q->where('id', '!=', $post->id))
            ->when($post->blog_category_id, fn ($q) => $q->where('blog_category_id', $post->blog_category_id))
            ->with('category')
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return response()
            ->view('frontend.blog.show', [
                'post' => $post,
                'related' => $related,
                'preview' => true,
            ])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /**
     * @param  array<int, string>  $tags
     */
    protected function previewTags(array $tags)
    {
        $names = [];
        foreach ($tags as $tagName) {
            foreach (preg_split('/\s*,\s*/', (string) $tagName) ?: [] as $part) {
                $part = trim($part);
                if ($part !== '') {
                    $names[] = $part;
                }
            }
        }

        return collect(array_unique($names))->map(fn (string $name) => new Tag([
            'name' => $name,
            'slug' => Str::slug($name),
        ]));
    }

    protected function syncTags(BlogPost $post, array $tags): void
    {
        $names = [];
        foreach ($tags as $tagName) {
            foreach (preg_split('/\s*,\s*/', (string) $tagName) ?: [] as $part) {
                $part = trim($part);
                if ($part !== '') {
                    $names[] = $part;
                }
            }
        }

        $ids = [];
        foreach (array_unique($names) as $tagName) {
            $tag = Tag::query()->firstOrCreate(
                ['slug' => Str::slug($tagName)],
                ['name' => $tagName]
            );
            $ids[] = $tag->id;
        }
        $post->tags()->sync($ids);
    }
}