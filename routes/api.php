<?php

use App\Http\Controllers\Api\V1\MessageController as MessageControllerV1;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('jwt.auth')->group(function () {
        Route::get('/messages', [MessageControllerV1::class, 'index']);
        Route::post ('/messages', [MessageControllerV1::class, 'store']);
        Route::post('/messages/{id}/reply', [MessageControllerV1::class, 'reply']);
    
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });    
});