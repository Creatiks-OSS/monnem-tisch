<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function __construct(protected ActivityLogService $activity) {}

    public function index(Request $request): View
    {
        $reviews = Review::query()
            ->with(['product', 'customer'])
            ->when($request->filled('status'), function ($q) use ($request) {
                if ($request->status === 'pending') {
                    $q->where('is_approved', false);
                } elseif ($request->status === 'approved') {
                    $q->where('is_approved', true);
                } elseif ($request->status === 'featured') {
                    $q->where('is_featured', true);
                }
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reviews.index', compact('reviews'));
    }

    public function edit(Review $review): View
    {
        $review->load(['product', 'customer']);

        return view('admin.reviews.edit', compact('review'));
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'is_approved' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
        ]);
        $data['is_approved'] = $request->boolean('is_approved');
        $data['is_featured'] = $request->boolean('is_featured');
        $review->update($data);
        $this->activity->log('Yorum güncellendi', $review);

        return redirect()->route('admin.reviews.index')->with('success', 'Yorum kaydedildi.');
    }

    public function approve(Review $review): RedirectResponse
    {
        $review->update(['is_approved' => true]);
        $this->activity->log('Yorum freigegeben', $review);

        return back()->with('success', 'Yorum onaylandı.');
    }

    public function feature(Review $review): RedirectResponse
    {
        $review->update(['is_featured' => ! $review->is_featured]);
        $this->activity->log('Yorum Hervorhebung umgeschaltet', $review);

        return back()->with('success', 'Öne çıkarma güncellendi.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();
        $this->activity->log('Yorum silindi', $review);

        return redirect()->route('admin.reviews.index')->with('success', 'Yorum silindi.');
    }
}