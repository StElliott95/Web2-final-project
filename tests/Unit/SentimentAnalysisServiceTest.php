<?php

namespace Tests\Unit;

use App\Services\SentimentAnalysisService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class SentimentAnalysisServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'task7.sentiment.endpoint' => '',
            'task7.sentiment.account_id' => '0123456789abcdef0123456789abcdef',
            'task7.sentiment.token' => 'test-cloudflare-token',
            'task7.sentiment.model' => '@cf/huggingface/distilbert-sst-2-int8',
            'task7.sentiment.timeout' => 12,
        ]);
    }

    public function test_it_calls_cloudflare_and_returns_the_highest_scoring_sentiment(): void
    {
        Http::fake([
            'api.cloudflare.com/*' => Http::response([
                'result' => [
                    ['label' => 'NEGATIVE', 'score' => 0.10],
                    ['label' => 'POSITIVE', 'score' => 0.87566],
                ],
                'success' => true,
            ]),
        ]);

        $result = app(SentimentAnalysisService::class)->analyze('The movie was fantastic.');

        $this->assertSame(['label' => 'positive', 'score' => 0.8757], $result);
        Http::assertSent(fn (Request $request): bool =>
            $request->url() === 'https://api.cloudflare.com/client/v4/accounts/0123456789abcdef0123456789abcdef/ai/run/@cf/huggingface/distilbert-sst-2-int8'
            && $request->hasHeader('Authorization', 'Bearer test-cloudflare-token')
            && $request['text'] === 'The movie was fantastic.'
        );
    }

    public function test_it_maps_the_negative_class(): void
    {
        Http::fake([
            'api.cloudflare.com/*' => Http::response([
                'result' => [['label' => 'negative', 'score' => 0.91]],
                'success' => true,
            ]),
        ]);

        $this->assertSame(
            ['label' => 'negative', 'score' => 0.91],
            app(SentimentAnalysisService::class)->analyze('I did not enjoy the movie.')
        );
    }

    public function test_it_requires_cloudflare_credentials_before_sending_a_request(): void
    {
        config(['task7.sentiment.account_id' => '', 'task7.sentiment.token' => '']);
        Http::fake();

        try {
            app(SentimentAnalysisService::class)->analyze('A review.');
            $this->fail('Expected a missing-token exception.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('CLOUDFLARE_API_TOKEN is required', $exception->getMessage());
        }

        Http::assertNothingSent();
    }

    public function test_it_rejects_unsupported_labels_and_invalid_scores(): void
    {
        Http::fake([
            'api.cloudflare.com/*' => Http::sequence()
                ->push(['result' => [['label' => 'mixed', 'score' => 0.9]]])
                ->push(['result' => [['label' => 'positive', 'score' => 1.2]]]),
        ]);

        try {
            app(SentimentAnalysisService::class)->analyze('A review.');
            $this->fail('Expected an unsupported-label response to fail.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('unsupported sentiment label', $exception->getMessage());
        }

        try {
            app(SentimentAnalysisService::class)->analyze('Another review.');
            $this->fail('Expected an invalid score response to fail.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('between 0 and 1', $exception->getMessage());
        }
    }

    public function test_it_surfaces_upstream_http_failures(): void
    {
        Http::fake([
            'api.cloudflare.com/*' => Http::response(['success' => false], 503),
        ]);

        $this->expectException(\Illuminate\Http\Client\RequestException::class);

        app(SentimentAnalysisService::class)->analyze('A review.');
    }
}
