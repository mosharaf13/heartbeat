<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\HeartbeatsImport;

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

    public function importExcel(Request $request)
    {
        $file = $request->file('excel');
        Excel::import(new HeartbeatsImport, $file);

        return back()->with('success', 'Excel data imported successfully.');
    }

    public function populateThreshold()
    {
        $playerIds = DB::table('heartbeats')->select('player_id')->distinct()->pluck('player_id');

        foreach ($playerIds as $playerId) {
            // Select the first 5 heartbeats for each player_id
            $firstFiveHeartbeats = DB::table('heartbeats')
                ->where('player_id', $playerId)
                ->orderBy('created_at')
                ->take(5)
                ->pluck('heartbeat'); // Replace 'heartbeat_value' with the actual column name

            if ($firstFiveHeartbeats->isNotEmpty()) {
                // Calculate the average
                $average = $firstFiveHeartbeats->average();

                // Update the threshold for all records of this player
                DB::table('heartbeats')
                    ->where('player_id', $playerId)
                    ->update(['threshold' => $average+5]);
            }
        }

        return response()->json('successful');
    }

    public function all()
    {
        // Get all variations
        $data = DB::table('heartbeats')->get();

        // Return the response
        return response()->json($data);
    }

    public function adjustRows()
    {
        $playerNumbers = DB::table('heartbeats')->select('player_number')->distinct()->pluck('player_number');

        foreach ($playerNumbers as $playerNumber) {
            $variations = DB::table('heartbeats')->where('player_number', $playerNumber)
                ->select('variation')
                ->distinct()
                ->pluck('variation');

            // Check if the player number has all required variations
            if ($variations->count() < 3) {
                // If not all variations are present, delete all records for this player number
                DB::table('heartbeats')->where('player_number', $playerNumber)->delete();
            } else {
                // Find the minimum number of rows among the variations for this player number
                $minRows = null;
                foreach ($variations as $variation) {
                    $count = DB::table('heartbeats')->where('player_number', $playerNumber)
                        ->where('variation', $variation)
                        ->count();

                    if (is_null($minRows) || $count < $minRows) {
                        $minRows = $count;
                    }
                }

                // Adjust rows for each variation
                foreach ($variations as $variation) {
                    $heartbeatIds = DB::table('heartbeats')
                        ->where('player_number', $playerNumber)
                        ->where('variation', $variation)
                        ->orderBy('created_at', 'asc')
                        ->pluck('id');

                    if ($heartbeatIds->count() > $minRows) {
                        // Calculate indices to keep
                        $indicesToKeep = range(0, $minRows - 1);
                        $idsToKeep = $heartbeatIds->only($indicesToKeep)->all();

                        // Delete records not in the idsToKeep array
                        DB::table('heartbeats')
                            ->where('player_number', $playerNumber)
                            ->where('variation', $variation)
                            ->whereNotIn('id', $idsToKeep)
                            ->delete();
                    }
                }
            }
        }

        return response()->json(['message' => 'Rows adjusted successfully.']);
    }

    public function adjustRowsSingleGameplay()
    {
        $playerNumbers = DB::table('heartbeats_single_gameplay')->select('player_number')->distinct()->pluck('player_number');

        foreach ($playerNumbers as $playerNumber) {
            $variations = DB::table('heartbeats_single_gameplay')->where('player_number', $playerNumber)
                ->select('variation')
                ->distinct()
                ->pluck('variation');

            // Check if the player number has all required variations
            if ($variations->count() < 3) {
                // If not all variations are present, delete all records for this player number
                DB::table('heartbeats_single_gameplay')->where('player_number', $playerNumber)->delete();
            } else {
                // Find the minimum number of rows among the variations for this player number
                $minRows = null;
                foreach ($variations as $variation) {
                    $count = DB::table('heartbeats_single_gameplay')->where('player_number', $playerNumber)
                        ->where('variation', $variation)
                        ->count();

                    if (is_null($minRows) || $count < $minRows) {
                        $minRows = $count;
                    }
                }

                // Adjust rows for each variation
                foreach ($variations as $variation) {
                    $heartbeatIds = DB::table('heartbeats_single_gameplay')
                        ->where('player_number', $playerNumber)
                        ->where('variation', $variation)
                        ->orderBy('created_at', 'asc')
                        ->pluck('id');

                    if ($heartbeatIds->count() > $minRows) {
                        // Calculate indices to keep
                        $indicesToKeep = range(0, $minRows - 1);
                        $idsToKeep = $heartbeatIds->only($indicesToKeep)->all();

                        // Delete records not in the idsToKeep array
                        DB::table('heartbeats_single_gameplay')
                            ->where('player_number', $playerNumber)
                            ->where('variation', $variation)
                            ->whereNotIn('id', $idsToKeep)
                            ->delete();
                    }
                }
            }
        }

        return response()->json(['message' => 'Rows adjusted successfully.']);
    }

    public function checkVariationRowCounts()
    {
        $playerNumbers = DB::table('heartbeats')->select('player_number')->distinct()->pluck('player_number');
        $variationCounts = []; // Store counts for each player number and variation

        foreach ($playerNumbers as $playerNumber) {
            $variations = DB::table('heartbeats')->where('player_number', $playerNumber)
                ->select('variation')
                ->distinct()
                ->pluck('variation');

            foreach ($variations as $variation) {
                $count = DB::table('heartbeats')->where('player_number', $playerNumber)
                    ->where('variation', $variation)
                    ->count();

                // Initialize player number in array if not already present
                if (!isset($variationCounts[$playerNumber])) {
                    $variationCounts[$playerNumber] = [];
                }

                $variationCounts[$playerNumber][$variation] = $count;
            }
        }

        // Analyze the counts to check consistency
        $inconsistencies = [];
        foreach ($variationCounts as $playerNumber => $counts) {
            if (count(array_unique($counts)) > 1) {
                // If counts are not the same for all variations, log an inconsistency
                $inconsistencies[] = "Player Number {$playerNumber} has inconsistent row counts across variations.";
            }
        }

        if (empty($inconsistencies)) {
            return response()->json(['message' => 'All player numbers have the same number of rows for each variation.']);
        } else {
            return response()->json(['message' => 'Inconsistencies found:', 'details' => $inconsistencies]);
        }
    }



    public function calculateThresholdBreach()
    {
        // Get all variations
        $variations = DB::table('heartbeats_single_gameplays')->distinct()->pluck('variation');

        foreach ($variations as $variation) {
            // Counter for each variation
            $breachCounter = 0;

            // Get distinct player_ids for this variation
            $playerIds = DB::table('heartbeats_single_gameplays')
                ->where('variation', $variation)
                ->distinct()
                ->pluck('player_id');

            foreach ($playerIds as $playerId) {
                // Check if there's a breach in this set
                $breachExists = DB::table('heartbeats_single_gameplays')
                    ->where('variation', $variation)
                    ->where('player_id', $playerId)
                    ->where('heartbeat', '>=', DB::raw('threshold'))
                    ->exists();

                if ($breachExists) {
                    // Increment breach counter for this variation
                    $breachCounter++;

                    // Update all rows in this set
                    DB::table('heartbeats_single_gameplays')
                        ->where('variation', $variation)
                        ->where('player_id', $playerId)
                        ->update(['threshold_breach_status' => $breachCounter]);
                } else {
                    // Update all rows in this set with the current counter value
                    DB::table('heartbeats_single_gameplays')
                        ->where('variation', $variation)
                        ->where('player_id', $playerId)
                        ->update(['threshold_breach_status' => $breachCounter]);
                }
            }
        }

        // Return the response
        return response()->json('successful');
    }

    public function sendChartData()
    {
        $players = DB::table('heartbeats')->distinct()->pluck('player_number');
        $variations = DB::table('heartbeats')->distinct()->pluck('variation');

        $chartData = [
            'labels' => $players->all(), // Player numbers as labels
            'datasets' => [],
        ];

        foreach ($variations as $variation) {
            $dataset = [
                'label' => "Variation $variation",
                'data' => [],
                'backgroundColor' => $this->getRandomColor(), // Assign different color for each variation
                // Add other properties as needed
            ];

            foreach ($players as $player) {
                $playingTimes = DB::table('heartbeats')
                    ->where('player_number', $player)
                    ->where('variation', $variation)
                    ->pluck('updated_at');
                if ($playingTimes->count() >= 2) {
                    $startTime = new \DateTime($playingTimes->first());
                    $endTime = new \DateTime($playingTimes->last());
//                    dd($startTime, $endTime);
                    $interval = $endTime->diff($startTime);

                    $seconds = ($interval->days * 24 * 60 * 60) + ($interval->h * 60 * 60) + ($interval->i * 60) + $interval->s;

                    $averageTime = $seconds; // If you need to do more complex average calculations, adjust here
                } else {
                    $averageTime = 0;
                }

                array_push($dataset['data'], $averageTime);
            }

            array_push($chartData['datasets'], $dataset);
        }

        dd($chartData);
        return response()->json($chartData);
    }

    public function sendHeartbeatChartData()
    {
        $players = DB::table('heartbeats')->distinct()->pluck('player_number');
        $variations = DB::table('heartbeats')->distinct()->pluck('variation');

        $chartData = [
            'labels' => $players->all(),
            'datasets' => [],
        ];

        foreach ($variations as $variation) {
            $dataset = [
                'label' => "Variation $variation",
                'data' => [],
                'backgroundColor' => $this->getRandomColor(),
            ];

            foreach ($players as $player) {
                $heartbeats = DB::table('heartbeats')
                    ->where('player_number', $player)
                    ->where('variation', $variation)
                    ->orderBy('updated_at')
                    ->get(['heartbeat']);

                if ($heartbeats->count() > 1) {
                    $changes = collect();

                    for ($i = 0; $i < $heartbeats->count() - 1; $i++) {
                        // Calculate the difference between consecutive heartbeats
                        $change = $heartbeats[$i + 1]->heartbeat - $heartbeats[$i]->heartbeat;
                        $changes->push($change);
                    }

                    $averageChange = $changes->average();
                } else {
                    $averageChange = 0;
                }


                array_push($dataset['data'], $averageChange);
            }

            array_push($chartData['datasets'], $dataset);
        }

        return response()->json($chartData);
    }

    protected function getRandomColor() {
        return '#' . substr(md5(rand()), 0, 6);
    }

    public function calculateGameplayTime()
    {
        // Get distinct player IDs
        $playerIds = DB::table('heartbeats')->distinct()->pluck('player_id');

        $timeSumsByVariation = [];
        $countByVariation = [];

        foreach ($playerIds as $playerId) {
            // Find the first and last record for this player_id
            $firstRecord = DB::table('heartbeats')
                ->where('player_id', $playerId)
                ->orderBy('updated_at', 'asc')
                ->first();

            $lastRecord = DB::table('heartbeats')
                ->where('player_id', $playerId)
                ->orderBy('updated_at', 'desc')
                ->first();

            if ($firstRecord && $lastRecord) {
                // Calculate time difference in seconds
                $startTime = new \DateTime($firstRecord->updated_at);
                $endTime = new \DateTime($lastRecord->updated_at);
                $timeDifference = $startTime->diff($endTime)->s; // Time difference in seconds

                // Collect and sum the time differences for each variation
                $variation = $firstRecord->variation;
                if (!isset($timeSumsByVariation[$variation])) {
                    $timeSumsByVariation[$variation] = 0;
                    $countByVariation[$variation] = 0;
                }
                $timeSumsByVariation[$variation] += $timeDifference;
                $countByVariation[$variation]++;
            }
        }

        // Calculate the average time difference for each variation
        $averageTimeByVariation = [];
        foreach ($timeSumsByVariation as $variation => $timeSum) {
            $averageTime = $timeSum / $countByVariation[$variation];
            $averageTimeByVariation[$variation] = $averageTime;
        }

        // Return the average time differences as JSON
        return response()->json($averageTimeByVariation);
    }

    public function sendThresholdBreachData()
    {
        $players = DB::table('heartbeats')->distinct()->pluck('player_number');
        $variations = DB::table('heartbeats')->distinct()->pluck('variation');

        $chartData = [
            'labels' => $players->all(),
            'datasets' => [],
        ];

        foreach ($variations as $variation) {
            $dataset = [
                'label' => "Variation $variation",
                'data' => [],
                'backgroundColor' => $this->getRandomColor(),
            ];

            foreach ($players as $player) {
                $breaches = DB::table('heartbeats')
                    ->where('player_number', $player)
                    ->where('variation', $variation)
                    ->whereColumn('heartbeat', '>=', 'threshold')
                    ->count();

                array_push($dataset['data'], $breaches);
            }

            array_push($chartData['datasets'], $dataset);
        }

        return response()->json($chartData);
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

        DB::table('heartbeats')
            ->where('player_id', $request->input('player_id'))
            ->latest('created_at')
            ->limit(1)
            ->update([
                'player_score' => $request->input('player_score', null),
                'variation' => $request->input('variation', null),
            ]);


        // Optionally, you can return a response to indicate success
        return response()->json(['message' => 'Heartbeat record updated successfully'], 201);
    }

}
