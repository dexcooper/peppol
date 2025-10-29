<?php

use Illuminate\Support\Facades\Route;
use App\Services\ApiLogger;
use Illuminate\Support\Facades\Http;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {

    $response = Http::loggable()->post('https://jsonplaceholder.typicode.com/posts', [
        'title' => 'Test Title',
        'body' => 'Test Body',
        'userId' => 1,
        'password' => 'secret123', // dit moet gemaskeerd worden
    ]);

    return $response->json();

});
