<?php

namespace App\Providers;

use App\Services\ActivityTracker;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        // Log aktivitas: masuk, keluar, dan gagal masuk (lihat App\Services\ActivityTracker)
        Event::listen(Login::class, fn (Login $e) => app(ActivityTracker::class)->login($e->user, request()));
        Event::listen(Logout::class, fn (Logout $e) => app(ActivityTracker::class)->logout($e->user, request()));
        Event::listen(Failed::class, fn (Failed $e) => app(ActivityTracker::class)->failed($e->credentials['email'] ?? null, $e->user, request()));
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
