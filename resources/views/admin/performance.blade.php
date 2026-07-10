@extends('admin.layout')

@section('page-title', 'Performance Tracker')

@section('content')
    <div class="as-stat-grid">
        <div class="as-stat-card">
            <div class="as-stat-icon orange">
                <i class="fa fa-trophy"></i>
            </div>
            <div class="as-stat-content">
                <div class="as-stat-label">Total Predictions</div>
                <div class="as-stat-value">{{ $overallStats['total'] }}</div>
            </div>
        </div>
        <div class="as-stat-card">
            <div class="as-stat-icon green">
                <i class="fa fa-check-circle"></i>
            </div>
            <div class="as-stat-content">
                <div class="as-stat-label">Won</div>
                <div class="as-stat-value">{{ $overallStats['won'] }}</div>
            </div>
        </div>
        <div class="as-stat-card">
            <div class="as-stat-icon red">
                <i class="fa fa-times-circle"></i>
            </div>
            <div class="as-stat-content">
                <div class="as-stat-label">Lost</div>
                <div class="as-stat-value">{{ $overallStats['lost'] }}</div>
            </div>
        </div>
        <div class="as-stat-card">
            <div class="as-stat-icon orange">
                <i class="fa fa-percent"></i>
            </div>
            <div class="as-stat-content">
                <div class="as-stat-label">Accuracy</div>
                <div class="as-stat-value">{{ $overallStats['accuracy'] }}%</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">
                        <i class="fa fa-line-chart"></i> Monthly Performance
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">
                        <i class="fa fa-pie-chart"></i> Win/Loss Distribution
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="winLossChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">
                        <i class="fa fa-bar-chart"></i> Daily Accuracy (Last 30 Days)
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="dailyChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyChart = new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_keys($monthlyStats->toArray())) !!},
                datasets: [{
                    label: 'Accuracy %',
                    data: {!! json_encode(array_column($monthlyStats->toArray(), 'accuracy')) !!},
                    borderColor: '#ff6b00',
                    backgroundColor: 'rgba(255, 107, 0, 0.08)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#ff6b00',
                    pointBorderColor: '#1a1a1a',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { color: '#888' }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            color: '#888',
                            callback: function (value) { return value + '%'; }
                        },
                        grid: { color: 'rgba(255, 255, 255, 0.04)' }
                    },
                    x: {
                        ticks: { color: '#888' },
                        grid: { color: 'rgba(255, 255, 255, 0.04)' }
                    }
                }
            }
        });

        const winLossCtx = document.getElementById('winLossChart').getContext('2d');
        const winLossChart = new Chart(winLossCtx, {
            type: 'doughnut',
            data: {
                labels: ['Won', 'Lost', 'Pending'],
                datasets: [{
                    data: [
                        {{ $overallStats['won'] }},
                        {{ $overallStats['lost'] }},
                        {{ $overallStats['pending'] }}
                    ],
                    backgroundColor: ['#10b981', '#ef4444', '#ff6b00'],
                    borderColor: '#1a1a1a',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#888',
                            padding: 15,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: { size: 12, weight: '600' }
                        }
                    }
                },
                cutout: '70%'
            }
        });

        const dailyCtx = document.getElementById('dailyChart').getContext('2d');
        const dailyChart = new Chart(dailyCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_keys($dailyStats->toArray())) !!},
                datasets: [{
                    label: 'Accuracy %',
                    data: {!! json_encode(array_column($dailyStats->toArray(), 'accuracy')) !!},
                    backgroundColor: function (context) {
                        const value = context.parsed.y;
                        return value >= 50 ? 'rgba(255, 107, 0, 0.7)' : 'rgba(239, 68, 68, 0.7)';
                    },
                    borderColor: function (context) {
                        const value = context.parsed.y;
                        return value >= 50 ? '#ff6b00' : '#ef4444';
                    },
                    borderWidth: 2,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { color: '#888' }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            color: '#888',
                            callback: function (value) { return value + '%'; }
                        },
                        grid: { color: 'rgba(255, 255, 255, 0.04)' }
                    },
                    x: {
                        ticks: {
                            color: '#888',
                            maxRotation: 45,
                            minRotation: 45
                        },
                        grid: { display: false }
                    }
                }
            }
        });
    </script>
@endpush
