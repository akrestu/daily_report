<x-app-layout>
    <x-slot name="header">Tugas Masuk</x-slot>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filter Card -->
    <div class="card mb-4 border-0 bg-light rounded-3">
        <div class="card-body p-3">
            <form action="{{ route('job-plans.assigned-to-me') }}" method="GET">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-primary"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0"
                                placeholder="Cari nama pekerjaan" value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-calendar3 text-primary"></i></span>
                            <input type="date" name="date_from" class="form-control border-start-0 ps-0"
                                value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-calendar3 text-primary"></i></span>
                            <input type="date" name="date_to" class="form-control border-start-0 ps-0"
                                value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-8 col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>⏳ Aktif</option>
                            <option value="converted" {{ request('status') === 'converted' ? 'selected' : '' }}>✅ Dikonversi</option>
                        </select>
                    </div>
                    <div class="col-4 col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-3 flex-grow-1">
                            <i class="bi bi-funnel me-1"></i><span class="d-none d-sm-inline"> Filter</span>
                        </button>
                        <a href="{{ route('job-plans.assigned-to-me') }}" class="btn btn-outline-secondary rounded-pill px-3">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Plans Table Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-inbox text-primary me-2"></i>Tugas Masuk
                    <span class="badge bg-primary ms-1">{{ $plans->total() }}</span>
                    @if($activeCount > 0)
                        <span class="badge bg-warning text-dark ms-1">{{ $activeCount }} aktif</span>
                    @endif
                </h5>
            </div>
        </div>
        <div class="card-body p-0">
            @if($plans->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 mb-3 opacity-25"></i>
                    <p class="mb-0">Belum ada tugas yang diberikan kepada Anda.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 d-none d-md-table-cell">#</th>
                                <th>Nama Pekerjaan</th>
                                <th class="d-none d-sm-table-cell">Ditugaskan Oleh</th>
                                <th class="d-none d-lg-table-cell">Section</th>
                                <th class="d-none d-lg-table-cell">Tgl Rencana</th>
                                <th class="d-none d-sm-table-cell">Tenggat</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($plans as $i => $plan)
                            <tr class="{{ $plan->status === 'assigned' && $plan->due_date?->isPast() ? 'table-danger' : '' }}">
                                <td class="ps-3 text-muted small d-none d-md-table-cell">{{ $plans->firstItem() + $i }}</td>
                                <td>
                                    <a href="{{ route('job-plans.show', $plan) }}" class="fw-semibold text-decoration-none">
                                        {{ $plan->job_name }}
                                    </a>
                                    {{-- Info tambahan hanya tampil di mobile --}}
                                    <div class="d-sm-none mt-1 text-muted small">
                                        <i class="bi bi-person-badge me-1"></i>{{ $plan->creator->name ?? '-' }}
                                        @if($plan->due_date)
                                            &nbsp;·&nbsp;
                                            @if($plan->due_date->isPast() && !$plan->isConverted())
                                                <span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>{{ $plan->due_date->format('d/m/Y') }}</span>
                                            @else
                                                <i class="bi bi-clock me-1"></i>{{ $plan->due_date->format('d/m/Y') }}
                                            @endif
                                        @endif
                                    </div>
                                </td>
                                <td class="d-none d-sm-table-cell">
                                    <span class="d-flex align-items-center gap-2">
                                        <i class="bi bi-person-badge text-muted small"></i>
                                        {{ $plan->creator->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="d-none d-lg-table-cell">{{ $plan->section->name ?? '-' }}</td>
                                <td class="d-none d-lg-table-cell">{{ $plan->planned_date?->format('d/m/Y') ?? '-' }}</td>
                                <td class="d-none d-sm-table-cell">
                                    @if($plan->due_date && $plan->due_date->isPast() && !$plan->isConverted())
                                        <span class="text-danger fw-semibold">
                                            <i class="bi bi-exclamation-triangle me-1 small"></i>{{ $plan->due_date->format('d/m/Y') }}
                                        </span>
                                    @else
                                        {{ $plan->due_date?->format('d/m/Y') ?? '-' }}
                                    @endif
                                </td>
                                <td>
                                    @if($plan->isConverted())
                                        <span class="badge bg-success rounded-pill">✅ <span class="d-none d-sm-inline">Dikonversi</span></span>
                                    @else
                                        <span class="badge bg-warning text-dark rounded-pill">⏳ <span class="d-none d-sm-inline">Aktif</span></span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-flex flex-nowrap gap-1 justify-content-end">
                                        <a href="{{ route('job-plans.show', $plan) }}"
                                            class="btn btn-sm btn-outline-primary rounded-pill px-2"
                                            title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @can('convert', $plan)
                                        <a href="{{ route('job-plans.convert', $plan) }}"
                                            class="btn btn-sm btn-success rounded-pill px-2"
                                            title="Jadikan Report">
                                            <i class="bi bi-file-earmark-text"></i><span class="d-none d-md-inline ms-1">Jadikan Report</span>
                                        </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($plans->hasPages())
                <div class="px-3 py-3 border-top">
                    {{ $plans->withQueryString()->links() }}
                </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
