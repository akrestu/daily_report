{{-- Field filter laporan; di-include oleh components/report/filters (variabel: $status, $departments, $sections, $progress, $suffix, $inline) --}}
@php($inline = $inline ?? false)
@if ($status)
    <div @class(['mb-3' => ! $inline])>
        <label for="f-status-{{ $suffix }}" @class(['form-label fw-semibold', 'visually-hidden' => $inline])>Progres</label>
        <select id="f-status-{{ $suffix }}" name="status" class="form-select">
            <option value="">Semua progres</option>
            @foreach ($progress as $value => $p)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $p['label'] }}</option>
            @endforeach
        </select>
    </div>
@endif
@if ($departments)
    <div @class(['mb-3' => ! $inline])>
        <label for="f-dept-{{ $suffix }}" @class(['form-label fw-semibold', 'visually-hidden' => $inline])>Departemen</label>
        <select id="f-dept-{{ $suffix }}" name="department" class="form-select">
            <option value="">Semua departemen</option>
            @foreach ($departments as $id => $name)
                <option value="{{ $id }}" @selected((string) request('department') === (string) $id)>{{ $name }}</option>
            @endforeach
        </select>
    </div>
@endif
@if ($sections && $sections->isNotEmpty())
    <div @class(['mb-3' => ! $inline])>
        <label for="f-sec-{{ $suffix }}" @class(['form-label fw-semibold', 'visually-hidden' => $inline])>Section</label>
        <select id="f-sec-{{ $suffix }}" name="section" class="form-select">
            <option value="">Semua section</option>
            @foreach ($sections as $section)
                <option value="{{ $section->id }}" @selected((string) request('section') === (string) $section->id)>{{ $section->name }}</option>
            @endforeach
        </select>
    </div>
@endif
<div @class(['row g-2' => ! $inline, 'd-contents' => $inline])>
    <div @class(['col-6' => ! $inline])>
        <label for="f-from-{{ $suffix }}" @class(['form-label fw-semibold', 'visually-hidden' => $inline])>Dari tanggal</label>
        <input type="date" id="f-from-{{ $suffix }}" name="date_from" class="form-control" value="{{ request('date_from') }}" max="{{ now()->format('Y-m-d') }}"
               @if ($inline) title="Dari tanggal" @endif>
    </div>
    <div @class(['col-6' => ! $inline])>
        <label for="f-to-{{ $suffix }}" @class(['form-label fw-semibold', 'visually-hidden' => $inline])>Sampai tanggal</label>
        <input type="date" id="f-to-{{ $suffix }}" name="date_to" class="form-control" value="{{ request('date_to') }}" max="{{ now()->format('Y-m-d') }}"
               @if ($inline) title="Sampai tanggal" @endif>
    </div>
</div>
