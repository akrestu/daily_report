<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center">
            <i class="fas fa-chart-line fs-4 text-primary me-2"></i>
            <span>Chart Test</span>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-header">Chart Test</div>
        <div class="card-body">
            <div style="height: 300px;">
                <canvas id="testChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Test Chart Page Loaded');
            console.log('Chart object available:', typeof Chart !== 'undefined');
            
            if (typeof Chart !== 'undefined') {
                const ctx = document.getElementById('testChart');
                if (ctx) {
                    console.log('Canvas element found');
                    try {
                        const chart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
                                datasets: [{
                                    label: '# of Votes',
                                    data: [12, 19, 3, 5, 2, 3],
                                    backgroundColor: [
                                        'rgba(255, 99, 132, 0.2)',
                                        'rgba(54, 162, 235, 0.2)',
                                        'rgba(255, 206, 86, 0.2)',
                                        'rgba(75, 192, 192, 0.2)',
                                        'rgba(153, 102, 255, 0.2)',
                                        'rgba(255, 159, 64, 0.2)'
                                    ],
                                    borderColor: [
                                        'rgba(255, 99, 132, 1)',
                                        'rgba(54, 162, 235, 1)',
                                        'rgba(255, 206, 86, 1)',
                                        'rgba(75, 192, 192, 1)',
                                        'rgba(153, 102, 255, 1)',
                                        'rgba(255, 159, 64, 1)'
                                    ],
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                        console.log('Chart created successfully');
                    } catch (error) {
                        console.error('Error creating chart:', error);
                    }
                } else {
                    console.error('Canvas element not found');
                }
            } else {
                console.error('Chart.js not available');
            }
        });
    </script>
</x-app-layout> 