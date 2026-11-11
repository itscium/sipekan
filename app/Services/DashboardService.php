<?php

namespace App\Services;

use App\Models\Departemen;
use App\Models\FPMachineLog;
use App\Models\FPMachineUser;
use App\Models\User;
use App\Models\Wilayah;
use App\Models\WIUM\A_SALFLDG;
use App\Models\WIUM\Payrol;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

/**
 * Data ringkasan untuk halaman Beranda.
 * Semua kueri hanya membaca data. Setiap blok berdiri sendiri: jika satu
 * sumber data gagal, blok lain tetap tampil.
 */
class DashboardService
{
    /** Tabel ledger SunSystems per wilayah (sama dengan Personal\KeuanganController). */
    private const LEDGER_TABLES = [
        1 => 'dbo.CIU_A_SALFLDG',
        2 => 'dbo.JLC_A_SALFLDG',
        3 => 'dbo.NSM_A_SALFLDG',
        4 => 'dbo.CSM_A_SALFLDG',
        5 => 'dbo.SSM_A_SALFLDG',
        6 => 'dbo.WJC_A_SALFLDG',
        7 => 'dbo.CJM_A_SALFLDG',
        8 => 'dbo.EJC_A_SALFLDG',
        9 => 'dbo.WKD_A_SALFLDG',
        10 => 'dbo.EKM_A_SALFLDG',
        11 => 'dbo.NTM_A_SALFLDG',
        10002 => 'dbo.JBC_A_SALFLDG',
        20003 => 'dbo.WIU_A_SALFLDG',
    ];

    private const CACHE_MINUTES = 5;

    public function forUser(User $user): array
    {
        return Cache::remember(
            'dashboard:' . $user->id . ':' . now()->format('YmdH'),
            now()->addMinutes(self::CACHE_MINUTES),
            fn () => $this->build($user)
        );
    }

    private function build(User $user): array
    {
        $isAdmin = $user->type === 'admin';

        $data = [
            'is_admin' => $isAdmin,
            'personal' => null,
            'travel' => null,
            'attendance' => null,
            'department' => null,
            'admin' => null,
            'errors' => [],
        ];

        if ($isAdmin) {
            $data['admin'] = $this->safe($data, 'admin', fn () => $this->adminStats());
            return $data;
        }

        $table = self::LEDGER_TABLES[(int) $user->wilayah_id] ?? null;

        if ($table && $user->ACCNT_CODE) {
            $data['personal'] = $this->safe($data, 'personal', fn () => $this->account($table, $user->ACCNT_CODE, 5));
        }
        if ($table && $user->travel_account) {
            $data['travel'] = $this->safe($data, 'travel', fn () => $this->account($table, $user->travel_account, 0));
        }
        if (Gate::forUser($user)->allows('uni')) {
            $data['attendance'] = $this->safe($data, 'attendance', fn () => $this->attendanceThisMonth($user->id));
        }
        $data['department'] = $this->safe($data, 'department', fn () => Departemen::where('kepala_departemen', $user->id)->budgetVisible()->first(['id', 'nama_departemen']));

        return $data;
    }

