<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PopulateThresholdBreachStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:populate-threshold-breach-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $playerData = DB::table('heartbeats_single_gameplays')
            ->get();


    }
}
