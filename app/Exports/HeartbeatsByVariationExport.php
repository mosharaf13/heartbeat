<?php

namespace App\Exports;

use App\Models\Heartbeat;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;

class HeartbeatsByVariationExport implements FromCollection, WithMapping
{
    public function collection()
    {
        $heartbeats = Heartbeat::get()
            ->sortBy(['player_number', 'variation']);

        $groupedHeartbeats = $heartbeats->groupBy('player_number');
        $rows = new Collection();

        foreach ($groupedHeartbeats as $playerNumber => $playerHeartbeats) {
            $playerRows = [];
            $maxHeartbeats = 0;

            // Preparing data for each variation
            for ($variation = 1; $variation <= 3; $variation++) {
                $variationData = $playerHeartbeats->where('variation', $variation)->pluck('heartbeat')->all();
                $maxHeartbeats = max($maxHeartbeats, count($variationData));
                $playerRows[$variation] = $variationData;
            }

            // Padding data and assembling rows
            for ($i = 0; $i < $maxHeartbeats; $i++) {
                $row = [];
                for ($variation = 1; $variation <= 3; $variation++) {
                    $row[] = $playerRows[$variation][$i] ?? null;
                }
                $row[] = $playerNumber; // Adding player number as the 4th column
                $rows->push($row);
            }
        }

        return $rows;
    }

    public function map($row): array
    {
        return [
            $row[0], // Variation 1 Heartbeat
            $row[1], // Variation 2 Heartbeat
            $row[2], // Variation 3 Heartbeat
            $row[3]  // Player Number
        ];
    }
}
