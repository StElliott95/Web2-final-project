<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class SentimentAnalysisService
{
    public function analyze(string $text): array
    {
        $endpoint = trim((string) config('task7.sentiment.endpoint'));

        if ($endpoint === '') {
            throw new RuntimeException('Sentiment Analysis API endpoint is not configured.');
        }

        $request = Http::acceptJson()->timeout((int) config('task7.timeout', 10));
        $token = trim((string) config('task7.sentiment.token'));

        if ($token !== '') {
            $request = $request->withToken($token);
        }

        $response = $request->post($endpoint, ['text' => $text])->throw();
        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException('Sentiment Analysis API returned an invalid JSON response.');
        }

        $label = strtolower(trim((string) ($payload['label'] ?? '')));
        if (! in_array($label, ['positive', 'neutral', 'negative'], true)) {
            throw new RuntimeException('Sentiment Analysis API returned an unsupported label.');
        }

        $score = $payload['score'] ?? null;
        if ($score !== null && (! is_numeric($score) || (float) $score < 0 || (float) $score > 1)) {
            throw new RuntimeException('Sentiment Analysis API score must be between 0 and 1.');
        }

        return [
            'label' => $label,
            'score' => $score === null ? null : round((float) $score, 4),
        ];
    }
}
