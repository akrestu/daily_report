<x-app-layout title="SLA &amp; Hari Libur">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger small">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Target SLA per Prioritas</h5>
                    <div class="text-muted small">
                        Dalam <strong>jam kerja</strong> (Senin–Sabtu {{ config('tickets.working_hours.start') }}–{{ config('tickets.working_hours.end') }}).
                        Boleh desimal, mis. 0.5 = 30 menit.
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.sla.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr><th>Prioritas</th><th>Respons (jam)</th><th>Penyelesaian (jam)</th></tr>
                                </thead>
                                <tbody>
                                    @foreach(\App\Models\Ticket::PRIORITIES as $priority => $label)
                                        @php($policy = $policies[$priority] ?? null)
                                        <tr>
                                            <td class="fw-semibold">{{ $label }}</td>
                                            <td>
                                                <input type="number" step="0.25" min="0.25" class="form-control form-control-sm"
                                                    name="policies[{{ $priority }}][response_hours]" required aria-label="Respons (jam) {{ $label }}"
                                                    value="{{ old("policies.$priority.response_hours", $policy ? round($policy->response_minutes / 60, 2) : '') }}">
                                            </td>
                                            <td>
                                                <input type="number" step="0.25" min="0.25" class="form-control form-control-sm"
                                                    name="policies[{{ $priority }}][resolution_hours]" required aria-label="Penyelesaian (jam) {{ $label }}"
                                                    value="{{ old("policies.$priority.resolution_hours", $policy ? round($policy->resolution_minutes / 60, 2) : '') }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-light border small">
                            <i class="bi bi-info-circle text-primary me-1"></i>
                            Perubahan berlaku untuk tiket baru. Tiket yang sudah berjalan tetap memakai tenggat lama,
                            kecuali prioritasnya dikoreksi.
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan Target SLA</button>
                    </form>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-white py-3"><h6 class="mb-0">Matriks Prioritas (Dampak × Urgensi)</h6></div>
                <div class="card-body table-responsive">
                    <table class="table table-sm table-bordered small text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start">Dampak \ Urgensi</th>
                                @foreach(\App\Models\Ticket::URGENCIES as $u => $uLabel)<th>{{ $uLabel }}</th>@endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_reverse(\App\Models\Ticket::IMPACTS, true) as $i => $iLabel)
                                <tr>
                                    <th class="text-start">{{ $iLabel }}</th>
                                    @foreach(array_keys(\App\Models\Ticket::URGENCIES) as $u)
                                        <td>{{ \App\Models\Ticket::PRIORITIES[\App\Models\Ticket::priorityFor($i, $u)] }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Hari Libur</h5>
                    <div class="text-muted small">Tanggal merah / cuti bersama tidak dihitung dalam jam SLA dan auto-close.</div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.sla.holidays.store') }}" class="row g-2 mb-3">
                        @csrf
                        <div class="col-5"><input type="date" name="date" class="form-control form-control-sm" aria-label="Tanggal libur" required value="{{ old('date') }}"></div>
                        <div class="col-7"><input type="text" name="name" class="form-control form-control-sm" aria-label="Keterangan hari libur" placeholder="Keterangan" required maxlength="255" value="{{ old('name') }}"></div>
                        <div class="col-12"><button type="submit" class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-plus-lg me-1"></i>Tambah Hari Libur</button></div>
                    </form>

                    <ul class="list-group list-group-flush small">
                        @forelse($holidays as $holiday)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 {{ $holiday->date->lt(today()) ? 'text-muted' : '' }}">
                                <span><strong>{{ $holiday->date->translatedFormat('D, d M Y') }}</strong> — {{ $holiday->name }}</span>
                                <form method="POST" action="{{ route('admin.sla.holidays.destroy', $holiday) }}" onsubmit="return confirm('Hapus hari libur ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link btn-sm text-danger p-0" aria-label="Hapus"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                                </form>
                            </li>
                        @empty
                            <li class="list-group-item px-0 text-muted">Belum ada hari libur.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
