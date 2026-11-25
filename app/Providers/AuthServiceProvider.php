<?php

namespace App\Providers;

use App\Models\Departemen;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use phpDocumentor\Reflection\Utils;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
        // Semua admin: admin utama dan admin wilayah (admin wilayah dibatasi lagi oleh middleware admin.scope)
        Gate::define('administrator', static function ($user){
            return $user->type == 'admin';
        });

        // Admin utama saja: tanpa batasan wilayah (menu Admin wilayah, SOP, impersonate, ubah data wilayah)
        Gate::define('super_admin', static function ($user){
            return $user->isSuperAdmin();
        });

        // Halaman SOP: semua pengguna kecuali admin wilayah
        Gate::define('sop_view', static function ($user){
            return !$user->isRegionalAdmin();
        });

        Gate::define('user', static function ($user){
            return $user->type == 'user';
        });

        // Kepala departemen yang anggarannya boleh ditampilkan
        // (wilayah di config sipekan.hidden_budget_wilayah_ids tidak termasuk).
        Gate::define('head_dept', static function ($user){
            $dept = $user->kepala_departemen;
            return $dept !== null && $dept->isBudgetVisible() && !\App\Models\FinanceLock::lockedFor($user);
        });

        // Pegawai yang laporan keuangannya masih terbuka (Admin > Akses keuangan)
        Gate::define('finance_open', static function ($user){
            return $user->type == 'user' && !\App\Models\FinanceLock::lockedFor($user);
        });

        Gate::define('finance', function ($user){
           return $user->hasRole('finance');
        });
        Gate::define('officers', function ($user){
           return $user->hasRole('officers');
        });

        Gate::define('ada_travel', function ($user){
           return $user->travel_account;
        });
        Gate::define('conference_finance', function ($user){
            $conference = false;
            if ($user->type == 'user' && $user->wilayah_id != 1 && $user->hasRole('finance')){
                $conference = true;
            }
           return $conference;
        });

        Gate::define('uni', static function ($user){
            return $user->wilayah_id == 1;
        });
    }
}
