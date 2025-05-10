<!-- Staff Dashboard -->

<!-- Personal Reports Status -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white p-3 border-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-file-alt text-primary me-2"></i>
                    <h5 class="mb-0">My Reports Status</h5>
                </div>
            </div>
            <div class="card-body py-4">
                <div class="row g-4">
                    <div class="col-sm-6 col-md-3">
                        <div class="p-3 bg-warning bg-opacity-10 rounded-3 text-center">
                            <div class="d-inline-flex align-items-center justify-content-center bg-warning text-white rounded-circle mb-3" style="width: 48px; height: 48px;">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <h3 class="fw-bold text-warning mb-1">{{ $myPendingReports }}</h3>
                            <p class="text-muted mb-0">Pending</p>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="p-3 bg-info bg-opacity-10 rounded-3 text-center">
                            <div class="d-inline-flex align-items-center justify-content-center bg-info text-white rounded-circle mb-3" style="width: 48px; height: 48px;">
                                <i class="fas fa-spinner"></i>
                            </div>
                            <h3 class="fw-bold text-info mb-1">{{ $myInProgressReports }}</h3>
                            <p class="text-muted mb-0">In Progress</p>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="p-3 bg-success bg-opacity-10 rounded-3 text-center">
                            <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle mb-3" style="width: 48px; height: 48px;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h3 class="fw-bold text-success mb-1">{{ $myCompletedReports }}</h3>
                            <p class="text-muted mb-0">Completed</p>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="p-3 bg-danger bg-opacity-10 rounded-3 text-center">
                            <div class="d-inline-flex align-items-center justify-content-center bg-danger text-white rounded-circle mb-3" style="width: 48px; height: 48px;">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <h3 class="fw-bold text-danger mb-1">{{ $myRejectedReports }}</h3>
                            <p class="text-muted mb-0">Rejected</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Personal Performance and Upcoming Deadlines -->
