<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;

class UserImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row): \Illuminate\Database\Eloquent\Model|User|null
    {
        return new User([
            'name'     => $row[0],
            'email'    => $row[1],
            'password'    => Hash::make('123456'),
            'ACCNT_CODE'    => $row[2],
            'travel_account'    => $row[3],
            'wilayah_id'    => $row[4],
            'type'    => 'user'
        ]);
    }
}
