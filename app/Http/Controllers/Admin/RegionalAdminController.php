<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Wilayah;
use App\Services\ActivityTracker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Menu Admin > Admin wilayah (khusus admin utama):
 * akun admin yang hanya mengelola Business Unit tertentu.
 */
class RegionalAdminController extends Controller
{
    public function __construct(private ActivityTracker $tracker)
    {
        $this->middleware(['auth', 'can:super_admin']);
    }

    public function index()
    {
        return view('admin.regional-admins.index', [
            // Sebelum migrasi kolom admin_wilayah dijalankan, daftar masih kosong
            'admins' => !User::hasAdminScopeColumn() ? collect() : User::where('type', 'admin')->whereNotNull('admin_wilayah')
                ->orderBy('name')->get()->filter->isRegionalAdmin()->values(),
            'wilayah' => Wilayah::orderBy('id')->get(['id', 'nama', 'kode', 'english_name'])->keyBy('id'),
        ]);
    }

    public function create()
    {
        return view('admin.regional-admins.form', $this->formData(new User()));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);

        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->type = 'admin';
        $user->status = 1;
        $user->admin_wilayah = $data['wilayah'];
        // Wilayah "rumah" untuk tampilan (nama di sidebar); hak akses tetap dari admin_wilayah
        $user->wilayah_id = $data['wilayah'][0];
        $user->save();

        $this->log($request, 'membuat admin wilayah ' . $user->name . ' (' . $this->codes($data['wilayah']) . ')');
        alert()->success('Tersimpan', 'Admin wilayah ' . $user->name . ' sudah bisa masuk.');
        return redirect()->route('admin.regional-admins.index');
    }

    public function edit(User $user)
    {
        abort_unless($user->isRegionalAdmin(), 404);
        return view('admin.regional-admins.form', $this->formData($user));
    }

    public function update(Request $request, User $user)
    {
        abort_unless($user->isRegionalAdmin(), 404);
        $data = $this->validated($request, $user);

        $user->name = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->admin_wilayah = $data['wilayah'];
        $user->wilayah_id = $data['wilayah'][0];
        $user->save();

        $this->log($request, 'mengubah admin wilayah ' . $user->name . ' (' . $this->codes($data['wilayah']) . ')');
        alert()->success('Tersimpan', 'Admin wilayah ' . $user->name . ' diperbarui.');
        return redirect()->route('admin.regional-admins.index');
    }

    public function destroy(Request $request, User $user)
    {
        // Hanya akun admin wilayah; admin utama tidak pernah bisa dihapus dari sini
        abort_unless($user->isRegionalAdmin(), 404);
        $name = $user->name;
        UserRole::where('user_id', $user->id)->delete();
        $user->delete();

        $this->log($request, 'menghapus admin wilayah ' . $name);
        alert()->success('Terhapus', 'Akun admin wilayah ' . $name . ' dihapus.');
        return redirect()->route('admin.regional-admins.index');
    }

    private function formData(User $user): array
    {
        return [
            'u' => $user,
            'wilayah' => Wilayah::orderBy('id')->get(['id', 'nama', 'kode', 'english_name']),
        ];
    }

    private function validated(Request $request, ?User $user): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore(optional($user)->id)],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::min(8)],
            'wilayah' => ['required', 'array', 'min:1'],
            'wilayah.*' => ['integer', 'exists:wilayah,id'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah dipakai akun lain.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'wilayah.required' => 'Pilih minimal satu Business Unit.',
            'wilayah.min' => 'Pilih minimal satu Business Unit.',
        ]);
        $data['wilayah'] = array_values(array_unique(array_map('intval', $data['wilayah'])));
        sort($data['wilayah']);
        return $data;
    }

    private function codes(array $ids): string
    {
        return Wilayah::whereIn('id', $ids)->orderBy('id')->get(['id', 'nama', 'kode', 'english_name'])
            ->map->short_code->implode(', ');
    }

    private function log(Request $request, string $label): void
    {
        $this->tracker->adminAction(Auth::user(), 'admin_user', $label, $request);
    }
}
