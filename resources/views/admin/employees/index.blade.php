<x-app-layout title="Master Karyawan">

    @foreach(['success' => 'check-circle', 'error' => 'exclamation-circle', 'warning' => 'exclamation-triangle'] as $type => $icon)
        @if(session($type))
            <div class="alert alert-{{ $type === 'error' ? 'danger' : $type }} alert-dismissible fade show" role="alert">
                <i class="bi bi-{{ $icon }} me-2" aria-hidden="true"></i>{{ session($type) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    @endforeach

    @if($errors->has('import_errors'))
        <div class="alert alert-danger small">
            <strong>Import gagal, tidak ada data yang disimpan:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->get('import_errors') as $messages)
                    @foreach((array) $messages as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center g-2">
                <div class="col-md-5">
                    <h5 class="mb-0">Master Karyawan</h5>
                    <div class="text-muted small">Dipakai untuk verifikasi NIK pemohon di form tiket publik.</div>
                </div>
                <div class="col-md-7 text-md-end">
                    <a href="{{ route('admin.employees.template') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-download me-1"></i>Template Excel
                    </a>
                    <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="collapse" data-bs-target="#importPanel">
                        <i class="bi bi-box-arrow-in-down me-1"></i>Impor
                    </button>
                    <a href="{{ route('admin.employees.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>Tambah Karyawan
                    </a>
                </div>
            </div>

            <div class="collapse mt-3" id="importPanel">
                <form action="{{ route('admin.employees.import') }}" method="POST" enctype="multipart/form-data"
                    class="border rounded p-3 bg-light">
                    @csrf
                    <div class="row g-2 align-items-end">
                        <div class="col-md-8">
                            <label for="f-file" class="form-label small fw-semibold">File Excel (.xlsx, .xls, .csv)</label>
                            <input id="f-file" type="file" name="file" class="form-control form-control-sm" accept=".xlsx,.xls,.csv" required>
                            <div class="form-text">
                                Kolom: nik, nama, departemen, jabatan, job_site, no_hp, status (aktif/nonaktif).
                                NIK yang sudah ada akan diperbarui. Format kolom NIK sebagai <strong>Teks</strong> agar angka 0 di depan tidak hilang.
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-success btn-sm w-100"><i class="bi bi-upload me-1"></i>Upload &amp; Import</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('admin.employees.index') }}" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" name="search" aria-label="Cari" class="form-control form-control-sm" placeholder="Cari NIK, nama, jabatan..."
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="department" class="form-select form-select-sm">
                        <option value="">Semua Departemen</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">Filter</button>
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle small">
                    <thead class="table-light">
                        <tr>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Departemen</th>
                            <th>Jabatan</th>
                            <th>Site</th>
                            <th class="text-center">Tiket</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                        <tr>
                            <td class="font-monospace">{{ $employee->nik }}</td>
                            <td class="fw-semibold">{{ $employee->name }}</td>
                            <td>{{ $employee->department->name ?? '-' }}</td>
                            <td>{{ $employee->position ?? '-' }}</td>
                            <td>{{ $employee->jobSite->name ?? '-' }}</td>
                            <td class="text-center">{{ $employee->tickets_count }}</td>
                            <td>
                                <span class="badge {{ $employee->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $employee->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-outline-primary btn-sm" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('admin.employees.destroy', $employee) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Hapus karyawan ini? Karyawan dengan riwayat tiket akan dinonaktifkan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus"><i class="bi bi-trash3"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Belum ada data karyawan. Download template lalu import data karyawan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $employees->links() }}
        </div>
    </div>
</x-app-layout>
