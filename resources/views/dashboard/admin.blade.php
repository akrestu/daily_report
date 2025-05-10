<!-- Admin Dashboard -->

<!-- System Overview Stats -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white p-3 border-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-tachometer-alt text-primary me-2"></i>
                    <h5 class="mb-0">System Overview</h5>
                </div>
            </div>
            <div class="card-body py-4">
                <div class="row g-4">
                    <div class="col-sm-6 col-md-3">
                        <div class="p-3 bg-primary bg-opacity-10 rounded-3 text-center">
                            <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle mb-3" style="width: 48px; height: 48px;">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3 class="fw-bold text-primary mb-1">{{ $totalUsers }}</h3>
                            <p class="text-muted mb-0">Total Users</p>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="p-3 bg-success bg-opacity-10 rounded-3 text-center">
                            <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle mb-3" style="width: 48px; height: 48px;">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <h3 class="fw-bold text-success mb-1">{{ $totalReports }}</h3>
                            <p class="text-muted mb-0">Total Reports</p>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="p-3 bg-warning bg-opacity-10 rounded-3 text-center">
                            <div class="d-inline-flex align-items-center justify-content-center bg-warning text-white rounded-circle mb-3" style="width: 48px; height: 48px;">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <h3 class="fw-bold text-warning mb-1">{{ $pendingReports }}</h3>
                            <p class="text-muted mb-0">Pending Reports</p>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="p-3 bg-info bg-opacity-10 rounded-3 text-center">
                            <div class="d-inline-flex align-items-center justify-content-center bg-info text-white rounded-circle mb-3" style="width: 48px; height: 48px;">
                                <i class="fas fa-file-import"></i>
                            </div>
                            <h3 class="fw-bold text-info mb-1">{{ $reportsToday }}</h3>
                            <p class="text-muted mb-0">Reports Today</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reports Analytics and User Stats -->
<div class="row g-4 mb-4">
    <!-- Report Trends Chart -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white p-3 border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-chart-line text-primary me-2"></i>
                        <h5 class="mb-0">Report Trends</h5>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ $selectedRange ?? 'Last 30 Days' }}
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('admin.dashboard', ['range' => 'week']) }}">Last 7 Days</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.dashboard', ['range' => 'month']) }}">Last 30 Days</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.dashboard', ['range' => 'year']) }}">Last 12 Months</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body p-3">
                <div class="chart-container" style="height: 300px; position: relative;">
                    <canvas id="reportTrendChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- User Activity -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white p-3 border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user-clock text-primary me-2"></i>
                        <h5 class="mb-0">User Activity</h5>
                    </div>
                </div>
            </div>
            <div class="card-body p-3">
                <!-- Status chart container -->
                <div class="chart-container mb-3" style="height: 200px; position: relative;">
                    <canvas id="statusChart" width="300" height="200"></canvas>
                </div>
                
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h6 class="text-muted mb-1">Active Users Today</h6>
                        <h4 class="mb-0">{{ $activeUsersToday }}</h4>
                    </div>
                    <div class="d-flex align-items-center text-success">
                        @if($activeUsersChange > 0)
                            <i class="fas fa-arrow-up me-1"></i>
                            <span>{{ $activeUsersChange }}%</span>
                        @elseif($activeUsersChange < 0)
                            <i class="fas fa-arrow-down me-1 text-danger"></i>
                            <span class="text-danger">{{ abs($activeUsersChange) }}%</span>
                        @else
                            <i class="fas fa-equals me-1 text-muted"></i>
                            <span class="text-muted">0%</span>
                        @endif
                    </div>
                </div>
                
                <div class="mb-4">
                    <h6 class="text-muted mb-3">User Roles Distribution</h6>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Admins</span>
                            <span class="fw-medium">{{ $adminsCount }}</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ ($adminsCount / $totalUsers) * 100 }}%" aria-valuenow="{{ ($adminsCount / $totalUsers) * 100 }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Leaders</span>
                            <span class="fw-medium">{{ $leadersCount }}</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($leadersCount / $totalUsers) * 100 }}%" aria-valuenow="{{ ($leadersCount / $totalUsers) * 100 }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Staff</span>
                            <span class="fw-medium">{{ $staffCount }}</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ ($staffCount / $totalUsers) * 100 }}%" aria-valuenow="{{ ($staffCount / $totalUsers) * 100 }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>

                <div>
                    <h6 class="text-muted mb-2">Most Active Departments</h6>
                    <ul class="list-group list-group-flush">
                        @forelse($topDepartments as $department)
                            <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center border-bottom">
                                <span>{{ $department->name }}</span>
                                <span class="badge bg-primary rounded-pill">{{ $department->reports_count }}</span>
                            </li>
                        @empty
                            <li class="list-group-item px-0 py-2 text-center text-muted">
                                No department data available
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Department Performance Chart -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white p-3 border-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-building text-primary me-2"></i>
                    <h5 class="mb-0">Department Performance</h5>
                </div>
            </div>
            <div class="card-body p-3">
                <div class="chart-container" style="height: 300px; position: relative;">
                    <canvas id="departmentChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Users Management and Recent Reports -->
<div class="row g-4 mb-4">
    <!-- Recent Users -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white p-3 border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-users text-primary me-2"></i>
                        <h5 class="mb-0">Recent Users</h5>
                    </div>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentUsers as $user)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.users.show', $user) }}" class="d-flex align-items-center text-decoration-none text-dark">
                                            <div class="avatar-sm me-2 bg-primary text-white rounded-circle">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="mb-0 fw-medium">{{ $user->name }}</p>
                                                <small class="text-muted">{{ $user->email }}</small>
                                            </div>
                                        </a>
                                    </td>
                                    <td>{{ $user->department->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($user->role === 'admin')
                                            <span class="badge bg-primary bg-opacity-10 text-primary">Admin</span>
                                        @elseif($user->role === 'leader')
                                            <span class="badge bg-success bg-opacity-10 text-success">Leader</span>
                                        @else
                                            <span class="badge bg-info bg-opacity-10 text-info">Staff</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fas fa-users mb-2 d-block" style="font-size: 2rem;"></i>
                                        No users found
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
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-file-alt text-primary me-2"></i>
                        <h5 class="mb-0">Recent Reports</h5>
                    </div>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Report Name</th>
                                <th>User</th>
                                <th>Department</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentReports as $report)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.reports.show', $report) }}" class="text-decoration-none fw-medium text-dark">
                                            {{ $report->job_name }}
                                        </a>
                                        @if($report->comments->count() > 0)
                                        <span class="badge bg-info ms-1" data-bs-toggle="tooltip" title="{{ $report->comments->count() }} Comments">
                                            <i class="fas fa-comments"></i> {{ $report->comments->count() }}
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $report->user->name }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $report->department->name ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $report->created_at->format('d M Y') }}</small>
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
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fas fa-file-alt mb-2 d-block" style="font-size: 2rem;"></i>
                                        No reports found
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

