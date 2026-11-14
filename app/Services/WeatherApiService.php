<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WeatherApiService
{
    public function current(float $latitude, float $longitude): array
    {
        $endpoint = trim((string) config('task7.weather.endpoint'));

        if ($endpoint === '') {
            throw new RuntimeException('Weather API endpoint is not configured.');
        }

        $cacheKey = sprintf('task7:weather:%.2f:%.2f', $latitude, $longitude);
        $cacheMinutes = max(1, (int) config('task7.weather.cache_minutes', 15));

        return Cache::store('file')->remember($cacheKey, now()->addMinutes($cacheMinutes), function () use ($endpoint, $latitude, $longitude): array {
            $payload = Http::acceptJson()
                ->timeout((int) config('task7.timeout', 15))
                ->get($endpoint, [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'current' => 'temperature_2m,weather_code',
                    'timezone' => 'auto',
                ])
                ->throw()
                ->json();

            $temperature = data_get($payload, 'current.temperature_2m');
            $weatherCode = data_get($payload, 'current.weather_code');

            if (! is_numeric($temperature) || ! is_numeric($weatherCode)) {
                throw new RuntimeException('Open-Meteo returned an invalid current weather response.');
            }

            [$condition, $description] = $this->describeWeatherCode((int) $weatherCode);

            return [
                'temperature_c' => (float) $temperature,
                'condition' => $condition,
                'description' => $description,
            ];
        });
    }

    /** @return array{0: string, 1: string} */
    private function describeWeatherCode(int $code): array
    {
        return match ($code) {
            0 => ['Clear', 'Clear sky'],
            1 => ['Mainly clear', 'Mainly clear'],
            2 => ['Partly cloudy', 'Partly cloudy'],
            3 => ['Cloudy', 'Overcast'],
            45 => ['Fog', 'Fog'],
            48 => ['Fog', 'Depositing rime fog'],
            51 => ['Drizzle', 'Light drizzle'],
            53 => ['Drizzle', 'Moderate drizzle'],
            55 => ['Drizzle', 'Dense drizzle'],
            56 => ['Drizzle', 'Light freezing drizzle'],
            57 => ['Drizzle', 'Dense freezing drizzle'],
            61 => ['Rain', 'Slight rain'],
            63 => ['Rain', 'Moderate rain'],
            65 => ['Rain', 'Heavy rain'],
            66 => ['Rain', 'Light freezing rain'],
            67 => ['Rain', 'Heavy freezing rain'],
            71 => ['Snow', 'Slight snowfall'],
            73 => ['Snow', 'Moderate snowfall'],
            75 => ['Snow', 'Heavy snowfall'],
            77 => ['Snow', 'Snow grains'],
            80 => ['Rain showers', 'Slight rain showers'],
            81 => ['Rain showers', 'Moderate rain showers'],
            82 => ['Rain showers', 'Violent rain showers'],
            85 => ['Snow showers', 'Slight snow showers'],
            86 => ['Snow showers', 'Heavy snow showers'],
            95 => ['Thunderstorm', 'Thunderstorm'],
            96 => ['Thunderstorm', 'Thunderstorm with slight hail'],
            99 => ['Thunderstorm', 'Thunderstorm with heavy hail'],
            default => ['Unknown', 'Weather conditions unavailable'],
        };
    }
}
