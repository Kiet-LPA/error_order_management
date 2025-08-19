@extends('layouts.master')
@section('title','Báo cáo tổng quan')

@section('content')
<style>
.report-card {
    border-radius: 18px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    background: #fff;
    padding: 24px;
    margin-bottom: 24px;
    height: auto;
    min-height: 300px;
}
.stat-card {
    border-radius: 16px;
    background: #f8fafc;
    text-align: center;
    padding: 18px 0 10px 0;
    margin-bottom: 16px;
    box-shadow: 0 1px 6px rgba(0,0,0,0.04);
}
.stat-card h2 { font-size: 2.2rem; font-weight: 700; margin-bottom: 4px; }
.stat-card .stat-label { font-size: 1rem; color: #888; }
.stat-blue { color: #558EC1; }
.stat-green { color: #22c55e; }
.stat-yellow { color: #facc15; }
.stat-red { color: #ef4444; }
.top-employee-avatar {
    width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; color: #fff; margin-right: 8px;
}
.efficiency-badge { border-radius: 8px; padding: 2px 10px; color: #fff; font-weight: 500; }
.eff-green { background: #22c55e; }
.eff-yellow { background: #facc15; color: #333; }
.eff-orange { background: #f59e42; }
.eff-red { background: #ef4444; }
.table-report th, .table-report td { vertical-align: middle; }
.table-report { border-radius: 12px; overflow: hidden; background: #fff; }

/* Chart container styles */
.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
    max-width: 100%;
    overflow: hidden;
}

.chart-container canvas {
    max-height: 300px !important;
    max-width: 100% !important;
}

/* Donut chart specific */
.donut-container {
    position: relative;
    height: 250px;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.donut-container canvas {
    max-height: 250px !important;
    max-width: 350px !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .chart-container {
        height: 250px;
    }
    .donut-container {
        height: 200px;
    }
    .report-card {
        min-height: 250px;
    }
}
</style>

<div class="mb-4">
    <h3 class="mb-3"><i class="bi bi-bar-chart-fill me-2"></i>Báo cáo tổng quan</h3>
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="stat-card">
                <h2 class="stat-blue">{{ $summary['total'] }}</h2>
                <div class="stat-label">Tổng công việc</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h2 class="stat-green">{{ $summary['finished'] }}</h2>
                <div class="stat-label">Hoàn thành</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h2 class="stat-yellow">{{ $summary['doing'] }}</h2>
                <div class="stat-label">Đang làm</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h2 class="stat-red">{{ $summary['overdue'] }}</h2>
                <div class="stat-label">Trễ hạn</div>
            </div>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="report-card">
                <h6 class="mb-3"><i class="bi bi-pie-chart me-2"></i>Phân bố trạng thái công việc</h6>
                <div class="donut-container">
                    <canvas id="statusDonut"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="report-card">
                <h6 class="mb-3"><i class="bi bi-graph-up-arrow me-2"></i>Tiến độ hoàn thành theo tuần</h6>
                <div class="chart-container">
                    <canvas id="weeklyLine"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="report-card">
                <h6 class="mb-3"><i class="bi bi-trophy me-2"></i>Top nhân viên xuất sắc</h6>
                @if(count($topEmployees) > 0)
                    <table class="table table-borderless align-middle mb-0">
                        <thead>
                            <tr><th>Nhân viên</th><th>Công việc hoàn thành</th><th>Hiệu suất</th></tr>
                        </thead>
                        <tbody>
                            @foreach($topEmployees as $emp)
                            <tr>
                                <td>
                                    <span class="top-employee-avatar" style="background: #{{ $emp['color'] }};">{{ $emp['initials'] }}</span>
                                    {{ $emp['name'] }}
                                </td>
                                <td>{{ $emp['done'] }}</td>
                                <td><span class="efficiency-badge {{ $emp['effClass'] }}">{{ $emp['eff'] }}%</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-people display-4"></i>
                        <p class="mt-2">Chưa có dữ liệu nhân viên</p>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-lg-6">
            <div class="report-card">
                <h6 class="mb-3"><i class="bi bi-bar-chart-steps me-2"></i>Phân tích theo phòng ban</h6>
                <div class="chart-container">
                    <canvas id="deptBar"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="report-card mt-4">
        <h6 class="mb-3"><i class="bi bi-list-ul me-2"></i>Báo cáo chi tiết</h6>
        @if(count($deptReport) > 0)
            <div class="table-responsive">
                <table class="table table-report mb-0">
                    <thead>
                        <tr>
                            <th>Phòng ban</th><th>Tổng công việc</th><th>Hoàn thành</th><th>Đang làm</th><th>Trễ hạn</th><th>Hiệu suất</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deptReport as $dept)
                        <tr>
                            <td>{{ $dept['name'] }}</td>
                            <td>{{ $dept['total'] }}</td>
                            <td>{{ $dept['finished'] }}</td>
                            <td>{{ $dept['doing'] }}</td>
                            <td>{{ $dept['overdue'] }}</td>
                            <td><span class="efficiency-badge {{ $dept['effClass'] }}">{{ $dept['eff'] }}%</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center text-muted py-4">
                <i class="bi bi-building display-4"></i>
                <p class="mt-2">Chưa có dữ liệu phòng ban</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    // Status distribution chart
    const donutCtx = document.getElementById('statusDonut');
    if (donutCtx) {
        const donut = new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: ['Hoàn thành', 'Đang làm', 'Chờ duyệt', 'Từ chối', 'Trễ hạn'],
                datasets: [{
                    data: [
                        {{ $summary['finished'] }},
                        {{ $summary['doing'] }},
                        {{ $summary['completed'] }},
                        {{ $summary['rejected'] }},
                        {{ $summary['overdue'] }}
                    ],
                    backgroundColor: ['#22c55e', '#558EC1', '#facc15', '#ef4444', '#f97316']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            boxWidth: 10
                        }
                    }
                }
            }
        });
    }

    // Weekly progress chart
    const weeklyCtx = document.getElementById('weeklyLine');
    if (weeklyCtx) {
        const weekly = new Chart(weeklyCtx, {
            type: 'line',
            data: {
                labels: @json($weekly['labels']),
                datasets: [{
                    label: 'Hoàn thành',
                    data: @json($weekly['values']),
                    fill: true,
                    tension: 0.3,
                    borderColor: '#558EC1',
                    backgroundColor: 'rgba(85, 142, 193, 0.1)',
                    pointBackgroundColor: '#558EC1',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
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
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // Department analysis chart
    const deptCtx = document.getElementById('deptBar');
    if (deptCtx) {
        const dept = new Chart(deptCtx, {
            type: 'bar',
            data: {
                labels: @json(array_keys($byDept->toArray())),
                datasets: [{
                    label: 'Số việc',
                    data: @json(array_values($byDept->toArray())),
                    backgroundColor: '#558EC1',
                    borderRadius: 6,
                    borderSkipped: false
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
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
