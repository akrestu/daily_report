<x-app-layout>
    <x-slot name="header">
        Pending Reports
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-6 mb-2 mb-md-0">
                    <h5 class="mb-0">Pending Reports</h5>
                    <p class="text-muted small mb-0 mt-1">Manage and monitor pending reports requiring approval.</p>
                </div>
                <div class="col-md-6 text-md-end mt-2 mt-md-0">
                    <a href="{{ route('daily-reports.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list me-1"></i> All Reports
                    </a>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="card-header bg-white pt-0 pb-3 border-0">
            <div class="tabs-container">
                <ul class="nav nav-tabs card-header-tabs mt-2 flex-nowrap">
                    <li class="nav-item mobile-tab-item">
                        <a class="nav-link {{ request()->get('view') != 'monitoring' ? 'active' : '' }} rounded-top d-flex align-items-center justify-content-center" href="{{ route('daily-reports.pending', ['view' => 'approval']) }}">
                            <i class="fas fa-tasks me-2 {{ request()->get('view') != 'monitoring' ? 'text-primary' : 'text-muted' }}"></i> 
                            <span>Job Approval</span>
                        </a>
                    </li>
                    @if(auth()->user()->isDepartmentHead())
                    <li class="nav-item mobile-tab-item">
                        <a class="nav-link {{ request()->get('view') == 'monitoring' ? 'active' : '' }} rounded-top d-flex align-items-center justify-content-center" href="{{ route('daily-reports.pending', ['view' => 'monitoring']) }}">
                            <i class="fas fa-chart-line me-2 {{ request()->get('view') == 'monitoring' ? 'text-primary' : 'text-muted' }}"></i> 
                            <span>Monitoring Approval</span>
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="card-body px-0 px-md-3">
        @if(request()->get('view') == 'monitoring' && auth()->user()->isDepartmentHead())
            <!-- Monitoring View -->
            <div class="d-block d-md-none mobile-card-view px-3">
                <div class="form-check mb-3">
                    <input class="form-check-input select-all-checkbox-mobile" type="checkbox" id="selectAllMobile">
                    <label class="form-check-label" for="selectAllMobile">
                        Select All Reports
                    </label>
                </div>

                @forelse($monitoringReports ?? [] as $report)
                <div class="card mb-3 report-card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title mb-0 fw-bold text-truncate">{{ $report->job_name }}</h6>
                            <span class="badge {{ $report->status == 'pending' ? 'bg-secondary' : ($report->status == 'in_progress' ? 'bg-info' : 'bg-primary') }}">
                                {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                            </span>
                        </div>
                        <div class="mb-2 small">
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="text-muted mb-1"><i class="fas fa-user me-1"></i> PIC:</div>
                                    <div class="fw-medium">{{ $report->pic->name ?? 'N/A' }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted mb-1"><i class="fas fa-calendar me-1"></i> Report Date:</div>
                                    <div class="fw-medium">{{ $report->report_date->format('d M Y') }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted mb-1"><i class="fas fa-hourglass-end me-1"></i> Due Date:</div>
                                    <div class="fw-medium">{{ $report->due_date->format('d M Y') }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted mb-1"><i class="fas fa-pen me-1"></i> Created By:</div>
                                    <div class="fw-medium">{{ $report->user->name ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('daily-reports.show', $report) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye me-1"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-clipboard-check mb-2 d-block" style="font-size: 2rem;"></i>
                    No pending reports found in your department
                </div>
                @endforelse

                @if(isset($monitoringReports))
                <div class="mt-3 d-flex justify-content-center">
                    {{ $monitoringReports->appends(['view' => 'monitoring'])->links() }}
                </div>
                @endif
            </div>

            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Job Name</th>
                            <th>PIC</th>
                            <th class="d-none d-md-table-cell">Report Date</th>
                            <th class="d-none d-md-table-cell">Due Date</th>
                            <th>Status</th>
                            <th class="d-none d-md-table-cell">Created By</th>
                            <th width="100">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monitoringReports ?? [] as $report)
                        <tr>
                            <td>
                                <a href="{{ route('daily-reports.show', $report) }}" class="fw-medium text-decoration-none">
                                    {{ $report->job_name }}
                                </a>
                                <div class="d-md-none">
                                    <small class="text-muted d-block">{{ $report->report_date->format('d M Y') }}</small>
                                    <small class="text-muted d-block">Due: {{ $report->due_date->format('d M Y') }}</small>
                                </div>
                            </td>
                            <td>{{ $report->pic->name ?? 'N/A' }}</td>
                            <td class="d-none d-md-table-cell">{{ $report->report_date->format('d M Y') }}</td>
                            <td class="d-none d-md-table-cell">{{ $report->due_date->format('d M Y') }}</td>
                            <td>
                                @if($report->status == 'pending')
                                    <span class="badge bg-secondary">Pending</span>
                                @elseif($report->status == 'in_progress')
                                    <span class="badge bg-info">In Progress</span>
                                @elseif($report->status == 'completed')
                                    <span class="badge bg-primary">Completed</span>
                                @endif
                            </td>
                            <td class="d-none d-md-table-cell">{{ $report->user->name ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('daily-reports.show', $report) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-clipboard-check mb-2 d-block" style="font-size: 2rem;"></i>
                                No pending reports found in your department
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @if(isset($monitoringReports))
                <div class="mt-3 d-flex justify-content-center justify-content-md-end px-3">
                    {{ $monitoringReports->appends(['view' => 'monitoring'])->links() }}
                </div>
                @endif
            </div>
        @else
            <!-- Regular Approval View -->
            @if(count($reports ?? []) > 0)
            <div class="d-block d-md-none mobile-card-view px-3">
                <div class="form-check mb-3">
                    <input class="form-check-input select-all-checkbox-mobile" type="checkbox" id="selectAllMobile">
                    <label class="form-check-label" for="selectAllMobile">
                        Select All Reports
                    </label>
                </div>
                
                <form id="bulk-action-form-mobile" method="POST" action="">
                    @csrf
                    @foreach($reports as $report)
                    <div class="card mb-3 report-card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="form-check my-0">
                                        <input class="form-check-input select-checkbox-mobile" type="checkbox" name="selected_reports[]" value="{{ $report->id }}">
                                    </div>
                                    <h6 class="card-title mb-0 fw-bold text-truncate">{{ $report->job_name }}</h6>
                                </div>
                                <span class="badge {{ $report->status == 'pending' ? 'bg-secondary' : ($report->status == 'in_progress' ? 'bg-info' : 'bg-primary') }}">
                                    {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                                </span>
                            </div>
                            <div class="mb-3 small">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="text-muted mb-1"><i class="fas fa-building me-1"></i> Department:</div>
                                        <div class="fw-medium">{{ $report->department->name ?? 'N/A' }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted mb-1"><i class="fas fa-calendar me-1"></i> Report Date:</div>
                                        <div class="fw-medium">{{ $report->report_date->format('d M Y') }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted mb-1"><i class="fas fa-hourglass-end me-1"></i> Due Date:</div>
                                        <div class="fw-medium">{{ $report->due_date->format('d M Y') }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted mb-1"><i class="fas fa-pen me-1"></i> Created By:</div>
                                        <div class="fw-medium">{{ $report->user->name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('daily-reports.show', $report) }}" class="btn btn-outline-primary btn-sm action-btn-card">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('approve-reports')
                                <button type="button" class="btn btn-outline-success btn-sm action-btn-card" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#approvalModal"
                                        data-report-id="{{ $report->id }}"
                                        data-report-name="{{ $report->job_name }}">
                                    <i class="fas fa-check"></i>
                                </button>
                                @endcan
                                @if(auth()->user()->id === $report->user_id)
                                <a href="{{ route('daily-reports.edit', $report) }}" class="btn btn-outline-secondary btn-sm action-btn-card">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </form>

                <div class="mt-3 mb-4">
                    <div class="bulk-actions d-flex flex-wrap gap-2 mb-3">
                        <button type="button" class="btn btn-success btn-sm bulk-action-button-mobile" disabled 
                                onclick="submitBulkActionMobile('{{ route('daily-reports.batch-approve') }}', 'POST', 'Are you sure you want to approve the selected reports?')">
                            <i class="fas fa-check-circle me-1"></i> Approve
                        </button>
                        <button type="button" class="btn btn-danger btn-sm bulk-action-button-mobile" disabled
                                onclick="showRejectModalMobile()">
                            <i class="fas fa-times-circle me-1"></i> Reject
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm bulk-action-button-mobile" disabled
                                onclick="submitBulkActionMobile('{{ route('daily-reports.batch-delete') }}', 'DELETE', 'Are you sure you want to delete the selected reports? This action cannot be undone.')">
                            <i class="fas fa-trash me-1"></i> Delete
                        </button>
                    </div>
                    <div class="d-flex justify-content-center">
                        {{ $reports->links() }}
                    </div>
                </div>
            </div>

            <div class="table-responsive mobile-optimized-table d-none d-md-block">
                <form id="bulk-action-form" method="POST" action="">
                    @csrf
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <div class="form-check">
                                        <input class="form-check-input select-all-checkbox" type="checkbox" id="selectAll">
                                    </div>
                                </th>
                                <th>Job Name</th>
                                <th class="d-none d-md-table-cell">Department</th>
                                <th class="d-none d-md-table-cell">Report Date</th>
                                <th class="d-none d-md-table-cell">Due Date</th>
                                <th>Status</th>
                                <th class="d-none d-md-table-cell">Created By</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $report)
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input select-checkbox" type="checkbox" name="selected_reports[]" value="{{ $report->id }}">
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('daily-reports.show', $report) }}" class="fw-medium text-decoration-none">
                                        {{ $report->job_name }}
                                    </a>
                                    <div class="d-md-none small">
                                        <div class="mt-1 text-muted">{{ $report->department->name ?? 'N/A' }}</div>
                                        <div class="text-muted">Date: {{ $report->report_date->format('d M Y') }}</div>
                                        <div class="text-muted">Due: {{ $report->due_date->format('d M Y') }}</div>
                                        <div class="text-muted">By: {{ $report->user->name ?? 'N/A' }}</div>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">{{ $report->department->name ?? 'N/A' }}</td>
                                <td class="d-none d-md-table-cell">{{ $report->report_date->format('d M Y') }}</td>
                                <td class="d-none d-md-table-cell">{{ $report->due_date->format('d M Y') }}</td>
                                <td>
                                    @if($report->status == 'pending')
                                        <span class="badge bg-secondary">Pending</span>
                                    @elseif($report->status == 'in_progress')
                                        <span class="badge bg-info">In Progress</span>
                                    @elseif($report->status == 'completed')
                                        <span class="badge bg-primary">Completed</span>
                                    @endif
                                </td>
                                <td class="d-none d-md-table-cell">{{ $report->user->name ?? 'N/A' }}</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <a href="{{ route('daily-reports.show', $report) }}" class="btn btn-sm btn-outline-primary action-btn" data-bs-toggle="tooltip" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('approve-reports')
                                        <button type="button" class="btn btn-sm btn-outline-success action-btn" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#approvalModal"
                                                data-report-id="{{ $report->id }}"
                                                data-report-name="{{ $report->job_name }}">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        @endcan
                                        @if(auth()->user()->id === $report->user_id)
                                        <a href="{{ route('daily-reports.edit', $report) }}" class="btn btn-sm btn-outline-secondary action-btn" data-bs-toggle="tooltip" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-3 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center px-3">
                        <div class="bulk-actions d-flex flex-wrap gap-2 mb-3 mb-md-0">
                            <button type="button" class="btn btn-success btn-sm bulk-action-button" disabled 
                                    onclick="submitBulkAction('{{ route('daily-reports.batch-approve') }}', 'POST', 'Are you sure you want to approve the selected reports?')">
                                <i class="fas fa-check-circle me-1"></i> Approve
                            </button>
                            <button type="button" class="btn btn-danger btn-sm bulk-action-button" disabled
                                    onclick="showRejectModal()">
                                <i class="fas fa-times-circle me-1"></i> Reject
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm bulk-action-button" disabled
                                    onclick="submitBulkAction('{{ route('daily-reports.batch-delete') }}', 'DELETE', 'Are you sure you want to delete the selected reports? This action cannot be undone.')">
                                <i class="fas fa-trash me-1"></i> Delete
                            </button>
                        </div>
                        <div class="pagination-container">
                            {{ $reports->links() }}
                        </div>
                    </div>
                </form>
            </div>
            @else
            <div class="alert alert-info mx-3">
                <i class="fas fa-info-circle me-2"></i> No pending reports requiring your approval as PIC were found.
            </div>
            @endif
        @endif
        </div>
    </div>

    <!-- Approval Modal -->
    <div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
                <form id="approvalForm" action="" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="approvalModalLabel">Approve Report</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to approve <strong id="reportName"></strong>?</p>
                        <div class="mb-3">
                            <label for="approval_action" class="form-label">Action</label>
                            <select class="form-select form-select-lg" id="approval_action" name="action" required>
                                <option value="approve">Approve Report</option>
                                <option value="reject">Reject Report</option>
                            </select>
                        </div>
                        
                        <div class="mb-3" id="rejectionReasonContainer" style="display: none;">
                            <label for="rejection_reason" class="form-label">Rejection Reason</label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer flex-wrap gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Decision</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Batch Reject Modal -->
    <div class="modal fade" id="batchRejectModal" tabindex="-1" aria-labelledby="batchRejectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">
                <form id="batchRejectForm" action="{{ route('daily-reports.batch-reject') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="batchRejectModalLabel">Reject Selected Reports</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to reject the selected reports?</p>
                        <div class="mb-3">
                            <label for="batch_rejection_reason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="batch_rejection_reason" name="rejection_reason" rows="3" required></textarea>
                            <div class="form-text">This reason will be applied to all selected reports.</div>
                        </div>
                        <div id="selectedReportsContainer">
                            <!-- Selected reports will be added here dynamically -->
                        </div>
                    </div>
                    <div class="modal-footer flex-wrap gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Reports</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Include modal dialogs component -->
    <x-modal-dialogs />

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Select all functionality for desktop
            const selectAllCheckbox = document.getElementById('selectAll');
            const selectCheckboxes = document.querySelectorAll('.select-checkbox');
            const bulkActionButtons = document.querySelectorAll('.bulk-action-button');
            
            // Select all functionality for mobile
            const selectAllCheckboxMobile = document.getElementById('selectAllMobile');
            const selectCheckboxesMobile = document.querySelectorAll('.select-checkbox-mobile');
            const bulkActionButtonsMobile = document.querySelectorAll('.bulk-action-button-mobile');
            
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    const isChecked = this.checked;
                    
                    selectCheckboxes.forEach(checkbox => {
                        checkbox.checked = isChecked;
                    });
                    
                    updateBulkActionButtons();
                });
            }
            
            if (selectAllCheckboxMobile) {
                selectAllCheckboxMobile.addEventListener('change', function() {
                    const isChecked = this.checked;
                    
                    selectCheckboxesMobile.forEach(checkbox => {
                        checkbox.checked = isChecked;
                    });
                    
                    updateBulkActionButtonsMobile();
                });
            }
            
            // Individual checkbox change handler for desktop
            selectCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateBulkActionButtons();
                    
                    // Update "select all" checkbox state
                    if (selectAllCheckbox) {
                        const allChecked = Array.from(selectCheckboxes).every(cb => cb.checked);
                        const someChecked = Array.from(selectCheckboxes).some(cb => cb.checked);
                        
                        selectAllCheckbox.checked = allChecked;
                        selectAllCheckbox.indeterminate = someChecked && !allChecked;
                    }
                });
            });
            
            // Individual checkbox change handler for mobile
            selectCheckboxesMobile.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateBulkActionButtonsMobile();
                    
                    // Update "select all" checkbox state
                    if (selectAllCheckboxMobile) {
                        const allChecked = Array.from(selectCheckboxesMobile).every(cb => cb.checked);
                        const someChecked = Array.from(selectCheckboxesMobile).some(cb => cb.checked);
                        
                        selectAllCheckboxMobile.checked = allChecked;
                        selectAllCheckboxMobile.indeterminate = someChecked && !allChecked;
                    }
                });
            });
            
            // Enable/disable bulk action buttons based on selection for desktop
            function updateBulkActionButtons() {
                const hasSelection = Array.from(selectCheckboxes).some(checkbox => checkbox.checked);
                
                bulkActionButtons.forEach(button => {
                    button.disabled = !hasSelection;
                });
            }
            
            // Enable/disable bulk action buttons based on selection for mobile
            function updateBulkActionButtonsMobile() {
                const hasSelection = Array.from(selectCheckboxesMobile).some(checkbox => checkbox.checked);
                
                bulkActionButtonsMobile.forEach(button => {
                    button.disabled = !hasSelection;
                });
            }
            
            // Approval modal setup
            const approvalModal = document.getElementById('approvalModal');
            if (approvalModal) {
                approvalModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const reportId = button.getAttribute('data-report-id');
                    const reportName = button.getAttribute('data-report-name');
                    
                    const modalForm = document.getElementById('approvalForm');
                    const modalReportName = document.getElementById('reportName');
                    
                    modalForm.action = "{{ url('daily-reports') }}/" + reportId + "/approval";
                    modalReportName.textContent = reportName;
                });
            }
            
            // Approval action change handler
            const approvalActionSelect = document.getElementById('approval_action');
            const rejectionReasonContainer = document.getElementById('rejectionReasonContainer');
            const rejectionReasonTextarea = document.getElementById('rejection_reason');
            
            if (approvalActionSelect) {
                approvalActionSelect.addEventListener('change', function() {
                    if (this.value === 'reject') {
                        rejectionReasonContainer.style.display = 'block';
                        rejectionReasonTextarea.setAttribute('required', 'required');
                    } else {
                        rejectionReasonContainer.style.display = 'none';
                        rejectionReasonTextarea.removeAttribute('required');
                    }
                });
            }
        });
        
        function submitBulkAction(url, method, confirmMessage) {
            window.showConfirmation(confirmMessage, function() {
                const form = document.getElementById('bulk-action-form');
                form.action = url;
                
                if (method !== 'POST') {
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = method;
                    form.appendChild(methodInput);
                }
                
                form.submit();
            }, method === 'DELETE' ? 'Delete' : 'Approve', method === 'DELETE' ? 'btn-danger' : 'btn-success');
        }
        
        function submitBulkActionMobile(url, method, confirmMessage) {
            window.showConfirmation(confirmMessage, function() {
                const form = document.getElementById('bulk-action-form-mobile');
                form.action = url;
                
                if (method !== 'POST') {
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = method;
                    form.appendChild(methodInput);
                }
                
                form.submit();
            }, method === 'DELETE' ? 'Delete' : 'Approve', method === 'DELETE' ? 'btn-danger' : 'btn-success');
        }
        
        function showRejectModal() {
            // Get selected report IDs and names
            const checkboxes = document.querySelectorAll('.select-checkbox:checked');
            const selectedReports = Array.from(checkboxes).map(checkbox => {
                const row = checkbox.closest('tr');
                const reportId = checkbox.value;
                const reportName = row.querySelector('a.fw-medium').textContent.trim();
                return { id: reportId, name: reportName };
            });
            
            if (selectedReports.length === 0) {
                window.showAlert('Please select at least one report to reject.');
                return;
            }
            
            // Transfer selected report IDs to the batch reject form
            const selectedReportsContainer = document.getElementById('selectedReportsContainer');
            selectedReportsContainer.innerHTML = '';
            
            if (selectedReports.length <= 10) {
                // Show selected report names if 10 or fewer
                const reportsList = document.createElement('ul');
                reportsList.className = 'mt-3';
                
                selectedReports.forEach(report => {
                    const listItem = document.createElement('li');
                    listItem.textContent = report.name;
                    
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'selected_reports[]';
                    hiddenInput.value = report.id;
                    
                    listItem.appendChild(hiddenInput);
                    reportsList.appendChild(listItem);
                });
                
                selectedReportsContainer.appendChild(reportsList);
            } else {
                // Just show count if more than 10
                const messageDiv = document.createElement('div');
                messageDiv.className = 'alert alert-info mt-3';
                messageDiv.textContent = `${selectedReports.length} reports selected for rejection`;
                
                selectedReports.forEach(report => {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'selected_reports[]';
                    hiddenInput.value = report.id;
                    messageDiv.appendChild(hiddenInput);
                });
                
                selectedReportsContainer.appendChild(messageDiv);
            }
            
            // Show the modal
            const batchRejectModal = new bootstrap.Modal(document.getElementById('batchRejectModal'));
            batchRejectModal.show();
        }
        
        function showRejectModalMobile() {
            // Get selected report IDs and names
            const checkboxes = document.querySelectorAll('.select-checkbox-mobile:checked');
            const selectedReports = Array.from(checkboxes).map(checkbox => {
                const card = checkbox.closest('.report-card');
                const reportId = checkbox.value;
                const reportName = card.querySelector('.card-title').textContent.trim();
                return { id: reportId, name: reportName };
            });
            
            if (selectedReports.length === 0) {
                window.showAlert('Please select at least one report to reject.');
                return;
            }
            
            // Transfer selected report IDs to the batch reject form
            const selectedReportsContainer = document.getElementById('selectedReportsContainer');
            selectedReportsContainer.innerHTML = '';
            
            if (selectedReports.length <= 10) {
                // Show selected report names if 10 or fewer
                const reportsList = document.createElement('ul');
                reportsList.className = 'mt-3';
                
                selectedReports.forEach(report => {
                    const listItem = document.createElement('li');
                    listItem.textContent = report.name;
                    
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'selected_reports[]';
                    hiddenInput.value = report.id;
                    
                    listItem.appendChild(hiddenInput);
                    reportsList.appendChild(listItem);
                });
                
                selectedReportsContainer.appendChild(reportsList);
            } else {
                // Just show count if more than 10
                const messageDiv = document.createElement('div');
                messageDiv.className = 'alert alert-info mt-3';
                messageDiv.textContent = `${selectedReports.length} reports selected for rejection`;
                
                selectedReports.forEach(report => {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'selected_reports[]';
                    hiddenInput.value = report.id;
                    messageDiv.appendChild(hiddenInput);
                });
                
                selectedReportsContainer.appendChild(messageDiv);
            }
            
            // Show the modal
            const batchRejectModal = new bootstrap.Modal(document.getElementById('batchRejectModal'));
            batchRejectModal.show();
        }
    </script>
    @endpush

    @push('styles')
    <style>
        /* Mobile optimizations */
        @media (max-width: 767.98px) {
            .card {
                border-radius: 0.5rem;
                margin-bottom: 1rem;
            }
            
            .table-responsive {
                margin: 0;
                padding: 0;
                border-radius: 0.5rem;
            }
            
            .mobile-optimized-table td {
                padding: 0.75rem 0.5rem;
            }
            
            .action-btn {
                width: 38px;
                height: 38px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0;
            }
            
            .bulk-actions {
                width: 100%;
            }
            
            .bulk-actions .btn {
                flex: 1;
                min-height: 40px;
            }
            
            .pagination-container {
                width: 100%;
                display: flex;
                justify-content: center;
            }
            
            .hide-scrollbar {
                scrollbar-width: none;
                -ms-overflow-style: none;
            }
            
            .hide-scrollbar::-webkit-scrollbar {
                display: none;
                width: 0;
                height: 0;
            }
            
            /* Larger form controls for better touch targets */
            .form-select-lg {
                height: 50px;
                font-size: 16px; /* Prevent zoom on iOS */
            }
            
            /* Increase checkbox size for better touch targets */
            .form-check-input[type="checkbox"] {
                width: 1.25rem;
                height: 1.25rem;
            }
            
            /* Card styling for reports */
            .report-card {
                transition: transform 0.15s ease-in-out;
                border: 1px solid rgba(0,0,0,0.1);
            }
            
            .report-card:active {
                transform: scale(0.98);
            }
            
            .report-card .form-check {
                margin-right: 6px;
            }
            
            .report-card .form-check-input {
                cursor: pointer;
                width: 1.25rem;
                height: 1.25rem;
                margin-top: 0;
            }
            
            .action-btn-card {
                width: 38px;
                height: 38px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0;
                border-radius: 6px;
            }
            
            /* Add more contrast to badges */
            .badge {
                padding: 0.4rem 0.6rem;
                font-weight: 500;
            }
            
            /* Tab styling for mobile */
            .mobile-tab-item {
                flex: 1;
                min-width: 150px;
                text-align: center;
            }
            
            .mobile-tab-item .nav-link {
                padding: 0.75rem 0.5rem;
                font-weight: 500;
                white-space: nowrap;
                border-bottom-width: 2px;
            }
            
            .mobile-tab-item .nav-link.active {
                font-weight: 600;
                box-shadow: 0 0 8px rgba(0,0,0,0.05);
            }
            
            .nav-tabs .nav-link {
                background-color: #f8f9fa;
                margin-right: 2px;
            }
            
            .nav-tabs .nav-link.active {
                background-color: #ffffff;
                border-bottom-color: var(--bs-primary);
            }
            
            .hide-scrollbar {
                -webkit-overflow-scrolling: touch;
            }
            
            /* Clean tab navigation without scrollbars */
            .tabs-container {
                overflow-x: auto;
                overflow-y: hidden;
                margin-bottom: -5px; /* Compensate for any space */
                -ms-overflow-style: none; /* IE and Edge */
                scrollbar-width: none; /* Firefox */
            }
            
            .tabs-container::-webkit-scrollbar {
                display: none; /* Chrome, Safari, Opera */
                height: 0;
                width: 0;
            }
            
            .nav-tabs {
                flex-wrap: nowrap;
                white-space: nowrap;
                border-bottom: 1px solid #dee2e6;
                width: max-content;
                min-width: 100%;
            }
        }
    </style>
    @endpush
</x-app-layout>