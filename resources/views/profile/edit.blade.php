<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center">
            <i class="fas fa-user-circle fs-4 me-2 text-primary"></i>
            <span>Profile Settings</span>
        </div>
    </x-slot>

    <div class="row g-4">
        <!-- Mobile-only profile picture section that appears at the top on mobile -->
        <div class="col-12 d-md-none">
            <div class="card shadow-sm mb-3 border-0">
                <div class="card-body text-center py-4">
                    <div class="d-flex justify-content-center mb-3">
                        @if($user->profile_picture)
                            <img src="{{ $user->profile_picture_url }}" alt="Profile Picture" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                        @else
                            <div class="avatar-lg bg-primary rounded-circle text-white d-flex align-items-center justify-content-center" style="box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="text-muted mb-3">{{ $user->email }}</p>
                    
                    <form action="{{ route('profile.update-picture') }}" method="POST" enctype="multipart/form-data" id="mobileProfilePictureForm">
                        @csrf
                        <div>
                            <div class="d-grid">
                                <label for="mobile_profile_picture" class="btn btn-primary">
                                    <i class="fas fa-camera me-1"></i> Change Picture
                                </label>
                                <input type="file" class="d-none" id="mobile_profile_picture" name="profile_picture" accept="image/*">
                            </div>
                            @error('profile_picture')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-center">Max size: 2MB. Formats: JPG, PNG, GIF</div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Desktop Profile Header - Desktop only -->
        <div class="col-12 d-none d-md-block mb-2">
            <div class="card border-0 overflow-hidden position-relative profile-header">
                <div class="profile-cover-bg"></div>
                <div class="card-body position-relative z-index-1 pt-5 pb-0">
                    <div class="d-flex align-items-end">
                        <div class="profile-pic me-4">
                            @if($user->profile_picture)
                                <img src="{{ $user->profile_picture_url }}" alt="Profile Picture" class="rounded-circle border border-4 border-white shadow" style="width: 120px; height: 120px; object-fit: cover;">
                            @else
                                <div class="avatar-xl bg-white rounded-circle text-indigo d-flex align-items-center justify-content-center border border-4 border-white shadow">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div class="profile-info pb-3">
                            <h4 class="mb-1 text-white">{{ $user->name }}</h4>
                            <div>
                                <span class="badge bg-white text-dark me-2">
                                    <i class="fas fa-building me-1 text-secondary"></i>
                                    {{ $user->department->name ?? 'No Department' }}
                                </span>
                                <span class="badge bg-white text-dark">
                                    <i class="fas fa-user-tag me-1 text-secondary"></i>
                                    {{ $user->role->name ?? 'No Role' }}
                                </span>
                            </div>
                        </div>
                        <div class="ms-auto">
                            <form action="{{ route('profile.update-picture') }}" method="POST" enctype="multipart/form-data" id="profilePictureForm" class="mb-0">
                                @csrf
                                <label for="profile_picture" class="btn btn-light btn-sm shadow-sm">
                                    <i class="fas fa-camera me-1"></i> Change Photo
                                </label>
                                <input type="file" class="d-none" id="profile_picture" name="profile_picture" accept="image/*">
                            </form>
                        </div>
                    </div>
                    
                    <ul class="nav nav-tabs mt-4 border-0">
                        <li class="nav-item">
                            <a class="nav-link active px-3" href="#profile-info" data-bs-toggle="tab">
                                <i class="fas fa-user me-2"></i>Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3" href="#security" data-bs-toggle="tab">
                                <i class="fas fa-shield-alt me-2"></i>Security
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3" href="#account-settings" data-bs-toggle="tab">
                                <i class="fas fa-cog me-2"></i>Account
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-8 order-1 order-md-0">
            <div class="tab-content d-none d-md-block">
                <!-- Profile Information Tab -->
                <div class="tab-pane fade show active" id="profile-info">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3 border-bottom">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-id-card text-primary me-2"></i>
                                <h5 class="mb-0">Profile Information</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('profile.update') }}" method="POST">
                                @csrf
                                @method('PATCH')
                                
                                <div class="mb-3">
                                    <x-input 
                                        label="Name" 
                                        name="name"
                                        required="true"
                                        placeholder="Enter your name"
                                        :value="old('name', $user->name)"
                                    />
                                </div>
                                
                                <div class="mb-3">
                                    <x-input 
                                        label="Email" 
                                        name="email"
                                        type="email"
                                        placeholder="Enter your email (optional)"
                                        :value="old('email', $user->email)"
                                    />
                                    
                                    @if($user->email)
                                        @if($user->email_verified_at)
                                            <div class="form-text text-success">
                                                <i class="fas fa-check-circle me-1"></i> Your email is verified.
                                            </div>
                                        @else
                                            <div class="form-text text-muted">
                                                <i class="fas fa-info-circle me-1"></i> Email verification is optional.
                                            </div>
                                        @endif
                                    @else
                                        <div class="form-text text-muted">
                                            <i class="fas fa-info-circle me-1"></i> Email is optional.
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="mb-3">
                                    <div class="d-flex flex-column flex-sm-row justify-content-between">
                                        <div class="mb-2 mb-sm-0">
                                            <strong>Department:</strong> {{ $user->department->name ?? 'Not assigned' }}
                                        </div>
                                        <div>
                                            <strong>Role:</strong> {{ $user->role->name ?? 'Not assigned' }}
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-grid d-sm-flex justify-content-sm-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Security Tab -->
                <div class="tab-pane fade" id="security">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3 border-bottom">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-lock text-primary me-2"></i>
                                <h5 class="mb-0">Change Password</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('password.update') }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="mb-3">
                                    <x-input 
                                        label="Current Password" 
                                        name="current_password"
                                        type="password"
                                        required="true"
                                        placeholder="Enter your current password"
                                    />
                                </div>
                                
                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <x-input 
                                            label="New Password" 
                                            name="password"
                                            type="password"
                                            required="true"
                                            placeholder="Enter new password"
                                        />
                                    </div>
                                    
                                    <div class="col-sm-6 mb-3">
                                        <x-input 
                                            label="Confirm Password" 
                                            name="password_confirmation"
                                            type="password"
                                            required="true"
                                            placeholder="Confirm new password"
                                        />
                                    </div>
                                </div>
                                
                                <div class="alert alert-info mb-3">
                                    <div class="d-flex">
                                        <i class="fas fa-info-circle me-2 mt-1"></i>
                                        <div>
                                            <strong>Password tips:</strong>
                                            <ul class="mb-0 ps-3 mt-1">
                                                <li>Use at least 8 characters</li>
                                                <li>Include uppercase and lowercase letters</li>
                                                <li>Use numbers and special characters</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-grid d-sm-flex justify-content-sm-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-key me-1"></i> Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Account Settings Tab -->
                <div class="tab-pane fade" id="account-settings">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3 border-bottom">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-cog text-primary me-2"></i>
                                <h5 class="mb-0">Account Details</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="text-muted mb-4">
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Account Created</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        {{ $user->created_at->format('F d, Y') }} ({{ $user->created_at->diffForHumans() }})
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <strong>Last Updated</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        {{ $user->updated_at->format('F d, Y') }} ({{ $user->updated_at->diffForHumans() }})
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            @if(auth()->user()->isAdmin())
                            <div class="mt-4">
                                <h5 class="text-danger mb-3">Delete Account</h5>
                                <p class="card-text">Once your account is deleted, all of its resources and data will be permanently deleted. This action cannot be undone.</p>
                                
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                                        <i class="fas fa-trash me-1"></i> Delete Account
                                    </button>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Mobile view cards - only visible on mobile -->
            <div class="d-md-none">
                <!-- Profile Information -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Profile Information</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('profile.update') }}" method="POST">
                            @csrf
                            @method('PATCH')
                            
                            <div class="mb-3">
                                <x-input 
                                    label="Name" 
                                    name="name"
                                    required="true"
                                    placeholder="Enter your name"
                                    :value="old('name', $user->name)"
                                />
                            </div>
                            
                            <div class="mb-3">
                                <x-input 
                                    label="Email" 
                                    name="email"
                                    type="email"
                                    placeholder="Enter your email (optional)"
                                    :value="old('email', $user->email)"
                                />
                                
                                @if($user->email)
                                    @if($user->email_verified_at)
                                        <div class="form-text text-success">
                                            <i class="fas fa-check-circle me-1"></i> Your email is verified.
                                        </div>
                                    @else
                                        <div class="form-text text-muted">
                                            <i class="fas fa-info-circle me-1"></i> Email verification is optional.
                                        </div>
                                    @endif
                                @else
                                    <div class="form-text text-muted">
                                        <i class="fas fa-info-circle me-1"></i> Email is optional.
                                    </div>
                                @endif
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex flex-column flex-sm-row justify-content-between">
                                    <div class="mb-2 mb-sm-0">
                                        <strong>Department:</strong> {{ $user->department->name ?? 'Not assigned' }}
                                    </div>
                                    <div>
                                        <strong>Role:</strong> {{ $user->role->name ?? 'Not assigned' }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid d-sm-flex justify-content-sm-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Change Password -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Change Password</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('password.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="mb-3">
                                <x-input 
                                    label="Current Password" 
                                    name="current_password"
                                    type="password"
                                    required="true"
                                    placeholder="Enter your current password"
                                />
                            </div>
                            
                            <div class="mb-3">
                                <x-input 
                                    label="New Password" 
                                    name="password"
                                    type="password"
                                    required="true"
                                    placeholder="Enter new password"
                                />
                            </div>
                            
                            <div class="mb-3">
                                <x-input 
                                    label="Confirm Password" 
                                    name="password_confirmation"
                                    type="password"
                                    required="true"
                                    placeholder="Confirm new password"
                                />
                            </div>
                            
                            <div class="d-grid d-sm-flex justify-content-sm-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-key me-1"></i> Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Desktop sidebar with account stats - Web only -->
        <div class="col-md-4 order-0 order-md-1 d-none d-md-block">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Account Information</h5>
                    <div class="list-group list-group-flush border-top pt-3">
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between border-0">
                            <div class="text-muted">Status</div>
                            <div><span class="badge bg-success">Active</span></div>
                        </div>
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between border-0">
                            <div class="text-muted">Account Type</div>
                            <div>{{ $user->role->name ?? 'User' }}</div>
                        </div>
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between border-0">
                            <div class="text-muted">Member Since</div>
                            <div>{{ $user->created_at->format('M d, Y') }}</div>
                        </div>
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between border-0">
                            <div class="text-muted">Last Updated</div>
                            <div>{{ $user->updated_at->format('M d, Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Quick Tips</h5>
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0 me-3">
                            <div class="icon-circle bg-light text-primary">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="mb-1">Secure Your Account</h6>
                            <p class="small text-muted mb-0">Use a strong password that you don't use elsewhere.</p>
                        </div>
                    </div>
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0 me-3">
                            <div class="icon-circle bg-light text-primary">
                                <i class="fas fa-envelope"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="mb-1">Verify Your Email</h6>
                            <p class="small text-muted mb-0">Adding a verified email improves account security.</p>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <div class="icon-circle bg-light text-primary">
                                <i class="fas fa-user-edit"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="mb-1">Keep Profile Updated</h6>
                            <p class="small text-muted mb-0">Make sure your information is current.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mobile-only sections -->
        <div class="col-12 d-md-none">
            <!-- Account Information -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="text-muted">
                        <div class="d-flex justify-content-between mb-3 pb-2 border-bottom">
                            <div><strong>Account Created</strong></div>
                            <div>{{ $user->created_at->format('d M Y') }}</div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div><strong>Last Updated</strong></div>
                            <div>{{ $user->updated_at->format('d M Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Delete Account (Mobile) -->
            @if(auth()->user()->isAdmin())
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white text-danger py-3">
                    <h5 class="mb-0">Delete Account</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Once your account is deleted, all of its resources and data will be permanently deleted.</p>
                    
                    <div class="d-grid">
                        <button type="button" class="btn btn-danger btn-lg py-3" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                            <i class="fas fa-trash me-1"></i> Delete Account
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    
    <!-- Delete Account Modal -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('profile.destroy') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteAccountModalLabel">Delete Account</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Are you sure you want to delete your account? Once your account is deleted, all of its resources and data will be permanently deleted.</p>
                        
                        <div class="mb-3">
                            <x-input 
                                label="Password" 
                                name="password"
                                type="password"
                                required="true"
                                placeholder="Enter your password to confirm"
                            />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <style>
        .avatar-lg {
            width: 100px;
            height: 100px;
            font-size: 40px;
        }
        
        .avatar-xl {
            width: 120px;
            height: 120px;
            font-size: 48px;
        }
        
        /* Profile header styling */
        .profile-header {
            border-radius: 10px;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        
        .profile-cover-bg {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            height: 100%;
            background: linear-gradient(120deg, #4338ca 0%, #3b82f6 100%);
            opacity: 0.85;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        
        .profile-pic {
            margin-top: -40px;
            position: relative;
            z-index: 1;
        }
        
        /* Tab styling */
        .nav-tabs {
            border-bottom: none;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: #fff;
            position: relative;
            font-weight: 500;
            padding: 0.75rem 1rem;
            border-radius: 0;
        }
        
        .nav-tabs .nav-link:hover {
            color: rgba(255, 255, 255, 0.8);
            background-color: transparent;
        }
        
        .nav-tabs .nav-link.active {
            color: #fff;
            background-color: transparent;
            font-weight: 600;
        }
        
        .nav-tabs .nav-link.active:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #fff;
        }
        
        /* Icon circle */
        .icon-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Mobile optimizations */
        @media (max-width: 767.98px) {
            .card {
                border-radius: 12px;
                overflow: hidden;
            }
            
            .card-header {
                padding: 1rem;
            }
            
            .card-body {
                padding: 1.25rem;
            }
            
            .btn {
                padding: 0.5rem 1rem;
                font-size: 1rem;
            }
            
            .form-control {
                font-size: 16px; /* Prevent auto-zoom on iOS */
                padding: 0.625rem 0.75rem;
                height: auto;
                min-height: 48px; /* Larger touch target */
            }
            
            .form-label {
                font-size: 0.95rem;
                margin-bottom: 0.375rem;
            }
            
            /* Improve spacing between cards */
            .mb-4 {
                margin-bottom: 1rem !important;
            }
            
            /* Fix modal on small screens */
            .modal-dialog {
                margin: 0.5rem;
            }
            
            .modal-body, .modal-footer {
                padding: 1rem;
            }
        }
    </style>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Profile picture upload handler - Desktop
            const profilePictureInput = document.getElementById('profile_picture');
            const profilePictureForm = document.getElementById('profilePictureForm');

            if (profilePictureInput) {
                profilePictureInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        profilePictureForm.submit();
                    }
                });
            }
            
            // Mobile profile picture upload handler
            const mobileProfilePictureInput = document.getElementById('mobile_profile_picture');
            const mobileProfilePictureForm = document.getElementById('mobileProfilePictureForm');
            
            if (mobileProfilePictureInput) {
                mobileProfilePictureInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        mobileProfilePictureForm.submit();
                    }
                });
            }
            
            // Bootstrap Tab initialization and retention
            const triggerTabList = [].slice.call(document.querySelectorAll('.nav-tabs a'));
            triggerTabList.forEach(function(triggerEl) {
                new bootstrap.Tab(triggerEl);
                
                triggerEl.addEventListener('click', function(event) {
                    event.preventDefault();
                    // Store the current tab in local storage
                    localStorage.setItem('activeProfileTab', this.getAttribute('href'));
                });
            });
            
            // Retrieve and activate the last active tab
            const activeTab = localStorage.getItem('activeProfileTab');
            if (activeTab) {
                const tab = document.querySelector(`.nav-tabs a[href="${activeTab}"]`);
                if (tab) {
                    const bsTab = new bootstrap.Tab(tab);
                    bsTab.show();
                }
            }
        });
    </script>
    @endpush
</x-app-layout>