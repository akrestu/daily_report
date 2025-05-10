<x-app-layout>
    <x-slot name="header">
        My Reports
    </x-slot>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-6 mb-2 mb-md-0">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-clipboard-check text-primary me-2"></i>My Report List
                    </h5>
                </div>
                <div class="col-md-6">
                    <div class="d-flex flex-column flex-sm-row justify-content-md-end">
                        <div class="d-flex mb-2 mb-sm-0 me-sm-2">
                            <a href="{{ route('daily-reports.export') }}?{{ http_build_query(request()->all()) }}" class="btn btn-outline-primary rounded-pill me-2">
                                <i class="fas fa-file-export"></i> <span class="d-none d-md-inline-block ms-1">Export</span>
                            </a>
                            <a href="{{ route('daily-reports.show-import') }}" class="btn btn-outline-success rounded-pill">
                                <i class="fas fa-file-import"></i> <span class="d-none d-md-inline-block ms-1">Import</span>
                            </a>
                        </div>
                        <a href="{{ route('daily-reports.index') }}" class="btn btn-outline-secondary rounded-pill">
                            <i class="fas fa-list"></i> <span class="d-none d-md-inline-block ms-1">All Reports</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-3 p-lg-4">
            <!-- Filter Controls -->
            <div class="card mb-4 border-0 bg-light rounded-3">
                <div class="card-body py-3">
                    <form action="{{ route('daily-reports.user-jobs') }}" method="GET">
                        <div class="row g-2">
                            <div class="col-md-3 col-sm-6 mb-2">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-search text-primary"></i>
                                    </span>
                                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search job name..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-2">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-tasks text-primary"></i>
                                    </span>
                                    <select name="status" class="form-select border-start-0 ps-0">
                                        <option value="">All Job Statuses</option>
                                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-6 mb-2">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-calendar text-primary"></i>
                                    </span>
                                    <input type="date" name="date_from" class="form-control border-start-0 ps-0" placeholder="From date" value="{{ request('date_from', date('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-6 mb-2">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-calendar text-primary"></i>
                                    </span>
                                    <input type="date" name="date_to" class="form-control border-start-0 ps-0" placeholder="To date" value="{{ request('date_to', date('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-12 mb-2">
                                <div class="d-flex gap-2 w-100">
                                    <button type="submit" class="btn btn-primary rounded-pill flex-grow-1">
                                        <i class="fas fa-filter me-1"></i> <span class="d-none d-sm-inline">Filter</span>
                                    </button>
                                    <a href="{{ route('daily-reports.user-jobs') }}" class="btn btn-light rounded-pill flex-grow-1 border">
                                        <i class="fas fa-redo-alt me-1"></i> <span class="d-none d-sm-inline">Reset</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if(count($reports ?? []) > 0)
            <div class="table-responsive rounded-3 overflow-hidden border d-none d-lg-block" id="reports-table-container">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="fw-semibold py-3">Job Name</th>
                            <th class="fw-semibold py-3">Department</th>
                            <th class="fw-semibold py-3">Status</th>
                            <th class="fw-semibold py-3">Report Date</th>
                            <th class="fw-semibold py-3">Due Date</th>
                            <th class="fw-semibold py-3">Created By</th>
                            <th class="fw-semibold py-3">Approval Status</th>
                            <th width="100" class="fw-semibold py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @foreach($reports as $report)
                        <tr class="report-row">
                            <td class="py-3">
                                <a href="{{ route('daily-reports.show', $report) }}" class="fw-medium text-decoration-none d-block text-truncate" style="max-width: 200px;">
                                    {{ $report->job_name }}
                                </a>
                                @if($report->comments->count() > 0)
                                <span class="badge bg-info rounded-pill ms-1" data-bs-toggle="tooltip" title="{{ $report->comments->count() }} Comments">
                                    <i class="fas fa-comments"></i> {{ $report->comments->count() }}
                                </span>
                                @endif
                            </td>
                            <td class="py-3">{{ $report->department->name ?? 'N/A' }}</td>
                            <td class="py-3">
                                @if($report->status === 'pending')
                                    <span class="badge bg-warning text-dark rounded-pill px-3">
                                        <i class="fas fa-clock me-1"></i> Pending
                                    </span>
                                @elseif($report->status === 'in_progress')
                                    <span class="badge bg-info rounded-pill px-3">
                                        <i class="fas fa-spinner me-1"></i> In Progress
                                    </span>
                                @else
                                    <span class="badge bg-success rounded-pill px-3">
                                        <i class="fas fa-check me-1"></i> Completed
                                    </span>
                                @endif
                            </td>
                            <td class="py-3">
                                <div class="d-flex align-items-center">
                                    <i class="far fa-calendar-alt text-primary me-2"></i>
                                    {{ $report->report_date->format('d M Y') }}
                                </div>
                            </td>
                            <td class="py-3">
                                <div class="d-flex align-items-center">
                                    <i class="far fa-calendar-check text-primary me-2"></i>
                                    {{ $report->due_date->format('d M Y') }}
                                </div>
                            </td>
                            <td class="py-3">{{ $report->user->name ?? 'N/A' }}</td>
                            <td class="py-3">
                                @if($report->approval_status === 'approved')
                                    <span class="badge bg-success rounded-pill px-3">
                                        <i class="fas fa-check-circle me-1"></i> Approved
                                    </span>
                                @elseif($report->approval_status === 'rejected')
                                    <span class="badge bg-danger rounded-pill px-3">
                                        <i class="fas fa-times-circle me-1"></i> Rejected
                                    </span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-3">
                                        <i class="fas fa-hourglass-half me-1"></i> Pending
                                    </span>
                                @endif
                                
                                @if($report->approved_by)
                                    <div class="small text-muted mt-1">
                                        <i class="fas fa-user me-1"></i> {{ $report->approver->name ?? 'N/A' }}
                                    </div>
                                @endif
                                
                                @if($report->approval_status === 'rejected' && $report->rejection_reason)
                                    <div class="small text-danger mt-1" data-bs-toggle="tooltip" title="{{ $report->rejection_reason }}">
                                        <i class="fas fa-info-circle me-1"></i> 
                                        <span class="text-truncate d-inline-block" style="max-width: 150px;">{{ $report->rejection_reason }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="py-3">
                                <div class="dropdown position-static">
                                    <button class="btn btn-sm btn-light rounded-pill px-3 border" type="button" onclick="toggleDropdown(this)" style="cursor: pointer;">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="display: none;">
                                        <li>
                                            <a href="{{ route('daily-reports.show', $report) }}" class="dropdown-item">
                                                <i class="fas fa-eye text-primary me-2"></i> View Details
                                            </a>
                                        </li>
                                        @if(!$report->approved_by)
                                        <li>
                                            <a href="{{ route('daily-reports.edit', $report) }}" class="dropdown-item">
                                                <i class="fas fa-edit text-success me-2"></i> Edit Report
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" class="dropdown-item text-danger" 
                                               onclick="event.preventDefault(); confirmDelete({{ $report->id }}, '{{ $report->job_name }}')">
                                                <i class="fas fa-trash text-danger me-2"></i> Delete
                                            </a>
                                        </li>
                                        @endif
                                        @if($report->status === 'pending' && auth()->user()->can('update-report-status'))
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a href="#" class="dropdown-item" 
                                               data-bs-toggle="modal" 
                                               data-bs-target="#updateStatusModal"
                                               data-report-id="{{ $report->id }}"
                                               data-report-name="{{ $report->job_name }}">
                                                <i class="fas fa-sync-alt text-info me-2"></i> Update Status
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="d-lg-none">
                @foreach($reports as $report)
                <div class="card mb-3 shadow-sm border report-card">
                    <div class="card-header bg-white d-flex justify-content-between py-2">
                        <div class="d-flex align-items-center">
                            <a href="{{ route('daily-reports.show', $report) }}" class="fw-bold text-decoration-none text-dark">
                                {{ $report->job_name }}
                            </a>
                            @if($report->comments->count() > 0)
                            <span class="badge bg-info rounded-pill ms-2" data-bs-toggle="tooltip" title="{{ $report->comments->count() }} Comments">
                                <i class="fas fa-comments"></i> {{ $report->comments->count() }}
                            </span>
                            @endif
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light rounded-pill px-2 border" type="button" onclick="toggleDropdown(this)" style="cursor: pointer;">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="display: none;">
                                <li>
                                    <a href="{{ route('daily-reports.show', $report) }}" class="dropdown-item">
                                        <i class="fas fa-eye text-primary me-2"></i> View Details
                                    </a>
                                </li>
                                @if(!$report->approved_by)
                                <li>
                                    <a href="{{ route('daily-reports.edit', $report) }}" class="dropdown-item">
                                        <i class="fas fa-edit text-success me-2"></i> Edit Report
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="dropdown-item text-danger" 
                                       onclick="event.preventDefault(); confirmDelete({{ $report->id }}, '{{ $report->job_name }}')">
                                        <i class="fas fa-trash text-danger me-2"></i> Delete
                                    </a>
                                </li>
                                @endif
                                @if($report->status === 'pending' && auth()->user()->can('update-report-status'))
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a href="#" class="dropdown-item" 
                                       data-bs-toggle="modal" 
                                       data-bs-target="#updateStatusModal"
                                       data-report-id="{{ $report->id }}"
                                       data-report-name="{{ $report->job_name }}">
                                        <i class="fas fa-sync-alt text-info me-2"></i> Update Status
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                    <div class="card-body py-2">
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="small text-muted">Department</div>
                                <div>{{ $report->department->name ?? 'N/A' }}</div>
                            </div>
                            <div class="col-6">
                                <div class="small text-muted">Status</div>
                                <div>
                                    @if($report->status === 'pending')
                                        <span class="badge bg-warning text-dark rounded-pill px-2">
                                            <i class="fas fa-clock me-1"></i> Pending
                                        </span>
                                    @elseif($report->status === 'in_progress')
                                        <span class="badge bg-info rounded-pill px-2">
                                            <i class="fas fa-spinner me-1"></i> In Progress
                                        </span>
                                    @else
                                        <span class="badge bg-success rounded-pill px-2">
                                            <i class="fas fa-check me-1"></i> Completed
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="small text-muted">Report Date</div>
                                <div class="d-flex align-items-center">
                                    <i class="far fa-calendar-alt text-primary me-1 small"></i>
                                    {{ $report->report_date->format('d M Y') }}
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="small text-muted">Due Date</div>
                                <div class="d-flex align-items-center">
                                    <i class="far fa-calendar-check text-primary me-1 small"></i>
                                    {{ $report->due_date->format('d M Y') }}
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="small text-muted">Created By</div>
                                <div>{{ $report->user->name ?? 'N/A' }}</div>
                            </div>
                            <div class="col-6">
                                <div class="small text-muted">Approval Status</div>
                                <div>
                                    @if($report->approval_status === 'approved')
                                        <span class="badge bg-success rounded-pill px-2">
                                            <i class="fas fa-check-circle me-1"></i> Approved
                                        </span>
                                    @elseif($report->approval_status === 'rejected')
                                        <span class="badge bg-danger rounded-pill px-2">
                                            <i class="fas fa-times-circle me-1"></i> Rejected
                                        </span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill px-2">
                                            <i class="fas fa-hourglass-half me-1"></i> Pending
                                        </span>
                                    @endif
                                </div>
                                
                                @if($report->approved_by)
                                    <div class="small text-muted mt-1">
                                        <i class="fas fa-user me-1"></i> {{ $report->approver->name ?? 'N/A' }}
                                    </div>
                                @endif
                                
                                @if($report->approval_status === 'rejected' && $report->rejection_reason)
                                    <div class="small text-danger mt-1 text-truncate" data-bs-toggle="tooltip" title="{{ $report->rejection_reason }}">
                                        <i class="fas fa-info-circle me-1"></i> {{ $report->rejection_reason }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-4 d-flex justify-content-end">
                {{ $reports->links() }}
            </div>
            @else
            <div class="alert alert-info border-0 shadow-sm">
                <div class="d-flex">
                    <div class="me-3">
                        <i class="fas fa-info-circle fa-2x text-info"></i>
                    </div>
                    <div>
                        <h5 class="alert-heading mb-1">No reports found</h5>
                        <p class="mb-0">Try adjusting your search criteria or create a new report.</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="updateStatusForm" action="" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header bg-light">
                        <h5 class="modal-title" id="updateStatusModalLabel">
                            <i class="fas fa-sync-alt text-primary me-2"></i>Update Job Status
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-4 pb-2 border-bottom">
                            <label class="form-label text-muted small">REPORT NAME</label>
                            <div class="fw-bold fs-5" id="reportName"></div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="status" class="form-label fw-medium">Status <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-tasks text-primary"></i></span>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status_remark" class="form-label fw-medium">Remarks</label>
                            <textarea class="form-control" id="status_remark" name="remark" rows="3" 
                                      placeholder="Add additional notes about this status update (optional)"
                                      style="border-radius: 0.375rem;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="fas fa-save me-1"></i> Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="deleteConfirmationModalLabel">
                        <i class="fas fa-trash text-danger me-2"></i>Delete Report
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-1">Are you sure you want to delete this report?</p>
                    <p class="text-muted mb-0">Report: <strong id="deleteReportName"></strong></p>
                    <p class="text-danger mb-0"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger rounded-pill px-4" id="confirmDeleteButton">
                        <i class="fas fa-trash me-1"></i> Delete Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Delete Form -->
    <form id="deleteForm" action="" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <style>
        /* Mobile optimization styles */
        @media (max-width: 767.98px) {
            .card-header {
                padding: 0.75rem;
            }
            
            .card-body {
                padding: 0.75rem;
            }
            
            .btn {
                padding: 0.375rem 0.75rem;
                font-size: 0.9rem;
            }
            
            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
            
            /* Button optimization for mobile */
            .card-header .btn {
                padding: 0.375rem 0.75rem;
                font-size: 0.875rem;
                display: flex;
                align-items: center;
                justify-content: center;
                min-width: 38px;
                margin-bottom: 0.25rem;
            }
            
            .card-header .d-flex.mb-2 {
                width: 100%;
                justify-content: space-between;
            }
            
            .card-header .d-flex.mb-2 .btn {
                flex: 1;
            }
            
            .card-header .btn-outline-secondary {
                width: 100%;
                justify-content: center;
            }
            
            .badge {
                font-size: 0.7rem;
            }
            
            .report-card {
                border-radius: 0.5rem;
                overflow: hidden;
            }
            
            .report-card .card-header {
                padding: 0.75rem;
            }
            
            .report-card .card-body {
                padding: 0.75rem;
            }
            
            .pagination {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .pagination .page-item {
                margin-bottom: 0.5rem;
            }
            
            /* Increase spacing between filter items */
            .col-sm-6, .col-sm-12 {
                margin-bottom: 0.5rem;
            }
            
            /* Improve form controls */
            .form-control, .form-select {
                font-size: 0.9rem;
            }
            
            /* Improve text readability */
            .small {
                font-size: 0.75rem;
            }
        }
        
        /* Extra small screen optimization */
        @media (max-width: 575.98px) {
            .card-header .d-flex {
                flex-direction: column;
            }
            
            .card-header .d-flex.mb-2 {
                flex-direction: row;
                margin-bottom: 0.5rem !important;
            }
            
            .card-header .d-flex .btn {
                margin-right: 0;
            }
            
            .card-header .d-flex.mb-2 .btn:first-child {
                margin-right: 0.5rem;
            }
            
            .card-header .btn {
                width: 100%;
                margin-bottom: 0.5rem;
                padding: 0.375rem 0.5rem;
            }
            
            /* Optimize icons on very small screens */
            .card-header .btn i {
                font-size: 1rem;
            }
        }
        
        /* Dropdown menu styles */
        .dropdown-menu {
            position: absolute;
            transform: translate3d(0px, 0px, 0px);
            top: 100%; 
            left: auto;
            right: 0;
            will-change: transform;
            z-index: 1000;
            display: none;
            min-width: 10rem;
            padding: 0.5rem 0;
            text-align: left;
            background-color: #fff;
            background-clip: padding-box;
            border-radius: 0.25rem;
        }
        
        /* Position the dropdown properly */
        .dropdown.position-static .dropdown-menu {
            position: absolute;
            right: 0;
            margin-top: 5px;
        }
        
        /* Ensure table rows have enough height */
        .report-row {
            min-height: 60px;
            position: relative;
        }
        
        /* Ensure the table has enough vertical space */
        .table-responsive {
            min-height: 200px;
            overflow: visible !important;
        }
        
        /* Ensure dropdowns appear on top of other elements */
        .dropdown-menu.show, .dropdown-menu[style*="display: block"] {
            z-index: 1050;
        }

        /* Styling untuk posisi modal delete */
        #deleteConfirmationModal .modal-dialog {
            margin-top: 2rem;
            margin-bottom: auto;
        }

        #deleteConfirmationModal.fade .modal-dialog {
            transform: translate(0, -100px);
            transition: transform 0.3s ease-out;
        }

        #deleteConfirmationModal.show .modal-dialog {
            transform: translate(0, 0);
        }
    </style>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Status modal handling
            const updateStatusModal = document.getElementById('updateStatusModal');
            if (updateStatusModal) {
                updateStatusModal.addEventListener('show.bs.modal', function (event) {
                    // Button that triggered the modal
                    const button = event.relatedTarget;
                    
                    // Extract info from data-* attributes
                    const reportId = button.getAttribute('data-report-id');
                    const reportName = button.getAttribute('data-report-name');
                    
                    // Update the modal's content
                    const reportNameElement = updateStatusModal.querySelector('#reportName');
                    const form = updateStatusModal.querySelector('#updateStatusForm');
                    
                    if (reportNameElement) reportNameElement.textContent = reportName;
                    if (form) form.action = `/daily-reports/${reportId}/status`;
                });
            }
        });
        
        // Function to show delete confirmation modal
        function confirmDelete(reportId, reportName) {
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
            const deleteReportNameElement = document.getElementById('deleteReportName');
            const confirmDeleteButton = document.getElementById('confirmDeleteButton');
            
            deleteReportNameElement.textContent = reportName;
            
            confirmDeleteButton.onclick = function() {
                const form = document.getElementById('deleteForm');
                form.action = `/daily-reports/${reportId}`;
                form.submit();
            };
            
            deleteModal.show();
        }
        
        // Simple function to toggle dropdown visibility
        function toggleDropdown(button) {
            // Find the dropdown menu (next sibling)
            const dropdownMenu = button.nextElementSibling;
            const tableContainer = document.getElementById('reports-table-container');
            
            // Close all other open dropdowns first
            document.querySelectorAll('.dropdown-menu').forEach(function(menu) {
                if (menu !== dropdownMenu && menu.style.display === 'block') {
                    menu.style.display = 'none';
                }
            });
            
            // Toggle this dropdown visibility
            if (dropdownMenu.style.display === 'block') {
                dropdownMenu.style.display = 'none';
            } else {
                // Make sure the container doesn't clip the dropdown
                if (tableContainer) {
                    tableContainer.style.overflow = 'visible';
                }
                
                dropdownMenu.style.display = 'block';
                
                // Ensure the dropdown is visible within the viewport
                const rect = dropdownMenu.getBoundingClientRect();
                const buttonRect = button.getBoundingClientRect();
                
                // Position dropdown relative to the button
                dropdownMenu.style.top = (buttonRect.height + 5) + 'px';
                
                // If dropdown goes beyond the viewport right edge on mobile
                if (window.innerWidth < 768 && rect.right > window.innerWidth) {
                    dropdownMenu.style.right = '0';
                }
                
                // If dropdown goes beyond the bottom of the viewport, position it above the button
                if (rect.bottom > window.innerHeight) {
                    const dropdownHeight = rect.height;
                    dropdownMenu.style.top = (-dropdownHeight - 5) + 'px';
                }
            }
            
            // Stop event propagation
            event.stopPropagation();
            
            return false;
        }
        
        // Close all dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu').forEach(function(menu) {
                    menu.style.display = 'none';
                });
            }
        });
    </script>
    @endpush
</x-app-layout>