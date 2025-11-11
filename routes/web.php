<?php

use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RatingController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->post('/articles/rate', [RatingController::class, 'rate']);

Route::prefix('chat')
    ->name('chat.')
    ->controller(ConversationController::class)
    ->group(function () {
        Route::get('/chat/{article}', 'chat')->name('chat');
        Route::post('conversation/', 'getOrCreate')->name('getOrCreate');
    });
Route::prefix('message')
    ->name('message.')
    ->controller(MessageController::class)
    ->group(function () {
        Route::post('/store', 'store')->name('store');
    });

require __DIR__.'/auth.php';
