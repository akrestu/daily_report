<?php

namespace App\Providers;

use App\Models\DailyReport;
use App\Policies\DailyReportPolicy;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        DailyReport::class => DailyReportPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        
        // Define gates for approval capabilities
        Gate::define('approve-reports', function (User $user) {
            // Admin, department heads, and leaders can approve reports
            return $user->isAdmin() || $user->isDepartmentHead() || $user->isLeader();
        });
        
        // Define gate for admin panel access - show for admin role or role_id 1
        Gate::define('view-admin-panel', function (User $user) {
            return $user->isAdmin() || $user->role_id === 1;
        });
    }
} 