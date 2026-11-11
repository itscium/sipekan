<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FPMachine extends Model
{
    use HasFactory;
    protected $table = 'fp_machine';

    protected $fillable = ['ip_address', 'port', 'location'];

    public function users()
    {
        return $this->hasMany(FPMachineUser::class, 'fp_machine_id');
    }

    public function logs()
    {
        return $this->hasMany(FPMachineLog::class, 'fp_machine_id');
    }
}
