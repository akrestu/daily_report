{{--
    Daftar laporan harian: kartu di <992px, tabel padat di desktop.
    :reports   = koleksi DailyReport (eager load department, pic, user)
    :showOwner = tampilkan nama pembuat (untuk atasan/admin)
--}}
@props(['reports', 'showOwner' => false, 'emptyTitle' => 'Belum ada laporan', 'emptyText' => null, 'caption' => 'Daftar laporan', 'selectable' => null])
{{-- :selectable = id form tujuan checkbox selected_reports[] (desktop), mis. form hapus massal --}}

@if ($reports->isEmpty())
    <x-sg.empty icon="bi-journal-text" :title="$emptyTitle" :text="$emptyText">{{ $empty ?? '' }}</x-sg.empty>
@else
    <div {{ $attributes->class(['sg-list d-lg-none']) }} data-sg-infinite>
        @foreach ($reports as $report)
            @php
                $meta = collect([
                    $showOwner ? $report->user?->name : null,
                    $report->department?->name,
                    $report->pic ? 'PIC '.$report->pic->name : null,
                ])->filter()->implode(' · ');
            @endphp
            <x-sg.list-card :href="route('daily-reports.show', $report)" :title="$report->job_name"
                            :time="\App\Support\Format::dayMonth($report->report_date ?? $report->created_at).', '.$report->created_at->format('H:i')" :meta="$meta">
                <x-slot:badge><x-sg.badge-status :value="$report->approval_status" /></x-slot:badge>
            </x-sg.list-card>
        @endforeach
    </div>

    <x-sg.table class="d-none d-lg-block" :caption="$caption">
        <x-slot:head>
            <tr>
                @if ($selectable)
                    <th scope="col" style="width:40px">
                        <input type="checkbox" class="form-check-input" aria-label="Pilih semua di halaman ini"
                               @change="document.querySelectorAll('[data-select-for={{ $selectable }}]').forEach(c => { c.checked = $event.target.checked; c.dispatchEvent(new Event('change', { bubbles: true })) })" x-data>
                    </th>
                @endif
                <th scope="col">Pekerjaan</th>
                @if ($showOwner)<th scope="col">Pembuat</th>@endif
                <th scope="col">Tanggal</th>
                <th scope="col">PIC</th>
                <th scope="col">Progres</th>
                <th scope="col">Status</th>
            </tr>
        </x-slot:head>
        @foreach ($reports as $report)
            <tr class="position-relative">
                @if ($selectable)
                    <td class="position-relative" style="z-index:2">
                        <input type="checkbox" class="form-check-input" name="selected_reports[]" value="{{ $report->id }}" form="{{ $selectable }}"
                               data-select-for="{{ $selectable }}" aria-label="Pilih {{ $report->job_name }}">
                    </td>
                @endif
                <td style="max-width:360px">
                    <a href="{{ route('daily-reports.show', $report) }}" class="stretched-link d-block text-reset text-decoration-none fw-semibold sg-clamp-1">{{ $report->job_name }}</a>
                    <div class="small text-muted sg-clamp-1">{{ $report->department?->name }}</div>
                </td>
                @if ($showOwner)<td class="text-nowrap">{{ $report->user?->name ?? '-' }}</td>@endif
                <td class="sg-num text-nowrap">{{ \App\Support\Format::date($report->report_date ?? $report->created_at) }}</td>
                <td class="text-nowrap">{{ $report->pic?->name ?? '-' }}</td>
                <td><x-sg.badge-progress :value="$report->status" /></td>
                <td><x-sg.badge-status :value="$report->approval_status" /></td>
            </tr>
        @endforeach
    </x-sg.table>
@endif
