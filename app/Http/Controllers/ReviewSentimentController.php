<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\SentimentAnalysisService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use RuntimeException;

class ReviewSentimentController extends Controller
{
    public function analyze(Review $review, SentimentAnalysisService $sentiment): RedirectResponse
    {
        abort_unless(
            (int) auth()->id() === (int) $review->user_id || auth()->user()->role === 'admin',
            403,
            'You may only analyze your own review.'
        );

        if (! is_string($review->body) || trim($review->body) === '') {
            return back()->with('error', 'Add review text before requesting sentiment analysis.');
        }

        try {
            $result = $sentiment->analyze($review->body);
        } catch (ConnectionException|RequestException|RuntimeException $exception) {
            report($exception);

            return back()->with('error', 'Sentiment analysis is unavailable right now.');
        }

        $review->update([
            'sentiment_label' => $result['label'],
            'sentiment_score' => $result['score'],
            'sentiment_analyzed_at' => now(),
        ]);

        return back()->with('success', 'Review sentiment updated.');
    }
}
