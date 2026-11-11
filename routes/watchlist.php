<?php
use App\Http\Controllers\WatchlistController;
use Illuminate\Support\Facades\Route;
// Task 5: Watchlist
// load it at the end of routes/web.php with:  require __DIR__.'/watchlist.php';
Route::middleware('auth')->group(function () {
   Route::get('watchlist', [WatchlistController::class, 'listWatchlist'])->name('list-watchlist');
   Route::post('watchlist', [WatchlistController::class, 'addMovie'])->name('add-watchlist');
   Route::delete('watchlist/{id}', [WatchlistController::class, 'removeMovie'])->name('remove-watchlist');
   Route::put('watchlist/{id}/watched', [WatchlistController::class, 'markWatched'])->name('watched-watchlist');
   Route::put('watchlist/{id}/unwatched', [WatchlistController::class, 'markUnwatched'])->name('unwatched-watchlist');
   Route::get('watch-history', [WatchlistController::class, 'history'])->name('watch-history');
});