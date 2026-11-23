<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Wilayah;
use App\Services\DashboardService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Cek apakah SIPekan tersambung ke database dan ledger SunSystems.
 *
 *   php artisan ledger:check                   semua koneksi + ledger tiap wilayah
 *   php artisan ledger:check --wilayah=20003   satu wilayah saja
 *   php artisan ledger:check --akun=ERMANKE01  plus transaksi terakhir satu akun
 *   php artisan ledger:check --cepat           hanya koneksi, tanpa menghitung ledger
 *
 * Semua kueri hanya membaca data. Kode keluar 0 = semua aman, 1 = ada yang gagal.
 */
class CheckLedger extends Command
{
    protected $signature = 'ledger:check
        {--wilayah= : Hanya cek ledger wilayah ini (ID wilayah)}
        {--akun= : Tampilkan saldo dan transaksi terakhir kode akun ini}
        {--cepat : Hanya cek koneksi database, tanpa memeriksa tabel ledger}';

    protected $description = 'Cek koneksi SIPekan ke database dan ledger SunSystems (A = aktual, B = anggaran)';

    private bool $ok = true;

    public function handle(): int
    {
        $this->line('');
        $this->info('SIPekan · cek ledger · ' . now()->format('d/m/Y H:i:s'));
        $this->line('PHP ' . PHP_VERSION . ' (' . PHP_BINARY . ')');
        $this->line('');

        // 1. Driver: penyebab paling umum gagal di server adalah PHP CLI tanpa pdo_sqlsrv
        $drivers = \PDO::getAvailableDrivers();
        if (!in_array('sqlsrv', $drivers, true)) {
            $this->error('Driver pdo_sqlsrv TIDAK terpasang di PHP ini (tersedia: ' . (implode(', ', $drivers) ?: 'tidak ada') . ').');
            $this->line('  Jalankan dengan PHP lain yang punya sqlsrv, mis: php8.1 artisan ledger:check');
            return self::FAILURE;
        }

        // 2. Koneksi database
        $this->info('Koneksi database');
        $rows = [];
        foreach ([
            'sqlsrv' => 'SIPekan (utama)',
            'sunplus_db' => 'SunSystems (ledger)',
            'aps_db' => 'APS (payroll)',
        ] as $conn => $label) {
            $rows[] = $this->checkConnection($conn, $label);
        }
        $this->table(['Koneksi', 'Fungsi', 'Server / database', 'Status', 'Waktu'], $rows);

        if (!$this->option('cepat')) {
            $this->checkAllLedgers();
        }

        // 4. Satu akun (opsional), juga berjalan dengan --cepat
        if ($kode = $this->option('akun')) {
            $this->checkAccount(strtoupper(trim($kode)));
        }

        return $this->finish();
    }

    /** 3. Ledger tiap wilayah (atau satu wilayah dengan --wilayah) */
    private function checkAllLedgers(): void
    {
        $this->info('Ledger SunSystems per wilayah');
        $only = $this->option('wilayah');
        $names = Wilayah::pluck('nama', 'id');
        $ledgerRows = [];
        foreach (DashboardService::LEDGER_TABLES as $wilayahId => $tableA) {
            if ($only !== null && (string) $only !== (string) $wilayahId) {
                continue;
            }
            $ledgerRows[] = $this->checkLedger($wilayahId, $names[$wilayahId] ?? '-', $tableA);
        }
        if (!$ledgerRows) {
            $this->warn("Wilayah {$only} tidak punya ledger yang terdaftar.");
            $this->ok = false;
        } else {
            $this->table(['Wilayah', 'Nama', 'Ledger', 'Aktual (A)', 'Anggaran (B)', 'Periode terakhir', 'Status'], $ledgerRows);
        }
    }

    private function checkConnection(string $conn, string $label): array
    {
        $cfg = config("database.connections.{$conn}", []);
        $target = trim(($cfg['host'] ?? '?') . ' / ' . ($cfg['database'] ?? '?'));
        $t = microtime(true);
        $lastError = null;
        // Coba dua kali: jaringan kantor kadang lambat di percobaan pertama (timeout sesaat)
        for ($attempt = 1; $attempt <= 2; $attempt++) {
            try {
                DB::connection($conn)->select('SELECT 1 AS ok');
                $note = $attempt > 1 ? ' (percobaan ke-2)' : '';
                return [$conn, $label, $target, '<info>TERSAMBUNG</info>' . $note, $this->ms($t)];
            } catch (\Throwable $e) {
                $lastError = $e;
                DB::purge($conn); // buang koneksi yang gagal sebelum mencoba lagi
            }
        }
        $this->ok = false;
        return [$conn, $label, $target, '<error>GAGAL</error> ' . $this->shortError($lastError), $this->ms($t)];
    }

