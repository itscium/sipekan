<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\User;
use App\Models\Wilayah;
use App\Services\ActivityTracker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/** Menu Admin > Pengumuman: pengumuman di Beranda pegawai per Business Unit. */
class AnnouncementController extends Controller
{
    public function __construct(private ActivityTracker $tracker)
    {
        $this->middleware(['auth', 'can:administrator']);
    }

    public function index()
    {
        return view('admin.announcements.index', [
            // Admin wilayah hanya melihat pengumuman yang seluruh sasarannya ada di Business Unit yang ia kelola
            'announcements' => Announcement::orderByDesc('starts_at')->orderByDesc('id')->get()
                ->filter(fn ($a) => $this->canManage($a))->values(),
            'wilayah' => Wilayah::orderBy('id')->get(['id', 'nama', 'kode', 'english_name'])->keyBy('id'),
        ]);
    }

    public function create()
    {
        return view('admin.announcements.form', $this->formData(new Announcement([
            'level' => 'penting',
            'display' => 'both',
            'starts_at' => now()->toDateString(),
            'target_wilayah' => [],
            'button_text' => 'Buka ' . config('sipekan.portal.name'),
            'button_url' => config('sipekan.portal.url'),
        ])));
    }

    public function store(Request $request)
    {
        $a = Announcement::create($this->validated($request) + ['created_by' => Auth::id()]);
        $this->logIfPublished($a, $request, 'menayangkan pengumuman');
        alert()->success('Tersimpan', $a->is_published ? 'Pengumuman ditayangkan sesuai jadwal.' : 'Pengumuman disimpan sebagai draf.');
        return redirect()->route('admin.announcements.index');
    }

    public function edit(Announcement $announcement)
    {
        $this->authorizeScope($announcement);
        return view('admin.announcements.form', $this->formData($announcement));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $this->authorizeScope($announcement);
        $wasPublished = $announcement->is_published;
        $announcement->update($this->validated($request));
        if (!$wasPublished) {
            $this->logIfPublished($announcement, $request, 'menayangkan pengumuman');
        }
        alert()->success('Tersimpan', 'Pengumuman diperbarui.');
        return redirect()->route('admin.announcements.index');
    }

    /** Tayangkan atau tarik kembali tanpa membuka formulir. */
    public function toggle(Request $request, Announcement $announcement)
    {
        $this->authorizeScope($announcement);
        $announcement->update(['is_published' => !$announcement->is_published]);
        $this->tracker->adminAction(Auth::user(), 'announce',
            ($announcement->is_published ? 'menayangkan' : 'menarik') . ' pengumuman "' . $announcement->title . '"', $request);
        alert()->success('Tersimpan', $announcement->is_published ? 'Pengumuman ditayangkan.' : 'Pengumuman ditarik dari Beranda.');
        return redirect()->route('admin.announcements.index');
    }

    public function destroy(Announcement $announcement)
    {
        $this->authorizeScope($announcement);
        $announcement->delete();
        alert()->success('Terhapus', 'Pengumuman dihapus.');
        return redirect()->route('admin.announcements.index');
    }

    private function formData(Announcement $a): array
    {
        $counts = User::where('type', 'user')->where('status', 1)
            ->selectRaw('wilayah_id, COUNT(*) AS n')->groupBy('wilayah_id')->pluck('n', 'wilayah_id');
        return [
            'a' => $a,
            'wilayah' => Wilayah::when($this->managed() !== null, fn ($q) => $q->whereIn('id', $this->managed() ?: [0]))
                ->orderBy('id')->get(['id', 'nama', 'kode', 'english_name']),
            'userCounts' => $counts,
        ];
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string', 'max:3000'],
            'button_text' => ['nullable', 'string', 'max:100', 'required_with:button_url'],
            'button_url' => ['nullable', 'url', 'starts_with:https://,http://', 'max:500', 'required_with:button_text'],
            'level' => ['required', 'in:penting,info'],
            'display' => ['required', 'in:both,banner'],
            'target_wilayah' => ['required', 'array', 'min:1'],
            'target_wilayah.*' => array_merge(['integer', 'exists:wilayah,id'],
                $this->managed() === null ? [] : [Rule::in($this->managed())]),
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_published' => ['nullable', 'boolean'],
        ], [
            'title.required' => 'Judul wajib diisi.',
            'body.required' => 'Isi pengumuman wajib diisi.',
            'button_url.url' => 'Tautan tombol harus alamat web yang lengkap, mis. https://portal.ciumcloud.org',
            'button_url.starts_with' => 'Tautan tombol harus diawali https://',
            'button_url.required_with' => 'Isi tautan untuk tombol, atau kosongkan teks tombol.',
            'button_text.required_with' => 'Isi teks tombol, atau kosongkan tautannya.',
            'target_wilayah.required' => 'Pilih minimal satu Business Unit.',
            'target_wilayah.min' => 'Pilih minimal satu Business Unit.',
            'target_wilayah.*.in' => 'Ada Business Unit di luar wewenang Anda.',
            'starts_at.required' => 'Tanggal mulai tayang wajib diisi.',
            'ends_at.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ]);
        $data['target_wilayah'] = array_values(array_map('intval', $data['target_wilayah']));
        $data['is_published'] = $request->boolean('is_published');
        return $data;
    }

    /** Wilayah yang dikelola admin yang sedang masuk (null = semua). */
    private function managed(): ?array
    {
        return Auth::user()->managedWilayahIds();
    }

    private function canManage(Announcement $a): bool
    {
        $ids = $this->managed();
        return $ids === null || array_diff(array_map('intval', (array) $a->target_wilayah), $ids) === [];
    }

    private function authorizeScope(Announcement $a): void
    {
        abort_unless($this->canManage($a), 403, 'Pengumuman ini juga menyasar Business Unit di luar wewenang Anda.');
    }

    private function logIfPublished(Announcement $a, Request $request, string $verb): void
    {
        if ($a->is_published) {
            $this->tracker->adminAction(Auth::user(), 'announce', $verb . ' "' . $a->title . '"', $request);
        }
    }
}
