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
            'payroll' => null,
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
        if ($user->ACCNT_CODE) {
            $data['payroll'] = $this->safe($data, 'payroll', fn () => $this->latestPayroll($user->ACCNT_CODE));
        }
        if (Gate::forUser($user)->allows('uni')) {
            $data['attendance'] = $this->safe($data, 'attendance', fn () => $this->attendanceThisMonth($user->id));
        }
        $data['department'] = $this->safe($data, 'department', fn () => Departemen::where('kepala_departemen', $user->id)->first(['id', 'nama_departemen']));

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

    /** Gaji bersih terakhir yang sudah diposting di APS. */
    private function latestPayroll(string $enrollmentCode): ?array
    {
        $net = Payrol::where('enrollment_code', $enrollmentCode)
            ->where('stub', 0)
            ->orderByRaw('CAST([year] AS INT) DESC, CAST([period] AS INT) DESC')
            ->first(['year', 'period', 'value', 'date_payment']);

        if (!$net) {
            return null;
        }

        $month = Carbon::createFromDate((int) $net->year, (int) $net->period, 1);

        return [
            'net' => (float) $net->value,
            'month' => $month,
            'paid_at' => $net->date_payment ? Carbon::parse($net->date_payment) : null,
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
