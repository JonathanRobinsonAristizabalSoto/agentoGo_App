<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
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
    }
}
