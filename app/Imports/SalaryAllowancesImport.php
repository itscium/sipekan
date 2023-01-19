<?php

namespace App\Imports;

use App\Models\SalarayAllowances;
use App\Models\SalaryAllowances;
use Maatwebsite\Excel\Concerns\ToModel;

class SalaryAllowancesImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new SalaryAllowances([
            'wilayah_id'     => $row[0],
            'nama'    => $row[1],
            'tipe'    => $row[2],
            'jenis'    => $row[3],
            'kategori'    => $row[4],
            'keterangan'    => $row[5]
        ]);
    }
}
