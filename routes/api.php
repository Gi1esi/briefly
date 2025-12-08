<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ArticleFlagController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserFactController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/articles', [ArticleController::class, 'index']);
Route::middleware('auth:sanctum')->get('/articles/paginate', [ArticleController::class, 'paginate']);
Route::middleware('auth:sanctum')->post('/flags/toggle', [ArticleFlagController::class, 'toggle']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user-facts', [UserFactController::class, 'list']); // List-only for API
    Route::post('/user-facts', [UserFactController::class, 'store']);
    Route::put('/user-facts/{userFact}', [UserFactController::class, 'update']);
    Route::delete('/user-facts/{userFact}', [UserFactController::class, 'destroy']);

});

Route::get('/tags', [TagController::class, 'index']);

