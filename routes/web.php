<?php

use Illuminate\Support\Facades\Route;
use App\Services\ApiLogger;
use Illuminate\Support\Facades\Http;

Route::get('/', function () {
    return view('welcome');
});
