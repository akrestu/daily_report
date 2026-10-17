<x-app-layout title="Detail Departemen" :back="route('admin.departments.index')">

    <div class="card">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">{{ $department->name }}</h5>
                </div>
                <div class="col text-end">
                    <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left me-1"></i> Back to Departments
                    </a>
                    <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-primary">
                        <i class="bi bi-pencil-square me-1"></i> Edit departemen
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="text-muted">Informasi departemen</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th class="bg-light" width="30%">Nama</th>
                                <td>{{ $department->name }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Kode</th>
                                <td>{{ $department->code }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Deskripsi</th>
                                <td>{{ $department->description ?: 'No description available' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Created</th>
                                <td>{{ $department->created_at->format('M d, Y h:i A') }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Terakhir diperbarui</th>
                                <td>{{ $department->updated_at->format('M d, Y h:i A') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted">Statistik departemen</h6>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="card bg-primary bg-opacity-10">
                                <div class="card-body text-center">
                                    <div class="fs-1 fw-bold text-primary">{{ $department->users->count() }}</div>
                                    <div>Total pengguna</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-success bg-opacity-10">
                                <div class="card-body text-center">
                                    <div class="fs-1 fw-bold text-success">{{ $department->dailyReports->count() }}</div>
                                    <div>Total laporan</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <h6 class="mb-3">Anggota departemen</h6>
            @if($department->users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($department->users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2 bg-primary rounded-circle text-white d-flex align-items-center justify-content-center">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div>{{ $user->name }}</div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->role->name ?? 'No Role' }}</td>
                                <td>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-person-gear me-1"></i> Edit pengguna
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i> Belum ada pengguna di departemen ini.
                </div>
            @endif
        </div>
        <div class="card-footer bg-white py-3">
            <div class="d-flex justify-content-end">
                <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-primary">
                    <i class="bi bi-pencil-square me-1"></i> Edit departemen
                </a>
            </div>
        </div>
    </div>
    
    <style>
        .avatar-sm {
            width: 36px;
            height: 36px;
            font-size: 14px;
        }
    </style>
</x-app-layout> 