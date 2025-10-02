<?php

use Illuminate\Support\Facades\Route;
use  willvincent\Feeds\Facades\FeedsFacade;

Route::get('/', function () {
    return view('welcome');
});


