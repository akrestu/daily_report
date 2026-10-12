<?php

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
