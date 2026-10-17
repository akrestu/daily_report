{{-- Beranda — mockup: DashMobile/DashDesktop (karyawan), DashApprover* (atasan), DashAdmin* (admin) --}}
@use('App\Support\DashboardSummary')
@use('App\Support\Format')
@php
    $summary = new DashboardSummary($user);
    $isAdmin = $user->isAdmin();
    $isApprover = ! $isAdmin && $user->getRoleLevel() >= 2;
    $canCreate = ! $isAdmin && ! $user->isLevel8();
    $approvals = $isApprover ? ($needsApproval ?? collect()) : collect();
    $plans = $canCreate ? $summary->assignedPlans() : null;
    $tickets = \App\Policies\TicketPolicy::isTriager($user) ? $summary->activeTickets() : null;
    $submitted = $canCreate ? $summary->submittedToday() : null;
    $trend = DashboardSummary::trendSeries($myReportTrend ?? $reportTrend ?? []);
    $recent = $isAdmin ? ($recentReports ?? collect()) : ($myRecentReports ?? collect());
    $firstName = \Illuminate\Support\Str::before($user->name, ' ') ?: $user->name;
    $approvalLabel = $approvals->count() >= 5 ? '5+' : $approvals->count();
@endphp

<x-app-layout title="Beranda">
    {{-- Kartu sambutan --}}
    <x-sg.brand-panel class="mb-3 mb-lg-4">
        <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between">
            <div class="min-w-0">
                <div class="small fw-semibold" style="color:var(--sg-accent)">
                    {{ Format::longDate(now()) }}{{ $user->jobSite ? ' · '.$user->jobSite->name : '' }}
                </div>
                <h2 class="h3 fw-bold mb-1 mt-1">{{ $summary->greeting() }}, {{ $firstName }}</h2>
                <div class="d-flex flex-wrap align-items-center gap-2 small opacity-75 mb-2">
                    <x-sg.badge-level :user="$user" />{{ $user->department?->name }}
                </div>
                <p class="mb-0">
                    @if ($isAdmin)
                        Ringkasan organisasi per {{ now()->format('H:i') }} WIB.
                    @elseif ($approvals->isNotEmpty())
                        <strong>{{ $approvalLabel }} laporan</strong> menunggu persetujuan Anda.
                    @elseif ($canCreate && ! $submitted)
                        Laporan hari ini belum dibuat. Batas kirim pukul 17:00 WIB.
                    @elseif ($canCreate)
                        Laporan hari ini sudah terkirim. Terima kasih.
                    @else
                        Tidak ada laporan yang menunggu persetujuan Anda.
                    @endif
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                @if ($approvals->isNotEmpty())
                    <a href="{{ route('daily-reports.pending') }}" class="btn btn-accent"><i class="bi bi-check2-square" aria-hidden="true"></i> Buka Antrian</a>
                @endif
                @if ($canCreate)
                    <a href="{{ route('daily-reports.create') }}" @class(['btn', 'btn-accent' => $approvals->isEmpty(), 'btn-outline-light' => $approvals->isNotEmpty()])>
                        <i class="bi bi-plus-lg" aria-hidden="true"></i> Buat Laporan
                    </a>
                @endif
            </div>
        </div>
    </x-sg.brand-panel>

    {{-- KPI --}}
    <section aria-label="Ringkasan" class="sg-kpi-grid mb-3 mb-lg-4">
        @if ($isAdmin)
            <x-sg.kpi label="Pengguna terdaftar" :value="Format::number($totalUsers ?? 0)" :note="Format::number($adminStats['totalDepartments'] ?? 0).' departemen'" />
            <x-sg.kpi label="Laporan bulan ini" :value="Format::number($reportsThisMonth ?? 0)" :note="Format::number($reportsToday ?? 0).' hari ini'" />
            <x-sg.kpi label="Approval tertunda" :value="Format::number($pendingReports ?? 0)" note="Belum mulai, belum ditolak" note-tone="pending" />
            <x-sg.kpi label="Tiket IT aktif" :value="Format::number($tickets['total'] ?? 0)"
                      :note="($tickets['breached'] ?? 0) ? $tickets['breached'].' melewati SLA' : 'Semua dalam SLA'"
                      :note-tone="($tickets['breached'] ?? 0) ? 'rejected' : 'approved'" :href="route('tickets.index')" />
        @else
            <x-sg.kpi label="Menunggu approval" :value="Format::number($myPendingReports ?? 0)" note="Laporan Anda & sebagai PIC" note-tone="pending" />
            <x-sg.kpi label="Dalam proses" :value="Format::number($myInProgressReports ?? 0)" note="Sedang dikerjakan" />
            <x-sg.kpi label="Selesai" :value="Format::number($myCompletedReports ?? 0)" note="Sudah disetujui" note-tone="approved" />
            <x-sg.kpi label="Ditolak" :value="Format::number($myRejectedReports ?? 0)"
                      :note="($myRejectedReports ?? 0) ? 'Perlu revisi' : 'Tidak ada'" :note-tone="($myRejectedReports ?? 0) ? 'rejected' : null" />
        @endif
    </section>

    <div class="row g-3 g-lg-4 mb-3 mb-lg-4">
        {{-- Grafik aktivitas --}}
        <div class="col-lg-8">
            <section class="card p-3 p-lg-4 h-100" aria-labelledby="trend-title">
                <div class="mb-3">
                    <h2 id="trend-title" class="h6 fw-bold mb-0">Aktivitas {{ $trend->count() }} hari</h2>
                    <div class="small text-muted">{{ Format::number($trend->sum('count')) }} laporan {{ $isAdmin ? 'di seluruh organisasi' : 'Anda' }}</div>
                </div>
                <div style="height:220px">
                    <canvas id="sg-trend" role="img" aria-label="Grafik jumlah laporan per hari, {{ $trend->count() }} hari terakhir"
                            data-series='@json($trend)'></canvas>
                </div>
            </section>
        </div>

        {{-- Kolom samping per peran --}}
        <div class="col-lg-4 d-flex flex-column gap-3 gap-lg-4">
            @if ($isApprover)
                <section class="card h-100" aria-labelledby="approval-title">
                    <div class="d-flex justify-content-between align-items-center p-3 pb-2">
                        <h2 id="approval-title" class="h6 fw-bold mb-0">Menunggu persetujuan Anda</h2>
                        @if ($approvals->isNotEmpty())<span class="sg-count">{{ $approvalLabel }}</span>@endif
                    </div>
                    @forelse ($approvals as $report)
                        <a href="{{ route('daily-reports.show', $report) }}" class="d-flex gap-2 align-items-center px-3 py-2 text-reset text-decoration-none border-top">
                            <x-sg.avatar :user="$report->user" :size="36" />
                            <span class="min-w-0 flex-grow-1">
                                <span class="d-block fw-semibold sg-clamp-1">{{ $report->job_name }}</span>
                                <span class="d-block small text-muted sg-clamp-1">{{ $report->user?->name }} · {{ $report->created_at->diffForHumans() }}</span>
                            </span>
                            <x-sg.badge-level :user="$report->user" short />
                        </a>
                    @empty
                        <x-sg.empty icon="bi-check2-all" title="Antrian kosong" text="Semua laporan tim sudah ditinjau." class="py-4" />
                    @endforelse
                    @if ($approvals->isNotEmpty())
                        <a href="{{ route('daily-reports.pending') }}" class="sg-notif-menu__foot border-top">Buka antrian persetujuan</a>
                    @endif
                </section>
            @endif

            @if ($plans)
                <section class="card h-100" aria-labelledby="plans-title">
                    <div class="d-flex justify-content-between align-items-center p-3 pb-2">
                        <h2 id="plans-title" class="h6 fw-bold mb-0">Tugas dari atasan</h2>
                        <a href="{{ route('job-plans.assigned-to-me') }}" class="small fw-semibold text-reset">Semua</a>
                    </div>
                    @forelse ($plans['items'] as $plan)
                        @php($late = $plan->due_date && $plan->due_date->isBefore(today()))
                        <a href="{{ route('job-plans.show', $plan) }}" class="d-block px-3 py-2 text-reset text-decoration-none border-top">
                            <span class="d-block fw-semibold sg-clamp-1">{{ $plan->job_name }}</span>
                            <span @class(['d-block small', 'text-danger fw-semibold' => $late, 'text-muted' => ! $late])>
                                @if ($late)
                                    Lewat {{ $plan->due_date->diffInDays(today()) }} hari · due {{ Format::dayMonth($plan->due_date) }}
                                @elseif ($plan->due_date)
                                    Due {{ Format::date($plan->due_date) }}
                                @else
                                    Dari {{ $plan->creator?->name }}
                                @endif
                            </span>
                        </a>
                    @empty
                        <x-sg.empty icon="bi-calendar-check" title="Tidak ada tugas" text="Rencana kerja dari atasan akan muncul di sini." class="py-4" />
                    @endforelse
                </section>
            @endif

            @if ($isAdmin && $tickets)
                <section class="card p-3 h-100" aria-labelledby="tickets-title">
                    <h2 id="tickets-title" class="h6 fw-bold mb-3">Tiket aktif per status</h2>
                    @foreach ($tickets['byStatus'] as $status => $count)
                        <div class="d-flex justify-content-between align-items-center py-2 border-top">
                            <x-sg.badge-status type="ticket_status" :value="$status" />
                            <span class="fw-bold sg-num">{{ Format::number($count) }}</span>
                        </div>
                    @endforeach
                    <div class="d-flex justify-content-between align-items-center py-2 border-top">
                        <span class="sg-sla sg-sla--breach"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>Melewati SLA</span>
                        <span class="fw-bold sg-num">{{ Format::number($tickets['breached']) }}</span>
                    </div>
                    <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary mt-2">Buka tiket masuk</a>
                </section>
            @endif
        </div>
    </div>

    {{-- Laporan terbaru --}}
    <section aria-labelledby="recent-title" class="mb-3 mb-lg-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 id="recent-title" class="h6 fw-bold mb-0">{{ $isAdmin ? 'Laporan terbaru' : 'Laporan terbaru Anda' }}</h2>
            <a href="{{ route($isAdmin ? 'daily-reports.index' : 'daily-reports.user-jobs') }}" class="small fw-semibold text-reset">Lihat semua</a>
        </div>
        <x-report.list :reports="$recent" :show-owner="$isAdmin" caption="Laporan terbaru"
                       empty-text="Laporan yang Anda buat atau tugaskan akan muncul di sini.">
            @if ($canCreate)
                <x-slot:empty><a href="{{ route('daily-reports.create') }}" class="btn btn-primary">Buat Laporan</a></x-slot:empty>
            @endif
        </x-report.list>
    </section>

    @if ($isAdmin && ! empty($adminStats['usersByDepartment']))
        <section class="card p-3 p-lg-4" aria-labelledby="dept-title">
            <h2 id="dept-title" class="h6 fw-bold mb-3">Pengguna per departemen</h2>
            @php($max = max(1, $adminStats['usersByDepartment']->max('users_count')))
            @foreach ($adminStats['usersByDepartment'] as $dept)
                <div class="d-flex align-items-center gap-3 py-1">
                    <span class="sg-clamp-1" style="width:40%">{{ $dept->name }}</span>
                    <span class="flex-grow-1 rounded" style="height:8px;background:var(--sg-surface-2)">
                        <span class="d-block rounded" style="height:8px;width:{{ round($dept->users_count / $max * 100) }}%;background:var(--sg-primary)"></span>
                    </span>
                    <span class="fw-semibold sg-num text-end" style="width:48px">{{ Format::number($dept->users_count) }}</span>
                </div>
            @endforeach
        </section>
    @endif

    @push('scripts')
        <script>
            (function () {
                const canvas = document.getElementById('sg-trend');
                if (!canvas || !window.Chart) return;
                const series = JSON.parse(canvas.dataset.series);
                const css = getComputedStyle(document.documentElement);
                const v = (name) => css.getPropertyValue(name).trim();
                new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: series.map((p) => p.label),
                        datasets: [{
                            data: series.map((p) => p.count),
                            backgroundColor: series.map((p) => (p.today ? v('--sg-accent') : v('--sg-primary'))),
                            borderRadius: 6,
                            maxBarThickness: 28,
                        }],
                    },
                    options: {
                        maintainAspectRatio: false,
                        animation: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? false : { duration: 200 },
                        plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c) => `${c.parsed.y} laporan` } } },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: v('--sg-muted'), maxRotation: 0, autoSkip: true, maxTicksLimit: 7 } },
                            y: { beginAtZero: true, ticks: { precision: 0, color: v('--sg-muted') }, grid: { color: v('--sg-border') } },
                        },
                    },
                });
            })();
        </script>
    @endpush
</x-app-layout>
