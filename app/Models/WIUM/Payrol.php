<?php

namespace App\Models\WIUM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payrol extends Model
{
    use HasFactory;
    protected $connection = 'aps_db';
    protected $table = 'v_payment_all';
}
