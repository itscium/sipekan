<?php

namespace App\Services;

use App\Models\FPMachine;
use App\Models\FPMachineLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Menarik log absensi dari mesin fingerprint (web service /iWsService)
 * dan menyimpannya ke tabel fp_machine_log tanpa duplikat.
 * Dipakai oleh tombol manual dan jadwal otomatis (fingerprint:sync-logs).
 */
class FingerprintSyncService
{
    /** Kunci cache untuk status penarikan terakhir (ditampilkan di halaman absensi). */
    public const STATUS_KEY = 'fingerprint:last_sync';

    /** Log sebelum tanggal ini tidak pernah diambil (sama seperti sebelumnya). */
    private const EARLIEST_DATE = '2025-03-09';

    /** Ambil ulang beberapa hari terakhir untuk menangkap log yang terlambat masuk. */
    private const OVERLAP_DAYS = 7;

    private const CONNECT_TIMEOUT = 10;
    private const READ_TIMEOUT = 120;

    /**
     * Tarik log dari semua mesin.
     *
     * @param string $trigger 'otomatis' atau 'manual'
     * @return array{started_at: string, finished_at: string, trigger: string, ok: bool, inserted: int, machines: array}
     */
    public function syncAll(string $trigger = 'manual'): array
    {
        $started = now();
        $results = [];

        foreach (FPMachine::orderBy('id')->get() as $machine) {
            try {
                $results[] = ['machine' => $machine->location, 'ok' => true] + $this->syncMachine($machine);
            } catch (\Throwable $e) {
                Log::warning("Tarik log fingerprint {$machine->location} ({$machine->ip_address}:{$machine->port}) gagal: " . $e->getMessage());
                $results[] = [
                    'machine' => $machine->location,
                    'ok' => false,
                    'inserted' => 0,
                    'read' => 0,
                    'error' => $e->getMessage(),
                ];
            }
        }

        $summary = [
            'started_at' => $started->toIso8601String(),
            'finished_at' => now()->toIso8601String(),
            'trigger' => $trigger,
            'ok' => collect($results)->every('ok') && count($results) > 0,
            'inserted' => collect($results)->sum('inserted'),
            'machines' => $results,
        ];

        Cache::forever(self::STATUS_KEY, $summary);
        Log::info('Tarik log fingerprint (' . $trigger . '): ' . json_encode($summary));

        return $summary;
    }

    /** @return array{inserted: int, read: int, from: string} */
    public function syncMachine(FPMachine $machine): array
    {
        $rows = $this->fetchFromMachine($machine->ip_address, (int) $machine->port);

        // Mulai dari tanggal log terakhir yang tersimpan (dikurangi beberapa hari), bukan dari awal.
        $last = FPMachineLog::where('fp_machine_id', $machine->id)->max('date');
        $from = $last
            ? Carbon::parse($last)->subDays(self::OVERLAP_DAYS)->toDateString()
            : self::EARLIEST_DATE;
        $from = max($from, self::EARLIEST_DATE);
        $today = now()->toDateString();

        // Kunci log yang sudah ada pada rentang ini: pin|status|datetime
        $existing = [];
        FPMachineLog::where('fp_machine_id', $machine->id)
            ->where('date', '>=', $from)
            ->select('pin', 'status', 'datetime')
            ->orderBy('id')
            ->chunk(5000, function ($chunk) use (&$existing) {
                foreach ($chunk as $r) {
                    $existing[$this->key($r->pin, $r->status, $r->datetime)] = true;
                }
            });

        $new = [];
        foreach ($rows as $r) {
            $ts = strtotime($r['datetime']);
            if ($ts === false) {
                continue;
            }
            $date = date('Y-m-d', $ts);
            if ($date < $from || $date > $today) {
                continue;
            }
            $k = $this->key($r['pin'], $r['status'], $r['datetime']);
            if (isset($existing[$k])) {
                continue;
            }
            $existing[$k] = true;
            $new[] = [
                'fp_machine_id' => $machine->id,
                'pin' => $r['pin'],
                'datetime' => $r['datetime'],
                'verified' => $r['verified'],
                'status' => $r['status'],
                'workcode' => $r['workcode'],
                'date' => $date,
                'time' => date('H:i:s', $ts),
            ];
        }

        // SQL Server membatasi 2100 parameter per kueri: 8 kolom x 200 baris = 1600.
        DB::transaction(function () use ($new) {
            foreach (array_chunk($new, 200) as $chunk) {
                FPMachineLog::insert($chunk);
            }
        });

        return ['inserted' => count($new), 'read' => count($rows), 'from' => $from];
    }

    private function key($pin, $status, $datetime): string
    {
        return trim((string) $pin) . '|' . trim((string) $status) . '|' . date('Y-m-d H:i:s', strtotime((string) $datetime));
    }

    /**
     * Minta semua log absensi ke mesin lewat SOAP (GetAttLog).
     * @return array<int, array{pin: string, datetime: string, verified: string, status: string, workcode: string}>
     */
    private function fetchFromMachine(string $ip, int $port): array
    {
        $conn = @fsockopen($ip, $port, $errno, $errstr, self::CONNECT_TIMEOUT);
        if (!$conn) {
            throw new \RuntimeException("Mesin {$ip}:{$port} tidak bisa dijangkau ({$errstr})");
        }
        stream_set_timeout($conn, self::READ_TIMEOUT);

        $soap = '<GetAttLog><ArgComKey xsi:type="xsd:integer">0</ArgComKey>'
            . '<Arg><PIN xsi:type="xsd:integer">ALL</PIN></Arg></GetAttLog>';
        fwrite($conn, "POST /iWsService HTTP/1.0\r\nContent-Type: text/xml\r\nContent-Length: "
            . strlen($soap) . "\r\n\r\n" . $soap . "\r\n");

        $buffer = '';
        while (!feof($conn)) {
            $line = fgets($conn, 4096);
            if ($line === false) {
                if (stream_get_meta_data($conn)['timed_out']) {
                    fclose($conn);
                    throw new \RuntimeException("Mesin {$ip}:{$port} tidak menjawab dalam " . self::READ_TIMEOUT . ' detik');
                }
                break;
            }
            $buffer .= $line;
        }
        fclose($conn);

        if (stripos($buffer, '<GetAttLogResponse>') === false) {
            throw new \RuntimeException("Mesin {$ip}:{$port} tidak mengirim data log (bukan web service absensi?)");
        }

        preg_match_all('/<Row>(.*?)<\/Row>/s', $buffer, $m);
        $rows = [];
        foreach ($m[1] as $row) {
            $pin = $this->tag($row, 'PIN');
            $datetime = $this->tag($row, 'DateTime');
            if ($pin === '' || $datetime === '') {
                continue;
            }
            $rows[] = [
                'pin' => $pin,
                'datetime' => $datetime,
                'verified' => $this->tag($row, 'Verified'),
                'status' => $this->tag($row, 'Status'),
                'workcode' => $this->tag($row, 'WorkCode'),
            ];
        }
        return $rows;
    }

    private function tag(string $xml, string $name): string
    {
        return preg_match("/<{$name}>(.*?)<\/{$name}>/s", $xml, $m) ? trim($m[1]) : '';
    }

    /** Jadwal penarikan berikutnya: Senin–Jumat pukul 09.00 WIB. */
    public static function nextRun(): Carbon
    {
        $next = now()->setTime(9, 0);
        if (now()->gte($next)) {
            $next->addDay();
        }
        while ($next->isWeekend()) {
            $next->addDay();
        }
        return $next;
    }
}
