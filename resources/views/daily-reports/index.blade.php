<x-app-layout>
    <x-slot name="header">
        Daily Reports
    </x-slot>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-6 mb-2 mb-md-0">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-clipboard-list text-primary me-2"></i>All Reports
                    </h5>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-end">
                        <a href="{{ route('daily-reports.export-all') }}?{{ http_build_query(request()->all()) }}" class="btn btn-outline-primary rounded-pill px-3">
                            <i class="fas fa-file-export me-1"></i> Export
                        </a>
                        <a href="{{ route('daily-reports.create') }}" class="btn btn-primary rounded-pill px-3">
                            <i class="fas fa-plus-circle me-1"></i> New Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Reports Tab Switcher -->
        <div class="card-header bg-white pt-0 pb-2 border-0 px-0 px-md-3">
            <div class="tab-wrapper px-2">
                <ul class="nav nav-tabs card-header-tabs mt-2 tab-container mx-0">
                    <li class="nav-item flex-fill text-center me-1">
                        <a class="nav-link {{ $reportType == 'approved' ? 'active approved-tab' : 'approved-tab' }} rounded-top d-flex align-items-center justify-content-center" href="{{ route('daily-reports.index', ['type' => 'approved']) }}">
                            <i class="fas fa-check-circle me-1 me-md-2"></i> <span class="tab-text">Approved</span>
                        </a>
                    </li>
                    <li class="nav-item flex-fill text-center ms-1">
                        <a class="nav-link {{ $reportType == 'rejected' ? 'active rejected-tab' : 'rejected-tab' }} rounded-top d-flex align-items-center justify-content-center" href="{{ route('daily-reports.index', ['type' => 'rejected']) }}">
                            <i class="fas fa-times-circle me-1 me-md-2"></i> <span class="tab-text">Rejected</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="card-body p-lg-4">
            <!-- Filter Controls -->
            <div class="card mb-4 border-0 bg-light rounded-3">
                <div class="card-body py-3">
                    <form action="{{ route('daily-reports.index') }}" method="GET">
                        <input type="hidden" name="type" value="{{ $reportType }}">
                        <div class="row g-2 align-items-center justify-content-center">
                            <div class="col-md-2 col-sm-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-search text-primary"></i>
                                    </span>
                                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search job name..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-calendar-alt text-primary"></i>
                                    </span>
                                    <input type="date" name="date_from" class="form-control border-start-0 ps-0" placeholder="From Date" value="{{ request('date_from', date('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-calendar-alt text-primary"></i>
                                    </span>
                                    <input type="date" name="date_to" class="form-control border-start-0 ps-0" placeholder="To Date" value="{{ request('date_to', date('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-building text-primary"></i>
                                    </span>
                                    <select name="department" class="form-select border-start-0 ps-0">
                                        <option value="">All Departments</option>
                                        @foreach($departments ?? [] as $id => $name)
                                            <option value="{{ $id }}" {{ request('department') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 d-none d-md-block">
                                <div class="d-flex justify-content-center gap-2">
                                    <button type="submit" class="btn btn-primary rounded-pill">
                                        <i class="fas fa-filter me-1"></i> Filter
                                    </button>
                                    <a href="{{ route('daily-reports.index', ['type' => $reportType]) }}" class="btn btn-light rounded-pill px-3 border">
                                        <i class="fas fa-redo-alt me-1"></i> Reset
                                    </a>
                                </div>
                            </div>
                            <div class="col-12 d-md-none mt-2">
                                <div class="d-flex justify-content-center gap-2">
                                    <button type="submit" class="btn btn-primary rounded-pill">
                                        <i class="fas fa-filter me-1"></i> Filter
                                    </button>
                                    <a href="{{ route('daily-reports.index', ['type' => $reportType]) }}" class="btn btn-light rounded-pill px-3 border">
                                        <i class="fas fa-redo-alt me-1"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if(count($reports ?? []) > 0)
            <!-- Desktop Table View -->
            <div class="table-responsive rounded-3 overflow-hidden border d-none d-lg-block" id="all-reports-table-container">
                <form id="bulk-action-form" method="POST">
                    @csrf
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                @if(auth()->user()->isAdmin() || auth()->user()->isDepartmentHead())
                                <th width="40" class="fw-semibold py-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAll" style="border-radius: 3px;">
                                    </div>
                                </th>
                                @endif
                                <th class="fw-semibold py-3">Job Name</th>
                                <th class="fw-semibold py-3">Department</th>
                                <th class="fw-semibold py-3">Status</th>
                                <th class="fw-semibold py-3">Report Date</th>
                                <th class="fw-semibold py-3">Due Date</th>
                                <th class="fw-semibold py-3">Created By</th>
                                @if($reportType == 'approved')
                                <th class="fw-semibold py-3">Approved By</th>
                                <th class="fw-semibold py-3">Approval Date</th>
                                @else
                                <th class="fw-semibold py-3">Rejected By</th>
                                <th class="fw-semibold py-3">Rejection Date</th>
                                <th class="fw-semibold py-3">Rejection Reason</th>
                                @endif
                                <th width="120" class="fw-semibold py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @foreach($reports as $report)
                            <tr>
                                @if(auth()->user()->isAdmin() || auth()->user()->isDepartmentHead())
                                <td class="py-3">
                                    <div class="form-check">
                                        <input class="form-check-input select-checkbox" type="checkbox" name="selected_reports[]" value="{{ $report->id }}" style="border-radius: 3px;">
                                    </div>
                                </td>
                                @endif
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
                                @if($reportType == 'approved')
                                <td class="py-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-check text-success me-2"></i>
                                        {{ $report->approver->name ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="py-3">{{ $report->updated_at->format('d M Y H:i') }}</td>
                                @else
                                <td class="py-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-times text-danger me-2"></i>
                                        {{ $report->approver->name ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="py-3">{{ $report->updated_at->format('d M Y H:i') }}</td>
                                <td class="py-3">
                                    <span class="d-inline-block text-truncate" style="max-width: 200px;" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $report->rejection_reason }}">
                                        <i class="fas fa-info-circle text-danger me-1"></i>
                                        {{ $report->rejection_reason }}
                                    </span>
                                </td>
                                @endif
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
                                            @if(auth()->user()->isAdmin() || auth()->user()->isDepartmentHead())
                                            <li>
                                                <a href="#" class="dropdown-item text-danger" 
                                                   onclick="event.preventDefault(); confirmDelete({{ $report->id }}, '{{ $report->job_name }}')">
                                                    <i class="fas fa-trash text-danger me-2"></i> Delete
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

                    <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap p-3 bg-light">
                        <div>
                            @if(auth()->user()->isAdmin() || auth()->user()->isDepartmentHead())
                            <button type="button" class="btn btn-danger rounded-pill px-4 bulk-action-button" disabled onclick="showBatchDeleteModal()">
                                <i class="fas fa-trash me-1"></i> Delete Selected
                            </button>
                            @endif
                        </div>
                        <div>
                            {{ $reports->appends(['type' => $reportType])->links() }}
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Mobile Card View -->
            <div class="d-lg-none" id="mobile-reports-container">
                <form id="mobile-bulk-action-form" method="POST">
                    @csrf
                    @if(auth()->user()->isAdmin() || auth()->user()->isDepartmentHead())
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="mobileSelectAll" style="border-radius: 3px;">
                            <label class="form-check-label" for="mobileSelectAll">Select All</label>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 mobile-bulk-action-button" disabled onclick="showBatchDeleteModal()">
                            <i class="fas fa-trash me-1"></i> Delete Selected
                        </button>
                    </div>
                    @endif
                    
                    <div class="row g-3">
                        @foreach($reports as $report)
                        <div class="col-12">
                            <div class="card border shadow-sm report-card">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1 fw-bold text-truncate" style="max-width: 200px;">
                                                {{ $report->job_name }}
                                                @if($report->comments->count() > 0)
                                                <span class="badge bg-info rounded-pill ms-1" data-bs-toggle="tooltip" title="{{ $report->comments->count() }} Comments">
                                                    <i class="fas fa-comments"></i> {{ $report->comments->count() }}
                                                </span>
                                                @endif
                                            </h6>
                                            <div class="small text-muted mb-2">{{ $report->department->name ?? 'N/A' }}</div>
                                        </div>
                                        @if(auth()->user()->isAdmin() || auth()->user()->isDepartmentHead())
                                        <div class="form-check">
                                            <input class="form-check-input mobile-select-checkbox" type="checkbox" name="selected_reports[]" value="{{ $report->id }}" style="border-radius: 3px;">
                                        </div>
                                        @endif
                                    </div>
                                    
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <i class="far fa-calendar-alt text-primary me-2 small"></i>
                                                <span class="small">{{ $report->report_date->format('d M Y') }}</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <i class="far fa-calendar-check text-primary me-2 small"></i>
                                                <span class="small">{{ $report->due_date->format('d M Y') }}</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user text-primary me-2 small"></i>
                                                <span class="small text-truncate">{{ $report->user->name ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
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
                                    
                                    @if($reportType == 'approved')
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-check text-success me-2 small"></i>
                                                <span class="small text-truncate">{{ $report->approver->name ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <i class="far fa-clock text-success me-2 small"></i>
                                                <span class="small">{{ $report->updated_at->format('d M Y') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    @else
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-times text-danger me-2 small"></i>
                                                <span class="small text-truncate">{{ $report->approver->name ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <i class="far fa-clock text-danger me-2 small"></i>
                                                <span class="small">{{ $report->updated_at->format('d M Y') }}</span>
                                            </div>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <div class="d-flex align-items-start">
                                                <i class="fas fa-info-circle text-danger me-2 small mt-1"></i>
                                                <span class="small text-muted">{{ $report->rejection_reason }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    
                                    <div class="d-flex justify-content-end">
                                        <a href="{{ route('daily-reports.show', $report) }}" class="btn btn-primary btn-sm rounded-pill px-3 me-2">
                                            <i class="fas fa-eye me-1"></i> View
                                        </a>
                                        @if(auth()->user()->isAdmin() || auth()->user()->isDepartmentHead())
                                        <button type="button" class="btn btn-danger btn-sm rounded-pill px-3" onclick="confirmDelete({{ $report->id }}, '{{ $report->job_name }}')">
                                            <i class="fas fa-trash me-1"></i> Delete
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-4 d-flex justify-content-center">
                        {{ $reports->appends(['type' => $reportType])->links() }}
                    </div>
                </form>
            </div>
            @else
            <div class="alert alert-info border-0 shadow-sm">
                <div class="d-flex">
                    <div class="me-3">
                        <i class="fas fa-info-circle fa-2x text-info"></i>
                    </div>
                    <div>
                        <h5 class="alert-heading mb-1">
                            @if($reportType == 'approved')
                            No approved reports found
                            @else
                            No rejected reports found
                            @endif
                        </h5>
                        <p class="mb-0">Try adjusting your search criteria or create a new report.</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Hidden Delete Form -->
    <form id="deleteForm" action="" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <!-- Batch Delete Form -->
    <form id="batchDeleteForm" action="" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="_method" value="DELETE">
        <!-- Selected reports will be appended here via JavaScript -->
    </form>

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

    <!-- Batch Delete Confirmation Modal -->
    <div class="modal fade" id="batchDeleteModal" tabindex="-1" aria-labelledby="batchDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="batchDeleteModalLabel">
                        <i class="fas fa-trash-alt text-danger me-2"></i>Delete Selected Reports
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex mb-3">
                        <div class="me-3 text-danger">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Are you sure you want to delete the selected reports?</h6>
                            <p class="text-muted mb-0"><span id="selectedCount" class="fw-bold">0</span> reports selected for deletion.</p>
                        </div>
                    </div>
                    <div class="alert alert-warning p-3 border-0 mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        This action cannot be undone. All selected reports will be permanently deleted.
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger rounded-pill px-4" id="confirmBatchDeleteButton">
                        <i class="fas fa-trash-alt me-1"></i> Delete Selected
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
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
        tr {
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
        
        /* Modal animation for consistency */
        .modal.fade .modal-dialog {
            transform: translate(0, -50px);
            transition: transform 0.3s ease-out;
        }

        .modal.show .modal-dialog {
            transform: translate(0, 0);
        }
        
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        /* Consistent styling for all modals */
        .modal-content {
            border: none;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .modal-header, .modal-footer {
            background-color: #f8f9fa;
            border-color: #e9ecef;
        }
        
        /* Mobile responsiveness styles */
        @media (max-width: 991.98px) {
            .card-header, .card-body {
                padding: 1rem;
            }
            
            .filter-bar .col {
                margin-bottom: 0.5rem;
            }
            
            .pagination {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .pagination .page-item {
                margin-bottom: 0.25rem;
            }
        }
        
        @media (max-width: 767.98px) {
            .card-header {
                padding: 0.75rem;
            }
            
            /* Add extra padding to the header with the "All Reports" title */
            .card-header h5.fw-bold {
                padding: 0.5rem 0;
                margin-left: 0.75rem;
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
            
            /* Form control optimization for mobile */
            .form-control, .form-select {
                font-size: 0.9rem;
                height: calc(1.5em + 0.75rem + 2px);
            }
            
            /* Report card optimization */
            .report-card {
                margin-bottom: 0.75rem;
            }
        }
        
        @media (max-width: 575.98px) {
            /* Extra small screen optimization */
            .card-header .nav-tabs .nav-link {
                padding: 0.5rem 0.75rem;
                font-size: 0.9rem;
            }
            
            .report-card .card-body {
                padding: 0.75rem;
            }
            
            /* Improve filter controls visibility */
            .input-group-text {
                padding: 0.25rem 0.5rem;
            }
            
            /* Improve button spacing */
            .btn-group > .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.8rem;
            }
        }
        
        /* Tab container wrapper to constrain content */
        .tab-wrapper {
            overflow: visible;
            width: 100%;
        }
        
        /* Tab container styling for mobile */
        .tab-container {
            display: flex;
            width: 100%;
            margin-left: 0;
            margin-right: 0;
        }
        
        .tab-container .nav-item {
            margin-bottom: 0;
        }
        
        .tab-container .nav-link {
            border-radius: 0;
            border-top-left-radius: 0.25rem;
            border-top-right-radius: 0.25rem;
            white-space: nowrap;
            padding: 0.6rem 0.5rem;
            transition: all 0.2s ease;
            position: relative;
            border: 1px solid transparent;
            border-bottom: none;
            font-weight: 500;
        }
        
        /* Approved tab styling */
        .approved-tab {
            color: #198754;
        }
        
        .approved-tab i {
            color: #198754;
        }
        
        .approved-tab:not(.active):hover {
            background-color: rgba(25, 135, 84, 0.05);
            color: #198754;
        }
        
        .approved-tab.active {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
            border-color: #198754;
            border-bottom-color: transparent;
        }
        
        .approved-tab.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #198754;
        }
        
        /* Rejected tab styling */
        .rejected-tab {
            color: #dc3545;
        }
        
        .rejected-tab i {
            color: #dc3545;
        }
        
        .rejected-tab:not(.active):hover {
            background-color: rgba(220, 53, 69, 0.05);
            color: #dc3545;
        }
        
        .rejected-tab.active {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border-color: #dc3545;
            border-bottom-color: transparent;
        }
        
        .rejected-tab.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #dc3545;
        }
        
        /* Make tab content fit on smaller screens */
        @media (max-width: 575.98px) {
            .tab-container .nav-link {
                font-size: 0.9rem;
                padding: 0.6rem 0.25rem;
            }
            
            .tab-container .nav-link i {
                font-size: 0.9rem;
            }
            
            .tab-text:after {
                content: "";
            }
        }
        
        /* Make the tabs the same width on mobile */
        @media (max-width: 767.98px) {
            .tab-container {
                flex-wrap: nowrap;
                gap: 0;
                width: 100%;
            }
            
            .tab-container .nav-item {
                flex: 1;
            }
            
            .tab-container .nav-link {
                border-radius: 0.5rem;
                box-shadow: 0 -2px 5px rgba(0,0,0,0.05);
                padding: 0.6rem 0.5rem;
                text-align: center;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .tab-text {
                display: inline-block;
            }
            
            .approved-tab {
                background-color: rgba(25, 135, 84, 0.05);
            }
            
            .rejected-tab {
                background-color: rgba(220, 53, 69, 0.05);
            }
            
            .approved-tab.active {
                background-color: rgba(25, 135, 84, 0.15);
                box-shadow: 0 -2px 5px rgba(25, 135, 84, 0.1);
            }
            
            .rejected-tab.active {
                background-color: rgba(220, 53, 69, 0.15);
                box-shadow: 0 -2px 5px rgba(220, 53, 69, 0.1);
            }
            
            .card-header .nav-tabs {
                border-bottom: none;
            }
            
            .card-header.bg-white {
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
        }
        
        @media (min-width: 768px) {
            .tab-text:after {
                content: " Reports";
            }
        }
        
        /* Enhanced tab styling with clearer borders */
        .nav-tabs .nav-link {
            border: 1px solid #dee2e6;
            border-bottom: none;
            background-color: #f8f9fa;
            color: #6c757d;
            font-weight: 500;
            padding: 0.75rem 1rem;
        }
        
        .nav-tabs .nav-link.active {
            border: 1px solid #dee2e6;
            border-bottom: none;
            background-color: #fff;
            color: #0d6efd;
            font-weight: 500;
            margin-bottom: -1px;
        }
        
        .nav-tabs .nav-link:hover:not(.active) {
            background-color: #e9ecef;
            border-color: #ced4da;
        }
        
        .approved-tab.active {
            background-color: rgba(25, 135, 84, 0.1) !important;
            color: #198754 !important;
            border-color: #dee2e6 !important;
        }
        
        .rejected-tab.active {
            background-color: rgba(220, 53, 69, 0.1) !important;
            color: #dc3545 !important;
            border-color: #dee2e6 !important;
        }
        
        .tab-container {
            border-bottom: 1px solid #dee2e6;
        }
        
        @media (max-width: 767.98px) {
            .nav-tabs .nav-link {
                padding: 0.5rem 0.5rem;
            }
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

            // Initialize all modals
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
            const batchDeleteModal = new bootstrap.Modal(document.getElementById('batchDeleteModal'));

            // Desktop Select all checkbox functionality
            const selectAllCheckbox = document.getElementById('selectAll');
            const selectCheckboxes = document.querySelectorAll('.select-checkbox');
            const bulkActionButton = document.querySelector('.bulk-action-button');
            
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    selectCheckboxes.forEach(checkbox => {
                        checkbox.checked = selectAllCheckbox.checked;
                    });
                    updateBulkActionButton();
                });
                
                selectCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        updateBulkActionButton();
                        // If any checkbox is unchecked, uncheck "Select All" checkbox
                        if (!this.checked) {
                            selectAllCheckbox.checked = false;
                        }
                        // If all checkboxes are checked, check "Select All" checkbox
                        else if (document.querySelectorAll('.select-checkbox:checked').length === selectCheckboxes.length) {
                            selectAllCheckbox.checked = true;
                        }
                    });
                });
            }
            
            // Mobile Select all checkbox functionality
            const mobileSelectAllCheckbox = document.getElementById('mobileSelectAll');
            const mobileSelectCheckboxes = document.querySelectorAll('.mobile-select-checkbox');
            const mobileBulkActionButton = document.querySelector('.mobile-bulk-action-button');
            
            if (mobileSelectAllCheckbox) {
                mobileSelectAllCheckbox.addEventListener('change', function() {
                    mobileSelectCheckboxes.forEach(checkbox => {
                        checkbox.checked = mobileSelectAllCheckbox.checked;
                    });
                    updateMobileBulkActionButton();
                });
                
                mobileSelectCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        updateMobileBulkActionButton();
                        // If any checkbox is unchecked, uncheck "Select All" checkbox
                        if (!this.checked) {
                            mobileSelectAllCheckbox.checked = false;
                        }
                        // If all checkboxes are checked, check "Select All" checkbox
                        else if (document.querySelectorAll('.mobile-select-checkbox:checked').length === mobileSelectCheckboxes.length) {
                            mobileSelectAllCheckbox.checked = true;
                        }
                    });
                });
            }
            
            function updateBulkActionButton() {
                if (bulkActionButton) {
                    const checkedCount = document.querySelectorAll('.select-checkbox:checked').length;
                    bulkActionButton.disabled = checkedCount === 0;
                }
            }
            
            function updateMobileBulkActionButton() {
                if (mobileBulkActionButton) {
                    const checkedCount = document.querySelectorAll('.mobile-select-checkbox:checked').length;
                    mobileBulkActionButton.disabled = checkedCount === 0;
                }
            }
            
            // Set up batch delete confirmation
            document.getElementById('confirmBatchDeleteButton').addEventListener('click', function() {
                processBatchDelete();
            });
        });
        
        // Function to show batch delete modal
        function showBatchDeleteModal() {
            // Check if we're on mobile or desktop view
            const isMobileView = window.innerWidth < 992;
            const selectedCheckboxes = isMobileView ? 
                document.querySelectorAll('.mobile-select-checkbox:checked') : 
                document.querySelectorAll('.select-checkbox:checked');
                
            // Update the count of selected items in the modal
            document.getElementById('selectedCount').textContent = selectedCheckboxes.length;
            
            // Show the modal
            const batchDeleteModal = new bootstrap.Modal(document.getElementById('batchDeleteModal'));
            batchDeleteModal.show();
        }
        
        // Function to process batch delete
        function processBatchDelete() {
            // Check if we're on mobile or desktop view
            const isMobileView = window.innerWidth < 992;
            const form = document.getElementById('batchDeleteForm');
            const selectedCheckboxes = isMobileView ? 
                document.querySelectorAll('.mobile-select-checkbox:checked') : 
                document.querySelectorAll('.select-checkbox:checked');
            
            // Clear any existing inputs
            const existingInputs = form.querySelectorAll('input[name="selected_reports[]"]');
            existingInputs.forEach(input => input.remove());
            
            // Add selected report IDs to the form
            selectedCheckboxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_reports[]';
                input.value = checkbox.value;
                form.appendChild(input);
            });
            
            // Set form action and submit
            form.action = '{{ route('daily-reports.batch-delete') }}';
            form.submit();
            
            // Hide the modal
            const batchDeleteModal = bootstrap.Modal.getInstance(document.getElementById('batchDeleteModal'));
            batchDeleteModal.hide();
        }
        
        // Function to toggle dropdown visibility
        function toggleDropdown(button) {
            // Find the dropdown menu (next sibling)
            const dropdownMenu = button.nextElementSibling;
            const tableContainer = document.getElementById('all-reports-table-container');
            
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
                // Make sure the table container doesn't clip the dropdown
                tableContainer.style.overflow = 'visible';
                
                dropdownMenu.style.display = 'block';
                
                // Ensure the dropdown is visible within the viewport
                const rect = dropdownMenu.getBoundingClientRect();
                const buttonRect = button.getBoundingClientRect();
                
                // Position dropdown relative to the button
                dropdownMenu.style.top = (buttonRect.height + 5) + 'px';
                
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
        
        // Function to show delete confirmation modal with Bootstrap
        function confirmDelete(reportId, reportName) {
            const deleteModalElement = document.getElementById('deleteConfirmationModal');
            const deleteModal = bootstrap.Modal.getInstance(deleteModalElement) || new bootstrap.Modal(deleteModalElement);
            
            // Update modal content
            document.getElementById('deleteReportName').textContent = reportName;
            
            // Set up confirm button action
            document.getElementById('confirmDeleteButton').onclick = function() {
                const form = document.getElementById('deleteForm');
                form.action = `/daily-reports/${reportId}`;
                form.submit();
                
                // Hide modal
                deleteModal.hide();
            };
            
            // Show the modal
            deleteModal.show();
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