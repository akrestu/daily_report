<?php

namespace App\Providers;

use App\Http\Middleware\AdminOnly;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
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
        // Use Bootstrap for pagination
        Paginator::useBootstrap();
        
        // Register the AdminOnly middleware
        Route::aliasMiddleware('admin.only', AdminOnly::class);
    }
}
