<?php
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;
// Task 4: Ratings & Reviews
// load it at the end of routes/web.php with:  require __DIR__.'/reviews.php';
Route::middleware('auth')->group(function () {
   Route::get('movies/{movieId}/reviews', [ReviewController::class, 'listReviews'])->name('list-reviews');
   Route::post('movies/{movieId}/reviews', [ReviewController::class, 'saveReview'])->name('save-review');
   Route::get('reviews/{id}/edit', [ReviewController::class, 'editReview'])->name('edit-review');
   Route::put('reviews/{id}', [ReviewController::class, 'updateReview'])->name('update-review');
   Route::delete('reviews/{id}', [ReviewController::class, 'deleteReview'])->name('delete-review');
   // admin
   Route::get('admin/reviews', [ReviewController::class, 'adminReviews'])->name('admin-reviews');
   Route::put('admin/reviews/{id}/toggle', [ReviewController::class, 'toggleReview'])->name('toggle-review');
   Route::delete('admin/reviews/{id}', [ReviewController::class, 'adminDeleteReview'])->name('admin-delete-review');
});
