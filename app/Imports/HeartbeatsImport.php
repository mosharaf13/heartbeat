<?php

namespace App\Imports;

use App\Models\Heartbeat;
use Maatwebsite\Excel\Concerns\ToModel;

class HeartbeatsImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Heartbeat([
            'id' => $row[0],
            'heartbeat' => $row[1],
            'variation' => $row[2],
            'player_id' => $row[3],
            'created_at' => $row[4],
            'updated_at' => $row[5],
            'player_score' => $row[6],
            'player_number' => $row[7],
            'threshold' => 0,
            'threshold_breach_status' => 0
        ]);
    }
}
