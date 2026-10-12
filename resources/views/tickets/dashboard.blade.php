<x-app-layout>
    <x-slot name="header">Dashboard Tiket</x-slot>

    @php
        $fmt = fn ($minutes) => $minutes === null ? '-' : \App\Services\TicketSla::formatMinutes($minutes);
        $pct = fn ($value) => $value === null ? '-' : rtrim(rtrim(number_format($value, 1, ',', ''), '0'), ',').'%';
        $pctClass = fn ($value, $target = 90) => $value === null ? 'text-muted' : ($value >= $target ? 'text-success' : ($value >= $target - 15 ? 'text-warning' : 'text-danger'));
    @endphp

    <style>
        .kpi .card-body { padding: .9rem 1rem; }
        .kpi .label { font-size: .75rem; text-transform: uppercase; letter-spacing: .04em; color: #6c757d; }
        .kpi .value { font-size: 1.6rem; font-weight: 700; line-height: 1.2; font-variant-numeric: tabular-nums; }
        .kpi .hint { font-size: .75rem; color: #6c757d; }
        .chart-box { position: relative; height: 260px; }
    </style>

    {{-- Filter --}}
    <form method="GET" action="{{ route('tickets.dashboard') }}" class="card shadow-sm border-0 mb-3">
        <div class="card-body row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Periode</label>
                <select name="period" class="form-select form-select-sm" id="periodSelect">
                    @foreach($periods as $value => $label)
                        <option value="{{ $value }}" {{ $period === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 custom-range">
                <label class="form-label small mb-1">Dari</label>
                <input type="date" name="from" class="form-control form-control-sm" value="{{ $from->toDateString() }}">
            </div>
            <div class="col-md-2 custom-range">
                <label class="form-label small mb-1">Sampai</label>
                <input type="date" name="to" class="form-control form-control-sm" value="{{ $to->toDateString() }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Site</label>
                <select name="job_site" class="form-select form-select-sm">
                    <option value="">Semua site</option>
                    @foreach($jobSites as $site)
                        <option value="{{ $site->id }}" {{ request('job_site') == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-filter me-1"></i>Terapkan</button>
            </div>
            <div class="col-12 small text-muted">
                {{ $from->translatedFormat('d M Y') }} – {{ $to->translatedFormat('d M Y') }} &bull;
                Durasi dihitung dalam jam kerja (Senin–Sabtu {{ config('tickets.working_hours.start') }}–{{ config('tickets.working_hours.end') }}), tidak termasuk waktu menunggu pemohon / persetujuan.
            </div>
        </div>
    </form>

    {{-- KPI --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <div class="card kpi shadow-sm border-0 h-100"><div class="card-body">
                <div class="label">Tiket Masuk</div>
                <div class="value">{{ number_format($summary['created']) }}</div>
                <div class="hint">Selesai dalam periode: {{ number_format($summary['resolved']) }}</div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ route('tickets.index') }}" class="text-decoration-none text-reset">
            <div class="card kpi shadow-sm border-0 h-100"><div class="card-body">
                <div class="label">Backlog Aktif</div>
                <div class="value">{{ number_format($summary['backlog']) }}</div>
                <div class="hint">
                    <span class="{{ $summary['backlog_breached'] ? 'text-danger fw-semibold' : '' }}">{{ $summary['backlog_breached'] }} lewat SLA</span>
                    &bull; {{ $summary['unassigned'] }} belum di-assign
                    @if($summary['awaiting_approval']) &bull; {{ $summary['awaiting_approval'] }} menunggu approval @endif
                </div>
            </div></div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card kpi shadow-sm border-0 h-100"><div class="card-body">
                <div class="label">Rata-rata Penyelesaian (MTTR)</div>
                <div class="value">{{ $fmt($summary['mttr_minutes']) }}</div>
                <div class="hint">Respons pertama rata-rata: {{ $fmt($summary['response_minutes']) }}</div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card kpi shadow-sm border-0 h-100"><div class="card-body">
                <div class="label">Kepuasan Pemohon (CSAT)</div>
                <div class="value">{{ $summary['csat_avg'] !== null ? number_format($summary['csat_avg'], 1, ',', '').' / 5' : '-' }}</div>
                <div class="hint">{{ $summary['csat_count'] }} penilaian &bull; puas (≥4): {{ $pct($summary['csat_satisfied_pct']) }}</div>
            </div></div>
        </div>
        <div class="col-6 col-lg-4">
            <div class="card kpi shadow-sm border-0 h-100"><div class="card-body">
                <div class="label">SLA Respons Tercapai</div>
                <div class="value {{ $pctClass($summary['response_sla_pct']) }}">{{ $pct($summary['response_sla_pct']) }}</div>
                <div class="hint">Tiket masuk periode ini yang sudah direspons</div>
            </div></div>
        </div>
        <div class="col-6 col-lg-4">
            <div class="card kpi shadow-sm border-0 h-100"><div class="card-body">
                <div class="label">SLA Penyelesaian Tercapai</div>
                <div class="value {{ $pctClass($summary['resolution_sla_pct']) }}">{{ $pct($summary['resolution_sla_pct']) }}</div>
                <div class="hint">Tiket yang selesai dalam periode</div>
            </div></div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card kpi shadow-sm border-0 h-100"><div class="card-body">
                <div class="label">Dibuka Kembali (Reopen)</div>
                <div class="value {{ $summary['reopen_pct'] !== null && $summary['reopen_pct'] > 10 ? 'text-danger' : '' }}">{{ $pct($summary['reopen_pct']) }}</div>
                <div class="hint">Tiket selesai yang pernah ditolak pemohon (makin kecil makin baik)</div>
            </div></div>
        </div>
    </div>

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
    <script>
        (function () {
            const period = document.getElementById('periodSelect');
            const toggleRange = () => document.querySelectorAll('.custom-range').forEach(el => el.style.display = period.value === 'custom' ? '' : 'none');
            period.addEventListener('change', toggleRange);
            toggleRange();

            if (typeof Chart === 'undefined') return;

            const trend = @json($trend);
            const breakdowns = @json($breakdowns);
            const blue = '#0d6efd', green = '#198754', palette = ['#0d6efd', '#6610f2', '#20c997', '#fd7e14', '#d63384', '#0dcaf0', '#6c757d', '#ffc107'];
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
