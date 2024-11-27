<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FetchClicksDataController extends Controller
{
    public function handleFetchClicks(Request $request)
    {
        // Validate incoming data
        $request->validate([
            'customer_id' => 'required|string',
            'plan_id' => 'required|string',
            'from_date' => 'required|date',
            'to_date' => 'required|date',
            'duration' => 'required|integer',
            'api_key' => 'required|string',
        ]);

        // Retrieve the incoming data
        $customerId = $request->input('customer_id');
        $planId = $request->input('plan_id');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $duration = $request->input('duration');
        $apiKey = $request->input('api_key');

        // Log the received data for debugging
        Log::info('Received click details:', [
            'customer_id' => $customerId,
            'plan_id' => $planId,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'duration' => $duration,
            'api_key' => $apiKey,
        ]);

        // Process the data as needed (e.g., save to database, perform calculations, etc.)

        // Return a response
        return response()->json(['message' => 'Click details received successfully!']);
    }
}
