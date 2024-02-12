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
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $subQuery = Heartbeat::select(DB::raw('MAX(created_at) as max_created_at'), 'variation', 'player_number')
            ->groupBy('variation', 'player_number');

        $lastPlayerIds = DB::table(DB::raw("({$subQuery->toSql()}) as sub"))
            ->join('heartbeats', function ($join) {
                $join->on('heartbeats.variation', '=', 'sub.variation')
                    ->on('heartbeats.player_number', '=', 'sub.player_number')
                    ->on('heartbeats.created_at', '=', 'sub.max_created_at');
            })
            ->mergeBindings($subQuery->getQuery()) // This is important to merge SQL bindings
            ->select('heartbeats.player_id')
            ->get()
            ->pluck('player_id');

        $rowsToInsert = Heartbeat::whereIn('player_id', $lastPlayerIds)->get();

        foreach ($rowsToInsert as $row) {
//            dd($row);
            DB::table('heartbeats_single_gameplay')->insert([
                // Assuming you have similar column names in the target table
                // You need to replace these with actual column names of your target table
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
    }
}
