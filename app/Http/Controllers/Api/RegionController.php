<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\PriceRecord;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function complianceStats()
    {
        $regions = Region::withCount('msmes')->get()->map(function ($region) {
            // Find all price records submitted by MSMEs in this specific region
            $records = PriceRecord::whereHas('msme', function ($query) use ($region) {
                $query->where('region_id', $region->id);
            })->get();

            $totalRecords = $records->count();
            $violations = $records->where('is_compliant', false)->count();
            
            // Avoid division by zero if a region has no records yet
            $complianceRate = $totalRecords > 0 
                ? (($totalRecords - $violations) / $totalRecords) * 100 
                : 100; // Default to 100% if no violations exist

            return [
                'id' => $region->id,
                'name' => $region->name,
                'total_msmes' => $region->msmes_count,
                'violations' => $violations,
                'compliance_rate' => round($complianceRate, 1)
            ];
        });

        // Sort by compliance rate, ascending (worst compliance at the top) or descending
        $sortedRegions = $regions->sortBy('compliance_rate')->values()->all();

        return response()->json($sortedRegions, 200);
    }
}