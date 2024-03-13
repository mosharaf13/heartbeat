<?php

namespace App\Exports;

use App\Models\Heartbeat;
use App\Models\HeartbeatsSingleGameplay;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;

class AverageHeartbeatsByVariationExport implements FromCollection, WithMapping
{
    public function collection()
    {
        $heartbeats = HeartbeatsSingleGameplay::get()
            ->sortBy(['player_number', 'variation']);

        $groupedHeartbeats = $heartbeats->groupBy('player_number');
        $rows = new Collection();

        foreach ($groupedHeartbeats as $playerNumber => $playerHeartbeats) {
            $playerRows = [];
            $maxHeartbeats = 0;

            // Preparing data for each variation
            for ($variation = 1; $variation <= 3; $variation++) {
                $variationData = $playerHeartbeats->where('variation', $variation)->pluck('heartbeat')->avg();
                $playerRows[$variation] = $variationData;
            }


            $row = [];
            for ($variation = 1; $variation <= 3; $variation++) {
                $row[] = $playerRows[$variation];
            }
            $row[] = $playerNumber;
            $row[] = $playerHeartbeats[0]->gender;
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
            $row[4]
        ];
    }
}