    private function checkLedger(int $wilayahId, string $nama, string $tableA): array
    {
        $tableB = str_replace('_A_', '_B_', $tableA);
        $prefix = substr(str_replace('dbo.', '', $tableA), 0, 3);
        try {
            $db = DB::connection('sunplus_db');
            $a = $db->selectOne("SELECT COUNT_BIG(*) AS n, MAX(PERIOD) AS mx FROM {$tableA} WHERE ALLOCATION <> 'C'");
            $b = $db->selectOne("SELECT COUNT_BIG(*) AS n FROM {$tableB} WHERE ALLOCATION <> 'C'");

            $last = $a->mx ? $this->periodLabel($a->mx) : '-';
            $status = '<info>OK</info>';
            if ((int) $a->n === 0) {
                $status = '<comment>KOSONG</comment>';
            } elseif ($a->mx && $this->monthsBehind($a->mx) > 2) {
                $status = '<comment>TERTINGGAL ' . $this->monthsBehind($a->mx) . ' bln</comment>';
            }

            return [$wilayahId, $nama, $prefix, number_format((int) $a->n, 0, ',', '.'), number_format((int) $b->n, 0, ',', '.'), $last, $status];
        } catch (\Throwable $e) {
            $this->ok = false;
            return [$wilayahId, $nama, $prefix, '-', '-', '-', '<error>GAGAL</error> ' . $this->shortError($e)];
        }
    }

    private function checkAccount(string $kode): void
    {
        $this->info("Akun {$kode}");
        $user = User::with('wilayah:id,nama')->where('ACCNT_CODE', $kode)->orWhere('travel_account', $kode)->first(['id', 'name', 'wilayah_id']);
        if (!$user) {
            $this->warn('  Kode akun ini tidak dimiliki pengguna SIPekan mana pun.');
            $this->ok = false;
            return;
        }
        $tableA = DashboardService::LEDGER_TABLES[(int) $user->wilayah_id] ?? null;
        $this->line("  Pengguna: {$user->name} · wilayah " . (optional($user->wilayah)->nama ?? $user->wilayah_id));
        if (!$tableA) {
            $this->warn('  Wilayah pengguna ini tidak punya ledger yang terdaftar di SIPekan.');
            $this->ok = false;
            return;
        }
        try {
            $db = DB::connection('sunplus_db');
            $sum = (float) $db->selectOne("SELECT SUM(AMOUNT) AS s FROM {$tableA} WHERE ALLOCATION <> 'C' AND ACCNT_CODE = ?", [$kode])->s;
            $last = $db->selectOne("SELECT TOP 1 PERIOD, TRANS_DATETIME, JRNAL_NO, DESCRIPTN FROM {$tableA} WHERE ALLOCATION <> 'C' AND ACCNT_CODE = ? ORDER BY PERIOD DESC, JRNAL_NO DESC", [$kode]);
            if (!$last) {
                $this->warn("  Belum ada transaksi untuk akun ini di {$tableA}.");
                return;
            }
            $balance = -$sum; // konvensi SIPekan: negatif = Cr. (lebih bayar)
            $saldo = number_format(abs($balance), 0, ',', '.') . ($balance < 0 ? ' Cr. (lebih bayar)' : ($balance > 0 ? ' (kewajiban)' : ''));
            $this->line("  Ledger: {$tableA}");
            $this->line("  Saldo: {$saldo}");
            $this->line('  Transaksi terakhir: ' . date('d/m/Y', strtotime($last->TRANS_DATETIME)) . " · jurnal {$last->JRNAL_NO} · " . trim($last->DESCRIPTN));
        } catch (\Throwable $e) {
            $this->ok = false;
            $this->error('  Gagal membaca ledger: ' . $this->shortError($e));
        }
        $this->line('');
    }

    private function finish(): int
    {
        if ($this->ok) {
            $this->info('Semua koneksi dan ledger dapat dibaca.');
            return self::SUCCESS;
        }
        $this->error('Ada pemeriksaan yang gagal. Lihat keterangan di atas.');
        return self::FAILURE;
    }

    /** "2026011" -> "Nov 2026" */
    private function periodLabel(string $period): string
    {
        $y = (int) substr($period, 0, 4);
        $m = (int) substr($period, -2);
        return $m >= 1 && $m <= 12 ? \Illuminate\Support\Carbon::create($y, $m, 1)->locale('id')->translatedFormat('M Y') : $period;
    }

    private function monthsBehind(string $period): int
    {
        $y = (int) substr($period, 0, 4);
        $m = (int) substr($period, -2);
        if ($m < 1 || $m > 12) {
            return 0;
        }
        return max(0, (now()->year - $y) * 12 + now()->month - $m);
    }

    private function shortError(\Throwable $e): string
    {
        $msg = $e->getMessage();
        // Potong bagian SQL agar ringkas dan tidak menampilkan isi kueri
        $msg = preg_replace('/\s*\(SQL:.*$/s', '', $msg);
        return mb_strimwidth(trim($msg), 0, 110, '…');
    }

    private function ms(float $start): string
    {
        return round((microtime(true) - $start) * 1000) . ' ms';
    }
}
