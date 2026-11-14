<?php

return [
    'timeout' => (int) env('TASK7_API_TIMEOUT', 10),

    'weather' => [
        'endpoint' => env('WEATHER_API_ENDPOINT'),
        'token' => env('WEATHER_API_TOKEN'),
        'temperature_path' => env('WEATHER_API_TEMPERATURE_PATH', 'temperature_c'),
        'condition_path' => env('WEATHER_API_CONDITION_PATH', 'condition'),
        'description_path' => env('WEATHER_API_DESCRIPTION_PATH', 'description'),
    ],

    'maps' => [
        'endpoint' => env('MAPS_API_ENDPOINT'),
        'token' => env('MAPS_API_TOKEN'),
        'results_path' => env('MAPS_API_RESULTS_PATH', 'results'),
    ],

    'sentiment' => [
        'endpoint' => env('SENTIMENT_API_ENDPOINT'),
        'token' => env('SENTIMENT_API_TOKEN'),
    ],
];
