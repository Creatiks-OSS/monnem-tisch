<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Extra;
use App\Services\ActivityLogService;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ExtraController extends Controller
{
    public function __construct(
        protected MediaService $media,
        protected ActivityLogService $activity
    ) {}

    public function index(Request $request): View
    {
        $extras = Extra::query()
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(40)
            ->withQueryString();

        return view('admin.extras.index', compact('extras'));
    }

    public function create(): View
    {
        return view('admin.extras.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        if ($request->hasFile('image')) {
            $media = $this->media->upload($request->file('image'), 'extras');
            $data['image'] = $media->path;
        }
        $extra = Extra::query()->create($data);
        $this->activity->log('Extra oluşturuldu', $extra);

        return redirect()->route('admin.extras.index')->with('success', 'Ekstra hizmet oluşturuldu.');
    }

    public function edit(Extra $extra): View
    {
        return view('admin.extras.edit', compact('extra'));
    }

    public function update(Request $request, Extra $extra): RedirectResponse
    {
        $data = $this->validated($request, $extra->id);
        if ($request->hasFile('image')) {
            $media = $this->media->upload($request->file('image'), 'extras');
            $data['image'] = $media->path;
        }
        $extra->update($data);
        $this->activity->log('Extra güncellendi', $extra);

        return redirect()->route('admin.extras.index')->with('success', 'Ekstra hizmet kaydedildi.');
    }

    public function destroy(Extra $extra): RedirectResponse
    {
        $extra->delete();
        $this->activity->log('Extra silindi', $extra);

        return redirect()->route('admin.extras.index')->with('success', 'Ekstra hizmet silindi.');
    }

    protected function validated(Request $request, ?int $id = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:5120'],
            'price' => ['required', 'numeric', 'min:0'],
            'price_type' => ['nullable', 'in:stück,person,fixed,hour'],
            'minimum_quantity' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        unset($data['image']);
        $data['is_active'] = $request->boolean('is_active');
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['kind'] = $request->boolean('is_drink') ? 'product' : 'service';
        $data['price_type'] = $data['price_type'] ?: ($data['kind'] === 'product' ? 'stück' : 'fixed');

        return $data;
    }
}
