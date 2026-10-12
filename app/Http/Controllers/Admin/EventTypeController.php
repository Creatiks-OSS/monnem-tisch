<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventType;
use App\Services\ActivityLogService;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EventTypeController extends Controller
{
    public function __construct(
        protected MediaService $media,
        protected ActivityLogService $activity
    ) {}

    public function index(): View
    {
        $events = EventType::query()->orderBy('sort_order')->orderBy('name')->paginate(20);

        return view('admin.events.index', compact('events'));
    }

    public function create(): View
    {
        return view('admin.events.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        if ($request->hasFile('image')) {
            $media = $this->media->upload($request->file('image'), 'events');
            $data['image'] = $media->path;
        }
        $event = EventType::query()->create($data);
        $this->activity->log('Etkinlik türü oluşturuldu', $event);

        return redirect()->route('admin.events.index')->with('success', 'Etkinlik türü oluşturuldu.');
    }

    public function edit(EventType $event): View
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, EventType $event): RedirectResponse
    {
        $data = $this->validated($request);
        if ($request->hasFile('image')) {
            $media = $this->media->upload($request->file('image'), 'events');
            $data['image'] = $media->path;
        }
        $event->update($data);
        $this->activity->log('Etkinlik türü güncellendi', $event);

        return redirect()->route('admin.events.index')->with('success', 'Etkinlik türü kaydedildi.');
    }

    public function destroy(EventType $event): RedirectResponse
    {
        $event->delete();
        $this->activity->log('Etkinlik türü silindi', $event);

        return redirect()->route('admin.events.index')->with('success', 'Etkinlik türü silindi.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:15360'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
        ]);
        unset($data['image']);
        $data['is_active'] = $request->boolean('is_active');
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        return $data;
    }
}