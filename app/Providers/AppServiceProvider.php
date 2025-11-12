<?php

namespace App\Providers;

use App\Http\Middleware\AdminOnly;
use Carbon\Carbon;
use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        
        // Set Carbon locale to Indonesian and timezone to Asia/Jakarta
        Carbon::setLocale('id');
        
        // Ensure all Carbon instances use Asia/Jakarta timezone
        if (config('app.timezone')) {
            date_default_timezone_set(config('app.timezone'));
        }
        
        // Log slow queries for performance monitoring
        if (config('app.env') === 'local') {
            DB::listen(function (QueryExecuted $query) {
                if ($query->time > 500) { // Log queries slower than 500ms
                    Log::warning('Slow query detected', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time . 'ms'
                    ]);
                }
            });
        }
    }
}
