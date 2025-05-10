<x-app-layout>
    <x-slot name="header">
        Department Management
    </x-slot>

    <div class="card">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-6 mb-2 mb-md-0">
                    <h5 class="mb-0">All Departments</h5>
                </div>
                <div class="col-md-6 text-md-end">
                    <button type="button" class="btn btn-primary w-100 w-md-auto" data-bs-toggle="modal" data-bs-target="#createDepartmentModal">
                        <i class="fas fa-plus me-1"></i> New Department
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter Controls -->
            <form action="{{ route('admin.departments.index') }}" method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4 col-12">
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}" style="height: 42px;">
                        </div>
                    </div>
                    <div class="col-md-2 col-12">
                        <div class="d-flex">
                            <button type="submit" class="btn btn-primary me-2">Filter</button>
                            <a href="{{ route('admin.departments.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </div>
            </form>

            @if(count($departments ?? []) > 0)
            <!-- Mobile Card View (visible only on mobile) -->
            <div class="d-md-none mb-4">
                <form id="mobile-bulk-action-form" method="POST" action="{{ route('admin.departments.batch-delete') }}">
                    @csrf
                    @method('DELETE')
                    
                    @foreach($departments as $department)
                    <div class="card mb-3 border rounded shadow-sm">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2 bg-primary rounded-circle text-white d-flex align-items-center justify-content-center">
                                        {{ substr($department->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $department->name }}</h6>
                                        <span class="text-muted small">{{ $department->code }}</span>
                                    </div>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input mobile-select-checkbox" type="checkbox" name="ids[]" value="{{ $department->id }}">
                                </div>
                            </div>
                            
                            <div class="mb-2">
                                <p class="mb-1 text-muted small">{{ Str::limit($department->description, 100) }}</p>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-info">{{ $department->users_count }} users</span>
                                
                                <div class="d-flex">
                                    <button type="button" class="btn btn-sm btn-outline-secondary me-1 dept-edit-btn"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editDepartmentModal" 
                                            data-dept-id="{{ $department->id }}"
                                            data-dept-name="{{ $department->name }}"
                                            data-dept-code="{{ $department->code }}"
                                            data-dept-description="{{ $department->description }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger dept-delete-btn"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteDepartmentModal" 
                                            data-dept-id="{{ $department->id }}"
                                            data-dept-name="{{ $department->name }}"
                                            data-dept-users="{{ $department->users_count }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    
                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <div>
                            <button type="button" class="btn btn-danger mobile-bulk-delete-btn" disabled data-bs-toggle="modal" data-bs-target="#bulkDeleteModal">
                                <i class="fas fa-trash me-1"></i> Delete Selected
                            </button>
                        </div>
                        <div>
                            {{ $departments->links() }}
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Desktop Table View (hidden on mobile) -->
            <div class="table-responsive d-none d-md-block">
                <form id="bulk-action-form" method="POST" action="{{ route('admin.departments.batch-delete') }}">
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
                                <th>Code</th>
                                <th>Description</th>
                                <th>Users</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($departments as $department)
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input select-checkbox" type="checkbox" name="ids[]" value="{{ $department->id }}">
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2 bg-primary rounded-circle text-white d-flex align-items-center justify-content-center">
                                            {{ substr($department->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <a href="#" class="fw-medium text-decoration-none dept-edit-btn"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editDepartmentModal" 
                                                data-dept-id="{{ $department->id }}"
                                                data-dept-name="{{ $department->name }}"
                                                data-dept-code="{{ $department->code }}"
                                                data-dept-description="{{ $department->description }}">
                                                {{ $department->name }}
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $department->code }}</td>
                                <td>{{ Str::limit($department->description, 30) }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $department->users_count }} users</span>
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <button type="button" class="btn btn-sm btn-outline-secondary me-1 dept-edit-btn"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editDepartmentModal" 
                                                data-dept-id="{{ $department->id }}"
                                                data-dept-name="{{ $department->name }}"
                                                data-dept-code="{{ $department->code }}"
                                                data-dept-description="{{ $department->description }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger dept-delete-btn"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteDepartmentModal" 
                                                data-dept-id="{{ $department->id }}"
                                                data-dept-name="{{ $department->name }}"
                                                data-dept-users="{{ $department->users_count }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
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
                            {{ $departments->links() }}
                        </div>
                    </div>
                </form>
            </div>
            @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> No departments found matching your criteria.
            </div>
            @endif
        </div>
    </div>

    <!-- Create Department Modal -->
    <div class="modal fade" id="createDepartmentModal" tabindex="-1" aria-labelledby="createDepartmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createDepartmentModalLabel">Create New Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.departments.store') }}" method="POST">
                    <div class="modal-body">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Department Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="name" name="name" required placeholder="Enter department name">
                        </div>
                        <div class="mb-3">
                            <label for="code" class="form-label">Department Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="code" name="code" required placeholder="Enter department code (e.g. HR, IT, FIN)">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control form-control-lg" id="description" name="description" rows="3" placeholder="Enter description"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between flex-wrap">
                        <button type="button" class="btn btn-secondary mb-2 mb-sm-0 col-12 col-sm-auto" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary col-12 col-sm-auto">Create Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Department Modal -->
    <div class="modal fade" id="editDepartmentModal" tabindex="-1" aria-labelledby="editDepartmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editDepartmentModalLabel">Edit Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editDepartmentForm" method="POST">
                    <div class="modal-body">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Department Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="edit_name" name="name" required placeholder="Enter department name">
                        </div>
                        <div class="mb-3">
                            <label for="edit_code" class="form-label">Department Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="edit_code" name="code" required placeholder="Enter department code (e.g. HR, IT, FIN)">
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control form-control-lg" id="edit_description" name="description" rows="3" placeholder="Enter description"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between flex-wrap">
                        <button type="button" class="btn btn-secondary mb-2 mb-sm-0 col-12 col-sm-auto" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary col-12 col-sm-auto">Update Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Department Modal -->
    <div class="modal fade" id="deleteDepartmentModal" tabindex="-1" aria-labelledby="deleteDepartmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteDepartmentModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete department <strong id="delete-dept-name"></strong>?</p>
                    <div id="delete-dept-warning" class="alert alert-danger d-none">
                        <i class="fas fa-exclamation-triangle me-2"></i> This department has <span id="delete-dept-users-count"></span> users assigned to it. You cannot delete it until all users are reassigned.
                    </div>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer justify-content-between flex-wrap">
                    <button type="button" class="btn btn-secondary mb-2 mb-sm-0 col-12 col-sm-auto" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteDepartmentForm" method="POST" class="col-12 col-sm-auto p-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100" id="confirmDeleteButton">Delete Department</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Delete Modal -->
    <div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-labelledby="bulkDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkDeleteModalLabel">Confirm Bulk Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete all selected departments?</p>
                    <p class="text-danger">This action cannot be undone. Departments with assigned users will not be deleted.</p>
                </div>
                <div class="modal-footer justify-content-between flex-wrap">
                    <button type="button" class="btn btn-secondary mb-2 mb-sm-0 col-12 col-sm-auto" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger col-12 col-sm-auto" id="confirmBulkDelete">Delete Selected Departments</button>
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
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            .card-body {
                padding: 1rem;
            }
            
            .modal-body, .modal-footer {
                padding: 1rem;
            }
            
            .form-control, .btn {
                font-size: 16px; /* Prevent auto-zoom on iOS */
            }
            
            .form-control-lg {
                height: 50px;
            }
            
            .modal-footer .btn {
                padding: 0.5rem 1rem;
            }
            
            /* Improve pagination on mobile */
            .pagination {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Select all checkbox functionality (desktop)
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
            
            // Mobile checkboxes functionality
            const mobileCheckboxes = document.querySelectorAll('.mobile-select-checkbox');
            const mobileBulkDeleteBtn = document.querySelector('.mobile-bulk-delete-btn');
            
            mobileCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateMobileBulkDeleteButton();
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

            // Edit department modal
            const editDeptBtns = document.querySelectorAll('.dept-edit-btn');
            editDeptBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const deptId = this.getAttribute('data-dept-id');
                    const deptName = this.getAttribute('data-dept-name');
                    const deptCode = this.getAttribute('data-dept-code');
                    const deptDescription = this.getAttribute('data-dept-description');
                    
                    // Set form action
                    document.getElementById('editDepartmentForm').action = '/admin/departments/' + deptId;
                    
                    // Populate form fields
                    document.getElementById('edit_name').value = deptName;
                    document.getElementById('edit_code').value = deptCode;
                    document.getElementById('edit_description').value = deptDescription || '';
                });
            });
            
            // Delete department modal
            const deleteDeptBtns = document.querySelectorAll('.dept-delete-btn');
            deleteDeptBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const deptId = this.getAttribute('data-dept-id');
                    const deptName = this.getAttribute('data-dept-name');
                    const deptUsers = parseInt(this.getAttribute('data-dept-users'));
                    
                    // Set form action
                    document.getElementById('deleteDepartmentForm').action = '/admin/departments/' + deptId;
                    
                    // Set department name in confirmation message
                    document.getElementById('delete-dept-name').textContent = deptName;
                    
                    // Show warning if department has users
                    const warningElement = document.getElementById('delete-dept-warning');
                    const deleteButton = document.getElementById('confirmDeleteButton');
                    
                    if (deptUsers > 0) {
                        warningElement.classList.remove('d-none');
                        document.getElementById('delete-dept-users-count').textContent = deptUsers;
                        deleteButton.disabled = true;
                    } else {
                        warningElement.classList.add('d-none');
                        deleteButton.disabled = false;
                    }
                });
            });
            
            // Bulk delete confirmation
            const bulkActionForm = document.getElementById('bulk-action-form');
            const mobileBulkActionForm = document.getElementById('mobile-bulk-action-form');
            const confirmBulkDelete = document.getElementById('confirmBulkDelete');
            
            if (confirmBulkDelete) {
                confirmBulkDelete.addEventListener('click', function() {
                    // Check which form to submit based on viewport
                    if (window.innerWidth < 768 && mobileBulkActionForm) {
                        mobileBulkActionForm.submit();
                    } else if (bulkActionForm) {
                        bulkActionForm.submit();
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout> 