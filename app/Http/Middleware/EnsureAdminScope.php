<?php

namespace App\Http\Middleware;

use App\Models\Departemen;
use App\Models\DepartmentExpense;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

/**
 * Admin wilayah hanya boleh membuka/mengubah data di Business Unit yang ia kelola.
 * Admin utama dan pengguna biasa tidak terpengaruh (pengguna biasa sudah ditolak oleh can:administrator).
 *
 * Wilayah sasaran dibaca dari parameter rute atau isian form, sesuai nama rute.
 */
class EnsureAdminScope
{
    private const DENIED = 'Wilayah ini di luar wewenang Anda.';

    /** Hanya admin utama: mengubah data wilayah itu sendiri, dan impor Excel (isi file bisa berisi wilayah mana pun). */
    private const SUPER_ONLY = [
        'wilayah.edit', 'wilayah.update',
        'wilayah.user.import', 'wilayah.salary.import', 'wilayah.user.salary.import',
    ];

    /** Parameter rute {id} adalah id wilayah. */
    private const WILAYAH_PARAM = [
        'wilayah.fingerprint-machine', 'wilayah.fingerprint-machine.create', 'wilayah.fingerprint-machine.store',
        'wilayah.fingerprint-machine.edit', 'wilayah.fingerprint-machine.update', 'wilayah.fingerprint-machine.destroy',
        'wilayah.fingerprint-machine.test',
        'wilayah.departemen', 'wilayah.departemen.tambah', 'wilayah.pengguna', 'wilayah.pengguna.tambah',
        'wilayah.roles', 'wilayah.roles.tambah', 'wilayah.salary', 'wilayah.salary.tambah',
        'wilayah.department-expense', 'wilayah.department-expense.tambah',
    ];

    /** Isian form wilayah_id menentukan wilayah data baru. */
    private const WILAYAH_INPUT = [
        'wilayah.departemen.simpan', 'wilayah.pengguna.simpan', 'wilayah.roles.simpan',
        'wilayah.salary.simpan', 'wilayah.department-expense.simpan',
    ];

    public function handle(Request $request, Closure $next)
    {
        $admin = $request->user();
        if (!$admin || !$admin->isRegionalAdmin()) {
            return $next($request);
        }

        $route = $request->route();
        $name = $route->getName();
        abort_if(in_array($name, self::SUPER_ONLY, true), 403, 'Hanya admin utama yang boleh melakukan ini.');

        // Wilayah langsung
        if ($route->hasParameter('id_wilayah')) {
            $this->allowWilayah($admin, $route->parameter('id_wilayah'));
        }
        if (in_array($name, self::WILAYAH_PARAM, true)) {
            $this->allowWilayah($admin, $route->parameter('id'));
        }
        if (in_array($name, self::WILAYAH_INPUT, true)) {
            $this->allowWilayah($admin, $request->input('wilayah_id'));
        }

        // Lewat data lain
        switch ($name) {
            case 'wilayah.departemen.edit':
                $this->allowWilayah($admin, optional(Departemen::find($route->parameter('id')))->wilayah_id);
                break;
            case 'wilayah.departemen.update':
                $this->allowWilayah($admin, optional(Departemen::find($request->input('id')))->wilayah_id);
                // Kepala departemen harus pegawai dari wilayah yang dikelola juga
                if ($request->filled('edit_kepala_departemen')) {
                    $this->allowUser($admin, $request->input('edit_kepala_departemen'));
                }
                break;
            case 'wilayah.departemen.simpan':
                if ($request->filled('kepala_departemen')) {
                    $this->allowUser($admin, $request->input('kepala_departemen'));
                }
                break;
            case 'wilayah.department-expense.edit':
                $this->allowWilayah($admin, optional(DepartmentExpense::find($route->parameter('id')))->wilayah_id);
                break;
            case 'wilayah.department-expense.update':
                $this->allowWilayah($admin, optional(DepartmentExpense::find($request->input('id')))->wilayah_id);
                break;
            case 'wilayah.pengguna.edit':
            case 'wilayah.pengguna.hapus':
            case 'users.aktifkan':
            case 'users.non-aktifkan':
                $this->allowUser($admin, $route->parameter('id'));
                break;
            case 'wilayah.pengguna.update':
                $this->allowUser($admin, $request->input('id'));
                break;
            case 'wilayah.pengguna.reset_password':
            case 'wilayah.fingerprint-machine.connect-user':
                $this->allowUser($admin, $request->input('user_id'));
                break;
            case 'wilayah.fingerprint-machine.hubungkan-user':
                $this->allowUser($admin, $route->parameter('id_user'));
                break;
        }

        return $next($request);
    }

    private function allowWilayah(User $admin, $wilayahId): void
    {
        abort_unless($admin->managesWilayah($wilayahId), 403, self::DENIED);
    }

    /** Pengguna sasaran harus di wilayah yang dikelola, dan bukan akun admin. */
    private function allowUser(User $admin, $userId): void
    {
        $target = User::find($userId);
        abort_unless($target, 404, 'Pengguna tidak ditemukan.');
        abort_if($target->type === 'admin', 403, 'Akun admin hanya bisa diubah oleh admin utama.');
        $this->allowWilayah($admin, $target->wilayah_id);
    }
}
