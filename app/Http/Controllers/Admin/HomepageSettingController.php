<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ActivityLogService;
use App\Services\HomepageContent;
use App\Services\SeoGeoService;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HomepageSettingController extends Controller
{
    public function __construct(
        protected HomepageContent $homepage,
        protected SettingService $settings,
        protected ActivityLogService $activity,
        protected SeoGeoService $seoGeo
    ) {}

    public function edit(): View
    {
        return view('admin.homepage.edit', [
            'homepage' => $this->homepage,
            'sections' => $this->homepage->sections(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'text' => ['required', 'array'],
            'text.*' => ['nullable', 'string', 'max:5000'],
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'file', 'max:15360', 'mimes:jpg,jpeg,png,webp'],
            'remove_image' => ['nullable', 'array'],
        ]);

        $defaults = $this->homepage->defaults();
        $submitted = $request->input('text', []);

        foreach ($defaults as $key => $default) {
            $value = trim((string) ($submitted[$key] ?? ''));
            if ($value === '' || $value === $default) {
                $this->homepage->forget($key);
                continue;
            }
            Setting::set($key, $value, HomepageContent::GROUP);
        }

        foreach ($this->homepage->imageKeys() as $key) {
            $remove = (bool) data_get($request->input('remove_image', []), $key);
            if ($request->hasFile('images.'.$key)) {
                $previous = trim((string) setting($key, '', HomepageContent::GROUP));
                $file = $request->file('images.'.$key);
                $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                $path = $file->storeAs('homepage', $key.'-'.Str::lower(Str::random(8)).'.'.$ext, 'public');
                Setting::set($key, $path, HomepageContent::GROUP);
                $this->deleteStoredImage($previous);
            } elseif ($remove) {
                $this->deleteStoredImage(trim((string) setting($key, '', HomepageContent::GROUP)));
                $this->homepage->forget($key);
            }
        }

        $homeSchema = $this->seoGeo->normalize($request->input('schema_json'));
        if ($homeSchema === null) {
            $this->homepage->forget('schema_json');
        } else {
            Setting::set('schema_json', $homeSchema, HomepageContent::GROUP);
        }

        $this->settings->forgetCache(HomepageContent::GROUP);
        $this->activity->log('Anasayfa metinleri güncellendi');

        return redirect()->route('admin.homepage.edit')->with('success', 'Anasayfa ayarları kaydedildi.');
    }

    protected function deleteStoredImage(string $path): void
    {
        if ($path === '' || str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return;
        }

        if (! str_starts_with($path, 'homepage/')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
