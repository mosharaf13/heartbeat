<?php

namespace App\Exports;

use App\Models\HeartbeatsSingleGameplay;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;

class PlayerGameplayTimeExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        $playerNumbers = HeartbeatsSingleGameplay::select('player_number')
            ->groupBy('player_number')
            ->pluck('player_number');

        $data = collect();

        foreach ($playerNumbers as $playerNumber) {
            $averageTimes = [];
            for ($variation = 1; $variation <= 3; $variation++) {
                // Get the earliest 'created_at' and the latest 'updated_at' for this player_number and variation
                $firstEntry = HeartbeatsSingleGameplay::where('player_number', $playerNumber)
                    ->where('variation', $variation)
                    ->orderBy('created_at', 'asc')
                    ->first();

                $lastEntry = HeartbeatsSingleGameplay::where('player_number', $playerNumber)
                    ->where('variation', $variation)
                    ->orderBy('updated_at', 'desc')
                    ->first();

                if ($firstEntry && $lastEntry) {
                    $startTime = $firstEntry->created_at; // Use 'created_at' of the first entry
                    $endTime = $lastEntry->updated_at; // Use 'updated_at' of the last entry
                    // Calculate the difference in seconds
                    $gameplayTime = $endTime->diffInSeconds($startTime);
                } else {
                    $gameplayTime = 'N/A'; // No data for this variation
                }

                $averageTimes["variation_{$variation}_time"] = $gameplayTime;
            }

            $data->push(array_merge(['player_number' => $playerNumber], $averageTimes));
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Player Number',
            'Variation 1 Gameplay Time (sec)',
            'Variation 2 Gameplay Time (sec)',
            'Variation 3 Gameplay Time (sec)',
        ];
    }
}
