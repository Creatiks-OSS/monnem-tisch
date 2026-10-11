<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CateringPackage;
use App\Models\EventType;
use App\Models\Faq;
use App\Models\Gallery;
use App\Models\Page;
use App\Models\Product;
use App\Models\Review;
use Illuminate\View\View;

class EventLandingController extends Controller
{
    /**
     * Reserved event landing slugs mapped to event type / page lookup keys.
     *
     * @var array<string, string>
     */
    protected array $landings = [
        'hochzeiten' => 'hochzeiten',
        'firmen-catering' => 'firmen-catering',
        'messe-catering' => 'messe-catering',
        'partyservice' => 'partyservice',
        'ueber-uns' => 'ueber-uns',
    ];

    public function show(string $slug): View
    {
        if (! array_key_exists($slug, $this->landings)) {
            abort(404);
        }

        $key = $this->landings[$slug];

        $page = Page::query()
            ->published()
            ->where(function ($query) use ($key, $slug) {
                $query->where('slug', $slug)
                    ->orWhere('slug', $key)
                    ->orWhere('type', 'landing_'.$key)
                    ->orWhere('type', 'event_'.$key);
            })
            ->with(['sections' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->first();

        $eventType = EventType::query()
            ->active()
            ->where(function ($query) use ($key, $slug) {
                $query->where('slug', $slug)
                    ->orWhere('slug', $key);
            })
            ->first();

        $packages = collect();
        if ($eventType) {
            $packages = CateringPackage::query()
                ->active()
                ->where('event_type_id', $eventType->id)
                ->with('products')
                ->orderBy('sort_order')
                ->get();
        }

        $products = Product::query()
            ->active()
            ->featured()
            ->with('images')
            ->orderBy('sort_order')
            ->limit(8)
            ->get();

        $reviews = Review::query()
            ->approved()
            ->featured()
            ->orderByDesc('reviewed_at')
            ->limit(4)
            ->get();

        $faqs = Faq::query()
            ->active()
            ->when($slug !== 'ueber-uns', fn ($q) => $q->where(function ($inner) use ($slug) {
                $inner->whereNull('category')
                    ->orWhere('category', $slug)
                    ->orWhere('category', 'general');
            }))
            ->orderBy('sort_order')
            ->limit(8)
            ->get();

        $galleries = Gallery::query()
            ->active()
            ->when($slug !== 'ueber-uns', fn ($q) => $q->where(function ($inner) use ($slug) {
                $inner->whereNull('category')
                    ->orWhere('category', $slug);
            }))
            ->with(['images' => fn ($q) => $q->limit(6)])
            ->orderBy('sort_order')
            ->limit(3)
            ->get();

        if ($slug === 'ueber-uns') {
            if (! $page) {
                $page = new Page([
                    'title' => 'Über uns',
                    'slug' => 'ueber-uns',
                    'content' => '',
                    'type' => 'landing',
                ]);
            }

            return view('frontend.pages.about', [
                'page' => $page,
                'contact' => [
                    'phone' => setting('phone', '', 'contact'),
                    'email' => setting('email', 'info@monnemtisch.de', 'contact'),
                    'street' => setting('street', 'H1, 10', 'contact'),
                    'postal_code' => setting('postal_code', '68159', 'contact'),
                    'city' => setting('city', 'Mannheim', 'contact'),
                ],
            ]);
        }

        return view('frontend.events.show', [
            'slug' => $slug,
            'page' => $page,
            'sections' => $page?->sections ?? collect(),
            'eventType' => $eventType,
            'packages' => $packages,
            'products' => $products,
            'reviews' => $reviews,
            'faqs' => $faqs,
            'galleries' => $galleries,
        ]);
    }
}
