<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/** Business Unit yang laporan keuangannya ditutup. Ada baris = ditutup. */
class FinanceLock extends Model
{
    protected $table = 'finance_locks';

    protected $fillable = ['wilayah_id', 'locked_by'];

    private const CACHE_KEY = 'finance_locks:ids';

    public function locker()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    /** Daftar ID wilayah yang ditutup (disimpan di cache 10 menit). */
    public static function lockedIds(): array
    {
        try {
            return Cache::remember(self::CACHE_KEY, 600, fn () => static::pluck('wilayah_id')->map(fn ($v) => (int) $v)->all());
        } catch (\Throwable $e) {
            // Tabel belum ada (migration belum dijalankan): anggap tidak ada yang ditutup
            Log::warning('Status akses keuangan tidak bisa dibaca: ' . $e->getMessage());
            return [];
        }
    }

    public static function isLocked($wilayahId): bool
    {
        return $wilayahId !== null && in_array((int) $wilayahId, static::lockedIds(), true);
    }

    /** Apakah laporan keuangan pengguna ini ditutup? Admin tidak pernah ditutup. */
    public static function lockedFor(?User $user): bool
    {
        return $user && $user->type !== 'admin' && static::isLocked($user->wilayah_id);
    }

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