    /** Jalankan satu blok; jika gagal catat ke log dan tandai blok itu. */
    private function safe(array &$data, string $key, callable $fn)
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            Log::warning("Dashboard blok '{$key}' gagal dimuat: " . $e->getMessage());
            $data['errors'][] = $key;
            return null;
        }
    }

    /** Periode SunSystems, contoh November 2026 = 2026011. */
    private function period(Carbon $date): string
    {
        return $date->format('Y') . '0' . $date->format('m');
    }

    /**
     * Saldo akun dengan konvensi yang sama seperti halaman Keuangan Personal:
     * saldo = -(jumlah AMOUNT), bernilai negatif ditampilkan sebagai "Cr.".
     * Di ledger ini Debit tersimpan negatif dan Kredit positif.
     */
    private function account(string $table, string $account, int $latest): array
    {
        $base = fn () => (new A_SALFLDG)->setTable($table)
            ->where('ALLOCATION', '<>', 'C')
            ->where('ACCNT_CODE', $account);

        $now = $this->period(now());
        $sum = (float) $base()->where('PERIOD', '<=', $now)->sum('AMOUNT');
        $balance = -$sum;

        $month = $base()->where('PERIOD', $now)
            ->selectRaw("SUM(CASE WHEN D_C = 'D' THEN -AMOUNT ELSE 0 END) AS debit, SUM(CASE WHEN D_C = 'C' THEN AMOUNT ELSE 0 END) AS credit")
            ->first();

        $transactions = [];
        if ($latest > 0) {
            $rows = $base()->where('PERIOD', '<=', $now)
                ->orderByDesc('TRANS_DATETIME')->orderByDesc('JRNAL_NO')->orderByDesc('JRNAL_LINE')
                ->limit($latest)
                ->get(['TRANS_DATETIME', 'JRNAL_NO', 'JRNAL_LINE', 'DESCRIPTN', 'D_C', 'AMOUNT']);

            foreach ($rows as $r) {
                $transactions[] = [
                    'date' => Carbon::parse($r->TRANS_DATETIME),
                    'journal' => $r->JRNAL_NO . '-' . $r->JRNAL_LINE,
                    'description' => trim($r->DESCRIPTN) ?: 'Tanpa keterangan',
                    'type' => $r->D_C === 'D' ? 'debit' : 'credit',
                    'amount' => abs((float) $r->AMOUNT),
                ];
            }
        }

        return [
            'balance' => abs($balance),
            'is_credit' => $balance < 0,
            'month_debit' => (float) ($month->debit ?? 0),
            'month_credit' => (float) ($month->credit ?? 0),
            'transactions' => $transactions,
        ];
    }

    /**
     * Riwayat gaji dari APS untuk bulan yang dipilih (format "YYYY-MM").
     * Jika bulan kosong atau tidak ada datanya, dipakai bulan terakhir yang sudah diposting.
     * Hasil: daftar bulan yang tersedia, rincian bulan terpilih, dan tren 12 bulan.
     */
    public function payroll(User $user, ?string $ym): array
    {
        if (!$user->ACCNT_CODE || $user->type === 'admin') {
            return ['available' => false, 'error' => false];
        }

        $ym = (is_string($ym) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $ym)) ? $ym : null;

        try {
            return Cache::remember(
                'dashboard:payroll:' . $user->id . ':' . ($ym ?? 'latest') . ':' . now()->format('YmdH'),
                now()->addMinutes(self::CACHE_MINUTES),
                fn () => $this->buildPayroll($user->ACCNT_CODE, $ym)
            );
        } catch (\Throwable $e) {
            Log::warning('Dashboard blok gaji gagal dimuat: ' . $e->getMessage());
            return ['available' => false, 'error' => true];
        }
    }

    private function buildPayroll(string $code, ?string $ym): array
    {
        // Semua bulan yang sudah diposting beserta gaji bersihnya (stub 0 = ringkasan).
        $months = Payrol::where('enrollment_code', $code)->where('stub', 0)
            ->selectRaw('[year], [period], MAX([value]) AS net, MAX([date_payment]) AS paid')
            ->groupBy('year', 'period')
            ->orderByRaw('CAST([year] AS INT) DESC, CAST([period] AS INT) DESC')
            ->get()
            ->map(fn ($r) => [
                'key' => sprintf('%04d-%02d', (int) $r->year, (int) $r->period),
                'year' => (int) $r->year,
                'month' => (int) $r->period,
                'net' => (float) $r->net,
                'paid' => $r->paid,
            ])
            ->values();

        if ($months->isEmpty()) {
            return ['available' => false, 'error' => false];
        }

        $index = $ym ? $months->search(fn ($m) => $m['key'] === $ym) : 0;
        $notFound = $ym && $index === false;
        $index = $index === false ? 0 : $index;
        $sel = $months[$index];

        $items = Payrol::where('enrollment_code', $code)->where('stub', 1)
            ->where('year', (string) $sel['year'])
            ->where('period', (string) $sel['month'])
            ->get(['signal', 'allowance_name', 'value']);

        $earnings = $items->where('signal', '+')->sortByDesc(fn ($i) => (float) $i->value)
            ->map(fn ($i) => ['name' => trim($i->allowance_name), 'value' => (float) $i->value])->values()->all();
        $deductions = $items->where('signal', '-')->sortByDesc(fn ($i) => (float) $i->value)
            ->map(fn ($i) => ['name' => trim($i->allowance_name), 'value' => (float) $i->value])->values()->all();

        // Tren: 12 bulan sampai bulan terpilih, urut dari yang terlama.
        $trend = $months->slice($index, 12)->reverse()->values()->map(fn ($m) => [
            'key' => $m['key'],
            'label' => Carbon::create($m['year'], $m['month'], 1)->locale('id')->translatedFormat('M y'),
            'net' => $m['net'],
        ])->all();

        return [
            'available' => true,
            'error' => false,
            'not_found' => $notFound,
            'requested' => $ym,
            'selected' => $sel['key'],
            'month' => Carbon::create($sel['year'], $sel['month'], 1),
            'paid_at' => $sel['paid'] ? Carbon::parse($sel['paid']) : null,
            'net' => $sel['net'],
            'earnings' => $earnings,
            'deductions' => $deductions,
            'total_earnings' => array_sum(array_column($earnings, 'value')),
            'total_deductions' => array_sum(array_column($deductions, 'value')),
            'prev' => $months[$index + 1]['key'] ?? null,
            'next' => $index > 0 ? $months[$index - 1]['key'] : null,
            'years' => $months->pluck('year')->unique()->values()->all(),
            'months_by_year' => $months->groupBy('year')->map(fn ($g) => $g->pluck('month')->all())->all(),
            'trend' => $trend,
        ];
    }

    /** Kehadiran bulan berjalan dari mesin sidik jari (Senin–Jumat). */
    private function attendanceThisMonth(int $userId): ?array
    {
        $pin = FPMachineUser::where('user_id', $userId)->value('pin');
        if (!$pin) {
            return null;
        }

        $start = now()->startOfMonth();
        $end = now();

        $logs = FPMachineLog::where('pin', $pin)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get(['date', 'time', 'status']);

        $days = $logs->groupBy(fn ($l) => Carbon::parse($l->date)->toDateString());

        $present = 0;
        $late = 0;
        foreach ($days as $logsOfDay) {
            $in = $logsOfDay->where('status', 0)->min('time');
            if ($in !== null) {
                $present++;
                if (strtotime($in) > strtotime('07:30:59')) {
                    $late++;
                }
            }
        }

        $workdays = 0;
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            if ($d->isWeekday()) {
                $workdays++;
            }
        }

        $lastSync = FPMachineLog::where('pin', $pin)->max('date');

        return [
            'present' => $present,
            'late' => $late,
            'workdays' => max($workdays, 1),
            // Jika data mesin belum disinkronkan bulan ini, angka 0 bukan berarti tidak hadir.
            'last_sync' => $lastSync ? Carbon::parse($lastSync) : null,
            'stale' => !$lastSync || Carbon::parse($lastSync)->lt($start),
        ];
    }

    private function adminStats(): array
    {
        return [
            'users_active' => User::where('type', 'user')->where('status', 1)->count(),
            'users_inactive' => User::where('type', 'user')->where('status', 0)->count(),
            'wilayah' => Wilayah::count(),
            'departemen' => Departemen::count(),
            'users_no_account' => User::where('type', 'user')->where('status', 1)
                ->where(fn ($q) => $q->whereNull('ACCNT_CODE')->orWhere('ACCNT_CODE', ''))->count(),
            'wilayah_list' => Wilayah::withCount(['users' => fn ($q) => $q->where('status', 1)])
                ->orderByDesc('users_count')->limit(6)->get(['id', 'nama']),
        ];
    }
}
