<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="fs-4 text-dark mb-0">
                <i class="fas fa-broom me-2"></i>Report Cleanup Management
            </h2>
        </div>
    </x-slot>

    <div class="container-fluid">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">{{ number_format($stats['total_reports']) }}</h4>
                                <p class="card-text">Total Reports</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-file-alt fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-warning text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">{{ number_format($stats['older_than_365_days']) }}</h4>
                                <p class="card-text">Older than 1 Year</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-calendar-times fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-info text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">{{ number_format($stats['with_attachments']) }}</h4>
                                <p class="card-text">With Attachments</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-paperclip fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">{{ $stats['storage_size'] }}</h4>
                                <p class="card-text">Storage Used</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-hdd fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cleanup Tool -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-tools me-2"></i>Cleanup Configuration</h5>
                    </div>
                    <div class="card-body">
                        <form id="cleanupForm">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="days" class="form-label">
                                            <i class="fas fa-calendar-alt me-1"></i>Keep Reports Newer Than
                                        </label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="days" name="days" 
                                                   value="365" min="1" max="3650" required>
                                            <span class="input-group-text">days</span>
                                        </div>
                                        <div class="form-text">Reports older than this will be deleted</div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-filter me-1"></i>Filter by Status
                                        </label>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="status_completed" 
                                                           name="statuses[]" value="completed">
                                                    <label class="form-check-label" for="status_completed">
                                                        Completed
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="status_approved" 
                                                           name="statuses[]" value="approved">
                                                    <label class="form-check-label" for="status_approved">
                                                        Approved
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="status_rejected" 
                                                           name="statuses[]" value="rejected">
                                                    <label class="form-check-label" for="status_rejected">
                                                        Rejected
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="keep_attachments" 
                                                           name="keep_attachments" value="1">
                                                    <label class="form-check-label" for="keep_attachments">
                                                        Keep Attachment Files
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-text">Only delete reports with selected statuses (leave empty for all)</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-info" id="previewBtn">
                                    <i class="fas fa-eye me-1"></i>Preview Cleanup
                                </button>
                                <button type="button" class="btn btn-danger" id="executeBtn" disabled>
                                    <i class="fas fa-trash me-1"></i>Execute Cleanup
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Current Statistics</h5>
                    </div>
                    <div class="card-body">
                        <h6>By Age:</h6>
                        <ul class="list-unstyled">
                            <li class="mb-1">
                                <span class="badge bg-light text-dark me-2">30d+</span>
                                {{ number_format($stats['older_than_30_days']) }} reports
                            </li>
                            <li class="mb-1">
                                <span class="badge bg-light text-dark me-2">90d+</span>
                                {{ number_format($stats['older_than_90_days']) }} reports
                            </li>
                            <li class="mb-1">
                                <span class="badge bg-light text-dark me-2">1y+</span>
                                {{ number_format($stats['older_than_365_days']) }} reports
                            </li>
                            <li class="mb-1">
                                <span class="badge bg-light text-dark me-2">2y+</span>
                                {{ number_format($stats['older_than_730_days']) }} reports
                            </li>
                        </ul>
                        
                        <hr>
                        
                        <h6>By Status:</h6>
                        <ul class="list-unstyled">
                            @foreach($stats['by_status'] as $status => $count)
                            <li class="mb-1">
                                <span class="badge bg-secondary me-2">{{ ucfirst($status ?: 'null') }}</span>
                                {{ number_format($count) }}
                            </li>
                            @endforeach
                        </ul>
                        
                        <hr>
                        
                        <h6>By Approval:</h6>
                        <ul class="list-unstyled">
                            @foreach($stats['by_approval_status'] as $status => $count)
                            <li class="mb-1">
                                <span class="badge bg-secondary me-2">{{ ucfirst($status ?: 'null') }}</span>
                                {{ number_format($count) }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Preview Results -->
        <div class="row mt-4" id="previewResults" style="display: none;">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-preview me-2"></i>Cleanup Preview</h5>
                    </div>
                    <div class="card-body" id="previewContent">
                        <!-- Preview content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const previewBtn = document.getElementById('previewBtn');
            const executeBtn = document.getElementById('executeBtn');
            
            previewBtn.addEventListener('click', previewCleanup);
            executeBtn.addEventListener('click', executeCleanup);
        });
        
        function previewCleanup() {
            const formData = new FormData(document.getElementById('cleanupForm'));
            const previewBtn = document.getElementById('previewBtn');
            
            previewBtn.disabled = true;
            previewBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Loading...';
            
            fetch('{{ route("admin.reports.cleanup.preview") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showPreviewResults(data.breakdown, data.cutoff_date);
                    document.getElementById('executeBtn').disabled = false;
                } else {
                    showError('Failed to preview cleanup');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('An error occurred during preview');
            })
            .finally(() => {
                previewBtn.disabled = false;
                previewBtn.innerHTML = '<i class="fas fa-eye me-1"></i>Preview Cleanup';
            });
        }
        
        function showPreviewResults(breakdown, cutoffDate) {
            const previewContent = document.getElementById('previewContent');
            
            let html = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Preview Results</strong> - Reports created before ${cutoffDate}
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6>Summary:</h6>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Total Reports to Delete:</span>
                                <strong class="text-danger">${breakdown.total.toLocaleString()}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Reports with Attachments:</span>
                                <strong>${breakdown.with_attachments.toLocaleString()}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Storage to Free:</span>
                                <strong>${breakdown.total_size}</strong>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>Breakdown by Status:</h6>
                        <ul class="list-group list-group-flush">`;
                        
            Object.entries(breakdown.by_status).forEach(([status, count]) => {
                html += `
                    <li class="list-group-item d-flex justify-content-between">
                        <span>${status || 'null'}:</span>
                        <span>${count.toLocaleString()}</span>
                    </li>`;
            });
            
            html += `
                        </ul>
                    </div>
                </div>`;
                
            if (breakdown.total > 0) {
                html += `
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This action cannot be undone. Please review the results carefully before executing.
                    </div>`;
            }
            
            previewContent.innerHTML = html;
            document.getElementById('previewResults').style.display = 'block';
        }
        
        function executeCleanup() {
            if (!confirm('Are you sure you want to execute this cleanup? This action cannot be undone!')) {
                return;
            }
            
            const formData = new FormData(document.getElementById('cleanupForm'));
            const executeBtn = document.getElementById('executeBtn');
            
            executeBtn.disabled = true;
            executeBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Executing...';
            
            fetch('{{ route("admin.reports.cleanup.execute") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Cleanup Completed!',
                        text: data.message,
                        confirmButtonText: 'Reload Page'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    showError(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('An error occurred during cleanup execution');
            })
            .finally(() => {
                executeBtn.disabled = false;
                executeBtn.innerHTML = '<i class="fas fa-trash me-1"></i>Execute Cleanup';
            });
        }
        
        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message
            });
        }
    </script>
    @endpush
</x-app-layout> 