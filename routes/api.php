<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\MessageController as MessageControllerV1;

Route::prefix('v1')->middleware('api')->group(function () {
    Route::get('/messages', [MessageControllerV1::class, 'index']);
    Route::post('/messages/{id}/reply', [MessageControllerV1::class, 'reply']);
});