<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
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
        Model::preventLazyLoading(! app()->isProduction());
        Gate::define('manage-topology', fn (User $user): bool => in_array($user->role, ['super_admin', 'ict_head', 'ict_staff'], true));
        Gate::define('manage-documents', fn (User $user): bool => in_array($user->role, ['super_admin', 'ict_head', 'ict_staff'], true));
        Gate::define('approve-documents', fn (User $user): bool => in_array($user->role, ['super_admin', 'ict_head'], true));
        Gate::define('view-reports', fn (User $user): bool => in_array($user->role, ['super_admin', 'ict_head', 'ict_staff', 'auditor', 'viewer'], true));
    }
}
