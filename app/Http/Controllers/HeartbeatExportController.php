<?php

namespace App\Http\Controllers;

use App\Exports\HeartbeatsByVariationExport;
use App\Exports\HeartbeatsByGenderExport;
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

    public function exportBygender()
    {
        return Excel::download(new HeartbeatsByGenderExport, 'heartbeats_by_gender.xlsx');
    }
}

