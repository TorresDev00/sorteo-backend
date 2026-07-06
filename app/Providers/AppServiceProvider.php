<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use App\Listeners\LoginLogListener;
use App\Models\Registro;
use App\Observers\RegistroObserver;
use App\Models\LiberacionPremio;
use App\Observers\LiberacionPremioObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Schema::defaultStringLength(191);
        Event::listen(Login::class, [LoginLogListener::class, 'handle']);
        Event::listen(Failed::class, [LoginLogListener::class, 'handle']);
        Registro::observe(RegistroObserver::class);
        LiberacionPremio::observe(LiberacionPremioObserver::class);
    }
}