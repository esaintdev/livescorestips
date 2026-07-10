@extends('admin.layout')

@section('page-title', 'Dashboard')

@section('content')
    <div class="as-stat-grid">
        <div class="as-stat-card">
            <div class="as-stat-icon orange">
                <i class="fa fa-futbol-o"></i>
            </div>
            <div class="as-stat-content">
                <div class="as-stat-label">Total Predictions</div>
                <div class="as-stat-value">{{ $totalPredictions }}</div>
            </div>
        </div>

        <div class="as-stat-card">
            <div class="as-stat-icon green">
                <i class="fa fa-trophy"></i>
            </div>
            <div class="as-stat-content">
                <div class="as-stat-label">Win Rate</div>
                <div class="as-stat-value">{{ $winRate }}%</div>
                <span class="as-stat-sub">{{ $wonPredictions }} Won / {{ $lostPredictions }} Lost</span>
            </div>
        </div>

        <div class="as-stat-card">
            <div class="as-stat-icon blue">
                <i class="fa fa-newspaper-o"></i>
            </div>
            <div class="as-stat-content">
                <div class="as-stat-label">Articles</div>
                <div class="as-stat-value">{{ $totalArticles }}</div>
            </div>
        </div>

        <div class="as-stat-card">
            <div class="as-stat-icon yellow">
                <i class="fa fa-users"></i>
            </div>
            <div class="as-stat-content">
                <div class="as-stat-label">Site Visits</div>
                <div class="as-stat-value">{{ $totalVisits }}</div>
                <span class="as-stat-sub">{{ $uniqueVisitors }} unique IPs</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">
                        <i class="fa fa-list-alt"></i> Recent Predictions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Match</th>
                                    <th>Tip</th>
                                    <th>Odds</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPredictions as $prediction)
                                    <tr>
                                        <td>{{ $prediction->home_team }} vs {{ $prediction->away_team }}</td>
                                        <td><span class="as-tip">{{ $prediction->tip }}</span></td>
                                        <td><span class="as-odds">{{ $prediction->odds }}</span></td>
                                        <td>
                                            @if($prediction->status == 'won')
                                                <span class="badge badge-success">Won</span>
                                            @elseif($prediction->status == 'lost')
                                                <span class="badge badge-danger">Lost</span>
                                            @else
                                                <span class="badge badge-warning">{{ ucfirst($prediction->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No predictions yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="text-right mt-3">
                        <a href="{{ route('admin.predictions.index') }}" class="as-link">
                            View All <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">
                        <i class="fa fa-pie-chart"></i> Performance Overview
                    </h6>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            var ctx = document.getElementById('performanceChart').getContext('2d');
            var myPieChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ["Won", "Lost", "Pending"],
                    datasets: [{
                        data: [{{ $wonPredictions }}, {{ $lostPredictions }}, {{ $pendingPredictions }}],
                        backgroundColor: ['#10b981', '#ef4444', '#ff6b00'],
                        borderColor: '#1a1a1a',
                        borderWidth: 4,
                        hoverOffset: 15
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                color: '#888',
                                padding: 25,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: {
                                    size: 12,
                                    weight: '600',
                                    family: "'Sofia Sans', sans-serif"
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#2a2a2a',
                            titleColor: '#ffffff',
                            bodyColor: '#e0e0e0',
                            borderColor: '#3d3d3d',
                            borderWidth: 1,
                            padding: 15,
                            displayColors: true,
                            cornerRadius: 10,
                            callbacks: {
                                label: function (context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += context.parsed + ' predictions';
                                    return label;
                                }
                            }
                        }
                    },
                    cutout: '75%',
                    animation: {
                        animateScale: true,
                        animateRotate: true
                    }
                },
            });
        </script>
    @endpush
@endsection
