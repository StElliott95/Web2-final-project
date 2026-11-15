<?php

namespace Tests\Unit;

use App\Services\SentimentAnalysisService;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SentimentAnalysisServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'task7.sentiment.endpoint' => 'https://sentiment.example.test/classify',
            'task7.sentiment.token' => 'test-inference-token',
            'task7.sentiment.timeout' => 12,
        ]);
    }

    public function test_it_sends_review_text_and_returns_the_highest_scoring_class(): void
    {
        Http::fake([
            'sentiment.example.test/classify' => Http::response([
                [
                    ['label' => 'LABEL_0', 'score' => 0.10],
                    ['label' => 'LABEL_2', 'score' => 0.87566],
                    ['label' => 'LABEL_1', 'score' => 0.02434],
                ],
            ]),
        ]);

        $result = app(SentimentAnalysisService::class)->analyze('The movie was fantastic.');

        $this->assertSame(['label' => 'positive', 'score' => 0.8757], $result);
        Http::assertSent(fn (Request $request): bool =>
            $request->url() === 'https://sentiment.example.test/classify'
            && $request->hasHeader('Authorization', 'Bearer test-inference-token')
            && $request['inputs'] === 'The movie was fantastic.'
        );
    }

    public function test_it_accepts_a_single_named_prediction(): void
    {
        Http::fake([
            'sentiment.example.test/classify' => Http::response([
                'label' => ' Neutral ',
                'score' => 0.73,
            ]),
        ]);

        $this->assertSame(
            ['label' => 'neutral', 'score' => 0.73],
            app(SentimentAnalysisService::class)->analyze('It was an okay movie.')
        );
    }

    public function test_it_requires_a_token_for_hugging_face_inference(): void
    {
        config([
            'task7.sentiment.endpoint' => 'https://router.huggingface.co/hf-inference/models/example',
            'task7.sentiment.token' => '',
        ]);
        Http::fake();

        try {
            app(SentimentAnalysisService::class)->analyze('A review.');
            $this->fail('Expected a missing-token exception.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('SENTIMENT_API_TOKEN is required', $exception->getMessage());
        }

        Http::assertNothingSent();
    }

    public function test_it_rejects_unknown_labels_and_invalid_scores(): void
    {
        Http::fake([
            'sentiment.example.test/classify' => Http::sequence()
                ->push([['label' => 'mixed', 'score' => 0.9]])
                ->push([['label' => 'positive', 'score' => 1.2]]),
        ]);

        try {
            app(SentimentAnalysisService::class)->analyze('A review.');
            $this->fail('Expected unsupported-label response to fail.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('unsupported label', $exception->getMessage());
        }

        try {
            app(SentimentAnalysisService::class)->analyze('Another review.');
            $this->fail('Expected invalid score response to fail.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('between 0 and 1', $exception->getMessage());
        }
    }

    public function test_it_surfaces_upstream_http_failures(): void
    {
        Http::fake([
            'sentiment.example.test/classify' => Http::response(['error' => 'temporarily unavailable'], 503),
        ]);

        $this->expectException(\Illuminate\Http\Client\RequestException::class);

        app(SentimentAnalysisService::class)->analyze('A review.');
    }
}
