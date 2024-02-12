<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use App\Models\Heartbeat;

class ValidateSingleGameplayTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'single_gameplay:validate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $playerData = DB::table('heartbeats_single_gameplay')->select('player_number', 'variation', 'player_id')
            ->groupBy('player_number', 'variation', 'player_id')
            ->get();

        $groupedByPlayerNumber = $playerData->groupBy('player_number');

        $validPlayerNumbers = collect();
        $invalidPlayerNumbers = collect(); // Collect for tracking invalid player numbers

        foreach ($groupedByPlayerNumber as $playerNumber => $data) {
            // Check if there are exactly 3 variations for this player_number
            $variationsCount = $data->groupBy('variation')->count();

            // Check if there is only one player_id for each variation
            $singlePlayerIdPerVariation = true;
            foreach ($data->groupBy('variation') as $variationGroup) {
                if ($variationGroup->groupBy('player_id')->count() > 1) {
                    $singlePlayerIdPerVariation = false;
                    break;
                }
            }

            if ($variationsCount === 3 && $singlePlayerIdPerVariation) {
                $validPlayerNumbers->push($playerNumber);
            } else {
                $invalidPlayerNumbers->push($playerNumber); // Add to invalid list if conditions not met
            }
        }

        // Print valid player numbers
        if ($validPlayerNumbers->isNotEmpty()) {
            $this->info('Valid player numbers found: ' . $validPlayerNumbers->count());
            foreach ($validPlayerNumbers as $playerNumber) {
                $this->line('Valid player number: ' . $playerNumber);
            }
        }

        // Print invalid player numbers
        if ($invalidPlayerNumbers->isNotEmpty()) {
            $this->error('Invalid player numbers found: ' . $invalidPlayerNumbers->count());
            foreach ($invalidPlayerNumbers as $playerNumber) {
                $this->line('Invalid player number: ' . $playerNumber);
            }
        } else {
            $this->info('No invalid player numbers found.');
        }
    }


}
