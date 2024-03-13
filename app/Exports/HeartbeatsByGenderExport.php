<?php

namespace App\Exports;

use App\Models\Heartbeat;
use App\Models\HeartbeatsSingleGameplay;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;

class HeartbeatsByGenderExport implements FromCollection, WithMapping
{
    public function collection()
    {
        $sessions = HeartbeatsSingleGameplay::get()->sortBy(['player_id', 'updated_at']);

        // Group by player_id for sessions
        $groupedSessions = $sessions->groupBy('player_id');
        $exportData = collect(); // Use collect() for consistency

        foreach ($groupedSessions as $playerId => $session) {
            $gender = $session->first()->gender; // Assuming consistent gender within a session
            $playerNumber = $session->first()->player_number;
            $threshold = $session->first()->threshold;
            $variation = $session->first()->variation;
            $thresholdBreachStatus = $session->first()->threshold_breach_status;

            // Find the records with max and min heart rates
            $maxHeartRateRecord = $session->max('heartbeat');
            $minHeartRateRecord = $session->min('heartbeat');

            // Retrieve the corresponding timestamps
            $maxHeartRateTimestamp = $session->where('heartbeat', $maxHeartRateRecord)->first()->updated_at;
            $minHeartRateTimestamp = $session->where('heartbeat', $minHeartRateRecord)->first()->updated_at;

            // Determine the direction of change
            $heartRateChange = $maxHeartRateRecord - $minHeartRateRecord;
            if ($minHeartRateTimestamp > $maxHeartRateTimestamp) {
                $heartRateChange *= -1; // Make the change negative if the max came before the min
            }

            // Prepare row for export
            $exportData->push([
                'Gender' => $gender,
                'Max Heart Rate Change' => $heartRateChange,
                'Player Number' => $playerNumber,
                'Threshold' => $threshold,
                'Variation' => $variation,
                'Threshold Breach Status' => $thresholdBreachStatus
            ]);
        }

        // Explicitly sort the collection by 'Gender', assuming gender values are simple strings
        $sortedExportData = $exportData->sort(function ($a, $b) {
            // First compare by Player Number
            if ($a['Player Number'] == $b['Player Number']) {
                // If Player Numbers are equal, then compare by Gender
                return strcmp($a['Gender'], $b['Gender']);
            }
            return ($a['Player Number'] < $b['Player Number']) ? -1 : 1;
        });

        return $sortedExportData->values(); // Re-index the collection
    }

    public function map($row): array
    {
        // Adjusted mapping to properly reference associative keys
        return [
            $row['Gender'],             // Gender
            $row['Max Heart Rate Change'], // Max Heart Rate Change
            $row['Player Number'],
            $row['Threshold'],
            $row['Variation'],
            $row['Threshold Breach Status']
        ];
    }

}
