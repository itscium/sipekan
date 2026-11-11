<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserActivityLog;
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

        $logs = UserActivityLog::with('user:id,name')
            ->where('action', '!=', 'page')
            ->orWhere(fn ($q) => $q->where('action', 'page')->where('route_name', '!=', 'dashboard'))
            ->orderByDesc('id')->limit(12)->get();

        return response()->json([
            'online' => $onlineList,
            'logs' => $logs->map(fn ($l) => $this->logRow($l))->values(),
            'today_users' => UserActivityLog::whereDate('created_at', today())->whereNotNull('user_id')
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

        $logs = UserActivityLog::with('user:id,name')
            ->when($data['q'] ?? null, function ($q, $term) {
                $q->where(fn ($w) => $w->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%"))
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('ip', 'like', "%{$term}%"));
            })
            ->when($data['aksi'] ?? null, fn ($q, $a) => $q->where('action', $a))
            ->when($data['dari'] ?? null, fn ($q, $d) => $q->where('created_at', '>=', Carbon::parse($d)->startOfDay()))
            ->when($data['sampai'] ?? null, fn ($q, $d) => $q->where('created_at', '<=', Carbon::parse($d)->endOfDay()))
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.activity.index', [
            'logs' => $logs,
            'filters' => $data,
            'actions' => UserActivityLog::ACTIONS,
        ]);
    }

    private function logRow(UserActivityLog $l): array
    {
        return [
            'id' => $l->id,
            'time' => $l->created_at->isToday() ? $l->created_at->format('H.i') : $l->created_at->format('d/m H.i'),
            'name' => optional($l->user)->name ?? 'Tidak dikenal',
            'sentence' => $l->sentence,
            'email' => $l->action === 'failed' ? $l->email : null,
            'device' => $l->device,
            'ip' => $l->ip,
            'action' => $l->action,
            'action_label' => UserActivityLog::ACTIONS[$l->action] ?? $l->action,
        ];
    }

    private function ago(int $ts): string
    {
        $s = now()->timestamp - $ts;
        return $s < 60 ? 'baru saja' : floor($s / 60) . ' mnt lalu';
    }
}
