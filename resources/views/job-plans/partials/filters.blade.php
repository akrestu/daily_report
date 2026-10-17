{{-- Filter rencana kerja (GET): chip status + toolbar cari/tanggal. Variabel: $action --}}
@php
    $chips = collect(['' => 'Semua', 'assigned' => 'Aktif', 'converted' => 'Dikonversi'])
        ->map(fn ($label, $value) => ['label' => $label, 'href' => $action.'?'.http_build_query(array_filter(['status' => $value] + request()->except('status', 'page'))), 'active' => (string) request('status') === (string) $value])
        ->values()->all();
@endphp
<x-sg.chip-filter class="mb-3" label="Status rencana" :items="$chips" />
<form method="GET" action="{{ $action }}" class="sg-filter-bar mb-3" role="search" aria-label="Saring rencana kerja">
    @if (request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
    <div class="sg-filter-bar__search"><x-sg.search name="search" placeholder="Cari nama pekerjaan" /></div>
    <label for="p-from" class="visually-hidden">Dari tanggal</label>
    <input type="date" id="p-from" name="date_from" class="form-control" value="{{ request('date_from') }}" title="Dari tanggal">
    <label for="p-to" class="visually-hidden">Sampai tanggal</label>
    <input type="date" id="p-to" name="date_to" class="form-control" value="{{ request('date_to') }}" title="Sampai tanggal">
    <button type="submit" class="btn btn-primary">Terapkan</button>
    @if (request()->hasAny(['search', 'date_from', 'date_to']))
        <a href="{{ $action }}{{ request('status') ? '?status='.request('status') : '' }}" class="btn btn-quiet">Reset</a>
    @endif
</form>
