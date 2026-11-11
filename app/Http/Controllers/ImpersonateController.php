<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ImpersonateController extends Controller
{
    public function impersonate($id)
    {
        // Hanya admin asli (bukan yang sedang impersonate) yang boleh masuk sebagai user lain.
        abort_unless(Gate::allows('administrator'), 403);
        abort_if(session()->has('impersonate'), 403, 'Sedang dalam mode impersonate.');

        $target = User::findOrFail($id);
        abort_if($target->id === Auth::id(), 422, 'Tidak bisa impersonate diri sendiri.');

        $adminId = Auth::id();
        Auth::loginUsingId($target->id);
        session()->put('impersonate', $adminId);

        return redirect()->route('dashboard');
    }

    public function destroy()
    {
        // Kembalikan ke akun admin asal yang tersimpan di sesi, bukan ke user tertentu.
        $adminId = session()->pull('impersonate');
        abort_unless($adminId, 403);

        $admin = User::find($adminId);
        if (!$admin || $admin->type !== 'admin') {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
            return redirect('/');
        }

        Auth::loginUsingId($admin->id);

        return redirect()->route('wilayah.index');
    }
}
