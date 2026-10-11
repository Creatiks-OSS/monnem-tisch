<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CateringPackage;
use App\Models\City;
use App\Models\DeliveryZone;
use App\Models\EventType;
use App\Models\Faq;
use App\Models\Gallery;
use App\Models\Page;
use App\Models\Review;
use App\Services\AvailabilityService;
use App\Services\CartService;
use App\Services\DeliveryService;
use App\Services\GoogleDistanceService;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected SettingService $settings
    ) {}

    public function index(): View
    {
        $page = Page::query()
            ->published()
            ->where(function ($query) {
                $query->where('slug', 'home')
                    ->orWhere('slug', 'startseite')
                    ->orWhere('type', 'homepage');
            })
            ->with(['sections' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'), 'seo'])
            ->first();

        $menus = collect(CateringPackage::MENUS)->map(function (array $meta, string $key) {
            return array_merge($meta, [
                'key' => $key,
                'url' => route('packages.index', $key),
            ]);
        });

        $faqs = Faq::query()
            ->active()
            ->orderBy('sort_order')
            ->limit(8)
            ->get();

        $galleries = Gallery::query()
            ->active()
            ->with(['images' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')])
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        $eventTypes = EventType::query()
            ->active()
            ->orderBy('sort_order')
            ->limit(8)
            ->get();

        $deliveryZones = DeliveryZone::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->limit(8)
            ->get();

        $cities = City::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(8)
            ->get();

        return view('frontend.home', [
            'page' => $page,
            'sections' => $page?->sections ?? collect(),
            'menus' => $menus,
            'faqs' => $faqs,
            'galleries' => $galleries,
            'eventTypes' => $eventTypes,
            'deliveryZones' => $deliveryZones,
            'cities' => $cities,
            'packages' => CateringPackage::query()
                ->active()
                ->with('slots')
                ->orderByDesc('is_popular')
                ->orderBy('sort_order')
                ->limit(6)
                ->get(),
            'trust' => $this->trustSettings(),
            'blockedDates' => app(AvailabilityService::class)->getBlockedDates(now(), now()->addMonths(18)),
            'reviewStats' => [
                'avg' => round((float) (Review::query()->approved()->avg('rating') ?: $this->trustSettings()['rating']), 1),
                'count' => (int) (Review::query()->approved()->count() ?: $this->trustSettings()['review_count']),
            ],
            'reviews' => Review::query()
                ->approved()
                ->orderByDesc('is_featured')
                ->orderByDesc('reviewed_at')
                ->orderByDesc('id')
                ->limit(3)
                ->get(),
        ]);
    }

    public function checkAvailability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'date' => ['nullable', 'date'],
        ]);

        $address = trim((string) ($validated['address'] ?? $validated['postal_code'] ?? ''));
        if ($address === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Bitte Straße, Nr, PLZ und Ort angeben.',
            ], 422);
        }

        $distance = app(GoogleDistanceService::class);
        $parsed = $distance->parseAddressLine($address);
        $plz = $parsed['postal_code'];
        if ($plz === '' && preg_match('/\b(\d{5})\b/', $address, $match)) {
            $plz = $match[1];
        }
        if ($plz === '') {
            $digits = preg_replace('/\D+/', '', $address) ?? '';
            if (strlen($digits) === 5) {
                $plz = $digits;
            }
        }

        $isFreeCity = $distance->isStuttgartRegion($address, $plz);
        $quote = $distance->quoteLine($address);
        $isFreeCity = $isFreeCity || (bool) ($quote['isStuttgart'] ?? $quote['isHannover']);

        if ($plz === '' && ! $isFreeCity && ! ($quote['ok'] ?? false)) {
            return response()->json([
                'ok' => false,
                'message' => 'Bitte Straße, Nr, PLZ und Ort angeben.',
            ], 422);
        }

        if (! empty($validated['date']) && ! app(AvailabilityService::class)->isDateAvailable($validated['date'])) {
            return response()->json([
                'ok' => false,
                'message' => 'Dieses Datum ist leider nicht verfügbar. Bitte wählen Sie einen anderen Tag.',
            ]);
        }

        $zone = $plz !== '' ? app(DeliveryService::class)->findZoneForPostalCode($plz) : null;
        $distanceKm = (float) ($quote['distanceKm'] ?? 0);
        $deliveryCost = $distance->costFor($isFreeCity, $distanceKm);

        if (! ($quote['ok'] ?? false) && ! $isFreeCity) {
            return response()->json([
                'ok' => false,
                'message' => $quote['error'] ?: 'Die Entfernung konnte nicht ermittelt werden. Bitte Adresse prüfen.',
            ], 422);
        }

        $city = $parsed['city'] ?: ($zone?->city ?: $zone?->name);
        if ($isFreeCity && ($city === null || $city === '')) {
            $city = (string) config('delivery.free_city', 'Mannheim');
        }

        $cart = app(CartService::class);
        $cart->setDelivery([
            'postal_code' => $plz,
            'city' => $city,
            'street' => $parsed['street'] !== '' ? $parsed['street'] : null,
            'house_number' => ($parsed['house_number'] !== '' && $parsed['house_number'] !== '–') ? $parsed['house_number'] : null,
            'formatted_address' => $quote['formattedAddress'] ?: $address,
            'distance_km' => $distanceKm,
            'cost' => $deliveryCost,
            'is_stuttgart' => $isFreeCity,
        ]);
        if (! empty($validated['date'])) {
            $cart->setEvent(['date' => $validated['date']]);
        }

        if ($isFreeCity) {
            $message = 'Wir liefern nach '.($city ?: 'Mannheim').' – kostenlos.';
        } else {
            $place = $city !== null && $city !== '' ? $city : 'Ihrem Standort';
            $message = 'Wir liefern nach '.$place.' — '
                .number_format($distanceKm, 1, ',', '.').' km, '
                .number_format($deliveryCost, 2, ',', '.').' € Lieferkosten.';
        }

        return response()->json([
            'ok' => true,
            'message' => $message,
            'redirect' => route('packages.index', 'buffets'),
        ]);
    }

    public function logo()
    {
        $file = site_logo_file();
        if ($file === null) {
            abort(404);
        }

        return response()->file($file, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function trustSettings(): array
    {
        return [
            'years_experience' => $this->settings->int('years_experience', 10, 'trust'),
            'events_count' => $this->settings->int('events_count', 500, 'trust'),
            'rating' => $this->settings->float('rating', 4.9, 'trust'),
            'review_count' => $this->settings->int('review_count', 120, 'trust'),
            'tagline' => $this->settings->string('tagline', 'Premium Catering aus Leidenschaft', 'trust'),
            'usp' => $this->settings->json('usp', [], 'trust'),
        ];
    }
}
