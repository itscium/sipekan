<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSalary extends Model
{
    use HasFactory;
    protected $table = 'dbo.user_gaji';
    protected $fillable = [
        'user_id',
        'gaji_tunjangan_id',
        'jumlah',
        'satuan',
        'keterangan',
        'year',
    ];

    public function allowances(){
        return $this->belongsTo(SalaryAllowances::class, 'gaji_tunjangan_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
