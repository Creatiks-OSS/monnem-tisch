<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ActivityLogService;
use App\Services\MenuPageContent;
use App\Services\SeoGeoService;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuPageController extends Controller
{
    public function __construct(
        protected MenuPageContent $pages,
        protected SettingService $settings,
        protected ActivityLogService $activity,
        protected SeoGeoService $seoGeo
    ) {}

    public function edit(): View
    {
        return view('admin.menu-pages.edit', [
            'pages' => $this->pages,
            'menus' => $this->pages->menus(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $keys = array_column($this->pages->menus(), 'key');

        $data = $request->validate([
            'text' => ['required', 'array'],
            'text.*' => ['nullable', 'string', 'max:20000'],
            'schema' => ['nullable', 'array'],
            'schema.*' => ['nullable', 'string', 'max:20000'],
        ]);

        $defaults = $this->pages->defaults();

        foreach ($keys as $key) {
            $value = trim((string) ($data['text'][$key] ?? ''));
            if ($value === '' || $value === trim((string) ($defaults[$key] ?? ''))) {
                $this->pages->forget($key);
                continue;
            }
            if (mb_strlen($value) < 700) {
                return back()
                    ->withInput()
                    ->withErrors(['text.'.$key => 'Bu metin en az 700 karakter olmalı.']);
            }
            Setting::set($key, $value, MenuPageContent::GROUP);
        }

        foreach ($keys as $key) {
            $schema = $this->seoGeo->normalize($data['schema'][$key] ?? null);
            if ($schema === null) {
                $this->pages->forget($key.'_schema');
                continue;
            }
            Setting::set($key.'_schema', $schema, MenuPageContent::GROUP);
        }

        $this->settings->forgetCache(MenuPageContent::GROUP);
        $this->activity->log('Kategori açıklamaları güncellendi');

        return redirect()->route('admin.menu-pages.edit')->with('success', 'Kategori açıklamaları kaydedildi.');
    }
}
