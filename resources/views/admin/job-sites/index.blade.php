<x-app-layout>
    <x-slot name="header">
        Job Site Management
    </x-slot>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-6 mb-2 mb-md-0">
                    <h5 class="mb-0">All Job Sites</h5>
                </div>
                <div class="col-md-6 text-md-end">
                    <button type="button" class="btn btn-primary w-100 w-md-auto" data-bs-toggle="modal" data-bs-target="#createJobSiteModal">
                        <i class="fas fa-plus me-1"></i> New Job Site
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter Controls -->
            <form action="{{ route('admin.job-sites.index') }}" method="GET" class="mb-4">
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
                            <a href="{{ route('admin.job-sites.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </div>
            </form>

            @if(count($jobSites ?? []) > 0)
            <!-- Mobile Card View (visible only on mobile) -->
            <div class="d-md-none mb-4">
                <form id="mobile-bulk-action-form" method="POST" action="{{ route('admin.job-sites.batch-delete') }}">
                    @csrf
                    @method('DELETE')

                    @foreach($jobSites as $jobSite)
                    <div class="card mb-3 border rounded shadow-sm">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2 bg-primary rounded-circle text-white d-flex align-items-center justify-content-center">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $jobSite->name }}</h6>
                                        <span class="text-muted small">{{ $jobSite->code }}</span>
                                    </div>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input mobile-select-checkbox" type="checkbox" name="ids[]" value="{{ $jobSite->id }}">
                                </div>
                            </div>

                            @if($jobSite->location)
                            <div class="mb-2">
                                <span class="text-muted small">
                                    <i class="fas fa-map-marker-alt me-1"></i>{{ $jobSite->location }}
                                </span>
                            </div>
                            @endif

                            <div class="mb-2">
                                <p class="mb-1 text-muted small">{{ Str::limit($jobSite->description, 100) }}</p>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge {{ $jobSite->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $jobSite->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <span class="badge bg-info">{{ $jobSite->daily_reports_count }} reports</span>
                                </div>

                                <div class="d-flex">
                                    <button type="button" class="btn btn-sm btn-outline-secondary me-1 jobsite-edit-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editJobSiteModal"
                                            data-jobsite-id="{{ $jobSite->id }}"
                                            data-jobsite-name="{{ $jobSite->name }}"
                                            data-jobsite-code="{{ $jobSite->code }}"
                                            data-jobsite-location="{{ $jobSite->location }}"
                                            data-jobsite-description="{{ $jobSite->description }}"
                                            data-jobsite-active="{{ $jobSite->is_active ? '1' : '0' }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger jobsite-delete-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteJobSiteModal"
                                            data-jobsite-id="{{ $jobSite->id }}"
                                            data-jobsite-name="{{ $jobSite->name }}"
                                            data-jobsite-reports="{{ $jobSite->daily_reports_count }}">
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
                            {{ $jobSites->links() }}
                        </div>
                    </div>
                </form>
            </div>

            <!-- Desktop Table View (hidden on mobile) -->
            <div class="table-responsive d-none d-md-block">
                <form id="bulk-action-form" method="POST" action="{{ route('admin.job-sites.batch-delete') }}">
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
                                <th>Location</th>
                                <th>Status</th>
                                <th>Reports</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jobSites as $jobSite)
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input select-checkbox" type="checkbox" name="ids[]" value="{{ $jobSite->id }}">
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2 bg-primary rounded-circle text-white d-flex align-items-center justify-content-center">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </div>
                                        <div>
                                            <a href="#" class="fw-medium text-decoration-none jobsite-edit-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editJobSiteModal"
                                                data-jobsite-id="{{ $jobSite->id }}"
                                                data-jobsite-name="{{ $jobSite->name }}"
                                                data-jobsite-code="{{ $jobSite->code }}"
                                                data-jobsite-location="{{ $jobSite->location }}"
                                                data-jobsite-description="{{ $jobSite->description }}"
                                                data-jobsite-active="{{ $jobSite->is_active ? '1' : '0' }}">
                                                {{ $jobSite->name }}
                                            </a>
                                            @if($jobSite->description)
                                            <br><small class="text-muted">{{ Str::limit($jobSite->description, 50) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $jobSite->code }}</td>
                                <td>
                                    @if($jobSite->location)
                                        <i class="fas fa-map-marker-alt text-muted me-1"></i>{{ $jobSite->location }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $jobSite->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $jobSite->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $jobSite->daily_reports_count }} reports</span>
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <button type="button" class="btn btn-sm btn-outline-secondary me-1 jobsite-edit-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editJobSiteModal"
                                                data-jobsite-id="{{ $jobSite->id }}"
                                                data-jobsite-name="{{ $jobSite->name }}"
                                                data-jobsite-code="{{ $jobSite->code }}"
                                                data-jobsite-location="{{ $jobSite->location }}"
                                                data-jobsite-description="{{ $jobSite->description }}"
                                                data-jobsite-active="{{ $jobSite->is_active ? '1' : '0' }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger jobsite-delete-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteJobSiteModal"
                                                data-jobsite-id="{{ $jobSite->id }}"
                                                data-jobsite-name="{{ $jobSite->name }}"
                                                data-jobsite-reports="{{ $jobSite->daily_reports_count }}">
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
                            {{ $jobSites->links() }}
                        </div>
                    </div>
                </form>
            </div>
            @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> No job sites found matching your criteria.
            </div>
            @endif
        </div>
    </div>

    <!-- Create Job Site Modal -->
    <div class="modal fade" id="createJobSiteModal" tabindex="-1" aria-labelledby="createJobSiteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createJobSiteModalLabel">Create New Job Site</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.job-sites.store') }}" method="POST">
                    <div class="modal-body">
                        @csrf

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="name" class="form-label">Job Site Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="Enter job site name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="code" class="form-label">Job Site Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" required placeholder="Enter job site code (e.g. HO, SBY, BDG)">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control form-control-lg @error('location') is-invalid @enderror" id="location" name="location" value="{{ old('location') }}" placeholder="Enter location">
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control form-control-lg @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Enter description">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between flex-wrap">
                        <button type="button" class="btn btn-secondary mb-2 mb-sm-0 col-12 col-sm-auto" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary col-12 col-sm-auto">Create Job Site</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Job Site Modal -->
    <div class="modal fade" id="editJobSiteModal" tabindex="-1" aria-labelledby="editJobSiteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editJobSiteModalLabel">Edit Job Site</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editJobSiteForm" method="POST">
                    <div class="modal-body">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Job Site Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="edit_name" name="name" required placeholder="Enter job site name">
                        </div>
                        <div class="mb-3">
                            <label for="edit_code" class="form-label">Job Site Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="edit_code" name="code" required placeholder="Enter job site code">
                        </div>
                        <div class="mb-3">
                            <label for="edit_location" class="form-label">Location</label>
                            <input type="text" class="form-control form-control-lg" id="edit_location" name="location" placeholder="Enter location">
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control form-control-lg" id="edit_description" name="description" rows="3" placeholder="Enter description"></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active">
                                <label class="form-check-label" for="edit_is_active">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between flex-wrap">
                        <button type="button" class="btn btn-secondary mb-2 mb-sm-0 col-12 col-sm-auto" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary col-12 col-sm-auto">Update Job Site</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Job Site Modal -->
    <div class="modal fade" id="deleteJobSiteModal" tabindex="-1" aria-labelledby="deleteJobSiteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteJobSiteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete job site <strong id="delete-jobsite-name"></strong>?</p>
                    <div id="delete-jobsite-warning" class="alert alert-danger d-none">
                        <i class="fas fa-exclamation-triangle me-2"></i> This job site has <span id="delete-jobsite-reports-count"></span> reports associated with it. You cannot delete it until all reports are reassigned.
                    </div>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer justify-content-between flex-wrap">
                    <button type="button" class="btn btn-secondary mb-2 mb-sm-0 col-12 col-sm-auto" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteJobSiteForm" method="POST" class="col-12 col-sm-auto p-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100" id="confirmDeleteButton">Delete Job Site</button>
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
                    <p>Are you sure you want to delete all selected job sites?</p>
                    <p class="text-danger">This action cannot be undone. Job sites with associated reports will not be deleted.</p>
                </div>
                <div class="modal-footer justify-content-between flex-wrap">
                    <button type="button" class="btn btn-secondary mb-2 mb-sm-0 col-12 col-sm-auto" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger col-12 col-sm-auto" id="confirmBulkDelete">Delete Selected Job Sites</button>
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

        @media (max-width: 768px) {
            .card-body {
                padding: 1rem;
            }

            .modal-body, .modal-footer {
                padding: 1rem;
            }

            .form-control, .btn {
                font-size: 16px;
            }

            .form-control-lg {
                height: 50px;
            }

            .modal-footer .btn {
                padding: 0.5rem 1rem;
            }

            .pagination {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Select all checkbox functionality
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

                    if (!this.checked) {
                        selectAllCheckbox.checked = false;
                    } else {
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

            // Mobile checkboxes
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

            // Edit job site modal
            const editJobSiteBtns = document.querySelectorAll('.jobsite-edit-btn');
            editJobSiteBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const jobsiteId = this.getAttribute('data-jobsite-id');
                    const jobsiteName = this.getAttribute('data-jobsite-name');
                    const jobsiteCode = this.getAttribute('data-jobsite-code');
                    const jobsiteLocation = this.getAttribute('data-jobsite-location');
                    const jobsiteDescription = this.getAttribute('data-jobsite-description');
                    const jobsiteActive = this.getAttribute('data-jobsite-active');

                    document.getElementById('editJobSiteForm').action = '{{ url("admin/job-sites") }}/' + jobsiteId;
                    document.getElementById('edit_name').value = jobsiteName;
                    document.getElementById('edit_code').value = jobsiteCode;
                    document.getElementById('edit_location').value = jobsiteLocation || '';
                    document.getElementById('edit_description').value = jobsiteDescription || '';
                    document.getElementById('edit_is_active').checked = jobsiteActive === '1';
                });
            });

            // Delete job site modal
            const deleteJobSiteBtns = document.querySelectorAll('.jobsite-delete-btn');
            deleteJobSiteBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const jobsiteId = this.getAttribute('data-jobsite-id');
                    const jobsiteName = this.getAttribute('data-jobsite-name');
                    const jobsiteReports = parseInt(this.getAttribute('data-jobsite-reports'));

                    document.getElementById('deleteJobSiteForm').action = '{{ url("admin/job-sites") }}/' + jobsiteId;
                    document.getElementById('delete-jobsite-name').textContent = jobsiteName;

                    const warningElement = document.getElementById('delete-jobsite-warning');
                    const deleteButton = document.getElementById('confirmDeleteButton');

                    if (jobsiteReports > 0) {
                        warningElement.classList.remove('d-none');
                        document.getElementById('delete-jobsite-reports-count').textContent = jobsiteReports;
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
                    if (window.innerWidth < 768 && mobileBulkActionForm) {
                        mobileBulkActionForm.submit();
                    } else if (bulkActionForm) {
                        bulkActionForm.submit();
                    }
                });
            }

            // Auto-open modal if there are validation errors
            @if($errors->any())
                const createModal = new bootstrap.Modal(document.getElementById('createJobSiteModal'));
                createModal.show();
            @endif
        });
    </script>
    @endpush
</x-app-layout>
