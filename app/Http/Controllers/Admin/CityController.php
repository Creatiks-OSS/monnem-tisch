<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Services\ActivityLogService;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CityController extends Controller
{
    public function __construct(
        protected MediaService $media,
        protected ActivityLogService $activity
    ) {}

    public function index(Request $request): View
    {
        $cities = City::query()
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return view('admin.cities.index', compact('cities'));
    }

    public function create(): View
    {
        return view('admin.cities.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        if ($request->hasFile('image')) {
            $media = $this->media->upload($request->file('image'), 'cities');
            $data['image'] = $media->path;
        }
        $city = City::query()->create($data);
        $this->activity->log('Şehir oluşturuldu', $city);

        return redirect()->route('admin.cities.index')->with('success', 'Şehir oluşturuldu.');
    }

    public function edit(City $city): View
    {
        return view('admin.cities.edit', compact('city'));
    }

    public function update(Request $request, City $city): RedirectResponse
    {
        $data = $this->validated($request);
        if ($request->hasFile('image')) {
            $media = $this->media->upload($request->file('image'), 'cities');
            $data['image'] = $media->path;
        }
        $city->update($data);
        $this->activity->log('Şehir güncellendi', $city);

        return redirect()->route('admin.cities.index')->with('success', 'Şehir kaydedildi.');
    }

    public function destroy(City $city): RedirectResponse
    {
        $city->delete();
        $this->activity->log('Şehir silindi', $city);

        return redirect()->route('admin.cities.index')->with('success', 'Şehir silindi.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'delivery_price' => ['nullable', 'numeric', 'min:0'],
            'delivery_radius' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:5120'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        unset($data['image']);
        $data['is_active'] = $request->boolean('is_active');
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        return $data;
    }
}