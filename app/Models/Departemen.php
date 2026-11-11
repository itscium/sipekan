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

    /** Hanya departemen yang anggarannya boleh ditampilkan (lihat config/sipekan.php). */
    public function scopeBudgetVisible($query)
    {
        return $query->whereNotIn('wilayah_id', array_map('strval', config('sipekan.hidden_budget_wilayah_ids', [])));
    }

    public function isBudgetVisible(): bool
    {
        return !in_array((string) $this->wilayah_id, array_map('strval', config('sipekan.hidden_budget_wilayah_ids', [])), true);
    }

    public function wilayah(){
        return $this->belongsTo(Wilayah::class, 'wilayah_id', 'id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'kepala_departemen', 'id');
    }
}
