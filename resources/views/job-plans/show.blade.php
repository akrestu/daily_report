{{-- Detail Rencana Kerja --}}
@use('App\Support\Format')
@php
    $badge = $plan->badge();
    $late = $plan->isLate();
@endphp

<x-app-layout title="Rencana Kerja" :back="url()->previous() !== url()->current() ? url()->previous() : route('job-plans.index')">
    <div class="row g-3 g-lg-4">
        <div class="col-lg-8">
            <section class="card p-3 p-lg-4 mb-3" aria-labelledby="plan-title">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="sg-badge sg-badge--{{ $badge['tone'] }}">{{ $badge['label'] }}</span>
                    @if ($plan->section)<span class="sg-progress-label"><i class="bi bi-layers" aria-hidden="true"></i>{{ $plan->section->name }}</span>@endif
                </div>
                <h1 id="plan-title" class="h4 fw-bold mb-3">{{ $plan->job_name }}</h1>

                <div class="sg-people mb-3">
                    <div class="sg-person">
                        <x-sg.avatar :user="$plan->creator" :size="40" />
                        <div class="min-w-0"><div class="small text-muted">Dari</div><div class="fw-semibold sg-clamp-1">{{ $plan->creator->name ?? '-' }}</div></div>
                    </div>
                    <div class="sg-person">
                        <x-sg.avatar :user="$plan->assignee" :size="40" />
                        <div class="min-w-0"><div class="small text-muted">Ditugaskan ke</div><div class="fw-semibold sg-clamp-1">{{ $plan->assignee->name ?? '-' }}</div></div>
                    </div>
                </div>

                <h2 class="sg-section-label">Deskripsi</h2>
                <p class="sg-prewrap">{{ $plan->description ?: '-' }}</p>
                @if ($plan->remark)
                    <h2 class="sg-section-label">Remark</h2>
                    <p class="sg-prewrap">{{ $plan->remark }}</p>
                @endif

                <dl class="sg-meta-grid mb-0">
                    <div><dt>Tanggal rencana</dt><dd>{{ Format::date($plan->planned_date) }}</dd></div>
                    <div><dt>Due date</dt><dd @class(['text-danger' => $late])>{{ Format::date($plan->due_date) }}</dd></div>
                    <div><dt>Departemen</dt><dd>{{ $plan->department->name ?? '-' }}</dd></div>
                    <div><dt>Job site</dt><dd>{{ $plan->jobSite->name ?? '-' }}</dd></div>
                    <div><dt>Dibuat</dt><dd>{{ Format::datetime($plan->created_at) }}</dd></div>
                    @if ($plan->isConverted() && $plan->converted_at)<div><dt>Dikonversi</dt><dd>{{ Format::datetime($plan->converted_at) }}</dd></div>@endif
                </dl>
            </section>

            @if ($plan->isConverted() && $plan->convertedReports->isNotEmpty())
                <section aria-labelledby="converted-title" class="mb-3">
                    <h2 id="converted-title" class="h6 fw-bold mb-2">Laporan hasil konversi</h2>
                    <x-report.list :reports="$plan->convertedReports" caption="Laporan hasil konversi" />
                </section>
            @endif
        </div>

        <aside class="col-lg-4">
            <section class="card p-3 sg-create-aside d-none d-lg-block" aria-label="Tindakan">
                <div class="d-grid gap-2">
                    @can('convert', $plan)
                        <a href="{{ route('job-plans.convert', $plan) }}" class="btn btn-primary"><i class="bi bi-arrow-right-circle" aria-hidden="true"></i> Konversi ke Laporan</a>
                    @endcan
                    @can('update', $plan)
                        <a href="{{ route('job-plans.edit', $plan) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil" aria-hidden="true"></i> Edit rencana</a>
                    @endcan
                    @can('delete', $plan)
                        <button type="button" class="btn btn-reject-outline" data-bs-toggle="modal" data-bs-target="#delete-plan"><i class="bi bi-trash3" aria-hidden="true"></i> Hapus</button>
                    @endcan
                </div>
            </section>
            <div class="d-flex gap-2 d-lg-none">
                @can('update', $plan)<a href="{{ route('job-plans.edit', $plan) }}" class="btn btn-outline-secondary flex-fill"><i class="bi bi-pencil" aria-hidden="true"></i> Edit</a>@endcan
                @can('delete', $plan)<button type="button" class="btn btn-reject-outline flex-fill" data-bs-toggle="modal" data-bs-target="#delete-plan"><i class="bi bi-trash3" aria-hidden="true"></i> Hapus</button>@endcan
            </div>
        </aside>
    </div>

    @can('convert', $plan)
        <x-sg.sticky-actions>
            <a href="{{ route('job-plans.convert', $plan) }}" class="btn btn-primary w-100"><i class="bi bi-arrow-right-circle" aria-hidden="true"></i> Konversi ke Laporan</a>
        </x-sg.sticky-actions>
    @endcan

    @can('delete', $plan)
        <x-sg.confirm-danger id="delete-plan" title="Hapus rencana kerja?" :action="route('job-plans.destroy', $plan)" :text="$plan->job_name" button="Hapus" />
    @endcan
</x-app-layout>
