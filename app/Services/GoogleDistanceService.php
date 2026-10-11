<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleDistanceService
{
    /**
     * @return array{
     *     ok: bool,
     *     isStuttgart: bool,
     *     isHannover: bool,
     *     distanceKm: float,
     *     formattedAddress: string,
     *     error: ?string
     * }
     */
    public function quote(string $street, string $houseNumber, string $postalCode, string $city, ?string $rawLine = null): array
    {
        $rawLine = trim((string) $rawLine);
        $inputAddress = $rawLine !== ''
            ? $this->withCountry($rawLine)
            : $this->buildDestination($street, $houseNumber, $postalCode, $city);
        $inFreeCity = $this->isStuttgartRegion($inputAddress, $postalCode);

        if (! $this->isUsableAddress($inputAddress)) {
            return $this->payload(
                ok: false,
                isFreeCity: $inFreeCity,
                distanceKm: 0,
                formattedAddress: '',
                error: 'Bitte Straße, Nr, PLZ und Ort angeben.'
            );
        }

        $key = trim((string) config('services.google.maps_api_key'));
        if ($key === '') {
            Log::warning('Google Distance Matrix: GOOGLE_MAPS_API_KEY fehlt.');

            return $this->payload(
                ok: false,
                isFreeCity: $inFreeCity,
                distanceKm: 0,
                formattedAddress: '',
                error: 'Die Entfernung konnte nicht ermittelt werden.'
            );
        }

        try {
            $response = Http::timeout(8)->get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                'origins' => config('delivery.origin'),
                'destinations' => $inputAddress,
                'mode' => 'driving',
                'language' => 'de',
                'units' => 'metric',
                'key' => $key,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Google Distance Matrix request failed', ['error' => $e->getMessage()]);

            return $this->payload(
                ok: false,
                isFreeCity: $inFreeCity,
                distanceKm: 0,
                formattedAddress: '',
                error: 'Die Entfernung konnte nicht ermittelt werden.'
            );
        }

        $json = $response->json() ?: [];
        $formatted = (string) ($json['destination_addresses'][0] ?? '');
        $element = $json['rows'][0]['elements'][0] ?? [];
        $status = (string) ($element['status'] ?? ($json['status'] ?? ''));
        $meters = (int) ($element['distance']['value'] ?? 0);

        if (! $response->successful() || ! in_array($status, ['OK'], true) || ($json['status'] ?? '') !== 'OK') {
            Log::info('Google Distance Matrix unsuccessful', [
                'status' => $json['status'] ?? null,
                'element' => $status,
                'error' => $json['error_message'] ?? null,
            ]);

            $apiError = trim((string) ($json['error_message'] ?? ''));
            $hint = 'Die Entfernung konnte nicht ermittelt werden.';
            if ($apiError !== '' && str_contains(strtolower($apiError), 'denied')) {
                $hint = 'Die Entfernung konnte nicht ermittelt werden. Bitte Distance Matrix API in Google Cloud aktivieren.';
            }

            return $this->payload(
                ok: false,
                isFreeCity: $inFreeCity,
                distanceKm: 0,
                formattedAddress: $formatted,
                error: $hint
            );
        }

        $haystack = trim($formatted.' '.$inputAddress);
        $isFreeCity = $this->isStuttgartRegion($haystack, $postalCode);

        return $this->payload(
            ok: true,
            isFreeCity: $isFreeCity,
            distanceKm: round($meters / 1000, 1),
            formattedAddress: $formatted !== '' ? $formatted : $inputAddress,
            error: null
        );
    }

    public function quoteLine(string $line): array
    {
        $parsed = $this->parseAddressLine($line);

        return $this->quote(
            $parsed['street'],
            $parsed['house_number'],
            $parsed['postal_code'],
            $parsed['city'],
            $line
        );
    }

    /**
     * @return array{street: string, house_number: string, postal_code: string, city: string, line: string}
     */
    public function parseAddressLine(string $line): array
    {
        $line = trim(preg_replace('/\s+/', ' ', $line) ?? '');
        $street = $line;
        $house = '';
        $plz = '';
        $city = '';

        if (preg_match('/^(.*?)[,\s]+(\d{5})\s+(.+)$/u', $line, $m)) {
            $streetPart = trim($m[1], " \t,");
            $plz = $m[2];
            $city = trim((string) preg_replace('/,?\s*(Deutschland|Germany)$/iu', '', trim($m[3], " \t,")));
            if (preg_match('/^(.*\D)\s+(\d+[a-zA-Z]?)$/u', $streetPart, $s)) {
                $street = trim($s[1]);
                $house = $s[2];
            } else {
                $street = $streetPart;
            }
        }

        return [
            'street' => $street !== '' ? $street : $line,
            'house_number' => $house !== '' ? $house : '–',
            'postal_code' => $plz,
            'city' => $city,
            'line' => $line,
        ];
    }

    public function containsErrorFreeToken(string $text): bool
    {
        return $this->containsTokens($text, config('delivery.error_free_tokens', ['mannheim']));
    }

    public function isUsableAddress(string $text): bool
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');
        $text = preg_replace('/,?\s*(Deutschland|Germany)$/iu', '', $text) ?? $text;

        return mb_strlen(trim($text)) >= 3;
    }

    public function isStuttgartRegion(string $text, string $postalCode = ''): bool
    {
        if ($this->containsTokens($text, config('delivery.free_city_tokens', ['mannheim']))) {
            return true;
        }

        $haystack = $text.' '.$postalCode;
        $pattern = (string) config(
            'delivery.free_city_plz_pattern',
            config('delivery.stuttgart_plz_pattern', '/\b(68159|6816[13579]|68199|68219|68229|68239|68259|6830[579])\b/')
        );

        return $pattern !== '' && (bool) preg_match($pattern, $haystack);
    }

    public function costFor(bool $isFreeCity, float $distanceKm): float
    {
        if ($isFreeCity) {
            return 0.0;
        }

        return round(max(0, $distanceKm) * (float) config('delivery.cost_per_km', 1.00), 2);
    }

    public function withCountry(string $address): string
    {
        $address = trim($address);
        if ($address === '') {
            return '';
        }

        $lower = mb_strtolower($address);
        if (str_contains($lower, 'deutschland') || str_contains($lower, 'germany')) {
            return $address;
        }

        return $address.', Germany';
    }

    public function buildDestination(string $street, string $houseNumber, string $postalCode, string $city): string
    {
        return trim(preg_replace('/\s+/', ' ', implode(' ', array_filter([
            trim($street),
            trim($houseNumber),
            trim($postalCode),
            trim($city),
            'Germany',
        ]))) ?? '');
    }

    /**
     * @param  array<int, string>  $tokens
     */
    public function containsTokens(string $text, array $tokens): bool
    {
        $haystack = mb_strtolower($text);
        foreach ($tokens as $token) {
            $token = mb_strtolower(trim((string) $token));
            if ($token !== '' && str_contains($haystack, $token)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{
     *     ok: bool,
     *     isStuttgart: bool,
     *     isHannover: bool,
     *     distanceKm: float,
     *     formattedAddress: string,
     *     error: ?string
     * }
     */
    protected function payload(bool $ok, bool $isFreeCity, float $distanceKm, string $formattedAddress, ?string $error): array
    {
        return [
            'ok' => $ok,
            'isStuttgart' => $isFreeCity,
            'isHannover' => $isFreeCity,
            'distanceKm' => $distanceKm,
            'formattedAddress' => $formattedAddress,
            'error' => $error,
        ];
    }
}
