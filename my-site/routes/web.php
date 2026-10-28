<?php

use App\Http\Controllers\Admin\SuggestedIdeasController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RandomIdeaController;
use App\Http\Controllers\SuggestedIdeaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RandomIdeaController::class, 'home'])
    ->middleware('throttle:60,1')
    ->name('home');

Route::get('/dashboard', [RandomIdeaController::class, 'dashboard'])
    ->middleware(['auth', 'verified', 'throttle:60,1'])
    ->name('dashboard');

Route::get('/catalog', [CatalogController::class, 'index'])
    ->middleware('throttle:60,1')
    ->name('catalog');

Route::middleware('auth')->group(function () {
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/{idea}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
    Route::delete('/history', [HistoryController::class, 'clear'])->name('history.clear');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::get('/suggest', [SuggestedIdeaController::class, 'create'])->name('suggest.create');
    Route::post('/suggest', [SuggestedIdeaController::class, 'store'])->name('suggest.store');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/suggested-ideas', [SuggestedIdeasController::class, 'index'])
        ->name('admin.suggested-ideas.index');
    Route::post('/suggested-ideas/{id}/approve', [SuggestedIdeasController::class, 'approve'])
        ->name('admin.suggested-ideas.approve');
    Route::post('/suggested-ideas/{id}/reject', [SuggestedIdeasController::class, 'reject'])
        ->name('admin.suggested-ideas.reject');
});
