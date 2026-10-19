{{-- Form pengguna (tambah & edit). $user = null saat menambah. Butuh: $roles, $departments, $jobSites (koleksi model). --}}
@php($editing = (bool) $user)
<form method="POST" action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}" novalidate
      x-data="{ busy: false, show: false }" @submit="busy = true">
    @csrf
    @if ($editing) @method('PUT') @endif

    <x-sg.page-header :title="$editing ? 'Edit Pengguna' : 'Tambah Pengguna'" class="d-none d-lg-flex" :subtitle="$editing ? $user->name : 'Akun baru bisa langsung masuk dengan User ID dan kata sandi ini.'">
        <x-slot:actions>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" class="btn btn-primary" :disabled="busy">Simpan</button>
        </x-slot:actions>
    </x-sg.page-header>

    @if ($errors->any())
        <div class="alert alert-danger d-flex gap-2 mb-3" role="alert">
            <i class="bi bi-exclamation-octagon fs-5" aria-hidden="true"></i><div><strong>Periksa kembali isian yang ditandai.</strong></div>
        </div>
    @endif

    <div class="row g-3 g-lg-4">
        <div class="col-lg-8">
            <section class="card mb-3" aria-labelledby="u-account">
                <div class="sg-entry__body border-0">
                    <h2 id="u-account" class="sg-section-label">Akun</h2>
                    <div class="mb-3">
                        <label for="u-name" class="form-label fw-semibold">Nama lengkap <span class="text-danger">*</span></label>
                        <input id="u-name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user?->name) }}" required maxlength="255" autocomplete="off">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label for="u-userid" class="form-label fw-semibold">User ID</label>
                            <input id="u-userid" name="user_id" class="form-control @error('user_id') is-invalid @enderror" value="{{ old('user_id', $user?->user_id) }}" maxlength="255" autocomplete="off" autocapitalize="off">
                            <div class="form-text">Dipakai untuk masuk. Mis. NIK karyawan.</div>
                            @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-6">
                            <label for="u-email" class="form-label fw-semibold">Email</label>
                            <input id="u-email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user?->email) }}" maxlength="255" autocomplete="off" placeholder="Opsional">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div>
                        <label for="u-password" class="form-label fw-semibold">Kata sandi @unless ($editing)<span class="text-danger">*</span>@endunless</label>
                        <div class="input-group">
                            <input id="u-password" :type="show ? 'text' : 'password'" name="password" class="form-control @error('password') is-invalid @enderror"
                                   {{ $editing ? '' : 'required' }} autocomplete="new-password" placeholder="{{ $editing ? 'Kosongkan bila tidak diganti' : 'Minimal 8 karakter' }}">
                            <button type="button" class="btn btn-outline-secondary btn-icon" @click="show = !show" :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                                <i class="bi" :class="show ? 'bi-eye-slash' : 'bi-eye'" aria-hidden="true"></i>
                            </button>
                        </div>
                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
            </section>

            <section class="card" aria-labelledby="u-org">
                <div class="sg-entry__body border-0">
                    <h2 id="u-org" class="sg-section-label">Peran & penempatan</h2>
                    <div class="mb-3">
                        <label for="u-role" class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                        <select id="u-role" name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                            <option value="">Pilih role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @selected(old('role_id', $user?->role_id) == $role->id)>{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label for="u-dept" class="form-label fw-semibold">Departemen</label>
                            <select id="u-dept" name="department_id" class="form-select @error('department_id') is-invalid @enderror">
                                <option value="">Tanpa departemen</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}" @selected(old('department_id', $user?->department_id) == $dept->id)>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Wajib untuk Level 1–7 agar alur persetujuan berjalan.</div>
                            @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-6">
                            <label for="u-site" class="form-label fw-semibold">Job site</label>
                            <select id="u-site" name="job_site_id" class="form-select @error('job_site_id') is-invalid @enderror">
                                <option value="">Semua site (khusus admin)</option>
                                @foreach ($jobSites as $site)
                                    <option value="{{ $site->id }}" @selected(old('job_site_id', $user?->job_site_id) == $site->id)>{{ $site->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Site utama: default saat membuat laporan. Wajib untuk Level 8.</div>
                            @error('job_site_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        {{-- Multi-site: site tambahan yang juga dicakup (laporan, persetujuan, daftar PIC) --}}
                        @php($extraSites = collect(old('extra_job_site_ids', $user?->extraJobSites?->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id))
                        <div class="col-12">
                            <fieldset>
                                <legend class="form-label fw-semibold fs-6 mb-1">Site tambahan <span class="text-muted fw-normal small">(opsional)</span></legend>
                                <div class="form-text mt-0 mb-2">Untuk pengguna yang membawahi lebih dari satu site. Laporan, persetujuan, dan daftar PIC mencakup site utama + site tambahan.</div>
                                <div class="sg-chip-row">
                                    @foreach ($jobSites as $site)
                                        <input type="checkbox" class="btn-check" name="extra_job_site_ids[]" id="u-xsite-{{ $site->id }}" value="{{ $site->id }}" autocomplete="off" @checked($extraSites->contains($site->id))>
                                        <label class="sg-chip" for="u-xsite-{{ $site->id }}">{{ $site->name }}</label>
                                    @endforeach
                                </div>
                                @error('extra_job_site_ids.*')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
                            </fieldset>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        @if ($editing && $user->id !== auth()->id())
            <aside class="col-lg-4">
                <section class="card p-3" aria-labelledby="u-danger">
                    <h2 id="u-danger" class="h6 fw-bold text-danger mb-1">Hapus pengguna</h2>
                    <p class="small text-muted">Akun dihapus permanen. Laporan yang sudah dibuat tetap tersimpan.</p>
                    <button type="button" class="btn btn-reject-outline w-100" data-bs-toggle="modal" data-bs-target="#user-delete">Hapus pengguna ini</button>
                </section>
            </aside>
        @endif
    </div>

    <x-sg.sticky-actions>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary flex-fill">Batal</a>
        <button type="submit" class="btn btn-primary flex-fill" :disabled="busy">Simpan</button>
    </x-sg.sticky-actions>
</form>

@if ($editing && $user->id !== auth()->id())
    <x-sg.confirm-danger id="user-delete" title="Hapus pengguna?" :action="route('admin.users.destroy', $user)"
                         :text="'Akun '.$user->name.' akan dihapus permanen.'" />
@endif
