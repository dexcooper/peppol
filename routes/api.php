<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\InvoiceLineController;
use App\Http\Controllers\Api\IsAliveController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/is-alive', [IsAliveController::class, 'isAlive']);

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');

Route::apiResource('invoices', InvoiceController::class)
    ->middleware(['auth:sanctum', 'company']);

Route::apiResource('invoices.invoice-lines', InvoiceLineController::class)
    ->shallow()
    ->middleware(['auth:sanctum', 'company']);
