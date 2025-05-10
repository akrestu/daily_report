<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center">
            <i class="fas fa-tachometer-alt fs-4 text-primary me-2"></i>
            <span>Dashboard</span>
        </div>
    </x-slot>

    <!-- Welcome Banner -->
    <div class="card bg-primary text-white mb-4 border-0 shadow-sm overflow-hidden">
        <div class="card-body position-relative py-4">
            <div class="row">
                <div class="col-lg-8">
                    <h4 class="mb-1">Welcome back, {{ $user->name }}!</h4>
                    <p class="mb-4 opacity-75">
                        @if($user->hasRole('admin'))
                            Here's an overview of the system activity.
                        @elseif($user->hasRole('department_head'))
                            Here's an overview of your department's performance.
                        @elseif($user->hasRole('leader'))
                            Here's an overview of your team's reports.
                        @else
                            Here's an overview of your reports.
                        @endif
                    </p>
                    <a href="{{ route('daily-reports.create') }}" class="btn btn-light">
                        <i class="fas fa-plus-circle me-2"></i>Create New Report
                    </a>
                </div>
            </div>
            <div class="position-absolute d-none d-lg-block" style="right: 1rem; bottom: -2rem;">
                <i class="fas fa-chart-line text-white opacity-25" style="font-size: 8rem;"></i>
            </div>
        </div>
    </div>

    <!-- Admin Dashboard -->
    @if($user->hasRole('admin'))
        @include('dashboard.admin')
    @endif

    <!-- Department Head Dashboard -->
    @if($user->hasRole('department_head'))
        @include('dashboard.department-head')
    @endif

    <!-- Leader Dashboard -->
    @if($user->hasRole('leader'))
        @include('dashboard.leader')
    @endif

    <!-- Staff Dashboard -->
    @if($user->hasRole('staff'))
        @include('dashboard.staff')
    @endif
</x-app-layout>

<!-- Dashboard JavaScript -->
<script>
    // Store dashboard data in a global object
    window.dashboardData = {
        reportTrend: @json($reportTrend),
        pendingReports: {{ $pendingReports }},
        inProgressReports: {{ $inProgressReports }},
        completedReports: {{ $completedReports }},
        rejectedReports: {{ $rejectedReports }},
        departmentPerformance: @json($departmentPerformance ?? [])
    };
</script>

<!-- Define chart initialization in a separate file to avoid duplication -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check if charts have already been initialized
        if (window.dashboardChartsInitialized === true) {
            console.log('Dashboard charts already initialized, skipping...');
            return;
        }
        
        // Wait for Chart.js to be fully loaded
        const waitForChart = function(callback, maxAttempts = 10, interval = 100) {
            let attempts = 0;
            
            const checkChart = function() {
                attempts++;
                if (typeof Chart !== 'undefined') {
                    callback();
                } else if (attempts < maxAttempts) {
                    setTimeout(checkChart, interval);
                } else {
                    console.error('Chart.js not available after maximum attempts');
                }
            };
            
            checkChart();
        };
        
        // Set flag to prevent multiple initializations
        window.dashboardChartsInitialized = true;
        
        // Initialize charts when Chart.js is available
        waitForChart(function() {
            console.log('Chart.js is available, initializing charts...');
            initializeDashboardCharts();
        });
    });
    
    /**
     * Initialize all dashboard charts
     */
    function initializeDashboardCharts() {
        const data = window.dashboardData;
        if (!data) {
            console.error('Dashboard data not available');
            return;
        }
        
        // Get canvas elements
        const trendCanvas = document.getElementById('reportTrendChart');
        const statusCanvas = document.getElementById('statusChart');
        const deptCanvas = document.getElementById('departmentChart');
        
        // Safety check for canvases - log message but don't treat as error
        if (!trendCanvas) console.log('reportTrendChart canvas not found');
        if (!statusCanvas) console.log('statusChart canvas not found');
        if (!deptCanvas) console.log('departmentChart canvas not found');
        
        // Initialize trend chart if canvas exists
        if (trendCanvas) {
            initializeChart(trendCanvas, {
                type: 'line',
                data: {
                    labels: data.reportTrend.map(item => item.date),
                    datasets: [{
                        label: 'Daily Reports',
                        data: data.reportTrend.map(item => item.count),
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
        }
        
        // Initialize status chart if canvas exists
        if (statusCanvas) {
            initializeChart(statusCanvas, {
                type: 'doughnut',
                data: {
                    labels: ['Pending', 'In Progress', 'Completed', 'Rejected'],
                    datasets: [{
                        data: [
                            data.pendingReports, 
                            data.inProgressReports, 
                            data.completedReports, 
                            data.rejectedReports
                        ],
                        backgroundColor: [
                            'rgba(245, 158, 11, 0.8)',  // warning
                            'rgba(59, 130, 246, 0.8)',  // info
                            'rgba(16, 185, 129, 0.8)',  // success
                            'rgba(239, 68, 68, 0.8)'    // danger
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    cutout: '70%'
                }
            });
        }
        
        // Initialize department chart if canvas exists
        if (deptCanvas && data.departmentPerformance && data.departmentPerformance.length > 0) {
            initializeChart(deptCanvas, {
                type: 'bar',
                data: {
                    labels: data.departmentPerformance.map(item => item.name),
                    datasets: [{
                        label: 'Completion Rate (%)',
                        data: data.departmentPerformance.map(item => item.completion_rate),
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderRadius: 4
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
        }
    }
    
    /**
     * Initialize a chart with safety checks for existing instances
     * @param {HTMLElement} canvas The canvas element
     * @param {Object} config Chart.js configuration
     * @returns {Chart|null} The chart instance or null if initialization failed
     */
    function initializeChart(canvas, config) {
        if (!canvas) return null;
        
        try {
            // Store chart instances globally if not already done
            window.chartInstances = window.chartInstances || {};
            
            // Check if there's an existing chart on this canvas and destroy it
            const chartId = canvas.id;
            if (window.chartInstances[chartId]) {
                window.chartInstances[chartId].destroy();
                console.log(`Destroyed existing chart on ${chartId}`);
            }
            
            // Create new chart instance
            const chart = new Chart(canvas, config);
            
            // Store this chart instance for future reference
            window.chartInstances[chartId] = chart;
            
            console.log(`Chart initialized successfully on ${chartId}`);
            return chart;
        } catch (error) {
            console.error(`Error initializing chart on ${canvas.id}:`, error);
            return null;
        }
    }
</script> 