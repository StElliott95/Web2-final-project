<?php
use App\Http\Controllers\AdminController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth',EnsureAdmin::class])->prefix('admin')->name('admin.')->group(function() {
    Route::get('/',[AdminController::class,'index'])->name('dashboard');
    Route::get('/users',[AdminController::class,'users'])->name('users');
    Route::put('/users/{user}',[AdminController::class,'updateUser'])->name('users.update');
    Route::delete('/users/{user}',[AdminController::class,'deleteUser'])->name('users.delete');
    Route::get('/movies',[AdminController::class,'movies'])->name('movies');
    Route::get('/movies/create',[AdminController::class,'createMovie'])->name('movies.create');
    Route::post('/movies',[AdminController::class,'storeMovie'])->name('movies.store');
    Route::get('/movies/{movie}/edit',[AdminController::class,'editMovie'])->name('movies.edit');
    Route::put('/movies/{movie}',[AdminController::class,'updateMovie'])->name('movies.update');
    Route::delete('/movies/{movie}',[AdminController::class,'deleteMovie'])->name('movies.delete');
    Route::get('/activity',[AdminController::class,'activity'])->name('activity');
});
