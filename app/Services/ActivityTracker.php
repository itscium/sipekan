<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Mencatat aktivitas pengguna dan status "sedang online".
 * Yang dicatat hanya nama halaman, waktu, IP, dan perangkat; tidak ada isi data atau nominal.
 */
class ActivityTracker
{
    /** Dianggap online jika ada aktivitas dalam rentang ini. */
    public const ONLINE_MINUTES = 5;

    private const ONLINE_KEY = 'activity:online';

    /** Halaman yang sama oleh user yang sama tidak dicatat ulang dalam rentang ini (detik). */
    private const DEDUP_SECONDS = 60;

    /** Nama halaman yang mudah dibaca, berdasarkan nama route (awalan cocok). */
    private const LABELS = [
        'dashboard' => 'Beranda',
        'personal.keuangan.payrol.print' => 'Slip gaji',
        'personal.keuangan.payrol.pdf' => 'Slip gaji (PDF)',
        'personal.keuangan.payrol' => 'Slip gaji',
        'personal.keuangan.travel' => 'Keuangan travel',
        'personal.keuangan' => 'Keuangan personal',
        'personal.profile.edit' => 'Edit profil',
        'personal.profile' => 'Profil',
        'personal.attendance' => 'Absensi',
        'personal.change-password' => 'Ganti password',
        'departemen.keuangan.detail' => 'Rincian keuangan departemen',
        'departemen.keuangan' => 'Keuangan departemen',
        'departemen.pegawai' => 'Pegawai departemen',
        'ledger.conference_on_wium.print' => 'Ledger konferens',
        'ledger.conference_on_wium' => 'Ledger konferens di WIUM',
        'report.departemen' => 'Laporan pengeluaran departemen',
        'employee.allowance' => 'Tunjangan pegawai',
        'wilayah.fingerprint-machine' => 'Mesin fingerprint',
        'wilayah.pengguna' => 'Pengguna wilayah',
        'wilayah.salary' => 'Gaji dan tunjangan wilayah',
        'wilayah.department-expense' => 'Anggaran departemen wilayah',
        'wilayah.departemen' => 'Departemen wilayah',
        'wilayah.roles' => 'Jabatan wilayah',
        'wilayah' => 'Master wilayah',
        'sop' => 'SOP',
        'admin.activity' => 'Log aktivitas',
    ];

    /** Route cetak/unduh dicatat sebagai aksi "print". */
    private const PRINT_ROUTES = [
        'personal.keuangan.payrol.print',
        'personal.keuangan.payrol.pdf',
        'ledger.conference_on_wium.print',
    ];

    /** Route yang tidak dicatat (permintaan latar belakang / berkas). */
    private const SKIP_ROUTES = [
        'admin.activity.feed',
        'personal.profile.photo',
        'android.download',
    ];

    /** Catat halaman yang dibuka (dipanggil middleware). */
    public function trackRequest(Request $request, User $user): void
    {
        $route = optional($request->route())->getName();
        if (!$route || in_array($route, self::SKIP_ROUTES, true) || $request->ajax() || $request->expectsJson()) {
            return;
        }

        $label = $this->label($route);
        $this->markOnline($user, $label);

        // Hindari log ganda saat halaman yang sama dimuat ulang berkali-kali
        $dedupKey = "activity:seen:{$user->id}:{$route}";
        if (!Cache::add($dedupKey, 1, self::DEDUP_SECONDS)) {
            return;
        }

        $this->write([
            'user_id' => $user->id,
            'action' => in_array($route, self::PRINT_ROUTES, true) ? 'print' : 'page',
            'route_name' => $route,
            'label' => $this->withPeriod($label, $request),
        ], $request);
    }

    public function login(User $user, Request $request): void
    {
        $this->markOnline($user, 'Baru masuk');
        $this->write(['user_id' => $user->id, 'action' => 'login', 'label' => 'Login'], $request);
    }

