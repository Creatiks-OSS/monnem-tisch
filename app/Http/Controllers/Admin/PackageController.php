<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CateringPackage;
use App\Models\EventType;
use App\Models\ProductCategory;
use App\Services\ActivityLogService;
use App\Services\MediaService;
use App\Services\SeoGeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function __construct(
        protected MediaService $media,
        protected ActivityLogService $activity,
        protected SeoGeoService $seoGeo
    ) {}

    public function index(Request $request): View
    {
        $packages = CateringPackage::query()
            ->with(['eventType', 'slots'])
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->when($request->filled('menu') && CateringPackage::isMenu($request->string('menu')->toString()), fn ($q) => $q->forMenu($request->string('menu')->toString()))
            ->where('menu_group', '!=', 'extras')
            ->orderBy('sort_order')
            ->paginate(24)
            ->withQueryString();

        $menus = CateringPackage::MENUS;

        return view('admin.packages.index', compact('packages', 'menus'));
    }

    public function create(): View
    {
        return view('admin.packages.form', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $schema = $this->seoGeo->normalize($request->input('schema_json'));
        $data = $this->validated($request);
        if ($request->hasFile('image')) {
            $media = $this->media->upload($request->file('image'), 'packages');
            $data['image'] = $media->path;
        }
        $package = CateringPackage::query()->create($data);
        $this->syncSlots($package, $request);
        $package->seo()->updateOrCreate([], ['schema_json' => $schema]);
        $this->activity->log('Paket oluşturuldu', $package);

        return redirect()->route('admin.packages.edit', $package)->with('success', 'Paket oluşturuldu.');
    }

    public function edit(CateringPackage $package): View
    {
        $package->load(['slots', 'seo']);

        return view('admin.packages.form', $this->formData($package));
    }

    public function update(Request $request, CateringPackage $package): RedirectResponse
    {
        $schema = $this->seoGeo->normalize($request->input('schema_json'));
        $data = $this->validated($request);
        if ($request->hasFile('image')) {
            $media = $this->media->upload($request->file('image'), 'packages');
            $data['image'] = $media->path;
        }
        $package->update($data);
        $this->syncSlots($package, $request);
        $package->seo()->updateOrCreate([], ['schema_json' => $schema]);
        $this->activity->log('Paket güncellendi', $package);

        return redirect()->route('admin.packages.edit', $package)->with('success', 'Paket kaydedildi.');
    }

    public function destroy(CateringPackage $package): RedirectResponse
    {
        $package->delete();
        $this->activity->log('Paket silindi', $package);

        return redirect()->route('admin.packages.index')->with('success', 'Paket silindi.');
    }

    protected function formData(?CateringPackage $package = null): array
    {
        return [
            'package' => $package,
            'eventTypes' => EventType::query()->orderBy('name')->get(),
            'categories' => ProductCategory::query()->with('parent')->orderBy('sort_order')->orderBy('name')->get(),
        ];
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'event_type_id' => ['nullable', 'exists:event_types,id'],
            'menu_group' => ['required', 'in:'.implode(',', CateringPackage::menuKeys())],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'seo_text' => ['nullable', 'string', 'max:20000'],
            'image' => ['nullable', 'image', 'max:5120'],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'price_per_person' => ['nullable', 'numeric', 'min:0'],
            'compare_price' => ['nullable', 'numeric', 'min:0'],
            'discount_label' => ['nullable', 'string', 'max:255'],
            'badge' => ['nullable', 'string', 'max:50'],
            'cta_label' => ['nullable', 'string', 'max:80'],
            'min_persons' => ['nullable', 'integer', 'min:1'],
            'max_persons' => ['nullable', 'integer', 'min:1'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_popular' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'highlights' => ['nullable', 'array'],
            'highlights.*' => ['nullable', 'string', 'max:80'],
        ]);
        unset($data['image']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_popular'] = $request->boolean('is_popular');
        $data['is_active'] = $request->boolean('is_active');
        $data['slug'] = filled($data['slug'] ?? null) ? $data['slug'] : Str::slug($data['name']);
        $data['highlights'] = array_values(array_filter($data['highlights'] ?? []));

        return $data;
    }

    protected function syncSlots(CateringPackage $package, Request $request): void
    {
        $keep = [];
        foreach ($request->input('slots', []) as $index => $row) {
            if (empty($row['name'])) {
                continue;
            }
            $payload = [
                'name' => $row['name'],
                'product_category_id' => $row['product_category_id'] ?: null,
                'required_count' => max(1, (int) ($row['required_count'] ?? 1)),
                'sort_order' => $index,
            ];
            if (! empty($row['id'])) {
                $slot = $package->slots()->whereKey($row['id'])->first();
                if ($slot) {
                    $slot->update($payload);
                } else {
                    $slot = $package->slots()->create($payload);
                }
            } else {
                $slot = $package->slots()->create($payload);
            }
            $keep[] = $slot->id;
        }
        $package->slots()->whereNotIn('id', $keep ?: [0])->delete();
    }
}
