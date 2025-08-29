@extends('layouts.master')
@section('title', 'Hoạt động của tôi')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="bi bi-graph-up text-primary"></i>
                    Hoạt động của tôi
                </h2>
                <div class="d-flex gap-2">
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="timeRangeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-calendar"></i>
                            {{ $selectedDays }} ngày gần nhất
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="timeRangeDropdown">
                            <li><a class="dropdown-item {{ $selectedDays == 30 ? 'active' : '' }}" href="{{ route('work-reports.my-activity', ['days' => 30]) }}">
                                <i class="bi bi-calendar-day"></i> 30 ngày gần nhất
                            </a></li>
                            <li><a class="dropdown-item {{ $selectedDays == 60 ? 'active' : '' }}" href="{{ route('work-reports.my-activity', ['days' => 60]) }}">
                                <i class="bi bi-calendar-week"></i> 60 ngày gần nhất
                            </a></li>
                            <li><a class="dropdown-item {{ $selectedDays == 90 ? 'active' : '' }}" href="{{ route('work-reports.my-activity', ['days' => 90]) }}">
                                <i class="bi bi-calendar-month"></i> 90 ngày gần nhất
                            </a></li>
                        </ul>
                    </div>
                    <a href="{{ route('work-reports.select-date') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i>
                        Tạo báo cáo mới
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="bi bi-file-text fa-2x text-primary"></i>
                    </div>
                    <h3 class="stat-number">{{ $totalReports }}</h3>
                    <p class="stat-label">Tổng báo cáo</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="bi bi-calendar-week fa-2x text-success"></i>
                    </div>
                    <h3 class="stat-number">{{ $thisWeekReports }}</h3>
                    <p class="stat-label">Tuần này</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="bi bi-calendar-month fa-2x text-info"></i>
                    </div>
                    <h3 class="stat-number">{{ $thisMonthReports }}</h3>
                    <p class="stat-label">Tháng này</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="bi bi-graph-up fa-2x text-warning"></i>
                    </div>
                    <h3 class="stat-number">{{ $recentReports->count() }}</h3>
                    <p class="stat-label">{{ $selectedDays }} ngày qua</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Báo cáo gần đây -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-history"></i>
                        Báo cáo gần đây ({{ $selectedDays }} ngày qua)
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentReports->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                                                 <thead>
                                     <tr>
                                         <th>Ngày</th>
                                         <th>Nội dung</th>
                                         <th>Trạng thái</th>
                                     </tr>
                                 </thead>
                                <tbody>
                                    @foreach($recentReports as $report)
                                        <tr>
                                            <td>
                                                <strong>{{ \Carbon\Carbon::parse($report->report_date)->format('d/m/Y') }}</strong>
                                                <br>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($report->report_date)->format('l') }}</small>
                                            </td>
                                            <td>
                                                <div class="report-content">
                                                    {{ Str::limit($report->daily_work, 80) }}
                                                    @if(strlen($report->daily_work) > 80)
                                                        <button class="btn btn-link btn-sm p-0" 
                                                                onclick="toggleReportContent({{ $report->id }})">
                                                            Xem thêm
                                                        </button>
                                                    @endif
                                                </div>
                                                <div class="report-content-full" id="report-{{ $report->id }}" style="display: none;">
                                                    {{ $report->daily_work }}
                                                    <button class="btn btn-link btn-sm p-0" 
                                                            onclick="toggleReportContent({{ $report->id }})">
                                                        Thu gọn
                                                    </button>
                                                                                                     </div>
                                                 </td>
                                                 <td class="text-center">
                                                @if($report->rejected_at)
                                                    <i class="bi bi-x-circle-fill text-danger" title="Báo cáo bị từ chối bởi admin"></i>
                                                @elseif($report->is_read)
                                                    <i class="bi bi-check-circle-fill text-success" title="Admin đã xem báo cáo này"></i>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-file-text fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có báo cáo nào trong {{ $selectedDays }} ngày qua</h5>
                            <p class="text-muted">Hãy tạo báo cáo để bắt đầu theo dõi hoạt động</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Thống kê theo tuần -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-bar-chart"></i>
                        Thống kê theo tuần
                    </h5>
                </div>
                <div class="card-body">
                    @if($weeklyStats->count() > 0)
                        @foreach($weeklyStats as $stat)
                            <div class="week-stat-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="week-label">Tuần {{ $stat->week }} ({{ $stat->year }})</span>
                                    <span class="week-count">{{ $stat->report_count }} báo cáo</span>
                                </div>
                                <div class="progress mt-1" style="height: 6px;">
                                    @php
                                        $maxReports = $weeklyStats->max('report_count');
                                        $percentage = $maxReports > 0 ? ($stat->report_count / $maxReports) * 100 : 0;
                                    @endphp
                                    <div class="progress-bar bg-primary" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">Chưa có dữ liệu thống kê</p>
                    @endif
                </div>
            </div>

            <!-- Thống kê theo tháng -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-pie-chart"></i>
                        Thống kê theo tháng
                    </h5>
                </div>
                <div class="card-body">
                    @if($monthlyStats->count() > 0)
                        @foreach($monthlyStats as $stat)
                            <div class="month-stat-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="month-label">
                                        {{ \Carbon\Carbon::createFromDate($stat->year, $stat->month, 1)->format('M Y') }}
                                    </span>
                                    <span class="month-count">{{ $stat->report_count }} báo cáo</span>
                                </div>
                                <div class="progress mt-1" style="height: 6px;">
                                    @php
                                        $maxReports = $monthlyStats->max('report_count');
                                        $percentage = $maxReports > 0 ? ($stat->report_count / $maxReports) * 100 : 0;
                                    @endphp
                                    <div class="progress-bar bg-success" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">Chưa có dữ liệu thống kê</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleReportContent(reportId) {
    const content = document.getElementById(`report-${reportId}`);
    const isHidden = content.style.display === 'none';
    
    if (isHidden) {
        content.style.display = 'block';
        content.previousElementSibling.style.display = 'none';
    } else {
        content.style.display = 'none';
        content.previousElementSibling.style.display = 'block';
    }
}

function deleteReport(reportId) {
    if (confirm('Bạn có chắc chắn muốn xóa báo cáo này?')) {
        fetch(`/work-reports/${reportId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                window.location.reload();
            } else {
                alert('Có lỗi xảy ra khi xóa báo cáo');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi xóa báo cáo');
        });
    }
}
</script>

<style>
.stat-icon {
    margin-bottom: 15px;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 5px;
}

.stat-label {
    color: #6c757d;
    margin-bottom: 0;
    font-size: 0.9rem;
}

.week-stat-item, .month-stat-item {
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e9ecef;
}

.week-stat-item:last-child, .month-stat-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.week-label, .month-label {
    font-weight: 500;
    color: #495057;
}

.week-count, .month-count {
    font-weight: bold;
    color: #007bff;
}

.report-content, .report-content-full {
    max-width: 300px;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.progress {
    background-color: #e9ecef;
}

.dropdown-item.active {
    background-color: #007bff;
    color: white;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
}

.dropdown-item.active:hover {
    background-color: #0056b3;
}
</style>
@endsection
