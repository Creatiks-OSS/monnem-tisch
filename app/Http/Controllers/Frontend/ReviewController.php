<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(): View
    {
        $reviews = Review::query()
            ->approved()
            ->with('product')
            ->orderByDesc('reviewed_at')
            ->paginate(12);

        $featured = Review::query()
            ->approved()
            ->featured()
            ->orderByDesc('reviewed_at')
            ->limit(6)
            ->get();

        $average = (float) (Review::query()->approved()->avg('rating') ?? 0);
        $count = Review::query()->approved()->count();

        return view('frontend.reviews.index', [
            'reviews' => $reviews,
            'featured' => $featured,
            'average' => round($average, 1),
            'count' => $count,
        ]);
    }
}
