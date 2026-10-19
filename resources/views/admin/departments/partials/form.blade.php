{{-- Form departemen (halaman). $department = null saat menambah. Di daftar, tambah/edit memakai sheet. --}}
@php($editing = (bool) $department)
<form method="POST" action="{{ $editing ? route('admin.departments.update', $department) : route('admin.departments.store') }}" novalidate
      x-data="{ busy: false }" @submit="busy = true">
    @csrf
    @if ($editing) @method('PUT') @endif

    <x-sg.page-header :title="$editing ? 'Edit Departemen' : 'Tambah Departemen'" class="d-none d-lg-flex" :subtitle="$editing ? $department->name : null">
        <x-slot:actions>
            <a href="{{ $editing ? route('admin.departments.show', $department) : route('admin.departments.index') }}" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" class="btn btn-primary" :disabled="busy">Simpan</button>
        </x-slot:actions>
    </x-sg.page-header>

    <section class="card" style="max-width:640px">
        <div class="sg-entry__body border-0">
            <div class="mb-3">
                <label for="dp-name" class="form-label fw-semibold">Nama <span class="text-danger">*</span></label>
                <input id="dp-name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $department?->name) }}" required maxlength="255">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="dp-code" class="form-label fw-semibold">Kode <span class="text-danger">*</span></label>
                <input id="dp-code" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $department?->code) }}" required maxlength="50" placeholder="Mis. HR, IT, FIN" autocapitalize="characters">
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div>
                <label for="dp-desc" class="form-label fw-semibold">Deskripsi</label>
                <textarea id="dp-desc" name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $department?->description) }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-check form-switch">
                <input type="hidden" name="handles_tickets" value="0">
                <input id="dp-tickets" type="checkbox" role="switch" name="handles_tickets" value="1" class="form-check-input" @checked(old('handles_tickets', $department?->handles_tickets))>
                <label for="dp-tickets" class="form-check-label fw-semibold">Tim layanan tiket</label>
                <div class="form-text">Anggota departemen ini memproses tiket masuk (level 4 ke atas memilah &amp; menugaskan).</div>
            </div>
        </div>
    </section>

    <x-sg.sticky-actions>
        <a href="{{ $editing ? route('admin.departments.show', $department) : route('admin.departments.index') }}" class="btn btn-outline-secondary flex-fill">Batal</a>
        <button type="submit" class="btn btn-primary flex-fill" :disabled="busy">Simpan</button>
    </x-sg.sticky-actions>
</form>
