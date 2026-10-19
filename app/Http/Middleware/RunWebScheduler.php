<?php

namespace App\Http\Middleware;

use App\Support\WebScheduler;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Jalankan tugas berkala (WebScheduler) setelah respons terkirim — pengganti cron di hosting tanpa scheduler */
class RunWebScheduler
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! config('app.web_scheduler') || app()->runningUnitTests()) {
            return;
        }

        app(WebScheduler::class)->runDue();
    }
}
