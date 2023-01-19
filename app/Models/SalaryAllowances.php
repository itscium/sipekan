<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryAllowances extends Model
{
    use HasFactory;
    protected $table = 'gaji_tunjangan';
    protected $fillable = [
        'wilayah_id',
        'nama',
        'tipe',
        'jenis',
        'kategori',
        'keterangan',
    ];
}
