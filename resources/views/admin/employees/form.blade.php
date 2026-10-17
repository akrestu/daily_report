<x-app-layout :title="$employee->exists ? 'Edit Karyawan' : 'Tambah Karyawan'" :back="route('admin.employees.index')">

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">{{ $employee->exists ? 'Edit Karyawan' : 'Tambah Karyawan' }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST"
                        action="{{ $employee->exists ? route('admin.employees.update', $employee) : route('admin.employees.store') }}">
                        @csrf
                        @if($employee->exists)
                            @method('PUT')
                        @endif

                        <div class="row g-3">
                            <div class="col-md-5">
                                <label for="f-nik" class="form-label small fw-semibold">NIK <span class="text-danger">*</span></label>
                                <input id="f-nik" type="text" name="nik" class="form-control @error('nik') is-invalid @enderror"
                                    value="{{ old('nik', $employee->nik) }}" required maxlength="30">
                                @error('nik')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-7">
                                <label for="f-name" class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                                <input id="f-name" type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $employee->name) }}" required maxlength="255">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="f-department_id" class="form-label small fw-semibold">Departemen <span class="text-danger">*</span></label>
                                <select id="f-department_id" name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                                    <option value="">— Pilih —</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="f-position" class="form-label small fw-semibold">Jabatan</label>
                                <input id="f-position" type="text" name="position" class="form-control" value="{{ old('position', $employee->position) }}" maxlength="255">
                            </div>
                            <div class="col-md-6">
                                <label for="f-job_site_id" class="form-label small fw-semibold">Site</label>
                                <select id="f-job_site_id" name="job_site_id" class="form-select">
                                    <option value="">— Tidak ada —</option>
                                    @foreach($jobSites as $site)
                                        <option value="{{ $site->id }}" {{ old('job_site_id', $employee->job_site_id) == $site->id ? 'selected' : '' }}>
                                            {{ $site->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="f-phone" class="form-label small fw-semibold">No. HP</label>
                                <input id="f-phone" type="text" name="phone" class="form-control" value="{{ old('phone', $employee->phone) }}" maxlength="30">
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                                        {{ old('is_active', $employee->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="is_active">Aktif (nonaktif = tidak bisa mengajukan tiket)</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
