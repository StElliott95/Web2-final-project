<?php

$sentimentProvider = strtolower((string) env('SENTIMENT_PROVIDER', 'local'));

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
        'provider' => $sentimentProvider,
        'endpoint' => $sentimentProvider === 'local'
            ? (env('SENTIMENT_LOCAL_ENDPOINT') ?: 'http://127.0.0.1:8765/analyze')
            : (env('SENTIMENT_API_ENDPOINT') ?: 'https://router.huggingface.co/hf-inference/models/cardiffnlp/twitter-roberta-base-sentiment-latest'),
        'token' => $sentimentProvider === 'local' ? '' : (env('SENTIMENT_API_TOKEN') ?: env('HF_TOKEN')),
        'timeout' => (int) env('SENTIMENT_API_TIMEOUT', 30),
    ],
];
