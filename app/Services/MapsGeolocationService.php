<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class MapsGeolocationService
{
    public function nearbyPlaces(float $latitude, float $longitude, float $radiusKm): array
    {
        $endpoint = trim((string) config('task7.maps.endpoint'));

        if ($endpoint === '') {
            throw new RuntimeException('Maps/Geolocation API endpoint is not configured.');
        }

        $request = Http::acceptJson()->timeout((int) config('task7.timeout', 10));
        $token = trim((string) config('task7.maps.token'));

        if ($token !== '') {
            $request = $request->withToken($token);
        }

        $response = $request->get($endpoint, [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'radius_km' => $radiusKm,
        ])->throw();

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException('Maps/Geolocation API returned an invalid JSON response.');
        }

        $items = data_get($payload, config('task7.maps.results_path', 'results'));

        if (! is_array($items)) {
            throw new RuntimeException('Maps/Geolocation API response must contain a results array.');
        }

        $places = [];

        foreach ($items as $item) {
            if (! is_array($item)
                || ! isset($item['name'], $item['latitude'], $item['longitude'])
                || ! is_numeric($item['latitude'])
                || ! is_numeric($item['longitude'])) {
                continue;
            }

            $placeLatitude = (float) $item['latitude'];
            $placeLongitude = (float) $item['longitude'];

            if ($placeLatitude < -90 || $placeLatitude > 90 || $placeLongitude < -180 || $placeLongitude > 180) {
                continue;
            }

            $mapUrl = $item['map_url'] ?? null;
            $scheme = is_string($mapUrl) ? parse_url($mapUrl, PHP_URL_SCHEME) : null;

            $places[] = [
                'name' => mb_substr(trim((string) $item['name']), 0, 150),
                'latitude' => $placeLatitude,
                'longitude' => $placeLongitude,
                'address' => isset($item['address']) ? mb_substr(trim((string) $item['address']), 0, 255) : null,
                'distance_km' => isset($item['distance_km']) && is_numeric($item['distance_km'])
                    ? (float) $item['distance_km']
                    : null,
                'map_url' => in_array($scheme, ['http', 'https'], true) ? $mapUrl : null,
            ];

            if (count($places) >= 15) {
                break;
            }
        }

        return $places;
    }
}
