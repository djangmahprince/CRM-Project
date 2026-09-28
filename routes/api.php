<?php

use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\V1\LeadController;
use Illuminate\Support\Facades\Route;

Route::post('/tokens', [TokenController::class, 'store'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->prefix('v1')->name('api.v1.')->group(function () {
    Route::apiResource('leads', LeadController::class);
});
