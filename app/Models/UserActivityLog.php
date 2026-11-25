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
        'lock' => 'Tutup akses',
        'unlock' => 'Buka akses',
        'announce' => 'Pengumuman',
        'admin_user' => 'Admin wilayah',
    ];

    /** Tindakan admin yang selalu tampil di log, walaupun aktivitas admin lain disembunyikan. */
    public const ADMIN_ACTIONS = ['lock', 'unlock', 'announce', 'admin_user'];

    /**
     * Log yang boleh ditampilkan: aktivitas akun admin utama disembunyikan (admin wilayah tetap tampil).
     * Percobaan gagal masuk tetap tampil (termasuk ke akun admin) karena penting untuk keamanan.
     */
    public function scopeVisible($query)
    {
        return $query->where(fn ($q) => $q->whereNull('user_id')
            ->orWhere('action', 'failed')
            ->orWhereIn('action', self::ADMIN_ACTIONS)
            ->orWhereNotIn('user_id', User::superAdminIdsQuery()));
    }

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
            'lock', 'unlock', 'announce', 'admin_user' => $this->label ?? '',
            default => 'membuka ' . ($this->label ?? 'halaman'),
        };
    }
}
