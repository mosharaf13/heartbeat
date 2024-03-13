<?php

namespace App\Exports;

use App\Models\HeartbeatsSingleGameplay;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;

class PlayerThresholdExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        $players = HeartbeatsSingleGameplay::select('player_number')
            ->groupBy('player_number')
            ->pluck('player_number');

        $data = collect();

        foreach ($players as $playerNumber) {
            $thresholds = HeartbeatsSingleGameplay::select('variation', 'threshold')
                ->where('player_number', $playerNumber)
                ->get()
                ->groupBy('variation')
                ->map(function ($item, $key) {
                    // Assuming you want the first (or a specific) threshold value for the variation
                    return $item->pluck('threshold')->first();
                });

            $data->push([
                'player_number' => $playerNumber,
                'variation_1_threshold' => $thresholds->get(1) ?? 'N/A', // Default to 'N/A' if not found
                'variation_2_threshold' => $thresholds->get(2) ?? 'N/A',
                'variation_3_threshold' => $thresholds->get(3) ?? 'N/A',
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Player Number',
            'Variation 1 Threshold',
            'Variation 2 Threshold',
            'Variation 3 Threshold',
        ];
    }
}
