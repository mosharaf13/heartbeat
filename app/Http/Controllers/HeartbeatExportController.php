<?php

namespace App\Http\Controllers;

use App\Exports\AverageHeartbeatsByVariationExport;
use App\Exports\HeartbeatsByVariationExport;
use App\Exports\HeartbeatsByGenderExport;
use App\Exports\HeartbeatsChangeByVariationExport;
use App\Exports\PlayerThresholdExport;
use App\Exports\PlayerGameplayTimeExport;
use App\Http\HeartbeatsExport;
use Maatwebsite\Excel\Facades\Excel;

class HeartbeatExportController extends Controller
{
    public function export()
    {
        return Excel::download(new HeartbeatsExport, 'heartbeats.xlsx');
    }

    public function exportByVariation()
    {
        return Excel::download(new HeartbeatsByVariationExport, 'heartbeats_by_variation.xlsx');
    }

    public function exportAverageByVariation()
    {
        return Excel::download(new AverageHeartbeatsByVariationExport(), 'average_heartbeats_by_variation.xlsx');
    }

    public function exportHeartBeatChangeByVariation()
    {
        return Excel::download(new HeartbeatsChangeByVariationExport(), 'heartbeats_change_by_variation.xlsx');
    }

    public function exportBygender()
    {
        return Excel::download(new HeartbeatsByGenderExport, 'heartbeats_by_gender.xlsx');
    }

    public function exportPlayerThresholds()
    {
        return Excel::download(new PlayerThresholdExport, 'player_thresholds.xlsx');
    }

    public function exportPlayerGameplayTimes()
    {
        return Excel::download(new PlayerGameplayTimeExport, 'player_gameplay_times.xlsx');
    }
}