    public function logout(?User $user, Request $request): void
    {
        if (!$user) {
            return;
        }
        $online = Cache::get(self::ONLINE_KEY, []);
        unset($online[$user->id]);
        Cache::forever(self::ONLINE_KEY, $online);
        $this->write(['user_id' => $user->id, 'action' => 'logout', 'label' => 'Logout'], $request);
    }

    public function failed(?string $email, ?User $user, Request $request): void
    {
        $this->write([
            'user_id' => optional($user)->id,
            'email' => $email ? mb_substr($email, 0, 255) : null,
            'action' => 'failed',
            'label' => 'Gagal masuk',
        ], $request);
    }

    /**
     * Pengguna yang aktif dalam ONLINE_MINUTES terakhir, terbaru dulu.
     * @return array<int, array{user_id:int, at:int, label:string}>
     */
    public function online(): array
    {
        $cut = now()->subMinutes(self::ONLINE_MINUTES)->timestamp;
        $online = array_filter(Cache::get(self::ONLINE_KEY, []), fn ($o) => $o['at'] >= $cut);
        uasort($online, fn ($a, $b) => $b['at'] <=> $a['at']);
        return $online;
    }

    /* ---------------------------------------------------------------- */

    private function markOnline(User $user, string $label): void
    {
        $online = Cache::get(self::ONLINE_KEY, []);
        $cut = now()->subMinutes(self::ONLINE_MINUTES * 2)->timestamp;
        $online = array_filter($online, fn ($o) => $o['at'] >= $cut); // buang yang sudah lama
        $online[$user->id] = ['user_id' => $user->id, 'at' => now()->timestamp, 'label' => $label];
        Cache::forever(self::ONLINE_KEY, $online);
    }

    private function write(array $data, Request $request): void
    {
        try {
            UserActivityLog::create($data + [
                'ip' => $request->ip(),
                'device' => $this->device((string) $request->userAgent()),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Pencatatan tidak boleh mengganggu pemakaian aplikasi
            Log::warning('Gagal mencatat aktivitas: ' . $e->getMessage());
        }
    }

    private function label(string $route): string
    {
        foreach (self::LABELS as $prefix => $label) {
            if ($route === $prefix || str_starts_with($route, $prefix . '.')) {
                return $label;
            }
        }
        return ucfirst(str_replace(['.', '-', '_'], ' ', $route));
    }

    /** Tambahkan bulan untuk slip gaji, mis. "Slip gaji Okt 2026". */
    private function withPeriod(string $label, Request $request): string
    {
        $tgl = $request->route('tgl') ?? $request->query('periode');
        if ($tgl && str_starts_with($label, 'Slip gaji') && preg_match('/^\d{4}-\d{2}/', $tgl)) {
            try {
                return $label . ' ' . \Illuminate\Support\Carbon::parse($tgl)->locale('id')->translatedFormat('M Y');
            } catch (\Throwable $e) {
            }
        }
        return $label;
    }

    /** Ringkasan perangkat dari User-Agent, mis. "Chrome · Windows" atau "Aplikasi Android". */
    public function device(string $ua): string
    {
        if ($ua === '') {
            return 'Tidak diketahui';
        }
        if (str_contains($ua, 'SIPekanApp')) {
            return 'Aplikasi Android';
        }
        $browser = match (true) {
            str_contains($ua, 'Edg/') => 'Edge',
            str_contains($ua, 'OPR/') || str_contains($ua, 'Opera') => 'Opera',
            str_contains($ua, 'SamsungBrowser') => 'Samsung Internet',
            str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Chrome/') => 'Chrome',
            str_contains($ua, 'Safari/') => 'Safari',
            default => 'Browser lain',
        };
        $os = match (true) {
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') => 'iOS',
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Mac OS') => 'macOS',
            str_contains($ua, 'Linux') => 'Linux',
            default => 'OS lain',
        };
        return "{$browser} · {$os}";
    }
}
