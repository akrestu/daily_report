/**
 * Dashboard JavaScript - Separated from template for better organization
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    initTooltips();
    
    // Initialize charts
    initJobsTrendChart();
    initJobsByStatusChart();
    
    // Initialize department jobs chart for admin users
    const deptChartElement = document.getElementById('departmentJobsChart');
    if (deptChartElement) {
        initDepartmentJobsChart();
    }
    
    // Initialize event handlers
    initEventHandlers();
});

/**
 * Initialize Bootstrap tooltips
 */
function initTooltips() {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if (tooltipTriggerList.length > 0) {
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => 
            new bootstrap.Tooltip(tooltipTriggerEl)
        );
    }
}

/**
 * Initialize event handlers for various interactive elements
 */
function initEventHandlers() {
    // Select all checkbox functionality
    const selectAll = document.getElementById('select-all');
    if (selectAll) {
        const reportCheckboxes = document.querySelectorAll('.select-report');
        
        selectAll.addEventListener('change', function() {
            reportCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
        });

        // Update "Select All" state when individual checkboxes change
        reportCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(reportCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(reportCheckboxes).some(cb => cb.checked);
                
                selectAll.checked = allChecked;
                selectAll.indeterminate = someChecked && !allChecked;
            });
        });
    }

    // Handle individual delete form submissions with confirmation
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this report?')) {
                this.submit();
            }
        });
    });

    // Handle batch delete
    const batchDeleteBtn = document.getElementById('batch-delete');
    if (batchDeleteBtn) {
        batchDeleteBtn.addEventListener('click', function() {
            const selectedCheckboxes = document.querySelectorAll('.select-report:checked');
            const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);

            if (selectedIds.length === 0) {
                alert('Please select at least one report to delete.');
                return;
            }

            if (confirm('Are you sure you want to delete all selected reports? This action cannot be undone.')) {
                batchDelete(selectedIds);
            }
        });
    }
    
    // Handle batch approve
    const batchApproveBtn = document.getElementById('batch-approve');
    if (batchApproveBtn) {
        batchApproveBtn.addEventListener('click', function() {
            const selectedCheckboxes = document.querySelectorAll('.select-report:checked');
            const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);

            if (selectedIds.length === 0) {
                alert('Please select at least one report to approve.');
                return;
            }

            if (confirm('Are you sure you want to approve all selected reports?')) {
                batchApprove(selectedIds);
            }
        });
    }
    
    // Toggle rejection reason field in approval form
    const statusRadios = document.querySelectorAll('input[name="status"]');
    if (statusRadios.length > 0) {
        statusRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                const rejectionReasonDiv = document.querySelector('.rejection-reason');
                if (rejectionReasonDiv) {
                    if (this.value === 'rejected') {
                        rejectionReasonDiv.classList.remove('d-none');
                    } else {
                        rejectionReasonDiv.classList.add('d-none');
                    }
                }
            });
        });
    }
    
    // Department toggle in admin dashboard
    const showAllDepts = document.getElementById('showAllDepartments');
    if (showAllDepts) {
        showAllDepts.addEventListener('change', function() {
            // This would filter the departments shown (to be implemented)
        });
    }
}

/**
 * Initialize Jobs Trend Chart
 */
function initJobsTrendChart() {
    const trendCtx = document.getElementById('jobsTrendChart');
    if (!trendCtx) return;
    
    // Get chart data from data attributes
    const chartData = getChartData(trendCtx);
    if (!chartData) return;
    
    const trendData = {
        labels: chartData.labels || [],
        datasets: [{
            label: 'Approved Jobs',
            data: chartData.approved || [],
            backgroundColor: [
                'rgba(13, 110, 253, 0.7)',    // Daily
                'rgba(23, 162, 184, 0.7)',    // Weekly
                'rgba(40, 167, 69, 0.7)'      // Monthly
            ],
            borderColor: [
                'rgb(13, 110, 253)',
                'rgb(23, 162, 184)',
                'rgb(40, 167, 69)'
            ],
            borderWidth: 1
        }]
    };
    
    // Check if there's any data to display
    const hasData = trendData.datasets[0].data.some(value => value > 0);
    
    // Create the chart
    const trendChart = new Chart(trendCtx, {
        type: 'bar',
        data: trendData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                title: {
                    display: true,
                    text: 'Approved Jobs Count'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.parsed.y;
                            return label;
                        }
                    }
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
    
    if (!hasData) {
        showNoDataOverlay(trendCtx, 'No approved jobs found', 'Complete some jobs to see statistics here');
    }
}

/**
 * Initialize Jobs by Status Chart (Pie/Doughnut chart)
 */
function initJobsByStatusChart() {
    const statusCtx = document.getElementById('jobsByStatusChart');
    if (!statusCtx) return;
    
    // Get chart data from data attributes
    const statusCounts = getStatusCounts(statusCtx);
    if (!statusCounts) return;
    
    const statusData = {
        labels: ['Completed', 'In Progress', 'Pending', 'Rejected', 'Approved'],
        datasets: [{
            data: [
                statusCounts.completed || 0, 
                statusCounts.in_progress || 0, 
                statusCounts.pending || 0, 
                statusCounts.rejected || 0,
                statusCounts.approved || 0
            ],
            backgroundColor: [
                '#0d6efd', // Completed - Blue
                '#17a2b8', // In Progress - Cyan
                '#ffc107', // Pending - Yellow
                '#dc3545', // Rejected - Red
                '#28a745'  // Approved - Green
            ],
            borderWidth: 0,
            hoverOffset: 15
        }]
    };
    
    // Remove zero values and their corresponding labels
    const filteredIndices = statusData.datasets[0].data
        .map((value, index) => value > 0 ? index : -1)
        .filter(index => index !== -1);
    
    const filteredLabels = filteredIndices.map(index => statusData.labels[index]);
    const filteredData = filteredIndices.map(index => statusData.datasets[0].data[index]);
    const filteredColors = filteredIndices.map(index => statusData.datasets[0].backgroundColor[index]);
    
    // Only create chart if there's data to display
    if (filteredData.length > 0) {
        // Update the data with filtered values
        statusData.labels = filteredLabels;
        statusData.datasets[0].data = filteredData;
        statusData.datasets[0].backgroundColor = filteredColors;
        
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: statusData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((acc, data) => acc + data, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 1000,
                    easing: 'easeOutCirc'
                },
                cutout: '65%'
            }
        });
    } else {
        showNoDataMessage(statusCtx, 'No status data available');
    }
}

