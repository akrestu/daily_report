<x-app-layout title="Edit Pengguna" :back="route('admin.users.index')">

    <div class="card">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">Edit data pengguna</h5>
                </div>
                <div class="col text-end">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Users
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PATCH')
                
                <div class="row">
                    <div class="col-md-6">
                        <x-input 
                            label="Name" 
                            name="name"
                            required="true"
                            placeholder="Nama lengkap"
                            :value="old('name', $user->name)"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-input
                            label="Email"
                            name="email"
                            type="email"
                            placeholder="Email (opsional)"
                            :value="old('email', $user->email)"
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
                            :selected="old('role_id', $user->role_id)"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-select
                            label="Department"
                            name="department_id"
                            required="true"
                            :options="$departments ?? []"
                            :selected="old('department_id', $user->department_id)"
                        />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <x-select
                            label="Job Site"
                            name="job_site_id"
                            :options="$jobSites ?? []"
                            :selected="old('job_site_id', $user->job_site_id)"
                            placeholder="Pilih job site (opsional)"
                        />
                        <small class="text-muted">Kosongkan untuk admin yang bisa mengakses semua job site</small>
                    </div>
                    <div class="col-md-6">
                        <x-input
                            label="User ID (optional)"
                            name="user_id"
                            placeholder="User ID"
                            :value="old('user_id', $user->user_id)"
                        />
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <x-input 
                            label="New Password (leave empty to keep current)" 
                            name="password"
                            type="password"
                            placeholder="Kata sandi baru"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-input 
                            label="Confirm Password" 
                            name="password_confirmation"
                            type="password"
                            placeholder="Ulangi kata sandi baru"
                        />
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-floppy me-1"></i> Simpan pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    @if($user->id !== auth()->id())
    <div class="card mt-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-danger">Danger Zone</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">Hapus pengguna ini</h6>
                    <p class="text-muted mb-0">Once deleted, all of this user's data will be permanently removed.</p>
                </div>
                <form action="{{ route('admin.users.destroy', $user) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" data-confirm="Hapus pengguna ini? Tindakan ini tidak dapat dibatalkan.">
                        <i class="bi bi-trash3 me-1"></i> Hapus pengguna
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
</x-app-layout> 