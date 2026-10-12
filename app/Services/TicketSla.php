<?php

namespace App\Services;

use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Support\WorkingCalendar;
use Illuminate\Support\Collection;

/**
 * Perhitungan SLA tiket (dalam jam kerja):
 * - Respons: tiket harus direspons (assign / ubah status / balasan publik) sebelum response_due_at.
 * - Penyelesaian: tiket harus "resolved" sebelum resolution_due_at.
 * Jam SLA berhenti saat status Menunggu Pemohon atau Selesai (menunggu konfirmasi);
 * lama jeda ditambahkan ke tenggat penyelesaian.
 */
class TicketSla
{
    const PAUSED_STATUSES = [Ticket::STATUS_PENDING, Ticket::STATUS_RESOLVED];

    const RUNNING_STATUSES = [Ticket::STATUS_OPEN, Ticket::STATUS_ASSIGNED, Ticket::STATUS_IN_PROGRESS];

    /**
     * Hitung tenggat dari waktu tiket dibuat + target SLA prioritasnya. Tidak menyimpan.
     */
    public function calculateDueDates(Ticket $ticket): void
    {
        $policy = SlaPolicy::forPriority($ticket->priority);
        if (! $policy) {
            return;
        }

        $start = $ticket->created_at ?? now();
        $ticket->response_due_at = WorkingCalendar::addWorkingMinutes($start, $policy->response_minutes);
        $ticket->resolution_due_at = WorkingCalendar::addWorkingMinutes(
            $start, $policy->resolution_minutes + (int) $ticket->sla_paused_minutes
        );

        if ($ticket->first_responded_at) {
            $ticket->response_breached = $ticket->first_responded_at->gt($ticket->response_due_at);
        }
    }

    /** Catat respons pertama tim IT (sekali saja). Tidak menyimpan. */
    public function markResponded(Ticket $ticket): void
    {
        if ($ticket->first_responded_at) {
            return;
        }

        $ticket->first_responded_at = now();
        if ($ticket->response_due_at) {
            $ticket->response_breached = now()->gt($ticket->response_due_at);
        }
    }

    /** Jeda / lanjutkan jam SLA saat status berubah. Tidak menyimpan. */
    public function onStatusChange(Ticket $ticket, ?string $from, string $to): void
    {
        $wasPaused = in_array($from, self::PAUSED_STATUSES, true);
        $isPaused = in_array($to, self::PAUSED_STATUSES, true);

        if (! $wasPaused && $isPaused) {
            $ticket->sla_paused_at = now();
            if ($to === Ticket::STATUS_RESOLVED && $ticket->resolution_due_at) {
                $ticket->resolution_breached = now()->gt($ticket->resolution_due_at);
            }
        } elseif ($wasPaused && in_array($to, self::RUNNING_STATUSES, true) && $ticket->sla_paused_at) {
            $ticket->sla_paused_minutes += WorkingCalendar::workingMinutesBetween($ticket->sla_paused_at, now());
            $ticket->sla_paused_at = null;
            $this->calculateDueDates($ticket);
            // Breach dinilai ulang saat resolved berikutnya / oleh pengecekan berkala
            $ticket->resolution_breached = false;
        }
    }

    /**
     * Tandai tiket aktif yang baru saja melewati tenggat. Return [[Ticket, 'response'|'resolution'], ...]
     */
    public function evaluateBreaches(): Collection
    {
        $breaches = collect();

        Ticket::whereIn('status', self::RUNNING_STATUSES)
            ->whereNull('first_responded_at')
            ->where('response_breached', false)
            ->where('response_due_at', '<=', now())
            ->get()
            ->each(function (Ticket $ticket) use ($breaches) {
                $ticket->forceFill(['response_breached' => true])->saveQuietly();
                $breaches->push([$ticket, 'response']);
            });

        Ticket::whereIn('status', self::RUNNING_STATUSES)
            ->where('resolution_breached', false)
            ->where('resolution_due_at', '<=', now())
            ->get()
            ->each(function (Ticket $ticket) use ($breaches) {
                $ticket->forceFill(['resolution_breached' => true])->saveQuietly();
                $breaches->push([$ticket, 'resolution']);
            });

        return $breaches;
    }

    /**
     * Ringkasan status SLA untuk tampilan.
     *
     * @return array{response: array, resolution: array}
     */
    public static function summary(Ticket $ticket): array
    {
        $now = now();

        $response = ['due' => $ticket->response_due_at, 'state' => 'none', 'remaining' => null];
        if ($ticket->response_due_at) {
            if ($ticket->first_responded_at) {
                $response['state'] = $ticket->response_breached ? 'breached' : 'met';
            } elseif (in_array($ticket->status, [Ticket::STATUS_CLOSED, Ticket::STATUS_CANCELLED], true)) {
                $response['state'] = 'none';
            } else {
                $response['state'] = $now->gt($ticket->response_due_at) ? 'breached' : 'running';
                $response['remaining'] = WorkingCalendar::workingMinutesBetween($now, $ticket->response_due_at);
            }
        }

        $resolution = ['due' => $ticket->resolution_due_at, 'state' => 'none', 'remaining' => null];
        if ($ticket->resolution_due_at) {
            if (in_array($ticket->status, [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED], true)) {
                $resolution['state'] = $ticket->resolution_breached ? 'breached' : 'met';
            } elseif ($ticket->status === Ticket::STATUS_CANCELLED) {
                $resolution['state'] = 'none';
            } elseif ($ticket->status === Ticket::STATUS_PENDING) {
                $resolution['state'] = 'paused';
                $resolution['remaining'] = WorkingCalendar::workingMinutesBetween($ticket->sla_paused_at ?? $now, $ticket->resolution_due_at);
            } else {
                $resolution['state'] = $now->gt($ticket->resolution_due_at) ? 'breached' : 'running';
                $resolution['remaining'] = WorkingCalendar::workingMinutesBetween($now, $ticket->resolution_due_at);
            }
        }

        return ['response' => $response, 'resolution' => $resolution];
    }

    public static function formatMinutes(?int $minutes): string
    {
        if ($minutes === null) {
            return '-';
        }

        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        return trim(($hours ? $hours.'j ' : '').($mins || ! $hours ? $mins.'m' : ''));
    }
}
