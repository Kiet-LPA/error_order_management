@extends('layouts.master')
@section('title', 'Báo cáo điểm danh')

@section('content')
<style>
.card-header {
    background: #198754;
    color: #fff;
    border-bottom: none;
}
.card-header h5 {
    color: #fff;
}

.filter-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.stats-card {
    background: #007bff;
    color: white;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1rem;
}

.stats-card h3 {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.stats-card p {
    margin-bottom: 0;
    opacity: 0.9;
}

.chart-container {
    height: 300px;
    margin: 1rem 0;
}
</style>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">📊 Báo cáo điểm danh</h5>
    </div>
    <div class="card-body">
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('admin.checkin.reports') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="date_from" class="form-label fw-semibold">
                        <i class="bi bi-calendar me-1"></i>Từ ngày
                    </label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="{{ $dateFrom }}">
                </div>

                <div class="col-md-3">
                    <label for="date_to" class="form-label fw-semibold">
                        <i class="bi bi-calendar me-1"></i>Đến ngày
                    </label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="{{ $dateTo }}">
                </div>

                @if($user->isAdmin() || $user->isDirector())
                <div class="col-md-3">
                    <label for="department_id" class="form-label fw-semibold">
                        <i class="bi bi-building me-1"></i>Phòng ban
                    </label>
                    <select class="form-select" id="department_id" name="department_id">
                        <option value="">Tất cả phòng ban</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>Xem báo cáo
                        </button>
                        <button type="button" class="btn btn-success" onclick="exportReport()">
                            <i class="bi bi-download me-1"></i>Xuất Excel
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Summary Statistics -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <h3>{{ $reports['summary']['total_checkins'] }}</h3>
                    <p><i class="bi bi-calendar-check me-2"></i>Tổng điểm danh</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card" style="background: #ffc107; color: #000;">
                    <h3>{{ $reports['summary']['success_rate'] }}%</h3>
                    <p><i class="bi bi-graph-up me-2"></i>Tỷ lệ thành công</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card" style="background: #198754;">
                    <h3>{{ $reports['summary']['total_users'] }}</h3>
                    <p><i class="bi bi-people me-2"></i>Số nhân viên tham gia</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Daily Report -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">📅 Báo cáo theo ngày</h5>
            </div>
            <div class="card-body">
                @if($reports['daily']->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Ngày</th>
                                    <th>Tổng</th>
                                    <th>Thành công</th>
                                    <th>Checkin</th>
                                    <th>Checkout</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reports['daily'] as $date => $data)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($date)->format('d/m') }}</td>
                                    <td><span class="badge bg-primary">{{ $data['total'] }}</span></td>
                                    <td><span class="badge bg-success">{{ $data['success'] }}</span></td>
                                    <td><span class="badge bg-warning">{{ $data['checkin'] }}</span></td>
                                    <td><span class="badge bg-info">{{ $data['checkout'] }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center text-muted">Không có dữ liệu điểm danh trong khoảng thời gian này.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Department Report -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">🏢 Báo cáo theo phòng ban</h5>
            </div>
            <div class="card-body">
                @if($reports['department']->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Phòng ban</th>
                                    <th>Tổng</th>
                                    <th>Thành công</th>
                                    <th>Số NV</th>
                                    <th>Tỷ lệ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reports['department'] as $dept => $data)
                                @php
                                    $successRate = $data['total'] > 0 ? round(($data['success'] / $data['total']) * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td>{{ $dept }}</td>
                                    <td><span class="badge bg-primary">{{ $data['total'] }}</span></td>
                                    <td><span class="badge bg-success">{{ $data['success'] }}</span></td>
                                    <td><span class="badge bg-info">{{ $data['users'] }}</span></td>
                                    <td>
                                        <span class="badge bg-{{ $successRate >= 80 ? 'success' : ($successRate >= 60 ? 'warning' : 'danger') }}">
                                            {{ $successRate }}%
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center text-muted">Không có dữ liệu phòng ban trong khoảng thời gian này.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
@if($reports['daily']->count() > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">📈 Biểu đồ thống kê</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h6>Điểm danh theo ngày</h6>
                        <div class="chart-container">
                            <canvas id="dailyChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6>Phân bố theo ca</h6>
                        <div class="chart-container">
                            <canvas id="sessionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Daily Chart
@if($reports['daily']->count() > 0)
const dailyCtx = document.getElementById('dailyChart').getContext('2d');
const dailyData = @json($reports['daily']);

new Chart(dailyCtx, {
    type: 'line',
    data: {
        labels: Object.keys(dailyData).map(date => new Date(date).toLocaleDateString('vi-VN')),
        datasets: [
            {
                label: 'Tổng điểm danh',
                data: Object.values(dailyData).map(item => item.total),
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4
            },
            {
                label: 'Thành công',
                data: Object.values(dailyData).map(item => item.success),
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4
            }
        ]
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

// Session Chart
const sessionCtx = document.getElementById('sessionChart').getContext('2d');
const sessionData = @json($reports['daily']);

let checkinTotal = 0;
let checkoutTotal = 0;

Object.values(sessionData).forEach(item => {
    checkinTotal += item.checkin;
    checkoutTotal += item.checkout;
});

new Chart(sessionCtx, {
    type: 'doughnut',
    data: {
        labels: ['Checkin', 'Checkout'],
        datasets: [{
            data: [checkinTotal, checkoutTotal],
            backgroundColor: ['#ffc107', '#17a2b8']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
@endif

function exportReport() {
    // Create export URL with current filters
    const params = new URLSearchParams({
        date_from: '{{ $dateFrom }}',
        date_to: '{{ $dateTo }}',
        @if($departmentId)
        department_id: '{{ $departmentId }}',
        @endif
        export: 'excel'
    });
    
    window.open(`{{ route('admin.checkin.reports') }}?${params.toString()}`, '_blank');
}
</script>
@endsection
