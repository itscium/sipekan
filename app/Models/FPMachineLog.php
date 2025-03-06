<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FPMachineLog extends Model
{
    use HasFactory;
    protected $table = 'fp_machine_log';
    protected $fillable = ['fp_machine_id', 'datetime', 'date', 'time', 'pin', 'verified', 'status', 'workcode', 'created_at', 'updated_at'];
    protected $timestamp = true;
}
