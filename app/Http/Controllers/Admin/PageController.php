<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageRequest;
use App\Models\Page;
use App\Models\PageSection;
use App\Services\ActivityLogService;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(
        protected MediaService $media,
        protected ActivityLogService $activity
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Page::class);
        $pages = Page::query()->with('parent')->orderBy('sort_order')->orderBy('title')->paginate(30);

        return view('admin.pages.index', compact('pages'));
    }

    public function create(): View
    {
        $this->authorize('create', Page::class);
        $parents = Page::query()->orderBy('title')->get();

        return view('admin.pages.create', compact('parents'));
    }

    public function store(PageRequest $request): RedirectResponse
    {
        $this->authorize('create', Page::class);

        $page = DB::transaction(function () use ($request) {
            $data = $request->safe()->except(['featured_image', 'sections', 'schema_json']);
            $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
            if ($request->hasFile('featured_image')) {
                $media = $this->media->upload($request->file('featured_image'), 'pages');
                $data['featured_image'] = $media->path;
            }
            $page = Page::query()->create($data);
            app(\App\Services\SeoGeoService::class)->save($page, $request->input('schema_json'));
            $this->syncSections($page, $request->input('sections', []));

            return $page;
        });

        $this->activity->log('Sayfa oluşturuldu', $page);

        return redirect()->route('admin.pages.edit', $page)->with('success', 'Sayfa oluşturuldu.');
    }

    public function edit(Page $page): View
    {
        $this->authorize('update', $page);
        $page->load(['sections', 'seo']);
        $parents = Page::query()->where('id', '!=', $page->id)->orderBy('title')->get();

        return view('admin.pages.edit', compact('page', 'parents'));
    }

    public function update(PageRequest $request, Page $page): RedirectResponse
    {
        $this->authorize('update', $page);

        DB::transaction(function () use ($request, $page) {
            $data = $request->safe()->except(['featured_image', 'sections', 'schema_json']);
            $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
            if ($request->hasFile('featured_image')) {
                $media = $this->media->upload($request->file('featured_image'), 'pages');
                $data['featured_image'] = $media->path;
            }
            $page->update($data);
            app(\App\Services\SeoGeoService::class)->save($page, $request->input('schema_json'));
            $this->syncSections($page, $request->input('sections', []));
        });

        $this->activity->log('Sayfa güncellendi', $page);

        return redirect()->route('admin.pages.edit', $page)->with('success', 'Sayfa kaydedildi.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $this->authorize('delete', $page);
        $page->delete();
        $this->activity->log('Sayfa silindi', $page);

        return redirect()->route('admin.pages.index')->with('success', 'Sayfa silindi.');
    }

    public function reorderSections(Request $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);

        $data = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:page_sections,id'],
        ]);

        foreach ($data['order'] as $index => $sectionId) {
            PageSection::query()
                ->where('page_id', $page->id)
                ->where('id', $sectionId)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true, 'message' => 'Sıra kaydedildi.']);
    }

    protected function syncSections(Page $page, array $sections): void
    {
        $keep = [];
        foreach ($sections as $i => $section) {
            if (empty($section['type'])) {
                continue;
            }
            $payload = [
                'type' => $section['type'],
                'title' => $section['title'] ?? null,
                'subtitle' => $section['subtitle'] ?? null,
                'content' => $section['content'] ?? null,
                'is_active' => ! empty($section['is_active']),
                'sort_order' => $i,
            ];
            if (! empty($section['id'])) {
                $existing = PageSection::query()->where('page_id', $page->id)->where('id', $section['id'])->first();
                if ($existing) {
                    $existing->update($payload);
                    $keep[] = $existing->id;
                    continue;
                }
            }
            $created = $page->sections()->create($payload);
            $keep[] = $created->id;
        }
        $page->sections()->whereNotIn('id', $keep)->delete();
    }
}