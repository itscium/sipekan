<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departemen extends Model
{
    use HasFactory;
    protected $table = 'departemen';

    protected $fillable = [
        'nama_departemen',
        'kepala_departemen',
        'wilayah_id',
        'department_code',
'travel_expense_code',
'travel_special_code',
'strategic_plan_code',
'office_expense_code',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'kepala_departemen', 'id');
    }
}
