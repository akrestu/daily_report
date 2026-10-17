<?php

namespace App\Support;

use App\Models\DailyReport;
use App\Models\JobPlan;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Data tambahan Beranda redesign yang belum dihitung DashboardController
 * (status laporan hari ini, tugas dari atasan, tiket aktif per status).
 */
class DashboardSummary
{
    public function __construct(private User $user)
    {
    }

    public function greeting(): string
    {
        $hour = Carbon::now()->hour;

        return match (true) {
            $hour < 11 => 'Selamat pagi',
            $hour < 15 => 'Selamat siang',
            $hour < 18 => 'Selamat sore',
            default => 'Selamat malam',
        };
    }

    public function submittedToday(): bool
    {
        return DailyReport::where('user_id', $this->user->id)
            ->whereDate('created_at', Carbon::today())
            ->exists();
    }

    /** Rencana kerja dari atasan yang belum dikonversi: ['count', 'overdue', 'items'] */
    public function assignedPlans(int $limit = 3): array
    {
        $base = JobPlan::where('assignee_id', $this->user->id)->where('status', 'assigned');

        return [
            'count' => (clone $base)->count(),
            'overdue' => (clone $base)->whereDate('due_date', '<', Carbon::today())->count(),
            'items' => (clone $base)->with('creator')->orderByRaw('due_date IS NULL, due_date')->limit($limit)->get(),
        ];
    }

    /** Tiket aktif per status + jumlah yang melewati SLA (admin / tim IT). */
    public function activeTickets(): array
    {
        $active = [Ticket::STATUS_OPEN, Ticket::STATUS_ASSIGNED, Ticket::STATUS_IN_PROGRESS, Ticket::STATUS_PENDING];
        $counts = Ticket::whereIn('status', $active)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return [
            'byStatus' => collect($active)->mapWithKeys(fn ($s) => [$s => (int) ($counts[$s] ?? 0)])->all(),
            'total' => (int) $counts->sum(),
            'breached' => Ticket::whereIn('status', $active)
                ->where(fn ($q) => $q->where('response_breached', true)->orWhere('resolution_breached', true))
                ->count(),
        ];
    }

    /**
     * Seri grafik dari data tren controller ([['date' => 'Oct 17', 'count' => n], ...] berakhir hari ini).
     * Label diganti tanggal Indonesia ("17 Okt"), titik terakhir = hari ini.
     */
    public static function trendSeries(array $trend): Collection
    {
        $days = count($trend);

        return collect($trend)->values()->map(fn ($point, $i) => [
            'label' => $i === $days - 1 ? 'Hari ini' : Format::dayMonth(Carbon::today()->subDays($days - 1 - $i)),
            'count' => (int) ($point['count'] ?? 0),
            'today' => $i === $days - 1,
        ]);
    }
}
