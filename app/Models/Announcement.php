<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Announcement extends Model
{
    protected $table = 'announcements';

    protected $fillable = [
        'title', 'body', 'button_text', 'button_url', 'level', 'display',
        'target_wilayah', 'starts_at', 'ends_at', 'is_published', 'created_by',
    ];

    protected $casts = [
        'target_wilayah' => 'array',
        'starts_at' => 'date',
        'ends_at' => 'date',
        'is_published' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Status untuk ditampilkan admin: draf, terjadwal, tayang, selesai. */
    public function getStatusAttribute(): string
    {
        if (!$this->is_published) {
            return 'draf';
        }
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'terjadwal';
        }
        if ($this->ends_at && $this->ends_at->endOfDay()->isPast()) {
            return 'selesai';
        }
        return 'tayang';
    }

    public function targets(int $wilayahId): bool
    {
        return in_array($wilayahId, array_map('intval', $this->target_wilayah ?? []), true);
    }

    /**
     * Pengumuman yang sedang tayang untuk pengguna ini (berdasarkan wilayahnya), terbaru dulu.
     * Jika tabel belum ada (migration belum dijalankan), kembalikan koleksi kosong.
     */
    public static function activeFor(?User $user)
    {
        if (!$user || $user->type === 'admin' || !$user->wilayah_id) {
            return collect();
        }
        try {
            $today = now()->toDateString();
            return static::where('is_published', true)
                ->whereDate('starts_at', '<=', $today)
                ->where(fn ($q) => $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', $today))
                ->orderByDesc('starts_at')->orderByDesc('id')
                ->get()
                ->filter(fn ($a) => $a->targets((int) $user->wilayah_id))
                ->values();
        } catch (\Throwable $e) {
            Log::warning('Pengumuman tidak bisa dimuat: ' . $e->getMessage());
            return collect();
        }
    }
}
