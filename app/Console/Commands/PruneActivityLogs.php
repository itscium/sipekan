<?php

namespace App\Console\Commands;

use App\Models\UserActivityLog;
use Illuminate\Console\Command;

class PruneActivityLogs extends Command
{
    protected $signature = 'activity:prune {--days=90 : Hapus log yang lebih lama dari jumlah hari ini}';

    protected $description = 'Hapus log aktivitas pengguna yang sudah lama';

    public function handle(): int
    {
        $days = max(7, (int) $this->option('days'));
        $total = 0;
        // Hapus bertahap agar tabel tidak terkunci lama
        do {
            $deleted = UserActivityLog::where('created_at', '<', now()->subDays($days))->limit(5000)->delete();
            $total += $deleted;
        } while ($deleted > 0);

        $this->info("{$total} log aktivitas lebih dari {$days} hari dihapus.");
        return self::SUCCESS;
    }
}
