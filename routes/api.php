<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MSMEController;
use App\Http\Controllers\Api\PriceRecordController;
use App\Http\Controllers\Api\RegionController;
use App\Models\PriceRecord;
use App\Models\Commodity;
use Illuminate\Support\Facades\Http;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('msmes', MSMEController::class);

Route::get('violations', [PriceRecordController::class, 'recentViolations']);

Route::get('regional-compliance', [RegionController::class, 'complianceStats']);

Route::get('daily-prices', [PriceRecordController::class, 'todaysPrices']);

Route::post('/submit-price', function (Request $request) {
    // 1. Find the commodity to get the official SRP
    $commodity = Commodity::findOrFail($request->commodity_id);
    
    // 2. Calculate the variance
    $variance = (($request->market_price - $commodity->srp) / $commodity->srp) * 100;
    $isCompliant = $variance <= 10;

    // 3. Save the new price record
    $record = PriceRecord::create([
        'msme_id' => $request->msme_id,
        'commodity_id' => $request->commodity_id,
        'market_price' => $request->market_price,
        'variance_percentage' => $variance,
        'is_compliant' => $isCompliant,
        'recorded_at' => now(),
    ]);

    // 4. Fire the Discord Webhook if it's a violation!
    if (!$isCompliant) {
        $record->load(['msme', 'commodity']); 
        
        $payload = [
            'content' => '🚨 **LIVE ALERT: SRP Violation Detected** 🚨',
            'embeds' => [[
                'title' => $record->msme->store_name,
                'description' => 'A retailer has exceeded the 10% maximum variance allowance.',
                'color' => 16711680,
                'fields' => [
                    ['name' => 'Commodity', 'value' => $record->commodity->name, 'inline' => true],
                    ['name' => 'Official SRP', 'value' => '₱' . number_format($record->commodity->srp, 2), 'inline' => true],
                    ['name' => 'Market Price', 'value' => '₱' . number_format($record->market_price, 2), 'inline' => true],
                    ['name' => 'Variance', 'value' => round($variance, 2) . '% Over SRP', 'inline' => false]
                ]
            ]]
        ];
        
        Http::post(env('ALERT_WEBHOOK_URL'), $payload);
    }

    return response()->json(['status' => 'success', 'data' => $record]);
});