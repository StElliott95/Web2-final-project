<?php

use App\Http\Controllers\FollowController;

use Illuminate\Support\Facades\Route;

// ===== Task 9: Follow Users =====

Route::middleware('auth')->group(function () {

    Route::get('people', [FollowController::class, 'listUsers'])->name('list-users');

    Route::post('people/{id}/follow', [FollowController::class, 'followUser'])->name('follow-user');

    Route::delete('people/{id}/follow', [FollowController::class, 'unfollowUser'])->name('unfollow-user');
    Route::get('people/{id}/followers', [FollowController::class, 'followers'])->name('user-followers');

    Route::get('people/{id}/following', [FollowController::class, 'following'])->name('user-following');
    Route::get('people/{id}', [FollowController::class, 'profile'])->name('user-profile');
    Route::get('feed', [FollowController::class, 'feed'])->name('feed');
    Route::get('privacy', [FollowController::class, 'privacySettings'])->name('privacy-settings');

    Route::put('privacy', [FollowController::class, 'updatePrivacy'])->name('update-privacy');
 
 

});
 