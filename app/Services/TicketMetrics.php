<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use App\Support\WorkingCalendar;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * KPI layanan IT untuk dashboard tiket. Semua durasi dalam menit JAM KERJA.
 */
class TicketMetrics
{
    const ACTIVE_STATUSES = [Ticket::STATUS_OPEN, Ticket::STATUS_ASSIGNED, Ticket::STATUS_IN_PROGRESS, Ticket::STATUS_PENDING];

    private ?Collection $created = null;

    private ?Collection $resolved = null;

    public function __construct(
        private CarbonImmutable $from,
        private CarbonImmutable $to,
        private ?int $jobSiteId = null,
    ) {}

    public static function for(CarbonInterface $from, CarbonInterface $to, ?int $jobSiteId = null): self
    {
        return new self(CarbonImmutable::instance($from)->startOfDay(), CarbonImmutable::instance($to)->endOfDay(), $jobSiteId);
    }

    public function summary(): array
    {
        $created = $this->createdInPeriod();
        $resolved = $this->resolvedInPeriod();
        $backlog = $this->query()->whereIn('status', self::ACTIVE_STATUSES)->get();
        $rated = $this->query()->whereBetween('csat_submitted_at', [$this->from, $this->to])->whereNotNull('csat_rating')->get();

        $responded = $created->filter(fn (Ticket $t) => $t->first_responded_at && $t->response_due_at);
        $resolvedWithSla = $resolved->filter(fn (Ticket $t) => $t->resolution_due_at);

        return [
            'created' => $created->count(),
            'resolved' => $resolved->count(),
            'backlog' => $backlog->count(),
            'backlog_breached' => $backlog->filter(fn (Ticket $t) => $this->isResolutionOverdue($t))->count(),
            'awaiting_approval' => $backlog->filter(fn (Ticket $t) => $t->awaitingApproval())->count(),
            'unassigned' => $backlog->whereNull('assigned_to')->count(),
            'mttr_minutes' => $this->average($resolved->map(fn (Ticket $t) => $this->resolutionMinutes($t))),
            'response_minutes' => $this->average($responded->map(fn (Ticket $t) => $this->responseMinutes($t))),
            'response_sla_pct' => $this->percentage($responded->where('response_breached', false)->count(), $responded->count()),
            'resolution_sla_pct' => $this->percentage($resolvedWithSla->where('resolution_breached', false)->count(), $resolvedWithSla->count()),
            'csat_avg' => $rated->count() ? round($rated->avg('csat_rating'), 2) : null,
            'csat_count' => $rated->count(),
            'csat_satisfied_pct' => $this->percentage($rated->where('csat_rating', '>=', 4)->count(), $rated->count()),
            'reopen_pct' => $this->percentage($resolved->where('reopen_count', '>', 0)->count(), $resolved->count()),
        ];
    }

    /** Tiket masuk vs selesai per hari */
    public function dailyTrend(): array
    {
        $created = $this->createdInPeriod()->countBy(fn (Ticket $t) => $t->created_at->toDateString());
        $resolved = $this->resolvedInPeriod()->countBy(fn (Ticket $t) => $t->resolved_at->toDateString());

        $labels = $createdSeries = $resolvedSeries = [];
        for ($day = $this->from; $day->lte($this->to); $day = $day->addDay()) {
            $key = $day->toDateString();
            $labels[] = $day->format('d M');
            $createdSeries[] = $created[$key] ?? 0;
            $resolvedSeries[] = $resolved[$key] ?? 0;
        }

        return ['labels' => $labels, 'created' => $createdSeries, 'resolved' => $resolvedSeries];
    }

    public function breakdowns(): array
    {
        $created = $this->createdInPeriod()->load('category');

        return [
            'category' => $created->countBy(fn (Ticket $t) => $t->category->name ?? '-')->sortDesc()->all(),
            'priority' => collect(Ticket::PRIORITIES)->mapWithKeys(fn ($label, $key) => [$label => $created->where('priority', $key)->count()])->all(),
            'type' => collect(Ticket::TYPES)->mapWithKeys(fn ($label, $key) => [$label => $created->where('type', $key)->count()])->all(),
            'department' => $created->countBy('requester_department')->sortDesc()->take(8)->all(),
        ];
    }

