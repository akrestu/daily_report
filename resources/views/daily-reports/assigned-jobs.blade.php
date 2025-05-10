<x-app-layout>
    <x-slot name="header">
        Assigned Reports
    </x-slot>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-6 mb-2 mb-md-0">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-user-check text-primary me-2"></i>Assigned Report List
                    </h5>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between justify-content-md-end gap-2">
                        <a href="{{ route('daily-reports.export') }}?{{ http_build_query(request()->all()) }}" class="btn btn-outline-primary rounded-pill d-flex align-items-center justify-content-center flex-grow-1 flex-md-grow-0" style="min-width: 110px;">
                            <i class="fas fa-file-export"></i> 
                            <span class="d-none d-sm-inline-block ms-1">Export</span>
                        </a>
                        <a href="{{ route('daily-reports.index') }}" class="btn btn-outline-secondary rounded-pill d-flex align-items-center justify-content-center flex-grow-1 flex-md-grow-0" style="min-width: 110px;">
                            <i class="fas fa-list"></i> 
                            <span class="d-none d-sm-inline-block ms-1">All Reports</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-3 p-lg-4">
            <!-- Filter Controls -->
            <div class="card mb-4 border-0 bg-light rounded-3">
                <div class="card-body py-3">
                    <form action="{{ route('daily-reports.assigned-jobs') }}" method="GET">
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
                                    <a href="{{ route('daily-reports.assigned-jobs') }}" class="btn btn-light rounded-pill flex-grow-1 border">
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
                                <div class="dropdown position-relative">
                                    <button class="btn btn-sm btn-light rounded-pill px-3 border" type="button" onclick="toggleDropdown(this)" style="cursor: pointer;">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="display: none; min-width: 10rem; right: 0; max-width: 12rem;">
                                        <li>
                                            <a href="{{ route('daily-reports.show', $report) }}" class="dropdown-item">
                                                <i class="fas fa-eye text-primary me-2"></i> View Details
                                            </a>
                                        </li>
                                        @if($report->status === 'pending' && auth()->user()->can('update-report-status'))
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
                <!-- Add extra padding space for the dropdown menu -->
                <div style="height: 150px;"></div>
            </div>

            <!-- Mobile Card View -->
            <div class="d-lg-none">
                @foreach($reports as $report)
                <div class="card shadow-sm border-0 mb-3 rounded-3">
                    <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-truncate" style="max-width: 200px;">
                            {{ $report->job_name }}
                            @if($report->comments->count() > 0)
                            <span class="badge bg-info rounded-pill ms-1" data-bs-toggle="tooltip" title="{{ $report->comments->count() }} Comments">
                                <i class="fas fa-comments"></i> {{ $report->comments->count() }}
                            </span>
                            @endif
                        </h6>
                        <div class="dropdown position-relative">
                            <button class="btn btn-sm btn-light rounded-pill px-2 border" type="button" onclick="toggleDropdown(this)" style="cursor: pointer;">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="display: none; min-width: 10rem; right: 0; max-width: 12rem; z-index: 1021;">
                                <li>
                                    <a href="{{ route('daily-reports.show', $report) }}" class="dropdown-item">
                                        <i class="fas fa-eye text-primary me-2"></i> View Details
                                    </a>
                                </li>
                                @if($report->status === 'pending' && auth()->user()->can('update-report-status'))
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
                    <div class="card-body p-3">
                        <div class="mb-2">
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-layer-group text-muted me-2"></i>
                                        <span class="small text-muted">Department:</span>
                                    </div>
                                    <div class="ms-4">{{ $report->department->name ?? 'N/A' }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-tasks text-muted me-2"></i>
                                        <span class="small text-muted">Status:</span>
                                    </div>
                                    <div class="ms-4">
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
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <i class="far fa-calendar-alt text-muted me-2"></i>
                                        <span class="small text-muted">Report Date:</span>
                                    </div>
                                    <div class="ms-4">{{ $report->report_date->format('d M Y') }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <i class="far fa-calendar-check text-muted me-2"></i>
                                        <span class="small text-muted">Due Date:</span>
                                    </div>
                                    <div class="ms-4">{{ $report->due_date->format('d M Y') }}</div>
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user text-muted me-2"></i>
                                        <span class="small text-muted">Created By:</span>
                                    </div>
                                    <div class="ms-4">{{ $report->user->name ?? 'N/A' }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check-double text-muted me-2"></i>
                                        <span class="small text-muted">Approval:</span>
                                    </div>
                                    <div class="ms-4">
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
                                </div>
                            </div>
                        </div>
                        
                        @if($report->approved_by)
                        <div class="small text-muted border-top pt-2 mt-2">
                            <i class="fas fa-user me-1"></i> Approved by: {{ $report->approver->name ?? 'N/A' }}
                        </div>
                        @endif
                        
                        @if($report->approval_status === 'rejected' && $report->rejection_reason)
                        <div class="small text-danger border-top pt-2 mt-2">
                            <i class="fas fa-info-circle me-1"></i> Reason: {{ $report->rejection_reason }}
                        </div>
                        @endif
                    </div>
                    <div class="card-footer bg-white border-top-0 pb-2 pt-0 d-flex justify-content-end">
                        <a href="{{ route('daily-reports.show', $report) }}" class="btn btn-sm btn-primary rounded-pill">
                            <i class="fas fa-eye me-1"></i> View Details
                        </a>
                    </div>
                </div>
                @endforeach
                
                <!-- Add extra padding space for mobile view dropdown -->
                <div style="height: 150px;"></div>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $reports->links() }}
            </div>
            @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> No reports found. Adjust your filters or create a new report.
            </div>
            @endif
        </div>
    </div>
    
    <!-- Update Status Modal -->
    @if(auth()->user()->can('update-report-status'))
    <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateStatusModalLabel">Update Job Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="updateStatusForm" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <p>Update the status for: <strong id="report-name-display"></strong></p>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    
    @push('scripts')
    <script>
        function toggleDropdown(button) {
            // Close all other open dropdowns
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                if (menu !== button.nextElementSibling) {
                    menu.style.display = 'none';
                }
            });
            
            // Toggle this dropdown
            const dropdown = button.nextElementSibling;
            dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
            
            // Ensure the dropdown stays within the viewport
            if (dropdown.style.display === 'block') {
                const rect = dropdown.getBoundingClientRect();
                const viewportWidth = window.innerWidth;
                
                // If the dropdown extends beyond the right edge of the viewport
                if (rect.right > viewportWidth) {
                    dropdown.style.right = '0';
                    dropdown.style.left = 'auto';
                }
            }
        }
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.style.display = 'none';
                });
            }
        });
        
        // For status update modal
        const updateStatusModal = document.getElementById('updateStatusModal');
        if (updateStatusModal) {
            updateStatusModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const reportId = button.getAttribute('data-report-id');
                const reportName = button.getAttribute('data-report-name');
                
                document.getElementById('report-name-display').textContent = reportName;
                document.getElementById('updateStatusForm').action = `/daily-reports/${reportId}`;
            });
        }
    </script>
    @endpush
    
    @push('styles')
    <style>
        /* Ensure dropdown menus don't overflow their container */
        .table-responsive {
            overflow-x: auto;
            position: relative;
        }
        
        .dropdown-menu {
            position: absolute;
            z-index: 1021;
        }
        
        .dropdown.position-relative {
            position: relative !important;
        }
        
        @media (max-width: 991.98px) {
            .dropdown-menu {
                position: absolute !important;
                right: 0 !important;
                left: auto !important;
            }
        }
    </style>
    @endpush
</x-app-layout> 