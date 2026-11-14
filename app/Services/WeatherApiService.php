<?php

namespace App\Services;

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

        $request = Http::acceptJson()->timeout((int) config('task7.timeout', 10));
        $token = trim((string) config('task7.weather.token'));

        if ($token !== '') {
            $request = $request->withToken($token);
        }

        $response = $request->get($endpoint, [
            'latitude' => $latitude,
            'longitude' => $longitude,
        ])->throw();

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException('Weather API returned an invalid JSON response.');
        }

        $temperature = data_get($payload, config('task7.weather.temperature_path', 'temperature_c'));
        $condition = data_get($payload, config('task7.weather.condition_path', 'condition'));
        $description = data_get($payload, config('task7.weather.description_path', 'description'));

        if (! is_numeric($temperature) || ! is_string($condition) || trim($condition) === '') {
            throw new RuntimeException('Weather API response does not match the configured response contract.');
        }

        return [
            'temperature_c' => (float) $temperature,
            'condition' => trim($condition),
            'description' => is_string($description) ? trim($description) : '',
        ];
    }
}
