<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\Employee;
use App\Models\Client;
use App\Models\Reservation;
use App\Policies\BusinessPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\ClientPolicy;
use App\Policies\ReservationPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Mapeo de políticas.
     */
    protected $policies = [
        Business::class => BusinessPolicy::class,
        Employee::class => EmployeePolicy::class,
        Client::class => ClientPolicy::class,
        Reservation::class => ReservationPolicy::class,
    ];

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
        // Registrar las policies para que el Gate las use
        Gate::policy(Business::class, BusinessPolicy::class);
        Gate::policy(Employee::class, EmployeePolicy::class);
        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(Reservation::class, ReservationPolicy::class);

        // Rate limiter por negocio: si hay header X-Business-Id lo usa, si no usa user id o ip
        RateLimiter::for('business', function (Request $request) {
            $key = $request->header('X-Business-Id') ?: ($request->user()?->id ?: $request->ip());
            return Limit::perMinute(60)->by($key);
        });

        // Rate limiter para intentos de login (protección contra fuerza bruta)
        RateLimiter::for('login', function (Request $request) {
            $key = $request->input('email') ?: $request->ip();
            // Límite corto para prevenir intentos bruteforce
            return Limit::perMinute((int) env('LOGIN_THROTTLE_PER_MIN', 5))->by($key);
        });

        // Registrar middleware de cabeceras de seguridad en el grupo 'api'
        if ($this->app->has('router')) {
            $this->app['router']->pushMiddlewareToGroup('api', \App\Http\Middleware\SecurityHeaders::class);
        }
    }
}
