<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
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

        $radiusKm = min(50, max(1, $radiusKm));
        $cacheKey = sprintf('task7:cinemas:%.3f:%.3f:%.1f', $latitude, $longitude, $radiusKm);
        $cacheMinutes = max(1, (int) config('task7.maps.cache_minutes', 60));

        return Cache::store('file')->remember($cacheKey, now()->addMinutes($cacheMinutes), function () use ($endpoint, $latitude, $longitude, $radiusKm): array {
            $radiusMeters = (int) round($radiusKm * 1000);
            $query = sprintf(
                '[out:json][timeout:%d];nwr(around:%d,%.6f,%.6f)["amenity"="cinema"];out body center;',
                max(1, (int) config('task7.timeout', 15)),
                $radiusMeters,
                $latitude,
                $longitude
            );

            $payload = Http::acceptJson()
                ->asForm()
                ->withUserAgent('FrameMovieDiscovery/1.0 (+https://github.com/StElliott95/Web2-final-project)')
                ->timeout((int) config('task7.timeout', 15))
                ->post($endpoint, ['data' => $query])
                ->throw()
                ->json();

            $items = data_get($payload, 'elements');

            if (! is_array($items)) {
                throw new RuntimeException('OpenStreetMap Overpass returned an invalid places response.');
            }

            $places = [];

            foreach ($items as $item) {
                if (! is_array($item) || ! is_array($item['tags'] ?? null)) {
                    continue;
                }

                $name = trim((string) ($item['tags']['name'] ?? ''));
                $placeLatitude = $item['lat'] ?? $item['center']['lat'] ?? null;
                $placeLongitude = $item['lon'] ?? $item['center']['lon'] ?? null;

                if ($name === '' || ! is_numeric($placeLatitude) || ! is_numeric($placeLongitude)) {
                    continue;
                }

                $placeLatitude = (float) $placeLatitude;
                $placeLongitude = (float) $placeLongitude;

                if ($placeLatitude < -90 || $placeLatitude > 90 || $placeLongitude < -180 || $placeLongitude > 180) {
                    continue;
                }

                $tags = $item['tags'];
                $address = $this->formatAddress($tags);
                $type = $item['type'] ?? null;
                $id = $item['id'] ?? null;
                $mapUrl = in_array($type, ['node', 'way', 'relation'], true) && is_numeric($id)
                    ? sprintf('https://www.openstreetmap.org/%s/%d', $type, (int) $id)
                    : null;

                $places[] = [
                    'name' => mb_substr($name, 0, 150),
                    'latitude' => $placeLatitude,
                    'longitude' => $placeLongitude,
                    'address' => $address,
                    'distance_km' => $this->distanceInKm($latitude, $longitude, $placeLatitude, $placeLongitude),
                    'map_url' => $mapUrl,
                ];
            }

            usort($places, fn (array $left, array $right): int => $left['distance_km'] <=> $right['distance_km']);

            return array_slice($places, 0, 15);
        });
    }

    private function formatAddress(array $tags): ?string
    {
        $street = trim(implode(' ', array_filter([
            $tags['addr:street'] ?? null,
            $tags['addr:housenumber'] ?? null,
        ], fn ($part): bool => is_string($part) && trim($part) !== '')));
        $locality = $tags['addr:city'] ?? $tags['addr:town'] ?? $tags['addr:village'] ?? null;

        $parts = array_filter([$street, $locality, $tags['addr:postcode'] ?? null], fn ($part): bool => is_string($part) && trim($part) !== '');
        $address = implode(', ', array_map('trim', $parts));

        return $address === '' ? null : mb_substr($address, 0, 255);
    }

    private function distanceInKm(float $fromLatitude, float $fromLongitude, float $toLatitude, float $toLongitude): float
    {
        $earthRadiusKm = 6371.0;
        $latitudeDelta = deg2rad($toLatitude - $fromLatitude);
        $longitudeDelta = deg2rad($toLongitude - $fromLongitude);
        $a = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($fromLatitude)) * cos(deg2rad($toLatitude)) * sin($longitudeDelta / 2) ** 2;

        return 2 * $earthRadiusKm * asin(min(1, sqrt($a)));
    }
}
