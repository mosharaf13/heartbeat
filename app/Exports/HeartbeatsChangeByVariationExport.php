<?php

namespace App\Exports;

use App\Models\Heartbeat;
use App\Models\HeartbeatsSingleGameplay;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;

class HeartbeatsChangeByVariationExport implements FromCollection, WithMapping
{
    public function collection()
    {
        $heartbeats = HeartbeatsSingleGameplay::get()
            ->sortBy(['player_number', 'variation']);

        $groupedHeartbeats = $heartbeats->groupBy('player_number');
        $rows = new Collection();

        foreach ($groupedHeartbeats as $playerNumber => $playerHeartbeats) {
            $exportData = collect();
            $playerRows = collect();

            // Preparing data for each variation
            for ($variation = 1; $variation <= 3; $variation++) {
                $gender = $playerHeartbeats->where('variation', $variation)->first()->gender;
                $playerNumber = $playerHeartbeats->where('variation', $variation)->first()->player_number;
                $avgHeartbeat = $playerHeartbeats->where('variation', $variation)->pluck('heartbeat')->avg();


                // Find the records with max and min heart rates
                $maxHeartRateRecord = $playerHeartbeats->where('variation', $variation)->max('heartbeat');
                $minHeartRateRecord = $playerHeartbeats->where('variation', $variation)->min('heartbeat');

                // Retrieve the corresponding timestamps
                $maxHeartRateTimestamp = $playerHeartbeats->where('heartbeat', $maxHeartRateRecord)->first()->updated_at;
                $minHeartRateTimestamp = $playerHeartbeats->where('heartbeat', $minHeartRateRecord)->first()->updated_at;

                // Determine the direction of change
                $heartRateChange = $maxHeartRateRecord - $minHeartRateRecord;
                if ($minHeartRateTimestamp > $maxHeartRateTimestamp) {
                    $heartRateChange *= -1; // Make the change negative if the max came before the min
                }

                $playerRows[$variation] = $heartRateChange;
            }


            $row = [];
            for ($variation = 1; $variation <= 3; $variation++) {
                $row[] = $playerRows[$variation];
            }
            $row[] = $playerNumber;
            $row[] = $gender;
            $row[] = $avgHeartbeat;
            $rows->push($row);

        }

        return $rows;
    }

    public function map($row): array
    {
        return [
            $row[0], // Variation 1 Heartbeat
            $row[1], // Variation 2 Heartbeat
            $row[2], // Variation 3 Heartbeat
            $row[3],  // Player Number
            $row[4], // gender
            $row[5] // Average heart beat
        ];
    }

}
