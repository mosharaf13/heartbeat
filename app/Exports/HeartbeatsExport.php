<?php

namespace App\Exports;

use App\Models\Heartbeat;
use Maatwebsite\Excel\Concerns\FromCollection;

class HeartbeatsExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Heartbeat::all();
    }
}
