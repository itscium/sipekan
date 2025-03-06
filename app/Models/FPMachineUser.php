<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FPMachineUser extends Model
{
    use HasFactory;
    protected $table = 'fp_machine_user';
    protected $fillable = ['fp_machine_id', 'user_id', 'pin', 'name', 'created_at', 'updated_at'];
    protected $timestamp = true;
}
