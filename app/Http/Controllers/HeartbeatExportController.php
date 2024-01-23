<?php

namespace App\Http\Controllers;

use App\Exports\HeartbeatsExport;
use Maatwebsite\Excel\Facades\Excel;

class HeartbeatExportController extends Controller
{
    public function export()
    {
        return Excel::download(new HeartbeatsExport, 'heartbeats.xlsx');
    }
}

