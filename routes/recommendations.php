<?php
use App\Http\Controllers\RecommendationController;
use Illuminate\Support\Facades\Route;
Route::middleware('auth')->group(function() {
    Route::get('/recommendations',[RecommendationController::class,'index'])->name('recommendations');
    Route::get('/preferences',[RecommendationController::class,'edit'])->name('preferences.edit');
    Route::put('/preferences',[RecommendationController::class,'update'])->name('preferences.update');
});
