<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     */
    public function register(): void
    {
        // Add service container bindings here if needed
    }

    /**
     * Bootstrap application services.
     */
    public function boot(): void
    {
        Gate::define('manage-users', fn(User $user): bool => $user->role === 'admin');

        Gate::define('manage-updates', fn(User $user): bool => $user->role === 'admin');
		
		Gate::define('manage-templates', fn(User $user): bool => $user->role === 'admin');

        Gate::define('manage-categories', fn(User $user): bool => $user->role === 'admin');
    }
}
