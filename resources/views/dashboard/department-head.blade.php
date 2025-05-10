<!-- Department Head Dashboard -->

<!-- Department Statistics Summary -->
<div class="row g-4 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                        <i class="fas fa-clock text-warning"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-muted">Pending Approval</h6>
                        <h3 class="mb-0">{{ $pendingReports }}</h3>
                    </div>
                </div>
                <div class="progress mt-3" style="height: 6px;">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ ($pendingReports / max($totalReports, 1)) * 100 }}%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                        <i class="fas fa-spinner text-info"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-muted">In Progress</h6>
                        <h3 class="mb-0">{{ $inProgressReports }}</h3>
                    </div>
                </div>
                <div class="progress mt-3" style="height: 6px;">
                    <div class="progress-bar bg-info" role="progressbar" style="width: {{ ($inProgressReports / max($totalReports, 1)) * 100 }}%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-muted">Completed</h6>
                        <h3 class="mb-0">{{ $completedReports }}</h3>
                    </div>
                </div>
                <div class="progress mt-3" style="height: 6px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($completedReports / max($totalReports, 1)) * 100 }}%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                        <i class="fas fa-times-circle text-danger"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-muted">Rejected</h6>
                        <h3 class="mb-0">{{ $rejectedReports }}</h3>
                    </div>
                </div>
                <div class="d-flex align-items-center mt-3">
                    <div class="progress flex-grow-1 me-2" style="height: 6px;">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: {{ ($rejectedReports / max($totalReports, 1)) * 100 }}%"></div>
                    </div>
                    <span class="fs-6 fw-bold">{{ $completionPercentage }}%</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Department Performance and Approvals -->
<div class="row g-4 mb-4">
    <!-- Department Productivity Trend -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white p-3 border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-chart-line text-primary me-2"></i>
                        <h5 class="mb-0">Department Productivity</h5>
                    </div>
                </div>
            </div>
            <div class="card-body p-3">
                <div class="chart-container" style="height: 300px; position: relative;">
                    <canvas id="departmentTrendChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports Awaiting Approval -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white p-3 border-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-clipboard-check text-primary me-2"></i>
                    <h5 class="mb-0">Awaiting Approval</h5>
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
                            No reports awaiting approval
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

<!-- Department Performance -->
<div class="row g-4 mb-4">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white p-3 border-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-chart-bar text-primary me-2"></i>
                    <h5 class="mb-0">Department Performance</h5>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Metric</th>
                                <th>Value</th>
                                <th class="pe-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-4 fw-medium">Completion Rate</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 6px; width: 100px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $completionPercentage }}%"></div>
                                        </div>
                                        <span>{{ $completionPercentage }}%</span>
                                    </div>
                                </td>
                                <td class="pe-4">
                                    @if($completionPercentage >= 80)
                                        <span class="badge bg-success">Excellent</span>
                                    @elseif($completionPercentage >= 60)
                                        <span class="badge bg-primary">Good</span>
                                    @elseif($completionPercentage >= 40)
                                        <span class="badge bg-warning">Average</span>
                                    @else
                                        <span class="badge bg-danger">Needs Improvement</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-medium">Pending Reports</td>
                                <td>{{ $pendingReports }} reports</td>
                                <td class="pe-4">
                                    @if($pendingReports <= 2)
                                        <span class="badge bg-success">Low</span>
                                    @elseif($pendingReports <= 5)
                                        <span class="badge bg-warning">Moderate</span>
                                    @else
                                        <span class="badge bg-danger">High</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-medium">Total Reports</td>
                                <td>{{ $totalReports }} reports</td>
                                <td class="pe-4">
                                    <span class="badge bg-primary">Informational</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row g-4">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white p-3 border-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-bolt text-primary me-2"></i>
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="{{ route('daily-reports.pending') }}" class="btn btn-primary w-100 h-100 py-3">
                            <i class="fas fa-clipboard-check me-2"></i>Review Pending Reports
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('daily-reports.create') }}" class="btn btn-outline-primary w-100 h-100 py-3">
                            <i class="fas fa-plus-circle me-2"></i>Create New Report
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('daily-reports.index') }}" class="btn btn-outline-primary w-100 h-100 py-3">
                            <i class="fas fa-list me-2"></i>View All Department Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Department Head JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Department Head Dashboard scripts loaded');
        
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
            
            // Department Trend Chart
            const trendCanvas = document.getElementById('departmentTrendChart');
            if (!trendCanvas) {
                console.error('departmentTrendChart canvas element not found');
            } else {
                console.log('departmentTrendChart canvas found');
                const trendData = @json($departmentTrend);
                console.log('Department trend data:', trendData);
                
                try {
                    // Check if there's an existing chart
                    window.chartInstances = window.chartInstances || {};
                    if (window.chartInstances['departmentTrendChart']) {
                        window.chartInstances['departmentTrendChart'].destroy();
                    }
                    
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
                    window.chartInstances['departmentTrendChart'] = trendChart;
                    console.log('Department trend chart initialized successfully');
                } catch (error) {
                    console.error('Error initializing department trend chart:', error);
                }
            }
        }
    });
</script> 