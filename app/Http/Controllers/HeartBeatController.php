<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class HeartBeatController extends Controller
{

    public function index()
    {
        // Get the current time
        $currentTime = now();

        // Calculate the time 5 minutes ago
        $fiveMinutesAgo = $currentTime->subMinutes(5);

        // Use the DB facade to select the latest "heartbeat" records within the last 5 minutes
        $latestHeartbeats = DB::table('heartbeats')
            ->where('created_at', '>=', $fiveMinutesAgo)
            ->get();

        // Return the "latestHeartbeats" as a JSON response
        return response()->json($latestHeartbeats);
    }

    public function store(Request $request)
    {
        // Validate the request data as needed
        $request->validate([
            'heartbeat' => 'required|string',
        ]);

        // Use the DB facade to insert a new record
        DB::table('heartbeats')->insert([
            'heartbeat' => $request->input('heartbeat'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        // Optionally, you can return a response to indicate success
        return response()->json(['message' => 'Heartbeat record created successfully'], 201);
    }

}