<!-- System Stats and Quick Actions -->
<div class="row g-4 mb-4">
    <!-- System Stats -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white p-3 border-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-chart-pie text-primary me-2"></i>
                    <h5 class="mb-0">Reports by Status</h5>
                </div>
            </div>
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-md-7">
                        <div class="chart-container" style="height: 220px; position: relative;">
                            <canvas id="reportStatusChart" width="400" height="220"></canvas>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="d-flex flex-column h-100 justify-content-center">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-circle text-success me-2 small"></i>
                                        <span>Completed</span>
                                    </div>
                                    <div>{{ $completedReports }}</div>
                                </div>
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $totalReports > 0 ? ($completedReports / $totalReports) * 100 : 0 }}%" aria-valuenow="{{ $totalReports > 0 ? ($completedReports / $totalReports) * 100 : 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-circle text-warning me-2 small"></i>
                                        <span>Pending</span>
                                    </div>
                                    <div>{{ $pendingReports }}</div>
                                </div>
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $totalReports > 0 ? ($pendingReports / $totalReports) * 100 : 0 }}%" aria-valuenow="{{ $totalReports > 0 ? ($pendingReports / $totalReports) * 100 : 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-circle text-info me-2 small"></i>
                                        <span>In Progress</span>
                                    </div>
                                    <div>{{ $inProgressReports }}</div>
                                </div>
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: {{ $totalReports > 0 ? ($inProgressReports / $totalReports) * 100 : 0 }}%" aria-valuenow="{{ $totalReports > 0 ? ($inProgressReports / $totalReports) * 100 : 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-circle text-danger me-2 small"></i>
                                        <span>Rejected</span>
                                    </div>
                                    <div>{{ $rejectedReports }}</div>
                                </div>
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $totalReports > 0 ? ($rejectedReports / $totalReports) * 100 : 0 }}%" aria-valuenow="{{ $totalReports > 0 ? ($rejectedReports / $totalReports) * 100 : 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Quick Actions -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white p-3 border-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-bolt text-primary me-2"></i>
                    <h5 class="mb-0">Administrative Actions</h5>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="d-grid gap-3">
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary p-3">
                        <i class="fas fa-user-plus me-2"></i>Add New User
                    </a>
                    <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-secondary p-3">
                        <i class="fas fa-building me-2"></i>Manage Departments
                    </a>
                    <a href="{{ route('admin.reports.index', ['status' => 'pending']) }}" class="btn btn-outline-warning p-3">
                        <i class="fas fa-hourglass-half me-2"></i>Review Pending Reports ({{ $pendingReports }})
                    </a>
                    <a href="{{ route('admin.settings') }}" class="btn btn-outline-dark p-3">
                        <i class="fas fa-cog me-2"></i>System Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Admin JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Admin Dashboard scripts loaded');
        
        // Clear any existing charts
        if (window.chartInstances) {
            Object.values(window.chartInstances).forEach(chart => {
                if (chart) chart.destroy();
            });
        }
        
        // Initialize chart instances storage
        window.chartInstances = {};
        
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
            
            // Report Trends Chart
            const reportTrendsCanvas = document.getElementById('reportTrendChart');
            if (!reportTrendsCanvas) {
                console.error('reportTrendChart canvas element not found');
            } else {
                console.log('reportTrendChart canvas found');
                
                try {
                    const ctx = reportTrendsCanvas.getContext('2d');
                    // Clear the canvas
                    ctx.clearRect(0, 0, reportTrendsCanvas.width, reportTrendsCanvas.height);
                    
                    const reportTrendsData = @json($reportTrendsData);
                    
                    window.chartInstances.reportTrend = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: reportTrendsData.labels,
                            datasets: [{
                                label: 'Total Reports',
                                data: reportTrendsData.total,
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                borderColor: 'rgba(59, 130, 246, 1)',
                                borderWidth: 2,
                                tension: 0.3,
                                fill: true
                            }, {
                                label: 'Completed Reports',
                                data: reportTrendsData.completed,
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                borderColor: 'rgba(16, 185, 129, 1)',
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
                                    position: 'top'
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
                    console.log('Report trends chart initialized successfully');
                } catch (error) {
                    console.error('Error initializing trend chart:', error);
                }
            }
            
            // Report Status Chart
            const reportStatusCanvas = document.getElementById('reportStatusChart');
            if (!reportStatusCanvas) {
                console.error('reportStatusChart canvas element not found');
            } else {
                console.log('statusChart canvas found');
                
                try {
                    const ctx = reportStatusCanvas.getContext('2d');
                    // Clear the canvas
                    ctx.clearRect(0, 0, reportStatusCanvas.width, reportStatusCanvas.height);
                    
                    window.chartInstances.reportStatus = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Completed', 'Pending', 'In Progress', 'Rejected'],
                            datasets: [{
                                data: [
                                    {{ $completedReports }}, 
                                    {{ $pendingReports }}, 
                                    {{ $inProgressReports }}, 
                                    {{ $rejectedReports }}
                                ],
                                backgroundColor: [
                                    'rgba(16, 185, 129, 0.8)',
                                    'rgba(245, 158, 11, 0.8)',
                                    'rgba(59, 130, 246, 0.8)',
                                    'rgba(239, 68, 68, 0.8)'
                                ],
                                borderColor: [
                                    'rgba(16, 185, 129, 1)',
                                    'rgba(245, 158, 11, 1)',
                                    'rgba(59, 130, 246, 1)',
                                    'rgba(239, 68, 68, 1)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            cutout: '65%'
                        }
                    });
                    console.log('Status chart initialized successfully');
                } catch (error) {
                    console.error('Error initializing status chart:', error);
                }
            }
            
            // Department Performance Chart
            const departmentCanvas = document.getElementById('departmentChart');
            if (!departmentCanvas) {
                console.error('departmentChart canvas element not found');
            } else {
                console.log('departmentChart canvas found');
                
                try {
                    const ctx = departmentCanvas.getContext('2d');
                    // Clear the canvas
                    ctx.clearRect(0, 0, departmentCanvas.width, departmentCanvas.height);
                    
                    // Extract department data from PHP
                    const deptData = @json($departmentPerformance ?? []);
                    console.log('Department data:', deptData);
                    
                    // Prepare chart data
                    const labels = deptData.map(d => d.name);
                    const completionRates = deptData.map(d => d.completion_rate);
                    
                    window.chartInstances.department = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Completion Rate (%)',
                                data: completionRates,
                                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                                borderColor: 'rgba(59, 130, 246, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100,
                                    ticks: {
                                        callback: function(value) {
                                            return value + '%';
                                        }
                                    }
                                }
                            }
                        }
                    });
                    console.log('Department chart initialized successfully');
                } catch (error) {
                    console.error('Error initializing department chart:', error);
                }
            }
        }
    });
</script> 