<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Product;
use App\Models\SeoMeta;
use App\Models\Setting;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeoController extends Controller
{
    public function __construct(protected ActivityLogService $activity) {}

    public function index(): View
    {
        $defaults = Setting::group('seo');

        return view('admin.seo.index', compact('defaults'));
    }

    public function updateDefaults(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'og_image' => ['nullable', 'string', 'max:500'],
            'robots' => ['nullable', 'string', 'max:100'],
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value, 'seo');
        }

        $this->activity->log('SEO-Standardwerte güncellendi');

        return back()->with('success', 'SEO standartları kaydedildi.');
    }

    public function edit(Request $request): View
    {
        $type = $request->query('type', 'page');
        $id = (int) $request->query('id');

        $model = match ($type) {
            'product' => Product::query()->findOrFail($id),
            'blog' => BlogPost::query()->findOrFail($id),
            default => Page::query()->findOrFail($id),
        };

        $seo = SeoMeta::query()
            ->where('seoable_type', $model->getMorphClass())
            ->where('seoable_id', $model->getKey())
            ->first();

        return view('admin.seo.edit', compact('model', 'seo', 'type'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:page,product,blog'],
            'id' => ['required', 'integer'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'keywords' => ['nullable', 'string', 'max:255'],
            'canonical' => ['nullable', 'string', 'max:500'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'string', 'max:500'],
            'twitter_title' => ['nullable', 'string', 'max:255'],
            'twitter_description' => ['nullable', 'string', 'max:500'],
            'twitter_image' => ['nullable', 'string', 'max:500'],
            'focus_keyword' => ['nullable', 'string', 'max:100'],
            'robots' => ['nullable', 'string', 'max:100'],
            'schema_json' => ['nullable', 'string', 'max:20000'],
        ]);
        app(\App\Services\SeoGeoService::class)->normalize($request->input('schema_json'));

        $model = match ($data['type']) {
            'product' => Product::query()->findOrFail($data['id']),
            'blog' => BlogPost::query()->findOrFail($data['id']),
            default => Page::query()->findOrFail($data['id']),
        };

        unset($data['type'], $data['id']);

        SeoMeta::query()->updateOrCreate(
            [
                'seoable_type' => $model->getMorphClass(),
                'seoable_id' => $model->getKey(),
            ],
            $data
        );

        $this->activity->log('SEO Meta güncellendi', $model);

        return back()->with('success', 'SEO kaydedildi.');
    }
}