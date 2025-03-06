<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MachineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('fp_machine')->insert([
            [
                'ip_address' => '10.13.0.200',
                'port' => '80',
                'location' => 'Lantai 3',
            ],
        ]);
    }
}
