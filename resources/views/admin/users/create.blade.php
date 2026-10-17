<x-app-layout title="Tambah Pengguna" :back="route('admin.users.index')">

    <div class="card">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">Pengguna baru</h5>
                </div>
                <div class="col text-end">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Users
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <x-input 
                            label="Name" 
                            name="name"
                            required="true"
                            placeholder="Nama lengkap"
                            :value="old('name')"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-input
                            label="Email"
                            name="email"
                            type="email"
                            placeholder="Email (opsional)"
                            :value="old('email')"
                        />
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <x-select
                            label="Role"
                            name="role_id"
                            required="true"
                            :options="$roles ?? []"
                            :selected="old('role_id')"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-select
                            label="Department"
                            name="department_id"
                            required="true"
                            :options="$departments ?? []"
                            :selected="old('department_id')"
                        />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <x-select
                            label="Job Site"
                            name="job_site_id"
                            :options="$jobSites ?? []"
                            :selected="old('job_site_id')"
                            placeholder="Pilih job site (opsional)"
                        />
                        <small class="text-muted">Kosongkan untuk admin yang bisa mengakses semua job site</small>
                    </div>
                    <div class="col-md-6">
                        <x-input
                            label="User ID (optional)"
                            name="user_id"
                            placeholder="User ID"
                            :value="old('user_id')"
                        />
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <x-input 
                            label="Password" 
                            name="password"
                            type="password"
                            required="true"
                            placeholder="Kata sandi"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-input 
                            label="Confirm Password" 
                            name="password_confirmation"
                            type="password"
                            required="true"
                            placeholder="Ulangi kata sandi"
                        />
                    </div>
                </div>
                
                <!-- Hide email verification toggle
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="markAsVerified" name="email_verified" value="1" checked>
                        <label class="form-check-label" for="markAsVerified">Tandai email terverifikasi</label>
                    </div>
                </div>
                -->
                
                <!-- Replace welcome email checkbox with notification info -->
                <div class="mb-3">
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle me-1"></i> New users will automatically receive a welcome notification.
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="reset" class="btn btn-secondary">Reset</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-floppy me-1"></i> Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout> 