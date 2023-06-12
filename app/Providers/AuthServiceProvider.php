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
        Gate::define('administrator', static function ($user){
            return $user->type == 'admin';
        });

        Gate::define('user', static function ($user){
            return $user->type == 'user';
        });

        Gate::define('head_dept', static function ($user){
            return $user->kepala_departemen->kepala_departemen ?? '';
        });

        Gate::define('finance', function ($user){
           return $user->hasRole('finance');
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
    }
}
