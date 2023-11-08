<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HeartBeatController extends Controller
{

    public function index()
    {
        // Use the DB facade to select all "heartbeat" records
        $heartbeats = DB::table('heartbeats')->get();
        // Return the "heartbeats" as a JSON response
        return response()->json($heartbeats);
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
        ]);

        // Optionally, you can return a response to indicate success
        return response()->json(['message' => 'Heartbeat record created successfully'], 201);
    }

}
