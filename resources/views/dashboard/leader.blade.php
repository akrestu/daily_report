<!-- Leader Dashboard -->

<!-- Personal and Team Status -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white p-3 border-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-user-circle text-primary me-2"></i>
                    <h5 class="mb-0">My Reports</h5>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="row g-0 border-bottom p-3">
                    <div class="col-6 col-md-3 text-center border-end">
                        <h6 class="text-muted mb-2">Pending</h6>
                        <h3 class="fw-bold mb-0 text-warning">{{ $myPendingReports }}</h3>
                    </div>
                    <div class="col-6 col-md-3 text-center border-end">
                        <h6 class="text-muted mb-2">In Progress</h6>
                        <h3 class="fw-bold mb-0 text-info">{{ $myInProgressReports }}</h3>
                    </div>
                    <div class="col-6 col-md-3 text-center border-end">
                        <h6 class="text-muted mb-2">Completed</h6>
                        <h3 class="fw-bold mb-0 text-success">{{ $myCompletedReports }}</h3>
                    </div>
                    <div class="col-6 col-md-3 text-center">
                        <h6 class="text-muted mb-2">Rejected</h6>
                        <h3 class="fw-bold mb-0 text-danger">{{ $myRejectedReports }}</h3>
                    </div>
                </div>
                <div class="p-3 d-grid">
                    <a href="{{ route('daily-reports.user-jobs') }}" class="btn btn-outline-primary">
                        <i class="fas fa-list me-2"></i>View All My Reports
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white p-3 border-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-users text-primary me-2"></i>
                    <h5 class="mb-0">Team Reports</h5>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="row g-0 border-bottom p-3">
                    <div class="col-6 col-md-3 text-center border-end">
                        <h6 class="text-muted mb-2">Pending</h6>
                        <h3 class="fw-bold mb-0 text-warning">{{ $pendingReports }}</h3>
                    </div>
                    <div class="col-6 col-md-3 text-center border-end">
                        <h6 class="text-muted mb-2">In Progress</h6>
                        <h3 class="fw-bold mb-0 text-info">{{ $inProgressReports }}</h3>
                    </div>
                    <div class="col-6 col-md-3 text-center border-end">
                        <h6 class="text-muted mb-2">Completed</h6>
                        <h3 class="fw-bold mb-0 text-success">{{ $completedReports }}</h3>
                    </div>
                    <div class="col-6 col-md-3 text-center">
                        <h6 class="text-muted mb-2">Rejected</h6>
                        <h3 class="fw-bold mb-0 text-danger">{{ $rejectedReports }}</h3>
                    </div>
                </div>
                <div class="p-3 d-grid">
                    <a href="{{ route('daily-reports.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-list me-2"></i>View All Team Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reports and Approvals -->
