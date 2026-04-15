<x-app-layout>
    <x-slot name="header">Detail Job Plan</x-slot>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Main Detail Card -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="mb-1 fw-bold">
                                <i class="fas fa-clipboard-check text-primary me-2"></i>{{ $plan->job_name }}
                            </h5>
                            <div class="d-flex gap-2 flex-wrap">
                                @if($plan->isConverted())
                                    <span class="badge bg-success rounded-pill">✅ Dikonversi</span>
                                @else
                                    <span class="badge bg-warning text-dark rounded-pill">⏳ Aktif</span>
                                @endif
                                @if($plan->section)
                                    <span class="badge bg-light text-dark border rounded-pill">
                                        <i class="fas fa-layer-group me-1 text-primary"></i>{{ $plan->section->name }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            @can('update', $plan)
                            <a href="{{ route('job-plans.edit', $plan) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                <i class="fas fa-edit me-1"></i>Edit
                            </a>
                            @endcan
                            @can('convert', $plan)
                            <a href="{{ route('job-plans.convert', $plan) }}" class="btn btn-sm btn-success rounded-pill px-3">
                                <i class="fas fa-file-alt me-1"></i>Jadikan Report
                            </a>
                            @endcan
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- Description -->
                    <div class="mb-4">
                        <h6 class="text-muted fw-semibold small text-uppercase mb-2">
                            <i class="fas fa-align-left me-1"></i>Deskripsi Pekerjaan
                        </h6>
                        <div class="p-3 bg-light rounded-3" style="white-space: pre-wrap;">{{ $plan->description }}</div>
                    </div>

                    @if($plan->remark)
                    <!-- Remark -->
                    <div class="mb-4">
                        <h6 class="text-muted fw-semibold small text-uppercase mb-2">
                            <i class="fas fa-comment-dots me-1"></i>Catatan Tambahan
                        </h6>
                        <div class="p-3 bg-light rounded-3" style="white-space: pre-wrap;">{{ $plan->remark }}</div>
                    </div>
                    @endif

                    <!-- Converted Reports -->
                    @if($plan->isConverted() && $plan->convertedReports->isNotEmpty())
                    <div class="mb-0">
                        <h6 class="text-muted fw-semibold small text-uppercase mb-2">
                            <i class="fas fa-file-check me-1 text-success"></i>Laporan Hasil Konversi
                        </h6>
                        <div class="list-group list-group-flush rounded-3 border">
                            @foreach($plan->convertedReports as $report)
                            <a href="{{ route('daily-reports.show', $report) }}"
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-semibold">{{ $report->job_name }}</span>
                                    <small class="text-muted ms-2">{{ $report->report_date?->format('d/m/Y') }}</small>
                                </div>
                                <span class="badge bg-{{ $report->approval_status === 'approved' ? 'success' : ($report->approval_status === 'rejected' ? 'danger' : 'secondary') }} rounded-pill">
                                    {{ ucfirst($report->approval_status) }}
                                </span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Side Info Card -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-muted small text-uppercase">
                        <i class="fas fa-info-circle me-1"></i>Informasi
                    </h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-start py-3">
                            <span class="text-muted small">Dibuat Oleh</span>
                            <span class="fw-semibold text-end">
                                <i class="fas fa-user-tie me-1 text-muted small"></i>{{ $plan->creator->name ?? '-' }}
                                <br><small class="text-muted fw-normal">{{ $plan->creator->role->name ?? '' }}</small>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start py-3">
                            <span class="text-muted small">Ditugaskan Kepada</span>
                            <span class="fw-semibold text-end">
                                <i class="fas fa-user me-1 text-muted small"></i>{{ $plan->assignee->name ?? '-' }}
                                <br><small class="text-muted fw-normal">{{ $plan->assignee->role->name ?? '' }}</small>
                            </span>
                        </li>
                        @if($plan->department)
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted small">Departemen</span>
                            <span class="fw-semibold">{{ $plan->department->name }}</span>
                        </li>
                        @endif
                        @if($plan->jobSite)
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted small">Job Site</span>
                            <span class="fw-semibold">{{ $plan->jobSite->name }}</span>
                        </li>
                        @endif
                        @if($plan->section)
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted small">Section</span>
                            <span class="fw-semibold">{{ $plan->section->name }}</span>
                        </li>
                        @endif
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted small">Tgl Rencana</span>
                            <span class="fw-semibold">{{ $plan->planned_date?->translatedFormat('d M Y') ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted small">Batas Waktu</span>
                            <span class="fw-semibold {{ !$plan->isConverted() && $plan->due_date?->isPast() ? 'text-danger' : '' }}">
                                @if(!$plan->isConverted() && $plan->due_date?->isPast())
                                    <i class="fas fa-exclamation-triangle me-1 small"></i>
                                @endif
                                {{ $plan->due_date?->translatedFormat('d M Y') ?? '-' }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted small">Dibuat Pada</span>
                            <span class="fw-semibold small">{{ $plan->created_at->translatedFormat('d M Y, H:i') }}</span>
                        </li>
                        @if($plan->isConverted() && $plan->converted_at)
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted small">Dikonversi Pada</span>
                            <span class="fw-semibold small text-success">
                                <i class="fas fa-check-circle me-1"></i>{{ $plan->converted_at->translatedFormat('d M Y, H:i') }}
                            </span>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-3 d-flex flex-column gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary rounded-pill">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                    @can('convert', $plan)
                    <a href="{{ route('job-plans.convert', $plan) }}" class="btn btn-success rounded-pill">
                        <i class="fas fa-file-alt me-1"></i>Jadikan Report
                    </a>
                    @endcan
                    @can('update', $plan)
                    <a href="{{ route('job-plans.edit', $plan) }}" class="btn btn-outline-primary rounded-pill">
                        <i class="fas fa-edit me-1"></i>Edit Plan
                    </a>
                    @endcan
                    @can('delete', $plan)
                    <form action="{{ route('job-plans.destroy', $plan) }}" method="POST"
                        onsubmit="return confirm('Hapus job plan ini? Tindakan ini tidak dapat dibatalkan.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger rounded-pill w-100">
                            <i class="fas fa-trash me-1"></i>Hapus Plan
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
