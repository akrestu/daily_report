<x-app-layout>
    <x-slot name="header">
        Create User
    </x-slot>

    <div class="card">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">New User</h5>
                </div>
                <div class="col text-end">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Users
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
                            placeholder="Enter name"
                            :value="old('name')"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-input 
                            label="Email" 
                            name="email"
                            type="email"
                            required="true"
                            placeholder="Enter email address"
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
                        <x-input 
                            label="Password" 
                            name="password"
                            type="password"
                            required="true"
                            placeholder="Enter password"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-input 
                            label="Confirm Password" 
                            name="password_confirmation"
                            type="password"
                            required="true"
                            placeholder="Confirm password"
                        />
                    </div>
                </div>
                
                <!-- Hide email verification toggle
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="markAsVerified" name="email_verified" value="1" checked>
                        <label class="form-check-label" for="markAsVerified">Mark email as verified</label>
                    </div>
                </div>
                -->
                
                <!-- Replace welcome email checkbox with notification info -->
                <div class="mb-3">
                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle me-1"></i> New users will automatically receive a welcome notification.
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="reset" class="btn btn-secondary">Reset</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout> 