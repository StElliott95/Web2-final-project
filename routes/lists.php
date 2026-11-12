<?php
use App\Http\Controllers\MovieListController;
use Illuminate\Support\Facades\Route;
Route::get('/community/lists',[MovieListController::class,'browse'])->name('lists.public');
Route::middleware('auth')->group(function() {
    Route::get('/lists',[MovieListController::class,'index'])->name('lists.index');
    Route::get('/lists/create',[MovieListController::class,'create'])->name('lists.create');
    Route::post('/lists',[MovieListController::class,'store'])->name('lists.store');
    Route::get('/lists/{list}/edit',[MovieListController::class,'edit'])->name('lists.edit');
    Route::put('/lists/{list}',[MovieListController::class,'update'])->name('lists.update');
    Route::delete('/lists/{list}',[MovieListController::class,'destroy'])->name('lists.destroy');
    Route::post('/lists/{list}/movies',[MovieListController::class,'add'])->name('lists.movies.add');
    Route::delete('/lists/{list}/movies/{movie}',[MovieListController::class,'remove'])->name('lists.movies.remove');
});
Route::get('/lists/{list}',[MovieListController::class,'show'])->name('lists.show');
