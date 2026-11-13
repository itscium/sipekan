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

    /**
     * Kode singkat wilayah, mis. CIUM, WIUM, NSM.
     * Urutan: config sipekan.wilayah_codes -> huruf depan nama Inggris -> kolom kode.
     */
    public function getShortCodeAttribute(): string
    {
        $map = config('sipekan.wilayah_codes', []);
        if (!empty($map[$this->id])) {
            return $map[$this->id];
        }
        $words = array_filter(preg_split('/[\s\-]+/', trim((string) $this->english_name)));
        if ($words) {
            return implode('', array_map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)), $words));
        }
        return (string) $this->kode;
    }
}
