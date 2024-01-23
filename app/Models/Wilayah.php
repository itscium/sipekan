<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    use HasFactory;

    protected $table = 'wilayah';
    protected $fillable = ['nama', 'kode', 'account_on_wium'];

    public function users (){
        return $this->hasMany(User::class, 'wilayah_id', 'id');
    }
}
