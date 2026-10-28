<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

use App\Http\Controllers\SuggestedIdeaController;

// Группа маршрутов только для авторизованных пользователей
Route::middleware(['auth'])->group(function () {
    Route::get('/suggest', [SuggestedIdeaController::class, 'create'])->name('suggest.create');
    Route::post('/suggest', [SuggestedIdeaController::class, 'store'])->name('suggest.store');
});


use App\Http\Controllers\Admin\SuggestedIdeasController;

// Маршруты только для администраторов
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/suggested-ideas', [SuggestedIdeasController::class, 'index'])
        ->name('admin.suggested-ideas.index');
    Route::post('/suggested-ideas/{id}/approve', [SuggestedIdeasController::class, 'approve'])
        ->name('admin.suggested-ideas.approve');
    Route::post('/suggested-ideas/{id}/reject', [SuggestedIdeasController::class, 'reject'])
        ->name('admin.suggested-ideas.reject');
});