<div class="row g-4 mb-4">
    <!-- Report Trend Chart -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white p-3 border-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-chart-line text-primary me-2"></i>
                    <h5 class="mb-0">Reports Trend</h5>
                </div>
            </div>
            <div class="card-body p-3">
                <div class="chart-container" style="height: 300px; position: relative;">
                    <canvas id="reportTrendChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports Needing Approval -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white p-3 border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-clipboard-check text-primary me-2"></i>
                        <h5 class="mb-0">Awaiting Your Approval</h5>
                    </div>
                    <span class="badge bg-warning rounded-pill">{{ count($needsApproval) }}</span>
                </div>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($needsApproval as $report)
                        <li class="list-group-item px-4 py-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <a href="{{ route('daily-reports.show', $report) }}" class="text-decoration-none fw-medium text-dark">
                                    {{ $report->job_name }}
                                </a>
                                <small class="text-muted">{{ $report->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="d-flex align-items-center text-muted small">
                                <span class="me-3">
                                    <i class="fas fa-user me-1"></i>
                                    {{ $report->pic->name ?? 'Unassigned' }}
                                </span>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-center py-4 text-muted">
                            <i class="fas fa-check-circle mb-2 d-block" style="font-size: 2rem;"></i>
                            No reports awaiting your approval
                        </li>
                    @endforelse
                </ul>
                <div class="p-3">
                    <a href="{{ route('daily-reports.pending') }}" class="btn btn-outline-primary w-100">
                        <i class="fas fa-eye me-2"></i>View All Pending Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Urgent Tasks and Recent Reports -->
<div class="row g-4 mb-4">
    <!-- Urgent Tasks -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white p-3 border-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    <h5 class="mb-0">Deadline Reminders</h5>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Report</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($urgentReports as $report)
                                <tr>
                                    <td>
                                        <a href="{{ route('daily-reports.show', $report) }}" class="text-decoration-none fw-medium text-dark">
                                            {{ $report->job_name }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge {{ Carbon\Carbon::parse($report->due_date)->isPast() ? 'bg-danger' : 'bg-warning' }} text-white">
                                            {{ Carbon\Carbon::parse($report->due_date)->format('d M Y') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($report->status === 'pending')
                                            <span class="badge bg-warning bg-opacity-10 text-warning">Pending</span>
                                        @elseif($report->status === 'in_progress')
                                            <span class="badge bg-info bg-opacity-10 text-info">In Progress</span>
                                        @elseif($report->status === 'rejected')
                                            <span class="badge bg-danger bg-opacity-10 text-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-success bg-opacity-10 text-success">Completed</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        <i class="fas fa-check-circle mb-2 d-block" style="font-size: 2rem;"></i>
                                        No urgent tasks
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Reports -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white p-3 border-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-history text-primary me-2"></i>
                    <h5 class="mb-0">My Recent Reports</h5>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Report</th>
                                <th>Created</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($myRecentReports as $report)
                                <tr>
                                    <td>
                                        <a href="{{ route('daily-reports.show', $report) }}" class="text-decoration-none fw-medium text-dark">
                                            {{ $report->job_name }}
                                        </a>
                                        @if($report->comments->count() > 0)
                                        <span class="badge bg-info ms-1" data-bs-toggle="tooltip" title="{{ $report->comments->count() }} Comments">
                                            <i class="fas fa-comments"></i> {{ $report->comments->count() }}
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $report->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        @if($report->status === 'pending')
                                            <span class="badge bg-warning bg-opacity-10 text-warning">Pending</span>
                                        @elseif($report->status === 'in_progress')
                                            <span class="badge bg-info bg-opacity-10 text-info">In Progress</span>
                                        @elseif($report->status === 'rejected')
                                            <span class="badge bg-danger bg-opacity-10 text-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-success bg-opacity-10 text-success">Completed</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        <i class="fas fa-file-alt mb-2 d-block" style="font-size: 2rem;"></i>
                                        No recent reports
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white p-3 border-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-bolt text-primary me-2"></i>
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="{{ route('daily-reports.create') }}" class="btn btn-primary w-100 h-100 py-3">
                            <i class="fas fa-plus-circle me-2"></i>Create New Report
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('daily-reports.pending') }}" class="btn btn-outline-primary w-100 h-100 py-3">
                            <i class="fas fa-clipboard-check me-2"></i>Review Pending Reports
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('daily-reports.user-jobs') }}" class="btn btn-outline-primary w-100 h-100 py-3">
                            <i class="fas fa-user me-2"></i>My Reports
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('daily-reports.index') }}" class="btn btn-outline-primary w-100 h-100 py-3">
                            <i class="fas fa-list me-2"></i>All Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leader JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Leader Dashboard scripts loaded');
        
        // Wait a moment to ensure Chart.js is fully loaded
        setTimeout(function() {
            initializeCharts();
        }, 500);
        
        function initializeCharts() {
            // Check if Chart.js is available
            if (typeof Chart === 'undefined') {
                console.error('Chart.js is not available. Charts will not be rendered.');
                return;
            }
            
            console.log('Chart.js is available');
            
            // Report Trend Chart
            const trendCanvas = document.getElementById('reportTrendChart');
            if (!trendCanvas) {
                console.error('reportTrendChart canvas element not found');
            } else {
                console.log('reportTrendChart canvas found');
                const trendData = @json($reportTrend);
                console.log('Trend data:', trendData);
                
                try {
                    // Check if there's an existing chart
                    window.chartInstances = window.chartInstances || {};
                    if (window.chartInstances['reportTrendChart']) {
                        window.chartInstances['reportTrendChart'].destroy();
                    }
                    
                    // Create new chart
                    const trendChart = new Chart(trendCanvas, {
                        type: 'line',
                        data: {
                            labels: trendData.map(item => item.date),
                            datasets: [{
                                label: 'Daily Reports',
                                data: trendData.map(item => item.count),
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                borderColor: 'rgba(59, 130, 246, 1)',
                                borderWidth: 2,
                                tension: 0.3,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            }
                        }
                    });

                    // Store chart in the global instances object
                    window.chartInstances['reportTrendChart'] = trendChart;
                    console.log('Trend chart initialized successfully');
                } catch (error) {
                    console.error('Error initializing trend chart:', error);
                }
            }
        }
    });
</script> 