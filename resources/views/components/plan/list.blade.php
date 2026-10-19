{{--
    Daftar rencana kerja (job plan): kartu di <992px, tabel di desktop. Mockup: PlanMobile, PlanDesktop.
    :plans  koleksi JobPlan (eager load assignee/creator, section)
    :mine   true = tampilan penerima (kolom "Dari", tombol Konversi ke Laporan); false = tampilan pembuat
--}}
@props(['plans', 'mine' => false])
@use('App\Support\Format')

@if ($plans->isEmpty())
    <x-sg.empty icon="bi-calendar-check" :title="$mine ? 'Tidak ada tugas' : 'Belum ada rencana kerja'"
                :text="$mine ? 'Rencana kerja dari atasan akan muncul di sini.' : 'Buat rencana kerja untuk menugaskan pekerjaan ke tim.'">{{ $slot }}</x-sg.empty>
@else
    <div class="sg-list d-lg-none">
        @foreach ($plans as $plan)
            @php($late = $plan->isLate())
            @php($badge = $plan->badge())
            <div class="sg-list-card flex-column align-items-stretch">
                <div class="sg-list-card__top">
                    <span class="sg-badge sg-badge--{{ $badge['tone'] }}">{{ $badge['label'] }}</span>
                    <span @class(['sg-list-card__time', 'text-danger fw-semibold' => $late])>Due {{ $plan->due_date ? Format::dayMonth($plan->due_date) : '-' }}</span>
                </div>
                <a href="{{ route('job-plans.show', $plan) }}" class="sg-list-card__title sg-clamp-2 text-reset text-decoration-none stretched-link">{{ $plan->job_name }}</a>
                <div class="sg-list-card__meta sg-clamp-1">{{ $mine ? 'Dari '.($plan->creator->name ?? '-') : 'Untuk '.($plan->assignee->name ?? '-') }}{{ $plan->section ? ' · '.$plan->section->name : '' }}</div>
                @if ($mine)
                    @can('convert', $plan)
                        <a href="{{ route('job-plans.convert', $plan) }}" class="btn btn-primary mt-2 position-relative" style="z-index:2"><i class="bi bi-arrow-right-circle" aria-hidden="true"></i> Konversi ke Laporan</a>
                    @endcan
                @endif
            </div>
        @endforeach
    </div>

    <x-sg.table class="d-none d-lg-block" caption="Daftar rencana kerja">
        <x-slot:head>
            <tr>
                <th scope="col">Pekerjaan</th>
                <th scope="col">{{ $mine ? 'Dari' : 'Ditugaskan ke' }}</th>
                <th scope="col">Section</th>
                <th scope="col">Rencana</th>
                <th scope="col">Due</th>
                <th scope="col">Status</th>
                <th scope="col" class="text-end">Aksi</th>
            </tr>
        </x-slot:head>
        @foreach ($plans as $plan)
            @php($late = $plan->isLate())
            @php($badge = $plan->badge())
            <tr>
                <td style="max-width:340px"><a href="{{ route('job-plans.show', $plan) }}" class="d-block fw-semibold text-reset text-decoration-none sg-clamp-1">{{ $plan->job_name }}</a></td>
                <td class="text-nowrap">{{ $mine ? ($plan->creator->name ?? '-') : ($plan->assignee->name ?? '-') }}</td>
                <td class="text-nowrap">{{ $plan->section->name ?? '-' }}</td>
                <td class="sg-num text-nowrap">{{ Format::date($plan->planned_date) }}</td>
                <td @class(['sg-num text-nowrap', 'text-danger fw-semibold' => $late])>{{ Format::date($plan->due_date) }}</td>
                <td><span class="sg-badge sg-badge--{{ $badge['tone'] }}">{{ $badge['label'] }}</span></td>
                <td class="text-end text-nowrap">
                    @if ($mine)
                        @can('convert', $plan)<a href="{{ route('job-plans.convert', $plan) }}" class="btn btn-sm btn-primary">Konversi</a>@endcan
                    @else
                        @can('update', $plan)<a href="{{ route('job-plans.edit', $plan) }}" class="btn btn-sm btn-outline-secondary btn-icon" aria-label="Edit {{ $plan->job_name }}"><i class="bi bi-pencil" aria-hidden="true"></i></a>@endcan
                        @can('delete', $plan)
                            <button type="button" class="btn btn-sm btn-reject-outline btn-icon" data-bs-toggle="modal" data-bs-target="#del-plan-{{ $plan->id }}" aria-label="Hapus {{ $plan->job_name }}"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                        @endcan
                    @endif
                </td>
            </tr>
        @endforeach
    </x-sg.table>

    @unless ($mine)
        @foreach ($plans as $plan)
            @can('delete', $plan)
                <x-sg.confirm-danger :id="'del-plan-'.$plan->id" title="Hapus rencana kerja?" :action="route('job-plans.destroy', $plan)"
                                     :text="$plan->job_name" button="Hapus" />
            @endcan
        @endforeach
    @endunless
@endif
