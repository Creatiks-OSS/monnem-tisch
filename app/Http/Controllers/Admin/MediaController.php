<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaUploadRequest;
use App\Models\Media;
use App\Services\ActivityLogService;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function __construct(
        protected MediaService $mediaService,
        protected ActivityLogService $activity
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $media = Media::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('filename', 'like', $term)
                        ->orWhere('alt', 'like', $term)
                        ->orWhere('title', 'like', $term)
                        ->orWhere('folder', 'like', $term);
                });
            })
            ->when($request->filled('folder'), fn ($q) => $q->where('folder', $request->folder))
            ->latest()
            ->paginate(24)
            ->withQueryString();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($media);
        }

        $folders = Media::query()->select('folder')->distinct()->orderBy('folder')->pluck('folder');

        return view('admin.media.index', compact('media', 'folders'));
    }

    public function store(MediaUploadRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $item = $this->mediaService->upload(
                $request->file('file'),
                $request->input('folder', 'uploads')
            );
            if ($request->filled('alt') || $request->filled('title')) {
                $item->update([
                    'alt' => $request->input('alt', $item->alt),
                    'title' => $request->input('title', $item->title),
                ]);
            }
            $this->activity->log('Medium hochgeladen', $item);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'media' => $item]);
            }

            return back()->with('success', 'Dosya yüklendi.');
        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Media $medium): JsonResponse|RedirectResponse
    {
        $this->mediaService->delete($medium);
        $this->activity->log('Medium silindi', $medium);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Dosya silindi.');
    }
}