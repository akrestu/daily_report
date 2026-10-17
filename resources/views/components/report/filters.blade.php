{{--
    Filter daftar laporan (GET). Mobile: cari + tombol "Filter" → bottom sheet. Desktop: toolbar inline.
    :action       URL tujuan
    :status       tampilkan pilihan progres (user-jobs, assigned-jobs)
    :departments  [id => nama] atau null
    :sections     koleksi Section atau null
    :keep         parameter tersembunyi yang dipertahankan, mis. ['type' => 'approved']
--}}
@props(['action', 'status' => false, 'departments' => null, 'sections' => null, 'keep' => []])
@php
    $fields = ['status', 'date_from', 'date_to', 'department', 'section'];
    $activeCount = collect($fields)->filter(fn ($f) => filled(request($f)))->count();
    $progress = config('sigap-ui.report_progress');
    $resetUrl = $action.($keep ? '?'.http_build_query($keep) : '');
@endphp

@foreach (['mobile', 'desktop'] as $mode)
    <form method="GET" action="{{ $action }}" @class(['mb-3', 'd-lg-none' => $mode === 'mobile', 'd-none d-lg-block' => $mode === 'desktop'])
          role="search" aria-label="Saring laporan">
        @foreach ($keep as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach

        @if ($mode === 'mobile')
            <div class="d-flex gap-2">
                <div class="flex-grow-1"><x-sg.search name="search" id="f-search-m" placeholder="Cari nama pekerjaan" /></div>
                <button type="button" class="btn btn-outline-secondary flex-none" data-bs-toggle="modal" data-bs-target="#report-filter">
                    <i class="bi bi-sliders" aria-hidden="true"></i> Filter
                    @if ($activeCount)<span class="sg-count ms-1">{{ $activeCount }}</span>@endif
                </button>
            </div>
            <x-sg.sheet id="report-filter" title="Filter laporan">
                @include('components.report.filter-fields', ['suffix' => 'm'])
                <x-slot:footer>
                    <a href="{{ $resetUrl }}" class="btn btn-outline-secondary">Reset</a>
                    <button type="submit" class="btn btn-primary">Terapkan</button>
                </x-slot:footer>
            </x-sg.sheet>
        @else
            <div class="sg-filter-bar">
                <div class="sg-filter-bar__search"><x-sg.search name="search" id="f-search-d" placeholder="Cari nama pekerjaan" /></div>
                @include('components.report.filter-fields', ['suffix' => 'd', 'inline' => true])
                <button type="submit" class="btn btn-primary">Terapkan</button>
                @if ($activeCount || filled(request('search')))
                    <a href="{{ $resetUrl }}" class="btn btn-quiet">Reset</a>
                @endif
            </div>
        @endif
    </form>
@endforeach
