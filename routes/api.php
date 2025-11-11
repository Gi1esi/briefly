<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/paginate', [ArticleController::class, 'paginate']);
Route::get('/tags', [TagController::class, 'index']);

