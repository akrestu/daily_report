<?php

namespace App\Providers;

use App\Models\DailyReport;
use App\Models\JobComment;
use App\Models\JobPlan;
use App\Observers\DailyReportObserver;
use App\Observers\JobCommentObserver;
use App\Observers\JobPlanObserver;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        DailyReport::observe(DailyReportObserver::class);
        JobComment::observe(JobCommentObserver::class);
        JobPlan::observe(JobPlanObserver::class);
    }
}
