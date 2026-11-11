<?php

namespace App\Console\Commands;

use App\Services\FingerprintSyncService;
use Illuminate\Console\Command;

class SyncFingerprintLogs extends Command
{
    protected $signature = 'fingerprint:sync-logs {--trigger=otomatis : Sumber penarikan (otomatis/manual)}';

    protected $description = 'Tarik log absensi dari semua mesin fingerprint ke database SIPekan';

    public function handle(FingerprintSyncService $sync): int
    {
        $this->info('Menarik log absensi dari mesin fingerprint...');
        $result = $sync->syncAll($this->option('trigger'));

        foreach ($result['machines'] as $m) {
            if ($m['ok']) {
                $this->line(sprintf('  %s: %d log dibaca, %d log baru disimpan (mulai %s)',
                    $m['machine'], $m['read'], $m['inserted'], $m['from']));
            } else {
                $this->error(sprintf('  %s: gagal - %s', $m['machine'], $m['error']));
            }
        }

        if (empty($result['machines'])) {
            $this->warn('Belum ada mesin fingerprint yang terdaftar.');
        }

        $this->info("Selesai. Total log baru: {$result['inserted']}.");
        return $result['ok'] ? self::SUCCESS : self::FAILURE;
    }
}
