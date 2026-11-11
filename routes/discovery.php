<?php

use App\Http\Controllers\DiscoveryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DiscoveryController::class, 'index'])->name('discover');
Route::get('/movies', [DiscoveryController::class, 'index'])->name('movies.index');
Route::get('/movies/{tmdbId}', [DiscoveryController::class, 'show'])
    ->where('tmdbId', '[0-9]+')
    ->name('movies.show');
