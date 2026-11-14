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

        $request = Http::acceptJson()->timeout(max(10, (int) config('task7.sentiment.timeout', 30)));
        $token = trim((string) config('task7.sentiment.token'));

        if ($token !== '') {
            $request = $request->withToken($token);
        }

        if (str_contains((string) parse_url($endpoint, PHP_URL_HOST), 'huggingface.co') && $token === '') {
            throw new RuntimeException('SENTIMENT_API_TOKEN is required for Hugging Face inference.');
        }

        $payload = $request->post($endpoint, ['inputs' => $text])->throw()->json();
        $prediction = $this->bestPrediction($payload);

        $label = strtolower(trim((string) ($prediction['label'] ?? '')));
        $label = match ($label) {
            'positive', 'label_2' => 'positive',
            'neutral', 'label_1' => 'neutral',
            'negative', 'label_0' => 'negative',
            default => '',
        };

        if ($label === '') {
            throw new RuntimeException('Sentiment Analysis API returned an unsupported label.');
        }

        $score = $prediction['score'] ?? null;
        if ($score !== null && (! is_numeric($score) || (float) $score < 0 || (float) $score > 1)) {
            throw new RuntimeException('Sentiment Analysis API score must be between 0 and 1.');
        }

        return [
            'label' => $label,
            'score' => $score === null ? null : round((float) $score, 4),
        ];
    }

    private function bestPrediction(mixed $payload): array
    {
        if (! is_array($payload)) {
            throw new RuntimeException('Sentiment Analysis API returned an invalid JSON response.');
        }

        if (isset($payload['label'])) {
            return $payload;
        }

        $predictions = $payload;

        if (isset($predictions[0]) && is_array($predictions[0]) && ! isset($predictions[0]['label'])) {
            $predictions = $predictions[0];
        }

        $predictions = array_values(array_filter(
            $predictions,
            fn ($item): bool => is_array($item) && isset($item['label'])
        ));

        if ($predictions === []) {
            throw new RuntimeException('Sentiment Analysis API returned no class predictions.');
        }

        usort($predictions, fn (array $left, array $right): int => (float) ($right['score'] ?? 0) <=> (float) ($left['score'] ?? 0));

        return $predictions[0];
    }
}
