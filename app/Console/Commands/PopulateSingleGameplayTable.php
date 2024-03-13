<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use App\Models\Heartbeat;

class PopulateSingleGameplayTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'single_gameplay:populate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populates single gameplay table with last valid player_id entries';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $playerNumbers = Heartbeat::select('player_number')->distinct()->pluck('player_number');

        foreach ($playerNumbers as $playerNumber) {
            $variations = Heartbeat::where('player_number', $playerNumber)
                ->select('variation')
                ->distinct()
                ->pluck('variation');

            foreach ($variations as $variation) {
                // Use a subquery to order the results before applying DISTINCT on player_id
                $playerIds = Heartbeat::where('player_number', $playerNumber)
                    ->where('variation', $variation)
                    ->orderBy('created_at', 'desc')
                    ->get(['player_id', 'created_at']) // Include 'created_at' to satisfy SQL mode restrictions
                    ->unique('player_id') // Apply unique filter on collection instead of SQL query
                    ->take(2)
                    ->pluck('player_id');

                foreach ($playerIds as $playerId) {
                    $rowCount = Heartbeat::where('player_number', $playerNumber)
                        ->where('variation', $variation)
                        ->where('player_id', $playerId)
                        ->count();

                    if ($rowCount > 2) {
                        $rowsToInsert = Heartbeat::where('player_number', $playerNumber)
                            ->where('variation', $variation)
                            ->where('player_id', $playerId)
                            ->get();

                        foreach ($rowsToInsert as $row) {
                            DB::table('heartbeats_single_gameplays')->insert([
                                'heartbeat' => $row->heartbeat,
                                'variation' => $row->variation,
                                'player_id' => $row->player_id,
                                'created_at' => $row->created_at,
                                'updated_at' => $row->updated_at,
                                'player_score' => $row->player_score,
                                'player_number' => $row->player_number,
                                'threshold' => $row->threshold,
                                'threshold_breach_status' => $row->threshold_breach_status,
                                'gender' => $row->gender,
                            ]);
                        }
                        break;
                    }
                }
            }
        }

        $this->info('Single gameplay table populated successfully.');
    }

}
