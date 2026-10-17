{{-- Admin · SLA & Hari Libur --}}
@php($priorityUi = config('sigap-ui.priority'))
<x-app-layout title="SLA &amp; Hari Libur">
    <x-sg.page-header title="SLA & Hari Libur"
                      :subtitle="'Target dihitung dalam jam kerja (Senin–Sabtu '.config('tickets.working_hours.start').'–'.config('tickets.working_hours.end').').'" />

    @if ($errors->any())
        <div class="alert alert-danger d-flex gap-2 mb-3" role="alert">
            <i class="bi bi-exclamation-octagon fs-5" aria-hidden="true"></i>
            <ul class="mb-0 ps-3 small">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="row g-3 g-lg-4">
        <div class="col-lg-7">
            <section class="card p-3 p-lg-4 mb-3" aria-labelledby="sla-targets">
                <h2 id="sla-targets" class="h6 fw-bold mb-1">Target per prioritas</h2>
                <p class="small text-muted">Boleh desimal, mis. 0,5 = 30 menit. Berlaku untuk tiket baru.</p>
                <form method="POST" action="{{ route('admin.sla.update') }}">
                    @csrf
                    @method('PUT')
                    @foreach (\App\Models\Ticket::PRIORITIES as $priority => $label)
                        @php($policy = $policies[$priority] ?? null)
                        <div class="sg-sla-row">
                            <div class="sg-sla-row__label">
                                <span class="sg-priority {{ $priorityUi[$priority]['class'] ?? '' }}">{{ $priorityUi[$priority]['code'] ?? '' }}</span>
                                <span class="fw-semibold">{{ $label }}</span>
                            </div>
                            <div>
                                <label class="small text-muted d-block" for="sla-{{ $priority }}-r">Respons (jam)</label>
                                <input id="sla-{{ $priority }}-r" type="number" step="0.25" min="0.25" inputmode="decimal" class="form-control" required
                                       name="policies[{{ $priority }}][response_hours]"
                                       value="{{ old("policies.$priority.response_hours", $policy ? round($policy->response_minutes / 60, 2) : '') }}">
                            </div>
                            <div>
                                <label class="small text-muted d-block" for="sla-{{ $priority }}-s">Penyelesaian (jam)</label>
                                <input id="sla-{{ $priority }}-s" type="number" step="0.25" min="0.25" inputmode="decimal" class="form-control" required
                                       name="policies[{{ $priority }}][resolution_hours]"
                                       value="{{ old("policies.$priority.resolution_hours", $policy ? round($policy->resolution_minutes / 60, 2) : '') }}">
                            </div>
                        </div>
                    @endforeach
                    <p class="small text-muted mt-3">Tiket yang sedang berjalan tetap memakai tenggat lama, kecuali prioritasnya dikoreksi.</p>
                    <button type="submit" class="btn btn-primary">Simpan target SLA</button>
                </form>
            </section>

            <section class="card p-3 p-lg-4" aria-labelledby="sla-matrix">
                <h2 id="sla-matrix" class="h6 fw-bold mb-3">Matriks prioritas (dampak × urgensi)</h2>
                <div class="table-responsive">
                    <table class="table table-sm align-middle text-center mb-0 sg-table">
                        <thead>
                            <tr>
                                <th class="text-start">Dampak \ Urgensi</th>
                                @foreach (\App\Models\Ticket::URGENCIES as $u => $uLabel)<th class="small">{{ $uLabel }}</th>@endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (array_reverse(\App\Models\Ticket::IMPACTS, true) as $i => $iLabel)
                                <tr>
                                    <th class="text-start small">{{ $iLabel }}</th>
                                    @foreach (array_keys(\App\Models\Ticket::URGENCIES) as $u)
                                        @php($p = \App\Models\Ticket::priorityFor($i, $u))
                                        <td><span class="sg-priority {{ $priorityUi[$p]['class'] ?? '' }}">{{ ($priorityUi[$p]['code'] ?? '').' '.\App\Models\Ticket::PRIORITIES[$p] }}</span></td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="col-lg-5">
            <section class="card p-3 p-lg-4" aria-labelledby="sla-holidays" x-data="{ item: {} }">
                <h2 id="sla-holidays" class="h6 fw-bold mb-1">Hari libur</h2>
                <p class="small text-muted">Tanggal merah dan cuti bersama tidak dihitung dalam jam SLA dan penutupan otomatis.</p>
                <form method="POST" action="{{ route('admin.sla.holidays.store') }}" class="row g-2 mb-3">
                    @csrf
                    <div class="col-5">
                        <label for="h-date" class="visually-hidden">Tanggal libur</label>
                        <input id="h-date" type="date" name="date" class="form-control" required value="{{ old('date') }}">
                    </div>
                    <div class="col-7">
                        <label for="h-name" class="visually-hidden">Keterangan hari libur</label>
                        <input id="h-name" type="text" name="name" class="form-control" placeholder="Keterangan" required maxlength="255" value="{{ old('name') }}">
                    </div>
                    <div class="col-12"><button type="submit" class="btn btn-outline-secondary w-100"><i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah hari libur</button></div>
                </form>

                @if ($holidays->isEmpty())
                    <p class="text-muted small mb-0">Belum ada hari libur.</p>
                @else
                    <ul class="list-unstyled mb-0">
                        @foreach ($holidays as $holiday)
                            <li class="d-flex align-items-center gap-2 py-2 border-top {{ $holiday->date->lt(today()) ? 'text-muted' : '' }}">
                                <span class="flex-grow-1 min-w-0">
                                    <span class="d-block fw-semibold">{{ $holiday->date->translatedFormat('D, d M Y') }}</span>
                                    <span class="d-block small sg-clamp-1">{{ $holiday->name }}</span>
                                </span>
                                <button type="button" class="btn btn-quiet btn-icon text-danger" aria-label="Hapus {{ $holiday->name }}"
                                        @click="item = @js(['name' => $holiday->name, 'url' => route('admin.sla.holidays.destroy', $holiday)]); window.bootstrap.Modal.getOrCreateInstance(document.getElementById('holiday-delete')).show()">
                                    <i class="bi bi-trash3" aria-hidden="true"></i>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <x-sg.sheet id="holiday-delete" title="Hapus hari libur?">
                    <form method="POST" :action="item.url">
                        @csrf
                        @method('DELETE')
                        <p><strong x-text="item.name"></strong> akan kembali dihitung sebagai hari kerja.</p>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-reject flex-fill">Hapus</button>
                        </div>
                    </form>
                </x-sg.sheet>
            </section>
        </div>
    </div>
</x-app-layout>
