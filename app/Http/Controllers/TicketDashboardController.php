<?php

namespace App\Http\Controllers;

use App\Models\JobSite;
use App\Models\Ticket;
use App\Services\TicketMetrics;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class TicketDashboardController extends Controller
{
    const PERIODS = [
        'this_month' => 'Bulan ini',
        'last_month' => 'Bulan lalu',
        'last_30' => '30 hari terakhir',
        'this_year' => 'Tahun ini',
        'custom' => 'Pilih tanggal',
    ];

    public function index(Request $request)
    {
        $this->authorize('viewAny', Ticket::class);

        $validated = $request->validate([
            'period' => 'nullable|in:'.implode(',', array_keys(self::PERIODS)),
            'from' => 'nullable|required_if:period,custom|date',
            'to' => 'nullable|required_if:period,custom|date|after_or_equal:from',
            'job_site' => 'nullable|exists:job_sites,id',
        ]);

        $period = $validated['period'] ?? 'this_month';
        [$from, $to] = $this->range($period, $validated);

        $metrics = TicketMetrics::for($from, $to, $validated['job_site'] ?? null);

        return view('tickets.dashboard', [
            'period' => $period,
            'periods' => self::PERIODS,
            'from' => $from,
            'to' => $to,
            'jobSites' => JobSite::where('is_active', true)->orderBy('name')->get(),
            'summary' => $metrics->summary(),
            'trend' => $metrics->dailyTrend(),
            'breakdowns' => $metrics->breakdowns(),
            'technicians' => $metrics->technicians(),
            'feedback' => $metrics->recentFeedback(),
        ]);
    }

    /** @return array{0: CarbonImmutable, 1: CarbonImmutable} */
    private function range(string $period, array $input): array
    {
        $today = CarbonImmutable::today();

        [$from, $to] = match ($period) {
            'last_month' => [$today->subMonthNoOverflow()->startOfMonth(), $today->subMonthNoOverflow()->endOfMonth()],
            'last_30' => [$today->subDays(29), $today],
            'this_year' => [$today->startOfYear(), $today],
            'custom' => [CarbonImmutable::parse($input['from']), CarbonImmutable::parse($input['to'])],
            default => [$today->startOfMonth(), $today],
        };

        // Batasi grafik harian maksimal 1 tahun
        if ($from->diffInDays($to) > 366) {
            $from = $to->subDays(366);
        }

        return [$from, $to];
    }
}
