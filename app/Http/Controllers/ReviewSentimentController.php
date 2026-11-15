<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\SentimentAnalysisService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ReviewSentimentController extends Controller
{
    public function analyze(Review $review, SentimentAnalysisService $sentiment): RedirectResponse
    {
        $user = auth()->user();

        abort_unless(
            $user && ((int) $user->id === (int) $review->user_id || $user->role === 'admin'),
            403,
            'You may only analyze your own review.'
        );

        if (! is_string($review->body) || trim($review->body) === '') {
            return back()->with('error', 'Add review text before requesting sentiment analysis.');
        }

        try {
            $result = $sentiment->analyze($review->body);
        } catch (ConnectionException|RequestException|RuntimeException $exception) {
            Log::warning('Review sentiment request failed.', [
                'review_id' => $review->getKey(),
                'exception' => $exception::class,
                'http_status' => $exception instanceof RequestException ? $exception->response->status() : null,
            ]);

            return back()->with('error', 'Sentiment analysis is unavailable. Check the Cloudflare settings or try again later.');
        }

        $review->forceFill([
            'sentiment_label' => $result['label'],
            'sentiment_score' => $result['score'],
            'sentiment_analyzed_at' => now(),
        ])->save();

        return back()->with('success', 'Review sentiment updated.');
    }
}
