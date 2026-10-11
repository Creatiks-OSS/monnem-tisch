<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CateringPackage;
use App\Models\City;
use App\Models\Faq;
use App\Models\Review;
use Illuminate\View\View;

class CityController extends Controller
{
    public function show(string $slug): View
    {
        $city = City::query()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        $packages = CateringPackage::query()
            ->active()
            ->with('slots')
            ->orderByDesc('is_popular')
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        $reviews = Review::query()
            ->approved()
            ->orderByDesc('reviewed_at')
            ->limit(4)
            ->get();

        $faqs = collect($city->faq ?? []);
        if ($faqs->isEmpty()) {
            $faqs = Faq::query()
                ->active()
                ->orderBy('sort_order')
                ->limit(6)
                ->get();
        }

        return view('frontend.cities.show', [
            'city' => $city,
            'menus' => CateringPackage::MENUS,
            'packages' => $packages,
            'reviews' => $reviews,
            'faqs' => $faqs,
        ]);
    }
}
