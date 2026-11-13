<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserActivityLog;
use App\Models\Wilayah;
use App\Services\ActivityTracker;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/** Pengguna yang sedang online dan log aktivitas (khusus admin). */
class ActivityController extends Controller
{
    public function __construct(private ActivityTracker $tracker)
    {
        $this->middleware(['auth', 'can:administrator']);
    }

    /** Data untuk panel Beranda, dibaca ulang setiap 30 detik. */
    public function feed()
    {
        $online = $this->tracker->online();
        $users = User::with('wilayah:id,nama')->whereIn('id', array_keys($online))
            ->get(['id', 'name', 'foto', 'wilayah_id'])->keyBy('id');

        $onlineList = [];
        foreach ($online as $id => $o) {
            $u = $users->get($id);
            if (!$u) {
                continue;
            }
            $onlineList[] = [
                'id' => $u->id,
                'name' => $u->name,
                'initials' => $u->initials,
                'photo' => $u->foto_url,
                'wilayah' => optional($u->wilayah)->nama,
                'label' => $o['label'],
                'ago' => $this->ago($o['at']),
            ];
        }

        return response()->json([
            'online' => $onlineList,
            'today_users' => UserActivityLog::visible()->whereDate('created_at', today())->whereNotNull('user_id')->where('action', '!=', 'failed')
                ->distinct()->count('user_id'),
            'failed_today' => UserActivityLog::whereDate('created_at', today())->where('action', 'failed')->count(),
            'server_time' => now()->format('H.i'),
        ])->header('Cache-Control', 'no-store');
    }

    /** Halaman log lengkap dengan filter. */
    public function index(Request $request)
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'aksi' => ['nullable', 'in:' . implode(',', array_keys(UserActivityLog::ACTIONS))],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date'],
        ]);

        // Kode singkat (CIUM, WIUM, ...) tidak tersimpan di database, jadi cocokkan dulu ke daftar wilayah:
        // kode singkat harus sama persis, nama wilayah cukup mengandung kata yang dicari.
        $term = trim($data['q'] ?? '');
        $wilayahIds = $term === '' ? [] : Wilayah::get(['id', 'nama', 'kode', 'english_name'])
            ->filter(fn ($w) => strcasecmp($w->short_code, $term) === 0 || stripos($w->nama, $term) !== false)
            ->pluck('id')->all();

        $logs = UserActivityLog::visible()->with(['user:id,name,wilayah_id', 'user.wilayah:id,nama,kode,english_name'])
            ->when($term !== '', function ($q) use ($term, $wilayahIds) {
                $q->where(fn ($w) => $w->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%"))
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('ip', 'like', "%{$term}%")
                    ->when($wilayahIds, fn ($x) => $x->orWhereHas('user', fn ($u) => $u->whereIn('wilayah_id', $wilayahIds))));
            })
            ->when($data['aksi'] ?? null, fn ($q, $a) => $q->where('action', $a))
            ->when($data['dari'] ?? null, fn ($q, $d) => $q->where('created_at', '>=', Carbon::parse($d)->startOfDay()))
            ->when($data['sampai'] ?? null, fn ($q, $d) => $q->where('created_at', '<=', Carbon::parse($d)->endOfDay()))
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        // Ringkasan hari ini
        $today = UserActivityLog::visible()->whereDate('created_at', today())
            ->selectRaw("COUNT(DISTINCT CASE WHEN action <> 'failed' THEN user_id END) AS users,
                SUM(CASE WHEN action = 'login' THEN 1 ELSE 0 END) AS logins,
                SUM(CASE WHEN action = 'failed' THEN 1 ELSE 0 END) AS failed,
                SUM(CASE WHEN action = 'print' THEN 1 ELSE 0 END) AS prints")
            ->first();

        return view('admin.activity.index', [
            'logs' => $logs,
            'filters' => $data,
            'actions' => UserActivityLog::ACTIONS,
            'today' => $today,
            'onlineCount' => count($this->tracker->online()),
        ]);
    }

    private function ago(int $ts): string
    {
        $s = now()->timestamp - $ts;
        return $s < 60 ? 'baru saja' : floor($s / 60) . ' mnt lalu';
    }
}
