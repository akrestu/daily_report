<x-app-layout>
    <x-slot name="header">
        User Management
    </x-slot>

    <div class="card">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-6 mb-2 mb-md-0">
                    <h5 class="mb-0">All Users</h5>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-flex flex-wrap justify-content-center justify-content-md-end gap-2">
                        <div class="btn-group me-md-2 mb-2 mb-md-0">
                            <a href="{{ route('admin.users.export') }}" class="btn btn-outline-primary">
                                <i class="fas fa-file-export me-1"></i> <span class="d-none d-sm-inline">Export</span>
                            </a>
                            <a href="{{ route('admin.users.show-import') }}" class="btn btn-outline-success">
                                <i class="fas fa-file-import me-1"></i> <span class="d-none d-sm-inline">Import</span>
                            </a>
                        </div>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                            <i class="fas fa-user-plus me-1"></i> <span class="d-none d-sm-inline">New User</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter Controls -->
            <form action="{{ route('admin.users.index') }}" method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-12 col-md-3">
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <select name="role" class="form-select">
                            <option value="">All Roles</option>
                            @foreach($roles ?? [] as $id => $name)
                                <option value="{{ $id }}" {{ request('role') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <select name="department" class="form-select">
                            <option value="">All Departments</option>
                            @foreach($departments ?? [] as $id => $name)
                                <option value="{{ $id }}" {{ request('department') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="fas fa-filter me-1"></i> <span class="d-none d-sm-inline">Filter</span>
                            </button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary flex-grow-1">
                                <i class="fas fa-redo-alt me-1"></i> <span class="d-none d-sm-inline">Reset</span>
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            @if(count($users ?? []) > 0)
            <!-- Desktop Table View -->
            <div class="table-responsive d-none d-lg-block">
                <form id="bulk-action-form" method="POST" action="{{ route('admin.users.batch-delete') }}">
                    @csrf
                    @method('DELETE')
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <div class="form-check">
                                        <input class="form-check-input select-all-checkbox" type="checkbox" id="selectAll">
                                    </div>
                                </th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Department</th>
                                <!-- <th>Status</th> -->
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input select-checkbox" type="checkbox" name="selected_users[]" value="{{ $user->id }}">
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2 bg-primary rounded-circle text-white d-flex align-items-center justify-content-center">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <a href="#" class="fw-medium text-decoration-none user-edit-btn" 
                                               data-bs-toggle="modal" 
                                               data-bs-target="#editUserModal" 
                                               data-user-id="{{ $user->id }}" 
                                               data-user-name="{{ $user->name }}" 
                                               data-user-email="{{ $user->email }}" 
                                               data-user-role="{{ $user->role_id }}" 
                                               data-user-department="{{ $user->department_id }}" 
                                               data-user-userid="{{ $user->user_id }}">
                                                {{ $user->name }}
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->role->name ?? 'No Role' }}</td>
                                <td>{{ $user->department->name ?? 'No Department' }}</td>
                                <!-- Hide verification status
                                <td>
                                    @if($user->email_verified_at)
                                        <span class="badge bg-success">Verified</span>
                                    @else
                                        <span class="badge bg-warning">Unverified</span>
                                    @endif
                                </td>
                                -->
                                <td>
                                    <div class="d-flex">
                                        <button type="button" class="btn btn-sm btn-outline-secondary me-1 user-edit-btn" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editUserModal" 
                                                data-user-id="{{ $user->id }}" 
                                                data-user-name="{{ $user->name }}" 
                                                data-user-email="{{ $user->email }}" 
                                                data-user-role="{{ $user->role_id }}" 
                                                data-user-department="{{ $user->department_id }}" 
                                                data-user-userid="{{ $user->user_id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @if(auth()->id() !== $user->id)
                                        <button type="button" class="btn btn-sm btn-outline-danger user-delete-btn" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteUserModal" 
                                                data-user-id="{{ $user->id }}" 
                                                data-user-name="{{ $user->name }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <div>
                            <button type="button" class="btn btn-danger bulk-delete-btn" disabled data-bs-toggle="modal" data-bs-target="#bulkDeleteModal">
                                <i class="fas fa-trash me-1"></i> Delete Selected
                            </button>
                        </div>
                        <div>
                            {{ $users->links() }}
                        </div>
                    </div>
                </form>
            </div>

            <!-- Mobile Card View -->
            <div class="d-lg-none">
                <form id="mobile-bulk-action-form" method="POST" action="{{ route('admin.users.batch-delete') }}">
                    @csrf
                    @method('DELETE')
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input mobile-select-all" type="checkbox" id="mobileSelectAll">
                            <label class="form-check-label" for="mobileSelectAll">Select All</label>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm mobile-bulk-delete-btn" disabled data-bs-toggle="modal" data-bs-target="#bulkDeleteModal">
                            <i class="fas fa-trash me-1"></i> Delete
                        </button>
                    </div>
                    
                    <div class="row g-3">
                        @foreach($users as $user)
                        <div class="col-12">
                            <div class="card shadow-sm h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2 bg-primary rounded-circle text-white d-flex align-items-center justify-content-center">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">{{ $user->name }}</h6>
                                                <span class="text-muted small">{{ $user->email }}</span>
                                            </div>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input mobile-select-checkbox" type="checkbox" name="selected_users[]" value="{{ $user->id }}">
                                        </div>
                                    </div>
                                    
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-tag text-primary me-2 small"></i>
                                                <span class="small text-truncate">{{ $user->role->name ?? 'No Role' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-building text-primary me-2 small"></i>
                                                <span class="small text-truncate">{{ $user->department->name ?? 'No Department' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <!-- Hide verification status
                                        <div>
                                            @if($user->email_verified_at)
                                                <span class="badge bg-success">Verified</span>
                                            @else
                                                <span class="badge bg-warning">Unverified</span>
                                            @endif
                                        </div>
                                        -->
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary user-edit-btn" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editUserModal" 
                                                data-user-id="{{ $user->id }}" 
                                                data-user-name="{{ $user->name }}" 
                                                data-user-email="{{ $user->email }}" 
                                                data-user-role="{{ $user->role_id }}" 
                                                data-user-department="{{ $user->department_id }}" 
                                                data-user-userid="{{ $user->user_id }}">
                                                <i class="fas fa-edit me-1"></i> Edit
                                            </button>
                                            @if(auth()->id() !== $user->id)
                                            <button type="button" class="btn btn-sm btn-outline-danger user-delete-btn" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteUserModal" 
                                                data-user-id="{{ $user->id }}" 
                                                data-user-name="{{ $user->name }}">
                                                <i class="fas fa-trash me-1"></i> Delete
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-3 d-flex justify-content-center">
                        {{ $users->links() }}
                    </div>
                </form>
            </div>
            @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> No users found matching your criteria.
            </div>
            @endif
        </div>
    </div>

    <!-- Create User Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createUserModalLabel">Create New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.users.store') }}" method="POST">
                    <div class="modal-body">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="role_id" name="role_id" required>
                                    <option value="">Select Role</option>
                                    @foreach($roles ?? [] as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="department_id" class="form-label">Department</label>
                                <select class="form-select" id="department_id" name="department_id">
                                    <option value="">Select Department</option>
                                    @foreach($departments ?? [] as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="user_id" class="form-label">User ID</label>
                                <input type="text" class="form-control" id="user_id" name="user_id">
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="alert alert-info small">
                                <i class="fas fa-info-circle me-1"></i> New users will automatically receive a welcome notification.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editUserForm" method="POST">
                    <div class="modal-body">
                        @csrf
                        @method('PUT')
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_role_id" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_role_id" name="role_id" required>
                                    <option value="">Select Role</option>
                                    @foreach($roles ?? [] as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_department_id" class="form-label">Department</label>
                                <select class="form-select" id="edit_department_id" name="department_id">
                                    <option value="">Select Department</option>
                                    @foreach($departments ?? [] as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_user_id" class="form-label">User ID</label>
                                <input type="text" class="form-control" id="edit_user_id" name="user_id">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_password" class="form-label">Password <small class="text-muted">(leave blank to keep current)</small></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="edit_password" name="password">
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="edit_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete User Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete user <strong id="delete-user-name"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteUserForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Delete Modal -->
    <div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-labelledby="bulkDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkDeleteModalLabel">Confirm Bulk Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete all selected users?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmBulkDelete">Delete Selected Users</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .avatar-sm {
            width: 36px;
            height: 36px;
            font-size: 14px;
        }
        
        /* Mobile specific styles */
        @media (max-width: 991.98px) {
            .card {
                border-radius: 10px;
                overflow: hidden;
            }
            
            .btn-group .btn {
                padding: 0.4rem 0.75rem;
                min-height: 38px;
            }
            
            /* Improve touch target size */
            .form-check-input[type="checkbox"] {
                width: 20px;
                height: 20px;
                cursor: pointer;
            }
            
            /* Make pagination more mobile friendly */
            .pagination {
                flex-wrap: wrap;
                justify-content: center;
                gap: 5px;
            }
            
            .page-item .page-link {
                min-width: 38px;
                height: 38px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }
    </style>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password toggle visibility
            const togglePasswordButtons = document.querySelectorAll('.toggle-password');
            togglePasswordButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const passwordInput = document.getElementById(targetId);
                    
                    // Toggle the type attribute
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    // Toggle the icon
                    this.querySelector('i').classList.toggle('fa-eye');
                    this.querySelector('i').classList.toggle('fa-eye-slash');
                });
            });
            
            // Desktop select all functionality
            const selectAllCheckbox = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.select-checkbox');
            const bulkDeleteBtn = document.querySelector('.bulk-delete-btn');

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    updateBulkDeleteButton();
                });
            }

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateBulkDeleteButton();
                    
                    // Update select all checkbox
                    if (!this.checked) {
                        selectAllCheckbox.checked = false;
                    } else {
                        // Check if all checkboxes are checked
                        let allChecked = true;
                        checkboxes.forEach(cb => {
                            if (!cb.checked) allChecked = false;
                        });
                        selectAllCheckbox.checked = allChecked;
                    }
                });
            });

            function updateBulkDeleteButton() {
                let checkedCount = 0;
                checkboxes.forEach(checkbox => {
                    if (checkbox.checked) checkedCount++;
                });
                
                if (bulkDeleteBtn) {
                    bulkDeleteBtn.disabled = checkedCount === 0;
                }
            }
            
            // Mobile select all functionality
            const mobileSelectAll = document.getElementById('mobileSelectAll');
            const mobileCheckboxes = document.querySelectorAll('.mobile-select-checkbox');
            const mobileBulkDeleteBtn = document.querySelector('.mobile-bulk-delete-btn');
            
            if (mobileSelectAll) {
                mobileSelectAll.addEventListener('change', function() {
                    mobileCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    updateMobileBulkDeleteButton();
                });
            }
            
            mobileCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateMobileBulkDeleteButton();
                    
                    // Update select all checkbox
                    if (!this.checked) {
                        mobileSelectAll.checked = false;
                    } else {
                        // Check if all checkboxes are checked
                        let allChecked = true;
                        mobileCheckboxes.forEach(cb => {
                            if (!cb.checked) allChecked = false;
                        });
                        mobileSelectAll.checked = allChecked;
                    }
                });
            });
            
            function updateMobileBulkDeleteButton() {
                let checkedCount = 0;
                mobileCheckboxes.forEach(checkbox => {
                    if (checkbox.checked) checkedCount++;
                });
                
                if (mobileBulkDeleteBtn) {
                    mobileBulkDeleteBtn.disabled = checkedCount === 0;
                }
            }

            // Edit user modal - update to work with both desktop and mobile buttons
            const editUserBtns = document.querySelectorAll('.user-edit-btn');
            editUserBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const userId = this.getAttribute('data-user-id');
                    const userName = this.getAttribute('data-user-name');
                    const userEmail = this.getAttribute('data-user-email');
                    const userRole = this.getAttribute('data-user-role');
                    const userDepartment = this.getAttribute('data-user-department');
                    const userUserId = this.getAttribute('data-user-userid');
                    
                    // Set form action
                    document.getElementById('editUserForm').action = '/admin/users/' + userId;
                    
                    // Populate form fields
                    document.getElementById('edit_name').value = userName;
                    document.getElementById('edit_email').value = userEmail;
                    document.getElementById('edit_role_id').value = userRole;
                    document.getElementById('edit_department_id').value = userDepartment || '';
                    document.getElementById('edit_user_id').value = userUserId || '';
                    document.getElementById('edit_password').value = '';
                });
            });
            
            // Delete user modal - update to work with both desktop and mobile buttons
            const deleteUserBtns = document.querySelectorAll('.user-delete-btn');
            deleteUserBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const userId = this.getAttribute('data-user-id');
                    const userName = this.getAttribute('data-user-name');
                    
                    // Set form action
                    document.getElementById('deleteUserForm').action = '/admin/users/' + userId;
                    
                    // Set user name in confirmation message
                    document.getElementById('delete-user-name').textContent = userName;
                });
            });
            
            // Bulk delete confirmation - update to work with both forms
            const bulkActionForm = document.getElementById('bulk-action-form');
            const mobileBulkActionForm = document.getElementById('mobile-bulk-action-form');
            const confirmBulkDelete = document.getElementById('confirmBulkDelete');
            
            if (confirmBulkDelete) {
                confirmBulkDelete.addEventListener('click', function() {
                    // Check which form to submit based on viewport
                    if (window.innerWidth >= 992) {
                        bulkActionForm.submit();
                    } else {
                        mobileBulkActionForm.submit();
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout> 