<div class="row g-4 mb-4">
    <!-- Personal Performance Chart -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white p-3 border-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-chart-line text-primary me-2"></i>
                    <h5 class="mb-0">My Job Reports Activity</h5>
                </div>
            </div>
            <div class="card-body p-3">
                <div class="chart-container" style="height: 300px; position: relative;">
                    <canvas id="performanceChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Deadlines -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white p-3 border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                        <h5 class="mb-0">Upcoming Deadlines</h5>
                    </div>
                    <span class="badge bg-warning rounded-pill">{{ count($urgentReports) }}</span>
                </div>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($urgentReports as $report)
                        <li class="list-group-item px-4 py-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <a href="{{ route('daily-reports.show', $report) }}" class="text-decoration-none fw-medium text-dark">
                                    {{ $report->job_name }}
                                </a>
                                <span class="badge {{ Carbon\Carbon::parse($report->due_date)->isPast() ? 'bg-danger' : 'bg-warning' }} text-white">
                                    {{ Carbon\Carbon::parse($report->due_date)->format('d M Y') }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center text-muted small">
                                <span class="me-3">
                                    <i class="fas fa-clipboard-list me-1"></i>
                                    @if($report->status === 'pending')
                                        <span class="text-warning">Pending</span>
                                    @elseif($report->status === 'in_progress')
                                        <span class="text-info">In Progress</span>
                                    @elseif($report->status === 'rejected')
                                        <span class="text-danger">Rejected</span>
                                    @else
                                        <span class="text-success">Completed</span>
                                    @endif
                                </span>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-center py-4 text-muted">
                            <i class="fas fa-calendar-check mb-2 d-block" style="font-size: 2rem;"></i>
                            No upcoming deadlines
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Recent Reports -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white p-3 border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-history text-primary me-2"></i>
                        <h5 class="mb-0">My Recent Reports</h5>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Report Name</th>
                                <th>Submitted</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Actions</th>
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
                                        <small class="text-muted">{{ $report->due_date ? Carbon\Carbon::parse($report->due_date)->format('d M Y') : 'Not set' }}</small>
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
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('daily-reports.show', $report) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if(in_array($report->status, ['pending', 'in_progress', 'rejected']))
                                                <a href="{{ route('daily-reports.edit', $report) }}" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fas fa-file-alt mb-2 d-block" style="font-size: 2rem;"></i>
                                        No recent reports
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white text-end p-3">
                <a href="{{ route('daily-reports.user-jobs') }}" class="btn btn-outline-primary">
                    <i class="fas fa-list me-2"></i>View All My Reports
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Productivity and Quick Actions -->
<div class="row g-4">
    <!-- Productivity Metrics -->
    <div class="col-md-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white p-3 border-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-tachometer-alt text-primary me-2"></i>
                    <h5 class="mb-0">My Productivity Metrics</h5>
                </div>
            </div>
            <div class="card-body p-4">
                @if($totalUserReports > 0)
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check-circle text-success me-3 fa-lg"></i>
                                <div>
                                    <h6 class="text-muted mb-0">Completion Rate</h6>
                                    <h4 class="mb-0">{{ $completionRate }}%</h4>
                                </div>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $completionRate }}%" aria-valuenow="{{ $completionRate }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-hourglass-half text-warning me-3 fa-lg"></i>
                                <div>
                                    <h6 class="text-muted mb-0">On-Time Delivery</h6>
                                    <h4 class="mb-0">{{ $onTimeRate }}%</h4>
                                </div>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $onTimeRate }}%" aria-valuenow="{{ $onTimeRate }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-clipboard-list text-info me-3 fa-lg"></i>
                                <div>
                                    <h6 class="text-muted mb-0">Reports This Month</h6>
                                    <h4 class="mb-0">{{ $reportsThisMonth }}</h4>
                                </div>
                            </div>
                            <div class="text-muted small">
                                @if($reportsChangePercentage > 0)
                                    <i class="fas fa-arrow-up text-success me-1"></i>
                                    <span class="text-success">{{ $reportsChangePercentage }}%</span> from last month
                                @elseif($reportsChangePercentage < 0)
                                    <i class="fas fa-arrow-down text-danger me-1"></i>
                                    <span class="text-danger">{{ abs($reportsChangePercentage) }}%</span> from last month
                                @else
                                    <i class="fas fa-equals text-muted me-1"></i>
                                    <span>No change</span> from last month
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-comments text-primary me-3 fa-lg"></i>
                                <div>
                                    <h6 class="text-muted mb-0">Comments Received</h6>
                                    <h4 class="mb-0">{{ $averageFeedback }} per report</h4>
                                </div>
                            </div>
                            <div class="text-muted small">
                                Total {{ $feedbackCount }} {{ Str::plural('comment', $feedbackCount) }} on your reports
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-chart-bar mb-3 d-block" style="font-size: 2.5rem;"></i>
                    <h5>No report data available yet</h5>
                    <p>Complete your first job report to see your productivity metrics</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-md-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white p-3 border-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-bolt text-primary me-2"></i>
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="d-grid gap-3">
                    <a href="{{ route('daily-reports.create') }}" class="btn btn-primary p-3">
                        <i class="fas fa-plus-circle me-2"></i>Create New Report
                    </a>
                    <a href="{{ route('daily-reports.user-jobs') }}" class="btn btn-outline-primary p-3">
                        <i class="fas fa-list me-2"></i>View All My Reports
                    </a>
                    @if($myRejectedReports > 0)
                        <a href="{{ route('daily-reports.user-jobs', ['status' => 'rejected']) }}" class="btn btn-outline-danger p-3">
                            <i class="fas fa-exclamation-circle me-2"></i>Review Rejected Reports
                        </a>
                    @endif
                    @if($myInProgressReports > 0)
                        <a href="{{ route('daily-reports.user-jobs', ['status' => 'in_progress']) }}" class="btn btn-outline-info p-3">
                            <i class="fas fa-spinner me-2"></i>Continue In-Progress Reports
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Staff JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Staff Dashboard scripts loaded');
        
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
            
            // Performance Chart
            const performanceCanvas = document.getElementById('performanceChart');
            if (!performanceCanvas) {
                console.error('performanceChart canvas element not found');
            } else {
                console.log('performanceChart canvas found');
                const performanceData = @json($performanceData);
                console.log('Performance data:', performanceData);
                
                try {
                    // Check if there's data to display
                    if (performanceData.labels.length === 0 || 
                        performanceData.completed.length === 0 || 
                        performanceData.total.length === 0) {
                        // No data available, show message
                        const ctx = performanceCanvas.getContext('2d');
                        ctx.font = '16px Arial';
                        ctx.textAlign = 'center';
                        ctx.fillStyle = '#6c757d';
                        ctx.fillText('No report data available yet', performanceCanvas.width / 2, performanceCanvas.height / 2);
                        console.log('No performance data available, showing message');
                        return;
                    }
                    
                    const performanceChart = new Chart(performanceCanvas, {
                        type: 'bar',
                        data: {
                            labels: performanceData.labels,
                            datasets: [{
                                label: 'Completed',
                                data: performanceData.completed,
                                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                                borderColor: 'rgba(16, 185, 129, 1)',
                                borderWidth: 1,
                                order: 1
                            }, {
                                label: 'Pending',
                                data: performanceData.pending,
                                backgroundColor: 'rgba(245, 158, 11, 0.8)',
                                borderColor: 'rgba(245, 158, 11, 1)',
                                borderWidth: 1,
                                order: 2
                            }, {
                                label: 'In Progress',
                                data: performanceData.in_progress,
                                backgroundColor: 'rgba(14, 165, 233, 0.8)',
                                borderColor: 'rgba(14, 165, 233, 1)',
                                borderWidth: 1,
                                order: 3
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top'
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    callbacks: {
                                        title: function(tooltipItems) {
                                            // Display the full date in the tooltip
                                            return tooltipItems[0].label;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    ticks: {
                                        maxRotation: 45,
                                        minRotation: 45
                                    },
                                    stacked: true
                                },
                                y: {
                                    beginAtZero: true,
                                    stacked: true,
                                    ticks: {
                                        precision: 0,
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });
                    console.log('Performance chart initialized successfully');
                } catch (error) {
                    console.error('Error initializing performance chart:', error);
                }
            }
        }
    });
</script> 