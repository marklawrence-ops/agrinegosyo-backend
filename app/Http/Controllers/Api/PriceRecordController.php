<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PriceRecord;
use Illuminate\Http\Request;

class PriceRecordController extends Controller
{
    public function recentViolations()
    {
        // Fetch records where is_compliant is false, grab the newest ones first
        $violations = PriceRecord::with(['msme.region', 'commodity'])
            ->where('is_compliant', false)
            ->orderBy('recorded_at', 'desc')
            ->take(5) // Limit to the top 5 most recent for the dashboard
            ->get();

        return response()->json($violations, 200);
    }

    public function todaysPrices()
    {
        // Fetch the latest price records, eager loading all necessary relationships
        $records = PriceRecord::with(['commodity', 'msme.region'])
            ->orderBy('recorded_at', 'desc')
            ->take(20) // Limit to the 20 most recent entries for the dashboard view
            ->get();

        return response()->json($records, 200);
    }
    
}