/**
 * Initialize Department Jobs Chart (Admin Only)
 */
function initDepartmentJobsChart() {
    const deptCtx = document.getElementById('departmentJobsChart');
    if (!deptCtx) return;
    
    // Get data from data attributes
    const deptData = getDepartmentData(deptCtx);
    if (!deptData) return;
    
    // Check if there's any data to display
    const hasDeptData = deptData.datasets.some(dataset => 
        dataset.data.some(value => value > 0)
    );
    
    if (hasDeptData) {
        const deptChart = new Chart(deptCtx, {
            type: 'bar',
            data: deptData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                animation: {
                    delay: function(context) {
                        return context.dataIndex * 50;
                    },
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });
    } else {
        showNoDataMessage(deptCtx, 'No department data available');
        
        // Disable department toggle
        const toggle = document.getElementById('showAllDepartments');
        if (toggle) {
            toggle.disabled = true;
            toggle.parentNode.classList.add('text-muted');
        }
    }
}

/**
 * Display "No Data" overlay for a chart
 */
function showNoDataOverlay(chartCtx, message, submessage = '') {
    const chartContainer = chartCtx.canvas.parentNode;
    const overlay = document.createElement('div');
    overlay.className = 'position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-white bg-opacity-75';
    overlay.style.zIndex = 10;
    overlay.innerHTML = `
        <div class="text-center">
            <i class="fas fa-exclamation-circle text-warning fa-2x mb-2"></i>
            <p class="mb-0">${message}</p>
            ${submessage ? `<small class="text-muted">${submessage}</small>` : ''}
        </div>
    `;
    
    // Make the chart container position relative for the overlay
    chartContainer.style.position = 'relative';
    chartContainer.appendChild(overlay);
}

/**
 * Replace chart with "No Data" message
 */
function showNoDataMessage(chartCtx, message) {
    const noDataMessage = document.createElement('div');
    noDataMessage.className = 'text-center py-4';
    noDataMessage.innerHTML = `
        <i class="fas fa-chart-pie text-muted fa-3x mb-3"></i>
        <p class="text-muted">${message}</p>
    `;
    chartCtx.canvas.parentNode.replaceChild(noDataMessage, chartCtx.canvas);
}

/**
 * Batch delete reports via AJAX
 */
function batchDelete(ids) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch('/daily-reports/batch-delete', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ ids: ids })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'An error occurred while deleting the reports.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing the request.');
    });
}

/**
 * Batch approve reports via AJAX
 */
function batchApprove(ids) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch('/daily-reports/batch-approve', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ ids: ids })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'An error occurred while approving the reports.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing the request.');
    });
}

/**
 * Get chart data from data-* attributes in the chart element
 */
function getChartData(chartElement) {
    if (!chartElement || !chartElement.dataset) return null;
    
    try {
        const labels = JSON.parse(chartElement.dataset.labels || '[]');
        const approved = JSON.parse(chartElement.dataset.approved || '[]');
        
        return { labels, approved };
    } catch (e) {
        console.error('Error parsing chart data:', e);
        return null;
    }
}

/**
 * Get status counts from data-* attributes in the chart element
 */
function getStatusCounts(chartElement) {
    if (!chartElement || !chartElement.dataset) return null;
    
    try {
        return {
            completed: parseInt(chartElement.dataset.completed || 0),
            in_progress: parseInt(chartElement.dataset.inProgress || 0),
            pending: parseInt(chartElement.dataset.pending || 0),
            rejected: parseInt(chartElement.dataset.rejected || 0),
            approved: parseInt(chartElement.dataset.approved || 0)
        };
    } catch (e) {
        console.error('Error parsing status data:', e);
        return null;
    }
}

/**
 * Get department data from data-* attributes in the chart element
 */
function getDepartmentData(chartElement) {
    if (!chartElement || !chartElement.dataset) return null;
    
    try {
        const labels = JSON.parse(chartElement.dataset.departments || '[]');
        const completed = JSON.parse(chartElement.dataset.completed || '[]');
        const inProgress = JSON.parse(chartElement.dataset.inProgress || '[]');
        const pending = JSON.parse(chartElement.dataset.pending || '[]');
        
        return {
            labels,
            datasets: [
                {
                    label: 'Completed',
                    data: completed,
                    backgroundColor: '#0d6efd'
                }, 
                {
                    label: 'In Progress',
                    data: inProgress,
                    backgroundColor: '#17a2b8'
                }, 
                {
                    label: 'Pending',
                    data: pending,
                    backgroundColor: '#ffc107'
                }
            ]
        };
    } catch (e) {
        console.error('Error parsing department data:', e);
        return null;
    }
} 