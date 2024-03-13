<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AdjustRowsSingleGameplay extends Command
{
    protected $signature = 'single_gameplay:adjust';
    protected $description = 'Adjust rows in single_gameplay to ensure uniformity across variations, preserving first and last rows.';

    public function handle()
    {
        $playerNumbers = DB::table('heartbeats_single_gameplays')
            ->select('player_number')
            ->distinct()
            ->pluck('player_number');

        foreach ($playerNumbers as $playerNumber) {
            $minRowCount = $this->getMinimumRowCount($playerNumber);

            $variations = DB::table('heartbeats_single_gameplays')
                ->where('player_number', $playerNumber)
                ->select('variation')
                ->distinct()
                ->pluck('variation');

            foreach ($variations as $variation) {
                $this->adjustRowsForVariation($playerNumber, $variation, $minRowCount);
            }
        }

        $this->printSummaryTable();
    }

    protected function getMinimumRowCount($playerNumber)
    {
        // Determine the minimum row count across all variations for this player number
        return DB::table('heartbeats_single_gameplays')
            ->where('player_number', $playerNumber)
            ->select(DB::raw('variation, COUNT(*) as cnt'))
            ->groupBy('variation')
            ->orderByRaw('COUNT(*)')
            ->first()
            ->cnt ?? 0;
    }

    protected function adjustRowsForVariation($playerNumber, $variation, $targetRowCount)
    {
        $rows = DB::table('heartbeats_single_gameplays')
            ->where('player_number', $playerNumber)
            ->where('variation', $variation)
            ->orderBy('created_at')
            ->get(['id']);

        if ($rows->count() <= $targetRowCount) return; // No adjustment needed

        // Calculate the number of rows to remove, excluding the first and last rows
        $rowsToRemove = $rows->count() - $targetRowCount;
        $idsToDelete = $rows->slice(1, $rowsToRemove)->pluck('id');

        DB::table('heartbeats_single_gameplays')->whereIn('id', $idsToDelete)->delete();
    }

    protected function printSummaryTable()
    {
        $summaryData = DB::table('heartbeats_single_gameplays')
            ->select('player_number', 'variation', DB::raw('COUNT(*) as row_count'))
            ->groupBy('player_number', 'variation')
            ->get();

        $tableData = $summaryData->map(function ($item) {
            return ['Player Number' => $item->player_number, 'Variation' => $item->variation, 'Row Count' => $item->row_count];
        });

        $this->table(['Player Number', 'Variation', 'Row Count'], $tableData->toArray());
    }
}
