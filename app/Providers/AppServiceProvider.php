<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        // Use Bootstrap pagination views across the app for consistent styling
        Paginator::useBootstrapFive();

        // Treat Admin role as super admin: grant all permissions by default.
        // This ensures adding permission checks will not lock out Admin users.
        Gate::before(function ($user, string $ability) {
            return $user instanceof User && $user->hasRole('Admin')
                ? true
                : null;
        });
    }
}
