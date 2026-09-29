<?php

use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\CaseController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\LeadController;
use App\Http\Controllers\Api\V1\OpportunityController;
use Illuminate\Support\Facades\Route;

Route::post('/tokens', [TokenController::class, 'store'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->prefix('v1')->name('api.v1.')->group(function () {
    Route::apiResource('leads', LeadController::class);
    Route::apiResource('accounts', AccountController::class);
    Route::apiResource('contacts', ContactController::class);
    Route::apiResource('opportunities', OpportunityController::class);
    Route::apiResource('cases', CaseController::class);
});
