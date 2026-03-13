<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MSME;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MSMEController extends Controller
{
    /**
     * GET: Retrieve all MSMEs
     * Route: GET /api/msmes
     */
    public function index()
    {
        // Eager load the 'region' relationship to optimize database queries
        // This ensures the region details are attached to each MSME in the JSON response
        $msmes = MSME::with('region')->get();
        
        return response()->json($msmes, 200);
    }

    /**
     * POST: Create a new MSME
     * Route: POST /api/msmes
     */
    public function store(Request $request)
    {
        // 1. Validate the incoming request data
        $validatedData = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'store_name' => 'required|string|max:255',
            'owner_identifier' => 'required|string', // Plain text submitted by the client
        ]);

        // 2. Hash the owner identifier for data privacy
        $hashedOwner = Hash::make($validatedData['owner_identifier']);

        // 3. Create the MSME record in the database
        $msme = MSME::create([
            'region_id' => $validatedData['region_id'],
            'store_name' => $validatedData['store_name'],
            'owner_hash' => $hashedOwner,
        ]);

        // 4. Return a success response with the newly created record
        return response()->json([
            'message' => 'MSME successfully created',
            'data' => $msme
        ], 201);
    }

    /**
     * GET: Retrieve a specific MSME by ID
     * Route: GET /api/msmes/{id}
     */
    public function show($id)
    {
        // Find the MSME and load both its Region and historical Price Records
        $msme = MSME::with(['region', 'priceRecords'])->findOrFail($id);
        
        return response()->json($msme, 200);
    }
}