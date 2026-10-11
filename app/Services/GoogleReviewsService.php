<?php

namespace App\Services;

use App\Models\Review;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleReviewsService
{
    public function __construct(
        protected SettingService $settings
    ) {}

    /**
     * Fetch reviews from Google Places API when credentials exist.
     *
     * @return array<int, array{author_name: string, rating: int, text: string, time: ?int, relative_time_description: ?string}>
     */
    public function fetchReviews(): array
    {
        $apiKey = (string) $this->settings->get('google_places_api_key', '', 'integrations');
        $placeId = (string) $this->settings->get('google_place_id', '', 'integrations');

        if ($apiKey === '' || $placeId === '') {
            return [];
        }

        try {
            $response = Http::timeout(10)->get('https://maps.googleapis.com/maps/api/place/details/json', [
                'place_id' => $placeId,
                'fields' => 'reviews,rating,user_ratings_total',
                'language' => 'de',
                'key' => $apiKey,
            ]);

            if (! $response->successful()) {
                Log::info('Google Reviews API non-success', ['status' => $response->status()]);

                return [];
            }

            $json = $response->json();
            if (($json['status'] ?? '') !== 'OK') {
                Log::info('Google Reviews API status', ['status' => $json['status'] ?? 'unknown']);

                return [];
            }

            $reviews = $json['result']['reviews'] ?? [];

            return array_map(function (array $review) {
                return [
                    'author_name' => $review['author_name'] ?? 'Google Nutzer',
                    'rating' => (int) ($review['rating'] ?? 5),
                    'text' => $review['text'] ?? '',
                    'time' => $review['time'] ?? null,
                    'relative_time_description' => $review['relative_time_description'] ?? null,
                ];
            }, is_array($reviews) ? $reviews : []);
        } catch (\Throwable $e) {
            Log::warning('Google Reviews fetch failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Sync fetched Google reviews into local reviews table.
     */
    public function syncToReviews(): int
    {
        $fetched = $this->fetchReviews();
        $synced = 0;

        foreach ($fetched as $review) {
            $body = trim((string) ($review['text'] ?? ''));
            $name = trim((string) ($review['author_name'] ?? 'Google Nutzer'));

            if ($body === '') {
                continue;
            }

            $exists = Review::query()
                ->where('source', 'google')
                ->where('name', $name)
                ->where('body', $body)
                ->exists();

            if ($exists) {
                continue;
            }

            Review::query()->create([
                'product_id' => null,
                'customer_id' => null,
                'name' => $name,
                'company' => null,
                'rating' => max(1, min(5, (int) ($review['rating'] ?? 5))),
                'title' => null,
                'body' => $body,
                'image' => null,
                'is_featured' => false,
                'is_approved' => true,
                'source' => 'google',
                'reviewed_at' => isset($review['time'])
                    ? \Carbon\Carbon::createFromTimestamp((int) $review['time'])
                    : now(),
            ]);

            $synced++;
        }

        return $synced;
    }
}
