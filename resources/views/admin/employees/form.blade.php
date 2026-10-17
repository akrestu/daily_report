{{-- Admin · Tambah / Edit Karyawan --}}
@php($editing = $employee->exists)
<x-app-layout :title="$editing ? 'Edit Karyawan' : 'Tambah Karyawan'" :back="route('admin.employees.index')">
    <form method="POST" action="{{ $editing ? route('admin.employees.update', $employee) : route('admin.employees.store') }}" novalidate
          x-data="{ busy: false }" @submit="busy = true">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-sg.page-header :title="$editing ? 'Edit Karyawan' : 'Tambah Karyawan'" class="d-none d-lg-flex" :subtitle="$editing ? $employee->name : 'Data ini dipakai untuk verifikasi NIK di form tiket publik.'">
            <x-slot:actions>
                <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary" :disabled="busy">Simpan</button>
            </x-slot:actions>
        </x-sg.page-header>

        <section class="card" style="max-width:720px">
            <div class="sg-entry__body border-0">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label for="e-nik" class="form-label fw-semibold">NIK <span class="text-danger">*</span></label>
                        <input id="e-nik" name="nik" inputmode="numeric" class="form-control @error('nik') is-invalid @enderror" value="{{ old('nik', $employee->nik) }}" required maxlength="30">
                        @error('nik')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-7">
                        <label for="e-name" class="form-label fw-semibold">Nama lengkap <span class="text-danger">*</span></label>
                        <input id="e-name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $employee->name) }}" required maxlength="255">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="e-dept" class="form-label fw-semibold">Departemen <span class="text-danger">*</span></label>
                        <select id="e-dept" name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                            <option value="">Pilih departemen</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" @selected(old('department_id', $employee->department_id) == $dept->id)>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="e-position" class="form-label fw-semibold">Jabatan</label>
                        <input id="e-position" name="position" class="form-control @error('position') is-invalid @enderror" value="{{ old('position', $employee->position) }}" maxlength="255">
                        @error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="e-site" class="form-label fw-semibold">Site</label>
                        <select id="e-site" name="job_site_id" class="form-select @error('job_site_id') is-invalid @enderror">
                            <option value="">Tidak ada</option>
                            @foreach ($jobSites as $site)
                                <option value="{{ $site->id }}" @selected(old('job_site_id', $employee->job_site_id) == $site->id)>{{ $site->name }}</option>
                            @endforeach
                        </select>
                        @error('job_site_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="e-phone" class="form-label fw-semibold">No. HP</label>
                        <input id="e-phone" type="tel" inputmode="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $employee->phone) }}" maxlength="30">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" id="e-active" @checked(old('is_active', $employee->exists ? $employee->is_active : true))>
                            <label class="form-check-label" for="e-active">Aktif (nonaktif = tidak bisa mengajukan tiket)</label>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <x-sg.sticky-actions>
            <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary flex-fill">Batal</a>
            <button type="submit" class="btn btn-primary flex-fill" :disabled="busy">Simpan</button>
        </x-sg.sticky-actions>
    </form>
</x-app-layout>
