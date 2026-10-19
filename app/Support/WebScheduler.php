<?php

namespace App\Support;

use App\Services\TicketSla;
use App\Services\TicketWorkflow;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Pengganti cron untuk hosting tanpa scheduler (Plesk tanpa SSH): tugas berkala dijalankan dari lalu lintas web,
 * setelah respons terkirim (middleware RunWebScheduler::terminate). Tiap tugas maksimal sekali per interval
 * (Cache::add sebagai kunci atomik), sehingga aman walau banyak request bersamaan.
 * Matikan dengan WEB_SCHEDULER=false bila cron server sudah aktif (routes/console.php tetap berlaku).
 */
class WebScheduler
{
    /** nama => [interval detik, callable] */
    private function tasks(): array
    {
        return [
            'tickets:sla-check' => [300, function () {
                $workflow = app(TicketWorkflow::class);
                app(TicketSla::class)->evaluateBreaches()->each(fn ($breach) => $workflow->notifySlaBreach(...$breach));
            }],
            'tickets:auto-close' => [3600, fn () => app(TicketWorkflow::class)->autoCloseResolved()],
            'notifications:cleanup' => [86400, fn () => Artisan::call('notifications:cleanup', ['--force' => true])],
        ];
    }

    public function runDue(): void
    {
        foreach ($this->tasks() as $name => [$interval, $task]) {
            if (! Cache::add('web-scheduler:'.$name, now()->timestamp, $interval)) {
                continue; // belum waktunya / sedang dijalankan request lain
            }

            try {
                $task();
            } catch (\Throwable $e) {
                Log::error("WebScheduler {$name} gagal: ".$e->getMessage());
            }
        }
    }
}
