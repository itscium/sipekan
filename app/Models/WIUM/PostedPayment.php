<?php

namespace App\Models\WIUM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostedPayment extends Model
{
    use HasFactory;
    protected $connection = 'aps_db';
    protected $table = 'v_posted_payment';

    public function payment() {
        return $this->hasMany(Payrol::class, 'id_payment', 'id_payment');
    }
}
