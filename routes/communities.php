<?php
use App\Http\Controllers\CommunityController;
use Illuminate\Support\Facades\Route;
Route::get('/communities',[CommunityController::class,'index'])->name('communities.index');
Route::middleware('auth')->group(function() {
    Route::get('/communities/create',[CommunityController::class,'create'])->name('communities.create');
    Route::post('/communities',[CommunityController::class,'store'])->middleware('throttle:10,1')->name('communities.store');
    Route::post('/communities/{community}/join',[CommunityController::class,'join'])->name('communities.join');
    Route::post('/communities/{community}/threads',[CommunityController::class,'thread'])->middleware('throttle:10,1')->name('communities.threads.store');
    Route::get('/communities/{community}/rooms/{room}',[CommunityController::class,'room'])->name('communities.room');
});
Route::get('/communities/{community}',[CommunityController::class,'show'])->name('communities.show');
