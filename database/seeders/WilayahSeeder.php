<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WilayahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @noinspection UnknownTableOrViewInspection
     */
    public function run()
    {
        DB::table('wilayah')->insert([
            [
                'nama' => 'Uni Indonesia Kawasan Barat',
                'kode' => 'UNI'
            ],
            [
                'nama' => 'Jakarta',
                'kode' => 'KDKI'
            ],
            [
                'nama' => 'Sumatera Utara',
                'kode' => 'DSKU'
            ],
            [
                'nama' => 'Sumatera Tengah',
                'kode' => 'DSKT'
            ],
            [
                'nama' => 'Sumatera Selatan',
                'kode' => 'DSKS'
            ],
            [
                'nama' => 'Jawa Barat',
                'kode' => 'KJKB'
            ],
            [
                'nama' => 'Jawa Tengah',
                'kode' => 'DJKT'
            ],
            [
                'nama' => 'Jawa Timur',
                'kode' => 'KJKT'
            ],
            [
                'nama' => 'Kalimantan Barat',
                'kode' => 'WKB'
            ],
            [
                'nama' => 'Kalimantan Timur',
                'kode' => 'DKKT'
            ],
            [
                'nama' => 'Kupang',
                'kode' => 'DNT'
            ]
        ]);
    }
}