    /** Kinerja per teknisi: beban aktif saat ini + penyelesaian dalam periode */
    public function technicians(): Collection
    {
        $resolved = $this->resolvedInPeriod()->groupBy('assigned_to');
        $active = $this->query()->whereIn('status', self::ACTIVE_STATUSES)->whereNotNull('assigned_to')->get()->groupBy('assigned_to');
        $ratings = $this->query()->whereBetween('csat_submitted_at', [$this->from, $this->to])->whereNotNull('csat_rating')->get()->groupBy('assigned_to');

        $userIds = $resolved->keys()->merge($active->keys())->filter()->unique();

        return User::whereIn('id', $userIds)->orderBy('name')->get()->map(function (User $user) use ($resolved, $active, $ratings) {
            $done = $resolved->get($user->id, collect());
            $withSla = $done->filter(fn (Ticket $t) => $t->resolution_due_at);
            $open = $active->get($user->id, collect());
            $rated = $ratings->get($user->id, collect());

            return [
                'name' => $user->name,
                'active' => $open->count(),
                'overdue' => $open->filter(fn (Ticket $t) => $this->isResolutionOverdue($t))->count(),
                'resolved' => $done->count(),
                'mttr_minutes' => $this->average($done->map(fn (Ticket $t) => $this->resolutionMinutes($t))),
                'sla_pct' => $this->percentage($withSla->where('resolution_breached', false)->count(), $withSla->count()),
                'csat_avg' => $rated->count() ? round($rated->avg('csat_rating'), 1) : null,
            ];
        })->sortByDesc('resolved')->values();
    }

    /** Ulasan CSAT terbaru dalam periode */
    public function recentFeedback(int $limit = 8): Collection
    {
        return $this->query()->whereBetween('csat_submitted_at', [$this->from, $this->to])
            ->whereNotNull('csat_rating')->with('assignedUser')
            ->latest('csat_submitted_at')->limit($limit)->get();
    }

    /** Durasi penyelesaian (jam kerja), dikurangi waktu jeda SLA (menunggu pemohon / persetujuan) */
    public function resolutionMinutes(Ticket $ticket): int
    {
        return max(0, WorkingCalendar::workingMinutesBetween($ticket->created_at, $ticket->resolved_at) - (int) $ticket->sla_paused_minutes);
    }

    /** Waktu respons pertama (jam kerja), dihitung sejak persetujuan atasan diverifikasi bila ada */
    public function responseMinutes(Ticket $ticket): int
    {
        $start = $ticket->approval_verified_at && $ticket->approval_verified_at->lt($ticket->first_responded_at)
            ? $ticket->approval_verified_at
            : $ticket->created_at;

        return WorkingCalendar::workingMinutesBetween($start, $ticket->first_responded_at);
    }

    private function isResolutionOverdue(Ticket $ticket): bool
    {
        return $ticket->resolution_breached
            || (! $ticket->sla_paused_at && $ticket->status !== Ticket::STATUS_PENDING
                && $ticket->resolution_due_at && $ticket->resolution_due_at->isPast());
    }

    private function query(): Builder
    {
        return Ticket::query()->when($this->jobSiteId, fn ($q) => $q->where('job_site_id', $this->jobSiteId));
    }

    private function createdInPeriod(): Collection
    {
        return $this->created ??= $this->query()->whereBetween('created_at', [$this->from, $this->to])->get();
    }

    private function resolvedInPeriod(): Collection
    {
        return $this->resolved ??= $this->query()->whereBetween('resolved_at', [$this->from, $this->to])->get();
    }

    private function average(Collection $values): ?int
    {
        return $values->count() ? (int) round($values->avg()) : null;
    }

    private function percentage(int $part, int $total): ?float
    {
        return $total ? round($part / $total * 100, 1) : null;
    }
}
