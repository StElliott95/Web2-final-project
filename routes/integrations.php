<?php

use App\Http\Controllers\CalendarExportController;
use App\Http\Controllers\DiscussionRoomController;
use App\Http\Controllers\NearbyCinemaController;
use App\Http\Controllers\ReviewSentimentController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::get('/integrations/weather', WeatherController::class)
    ->middleware('throttle:30,1')
    ->name('integrations.weather');
Route::get('/integrations/nearby-cinemas', NearbyCinemaController::class)
    ->middleware('throttle:30,1')
    ->name('integrations.nearby');
Route::get('/movies/{movie}/calendar.ics', CalendarExportController::class)
    ->name('integrations.calendar-export');

Route::middleware('auth')->group(function () {
    Route::post('/reviews/{review}/sentiment', [ReviewSentimentController::class, 'analyze'])
        ->middleware('throttle:10,1')
        ->name('integrations.review-sentiment');

    Route::get('/movies/{movie}/discussion', [DiscussionRoomController::class, 'show'])
        ->name('integrations.discussion-room');
    Route::get('/discussion-rooms/{room}/stream', [DiscussionRoomController::class, 'stream'])
        ->name('integrations.discussion-stream');
    Route::post('/discussion-rooms/{room}/messages', [DiscussionRoomController::class, 'send'])
        ->middleware('throttle:30,1')
        ->name('integrations.discussion-messages');
});
