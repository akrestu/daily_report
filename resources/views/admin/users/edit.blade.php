<x-app-layout>
    <x-slot name="header">
        Edit User
    </x-slot>

    <div class="card">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">Edit User Information</h5>
                </div>
                <div class="col text-end">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Users
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
                            placeholder="Enter name"
                            :value="old('name', $user->name)"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-input 
                            label="Email" 
                            name="email"
                            type="email"
                            required="true"
                            placeholder="Enter email address"
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
                        <x-input 
                            label="New Password (leave empty to keep current)" 
                            name="password"
                            type="password"
                            placeholder="Enter new password"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-input 
                            label="Confirm Password" 
                            name="password_confirmation"
                            type="password"
                            placeholder="Confirm new password"
                        />
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update User
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
                    <h6 class="mb-1">Delete This User</h6>
                    <p class="text-muted mb-0">Once deleted, all of this user's data will be permanently removed.</p>
                </div>
                <form action="{{ route('admin.users.destroy', $user) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" data-confirm="Are you sure you want to delete this user? This action cannot be undone.">
                        <i class="fas fa-trash me-1"></i> Delete User
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
</x-app-layout> 