<?php

namespace App\Exports;

use App\Models\Heartbeat;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;

class HeartbeatsByGenderExport implements FromCollection, WithMapping
{
    public function collection()
    {
        // Adjusted to first sort by gender, then player_number, and variation
        $heartbeats = Heartbeat::get()->sortBy(['gender', 'player_number', 'variation']);

        // Group heartbeats by player_number
        $groupedHeartbeats = $heartbeats->groupBy('player_number');
        $rows = new Collection();

        foreach ($groupedHeartbeats as $playerNumber => $playerHeartbeats) {
            $playerRows = [];
            $maxHeartbeats = 0;
            // Assuming gender is consistent within the same player_number
            $gender = $playerHeartbeats->first()->gender;

            // Preparing data for each variation
            for ($variation = 1; $variation <= 3; $variation++) {
                $variationData = $playerHeartbeats->where('variation', $variation)->pluck('heartbeat')->all();
                $maxHeartbeats = max($maxHeartbeats, count($variationData));
                $playerRows[$variation] = $variationData;
            }

            // Padding data and assembling rows
            for ($i = 0; $i < $maxHeartbeats; $i++) {
                $row = [$gender]; // Start each row with the gender
                for ($variation = 1; $variation <= 3; $variation++) {
                    $row[] = $playerRows[$variation][$i] ?? null; // Add heartbeats for each variation
                }
                $row[] = $playerNumber; // Adding player number as the last column
                $rows->push($row);
            }
        }

        // Since the grouping was by player_number, to ensure sorting by gender,
        // we sort the final collection of rows by the gender column (index 0) before returning.
        return $rows->sortBy(function ($row) {
            return $row[0]; // Sort by gender (first element in each row)
        });
    }

    public function map($row): array
    {
        // Adjusted mapping to include the gender column
        return [
            $row[0], // Gender
            $row[1], // Variation 1 Heartbeat
            $row[2], // Variation 2 Heartbeat
            $row[3], // Variation 3 Heartbeat
            $row[4]  // Player Number
        ];
    }
}
