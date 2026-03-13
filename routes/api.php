<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MSMEController;
use App\Http\Controllers\Api\PriceRecordController;
use App\Http\Controllers\Api\RegionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('msmes', MSMEController::class);

Route::get('violations', [PriceRecordController::class, 'recentViolations']);

Route::get('regional-compliance', [RegionController::class, 'complianceStats']);

Route::get('daily-prices', [PriceRecordController::class, 'todaysPrices']);