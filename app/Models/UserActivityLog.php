<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivityLog extends Model
{
    protected $table = 'user_activity_logs';

    public $timestamps = false;

    protected $fillable = ['user_id', 'email', 'action', 'route_name', 'label', 'ip', 'device', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];

    public const ACTIONS = [
        'login' => 'Login',
        'logout' => 'Logout',
        'failed' => 'Gagal masuk',
        'page' => 'Halaman',
        'print' => 'Cetak',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Kalimat aktivitas, mis. "membuka Keuangan personal". */
    public function getSentenceAttribute(): string
    {
        return match ($this->action) {
            'login' => 'masuk',
            'logout' => 'keluar',
            'failed' => 'gagal masuk (password salah)',
            'print' => 'mencetak ' . lcfirst($this->label ?? 'dokumen'),
            default => 'membuka ' . ($this->label ?? 'halaman'),
        };
    }
}
