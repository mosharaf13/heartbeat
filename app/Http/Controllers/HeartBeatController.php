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

        // Use the DB facade to select the latest "heartbeat" record within the last 5 minutes
        $latestHeartbeat = DB::table('heartbeats')
            ->where('created_at', '>=', $fiveMinutesAgo)
            ->latest('created_at') // Order by created_at in descending order
            ->get();

        // Return the "latestHeartbeat" as a JSON response
        return response()->json($latestHeartbeat);
    }

    public function latest()
    {
        // Get the current time
        $currentTime = now();

        // Calculate the time 5 minutes ago
        $fiveMinutesAgo = $currentTime->subMinutes(5);

        // Use the DB facade to select the latest "heartbeat" record within the last 5 minutes
        $latestHeartbeat = DB::table('heartbeats')
            ->where('created_at', '>=', $fiveMinutesAgo)
            ->latest('created_at') // Order by created_at in descending order
            ->first();

        // Return the "latestHeartbeat" as a JSON response
        return response()->json($latestHeartbeat);
    }

    public function threshold()
    {
        // Get the current time
        $currentTime = now();

        // Calculate the time 5 minutes ago
        $fiveMinutesAgo = $currentTime->subMinutes(5);

        // Use the DB facade to select the latest "heartbeat" record within the last 5 minutes
        $latestHeartbeat = DB::table('heartbeats')
            ->where('created_at', '>=', $fiveMinutesAgo)
            ->latest('created_at') // Order by created_at in descending order
            ->first();

        if(is_null($latestHeartbeat)){
            return 0;
        }

        // Use the DB facade to select the latest "heartbeat" record within the last 5 minutes
        $threshold = DB::table('heartbeats')
            ->where('player_id', $latestHeartbeat->player_id)
            ->where('created_at', '>=', $fiveMinutesAgo)
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->pluck('heartbeat')
            ->avg();

        // Return the "latestHeartbeat" as a JSON response
        return $threshold;
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
            'variation' => $request->input('variation'),
            'player_id' => $request->input('player_id'),
            'player_score' => $request->input('player_score', null),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        // Optionally, you can return a response to indicate success
        return response()->json(['message' => 'Heartbeat record created successfully'], 201);
    }

    public function update(Request $request)
    {

        DB::table('heartbeats')->where('player_id', $request->input('player_id'))
            ->update(['player_score' => $request->input('player_score', null)]);

        // Optionally, you can return a response to indicate success
        return response()->json(['message' => 'Heartbeat record updated successfully'], 201);
    }

}
