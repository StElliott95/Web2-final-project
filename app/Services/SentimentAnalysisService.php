<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class SentimentAnalysisService
{
    public function analyze(string $text): array
    {
        $token = trim((string) config('task7.sentiment.token'));
        if ($token === '') {
            throw new RuntimeException('CLOUDFLARE_API_TOKEN is required for sentiment analysis.');
        }

        $endpoint = trim((string) config('task7.sentiment.endpoint'));
        if ($endpoint === '') {
            $accountId = trim((string) config('task7.sentiment.account_id'));
            $model = trim((string) config('task7.sentiment.model', '@cf/huggingface/distilbert-sst-2-int8'));

            if ($accountId === '') {
                throw new RuntimeException('CLOUDFLARE_ACCOUNT_ID is required for sentiment analysis.');
            }

            if (! preg_match('/^[a-f0-9]{32}$/i', $accountId)) {
                throw new RuntimeException('CLOUDFLARE_ACCOUNT_ID must be a 32-character hexadecimal ID.');
            }

            if ($model === '' || str_contains($model, '..')) {
                throw new RuntimeException('SENTIMENT_API_MODEL is invalid.');
            }

            $endpoint = sprintf(
                'https://api.cloudflare.com/client/v4/accounts/%s/ai/run/%s',
                $accountId,
                ltrim($model, '/')
            );
        }

        $payload = Http::acceptJson()
            ->withToken($token)
            ->timeout(max(5, (int) config('task7.sentiment.timeout', 20)))
            ->post($endpoint, ['text' => $text])
            ->throw()
            ->json();

        $prediction = $this->bestPrediction(is_array($payload) ? ($payload['result'] ?? $payload) : $payload);

        $label = strtolower(trim((string) ($prediction['label'] ?? '')));
        $label = match ($label) {
            'positive', 'label_2' => 'positive',
            'neutral', 'label_1' => 'neutral',
            'negative', 'label_0' => 'negative',
            default => '',
        };

        if ($label === '') {
            throw new RuntimeException('Cloudflare Workers AI returned an unsupported sentiment label.');
        }

        $score = $prediction['score'] ?? null;
        if ($score !== null && (! is_numeric($score) || (float) $score < 0 || (float) $score > 1)) {
            throw new RuntimeException('Sentiment score must be between 0 and 1.');
        }

        return [
            'label' => $label,
            'score' => $score === null ? null : round((float) $score, 4),
        ];
    }

    private function bestPrediction(mixed $payload): array
    {
        if (! is_array($payload)) {
            throw new RuntimeException('Cloudflare Workers AI returned an invalid JSON response.');
        }

        if (isset($payload['label'])) {
            return $payload;
        }

        if (isset($payload['predictions']) && is_array($payload['predictions'])) {
            $payload = $payload['predictions'];
        }

        if (isset($payload[0]) && is_array($payload[0]) && ! isset($payload[0]['label'])) {
            $payload = $payload[0];
        }

        $predictions = array_values(array_filter(
            $payload,
            fn ($item): bool => is_array($item) && isset($item['label'])
        ));

        if ($predictions === []) {
            throw new RuntimeException('Cloudflare Workers AI returned no class predictions.');
        }

        usort(
            $predictions,
            fn (array $left, array $right): int => (float) ($right['score'] ?? 0) <=> (float) ($left['score'] ?? 0)
        );

        return $predictions[0];
    }
}
