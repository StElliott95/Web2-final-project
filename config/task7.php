<?php

return [
    'timeout' => (int) env('TASK7_API_TIMEOUT', 15),

    'weather' => [
        'endpoint' => env('WEATHER_API_ENDPOINT') ?: 'https://api.open-meteo.com/v1/forecast',
        'cache_minutes' => (int) env('WEATHER_API_CACHE_MINUTES', 15),
    ],

    'maps' => [
        'endpoint' => env('MAPS_API_ENDPOINT') ?: 'https://overpass.private.coffee/api/interpreter',
        'fallback_endpoint' => env('MAPS_API_FALLBACK_ENDPOINT') ?: 'https://overpass-api.de/api/interpreter',
        'timeout' => (int) env('MAPS_API_TIMEOUT', 30),
        'cache_minutes' => (int) env('MAPS_API_CACHE_MINUTES', 60),
    ],

    'sentiment' => [
        'endpoint' => env('SENTIMENT_API_ENDPOINT'),
        'token' => env('SENTIMENT_API_TOKEN'),
    ],
];
