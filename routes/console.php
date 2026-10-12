<?php

use App\Services\TicketSla;
use App\Services\TicketWorkflow;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tickets:auto-close', function (TicketWorkflow $workflow) {
    $closed = $workflow->autoCloseResolved();
    $this->info("{$closed} tiket ditutup otomatis.");
})->purpose('Tutup tiket "resolved" yang tidak dikonfirmasi pemohon dalam N hari kerja');

Schedule::command('tickets:auto-close')->hourly()->withoutOverlapping();

Artisan::command('tickets:sla-check', function (TicketSla $sla, TicketWorkflow $workflow) {
    $breaches = $sla->evaluateBreaches();
    $breaches->each(fn ($breach) => $workflow->notifySlaBreach(...$breach));
    $this->info($breaches->count().' pelanggaran SLA baru.');
})->purpose('Tandai & beri notifikasi tiket yang melewati tenggat SLA');

Schedule::command('tickets:sla-check')->everyFiveMinutes()->withoutOverlapping();
