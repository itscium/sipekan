<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentExpense extends Model
{
    use HasFactory;
    protected $table = 'department_expense';
    protected $fillable = [
        'wilayah_id',
        'nama',
        'account_code',
    ];
}
