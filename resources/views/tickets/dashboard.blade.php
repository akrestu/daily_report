<x-app-layout title="Dashboard Tiket">
    {{-- Dashboard KPI tiket — mockup: TicketKpiMobile, TicketKpiDesktop --}}
    @php
        $fmt = fn ($minutes) => $minutes === null ? '-' : \App\Services\TicketSla::formatMinutes($minutes);
        $pct = fn ($value) => $value === null ? '-' : \App\Support\Format::percent($value);
        $pctClass = fn ($value, $target = 90) => $value === null ? 'text-muted' : ($value >= $target ? 'text-success' : ($value >= $target - 15 ? 'text-warning' : 'text-danger'));
        $tone = fn ($value, $target = 90) => $value === null ? null : ($value >= $target ? 'approved' : ($value >= $target - 15 ? 'pending' : 'rejected'));
    @endphp

    <style>.chart-box { position: relative; height: 260px; }</style>

    <x-sg.page-header title="Dashboard Tiket"
        :subtitle="$from->translatedFormat('d M Y').' – '.$to->translatedFormat('d M Y').' · durasi dalam jam kerja ('.config('tickets.working_hours.start').'–'.config('tickets.working_hours.end').')'" />

    <form method="GET" action="{{ route('tickets.dashboard') }}" class="sg-filter-bar mb-3" aria-label="Periode dashboard">
        <label for="periodSelect" class="visually-hidden">Periode</label>
        <select name="period" class="form-select" id="periodSelect">
            @foreach ($periods as $value => $label)
                <option value="{{ $value }}" @selected($period === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <label for="kpi-from" class="visually-hidden">Dari</label>
        <input type="date" id="kpi-from" name="from" class="form-control custom-range" value="{{ $from->toDateString() }}" title="Dari">
        <label for="kpi-to" class="visually-hidden">Sampai</label>
        <input type="date" id="kpi-to" name="to" class="form-control custom-range" value="{{ $to->toDateString() }}" title="Sampai">
        <label for="kpi-site" class="visually-hidden">Site</label>
        <select id="kpi-site" name="job_site" class="form-select">
            <option value="">Semua site</option>
            @foreach ($jobSites as $site)
                <option value="{{ $site->id }}" @selected(request('job_site') == $site->id)>{{ $site->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">Terapkan</button>
    </form>

    <section class="sg-kpi-grid mb-3" aria-label="Ringkasan tiket">
        <x-sg.kpi label="Tiket masuk" :value="\App\Support\Format::number($summary['created'])" :note="'Selesai: '.\App\Support\Format::number($summary['resolved'])" />
        <x-sg.kpi label="Backlog aktif" :value="\App\Support\Format::number($summary['backlog'])" :href="route('tickets.index')"
                  :note="$summary['backlog_breached'].' lewat SLA · '.$summary['unassigned'].' belum di-assign'.($summary['awaiting_approval'] ? ' · '.$summary['awaiting_approval'].' menunggu approval' : '')"
                  :note-tone="$summary['backlog_breached'] ? 'rejected' : null" />
        <x-sg.kpi label="MTTR" :value="$fmt($summary['mttr_minutes'])" :note="'Respons pertama: '.$fmt($summary['response_minutes'])" />
        <x-sg.kpi label="CSAT" :value="$summary['csat_avg'] !== null ? \App\Support\Format::number($summary['csat_avg'], 1).' / 5' : '-'"
                  :note="$summary['csat_count'].' penilaian · puas: '.$pct($summary['csat_satisfied_pct'])" />
    </section>
    <section class="row g-3 mb-3" aria-label="Kepatuhan SLA">
        <div class="col-6 col-lg-4"><x-sg.kpi label="SLA respons tercapai" :value="$pct($summary['response_sla_pct'])" note="Tiket masuk yang sudah direspons" :note-tone="$tone($summary['response_sla_pct'])" /></div>
        <div class="col-6 col-lg-4"><x-sg.kpi label="SLA penyelesaian tercapai" :value="$pct($summary['resolution_sla_pct'])" note="Tiket yang selesai dalam periode" :note-tone="$tone($summary['resolution_sla_pct'])" /></div>
        <div class="col-12 col-lg-4"><x-sg.kpi label="Dibuka kembali" :value="$pct($summary['reopen_pct'])" note="Makin kecil makin baik"
                  :note-tone="$summary['reopen_pct'] !== null && $summary['reopen_pct'] > 10 ? 'rejected' : null" /></div>
    </section>

    {{-- Grafik --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-semibold">Tiket Masuk vs Selesai per Hari</div>
                <div class="card-body"><div class="chart-box"><canvas id="trendChart" aria-label="Grafik tiket masuk dan selesai per hari" role="img"></canvas></div></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-semibold">Prioritas</div>
                <div class="card-body"><div class="chart-box"><canvas id="priorityChart" aria-label="Grafik tiket per prioritas" role="img"></canvas></div></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-semibold">Kategori</div>
                <div class="card-body"><div class="chart-box"><canvas id="categoryChart" aria-label="Grafik tiket per kategori" role="img"></canvas></div></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-semibold">Departemen Pemohon Terbanyak</div>
                <div class="card-body"><div class="chart-box"><canvas id="departmentChart" aria-label="Grafik tiket per departemen" role="img"></canvas></div></div>
            </div>
        </div>
    </div>

    {{-- Kinerja teknisi --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-white fw-semibold">Kinerja Teknisi</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Teknisi</th>
                            <th class="text-end">Aktif</th>
                            <th class="text-end">Lewat SLA</th>
                            <th class="text-end">Selesai (periode)</th>
                            <th class="text-end">Rata-rata Penyelesaian</th>
                            <th class="text-end">SLA Tercapai</th>
                            <th class="text-end pe-3">CSAT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($technicians as $tech)
                            <tr>
                                <td class="ps-3 fw-semibold">{{ $tech['name'] }}</td>
                                <td class="text-end">{{ $tech['active'] }}</td>
                                <td class="text-end {{ $tech['overdue'] ? 'text-danger fw-semibold' : 'text-muted' }}">{{ $tech['overdue'] }}</td>
                                <td class="text-end">{{ $tech['resolved'] }}</td>
                                <td class="text-end">{{ $fmt($tech['mttr_minutes']) }}</td>
                                <td class="text-end {{ $pctClass($tech['sla_pct']) }}">{{ $pct($tech['sla_pct']) }}</td>
                                <td class="text-end pe-3">{{ $tech['csat_avg'] !== null ? number_format($tech['csat_avg'], 1, ',', '') : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Belum ada tiket yang ditangani pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Masukan pemohon --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-semibold">Masukan Pemohon Terbaru</div>
        <ul class="list-group list-group-flush small">
            @forelse($feedback as $item)
                <li class="list-group-item">
                    <div class="d-flex justify-content-between flex-wrap gap-2">
                        <a href="{{ route('tickets.show', $item) }}" class="fw-semibold text-decoration-none">{{ $item->ticket_number }} &mdash; {{ $item->title }}</a>
                        <span class="text-warning" aria-label="{{ $item->csat_rating }} dari 5">{!! str_repeat('&#9733;', $item->csat_rating) !!}<span class="text-muted">{!! str_repeat('&#9733;', 5 - $item->csat_rating) !!}</span></span>
                    </div>
                    <div class="text-muted">{{ $item->requester_name }} &bull; ditangani {{ $item->assignedUser->name ?? '-' }} &bull; {{ $item->csat_submitted_at->format('d M Y') }}</div>
                    @if($item->csat_comment)<div class="mt-1">"{{ $item->csat_comment }}"</div>@endif
                </li>
            @empty
                <li class="list-group-item text-muted text-center py-3">Belum ada penilaian pada periode ini.</li>
            @endforelse
        </ul>
    </div>

    @push('scripts')
        <script src="{{ asset('vendor/chart.js/chart.umd.min.js') }}"></script>
    <script>
        (function () {
            const period = document.getElementById('periodSelect');
            const toggleRange = () => document.querySelectorAll('.custom-range').forEach(el => el.style.display = period.value === 'custom' ? '' : 'none');
            period.addEventListener('change', toggleRange);
            toggleRange();

            if (typeof Chart === 'undefined') return;

            const trend = @json($trend);
            const breakdowns = @json($breakdowns);
            const blue = '#111111', green = '#16A34A', palette = ['#111111', '#F5D334', '#2563EB', '#16A34A', '#DC2626', '#EA580C', '#4F46E5', '#94A3B8'];
            const common = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } };
            const integerAxis = { beginAtZero: true, ticks: { precision: 0 } };

            new Chart(document.getElementById('trendChart'), {
                type: 'line',
                data: { labels: trend.labels, datasets: [
                    { label: 'Masuk', data: trend.created, borderColor: blue, backgroundColor: blue + '22', fill: true, tension: .3, pointRadius: 2 },
                    { label: 'Selesai', data: trend.resolved, borderColor: green, backgroundColor: green + '22', fill: true, tension: .3, pointRadius: 2 },
                ] },
                options: { ...common, interaction: { mode: 'index', intersect: false }, scales: { y: integerAxis } },
            });

            new Chart(document.getElementById('priorityChart'), {
                type: 'doughnut',
                data: { labels: Object.keys(breakdowns.priority), datasets: [{ data: Object.values(breakdowns.priority), backgroundColor: ['#dc3545', '#fd7e14', '#0dcaf0', '#6c757d'] }] },
                options: common,
            });

            new Chart(document.getElementById('categoryChart'), {
                type: 'bar',
                data: { labels: Object.keys(breakdowns.category), datasets: [{ label: 'Tiket', data: Object.values(breakdowns.category), backgroundColor: palette }] },
                options: { ...common, plugins: { legend: { display: false } }, scales: { y: integerAxis } },
            });

            new Chart(document.getElementById('departmentChart'), {
                type: 'bar',
                data: { labels: Object.keys(breakdowns.department), datasets: [{ label: 'Tiket', data: Object.values(breakdowns.department), backgroundColor: blue }] },
                options: { ...common, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: integerAxis } },
            });
        })();
    </script>
    @endpush
</x-app-layout>
