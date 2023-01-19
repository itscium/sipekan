<?php

namespace App\Imports;

use App\Models\UserSalary;
use Maatwebsite\Excel\Concerns\ToModel;

class UserSalaryImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new UserSalary([
            'user_id'     => $row[0],
            'gaji_tunjangan_id'    => $row[1],
            'jumlah'    => $row[2],
            'satuan'    => $row[3],
            'keterangan'    => $row[4]
        ]);
    }
}
