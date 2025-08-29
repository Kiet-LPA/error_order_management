@extends('layouts.master')
@section('title', 'Báo cáo tháng này')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="fas fa-calendar-alt text-primary"></i>
                    Báo cáo tháng này
                </h2>
                <a href="{{ route('work-reports.select-date') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Tạo báo cáo mới
                </a>
            </div>
        </div>
    </div>

    <!-- Thông tin tháng -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-info-circle text-info"></i>
                        Thông tin tháng
                    </h5>
                    <div class="row">
                        <div class="col-6">
                            <p class="mb-1"><strong>Tháng:</strong> {{ $monthInfo['month_name'] }}</p>
                            <p class="mb-1"><strong>Năm:</strong> {{ $monthInfo['year'] }}</p>
                            <p class="mb-1"><strong>Số ngày:</strong> {{ $monthInfo['days_in_month'] }}</p>
                        </div>
                        <div class="col-6">
                            <p class="mb-1"><strong>Số tuần:</strong> {{ $monthInfo['weeks_in_month'] }}</p>
                            <p class="mb-1"><strong>Tuần có báo cáo:</strong> {{ $totalWeeks }}</p>
                            <p class="mb-0"><strong>Ngày có báo cáo:</strong> {{ $completedDays }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-chart-pie text-success"></i>
                        Thống kê tháng
                    </h5>
                                         <div class="row text-center">
                         <div class="col-6">
                             <div class="stat-item">
                                 <h3 class="text-primary">{{ $totalReports }}</h3>
                                 <small>Tổng báo cáo</small>
                             </div>
                         </div>
                         <div class="col-6">
                             <div class="stat-item">
                                 <h3 class="text-success">{{ $totalWeeks }}</h3>
                                 <small>Tuần có báo cáo</small>
                             </div>
                         </div>
                     </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Báo cáo theo tuần -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list"></i>
                        Báo cáo theo tuần
                    </h5>
                </div>
                <div class="card-body">
                    @if($reportsByWeek->count() > 0)
                        @foreach($reportsByWeek as $week => $weekReports)
                            <div class="week-section mb-4">
                                <h6 class="week-title">
                                    <i class="fas fa-calendar-week text-primary"></i>
                                    Tuần {{ $week }} ({{ $weekReports->count() }} báo cáo)
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                                                                 <thead class="table-light">
                                             <tr>
                                                                                         <th>Ngày</th>
                                         <th>Nội dung công việc</th>
                                         <th>Trạng thái</th>
                                             </tr>
                                         </thead>
                                        <tbody>
                                            @foreach($weekReports as $report)
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
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có báo cáo nào trong tháng này</h5>
                            <p class="text-muted">Hãy tạo báo cáo đầu tiên để bắt đầu theo dõi công việc của bạn</p>
                            <a href="{{ route('work-reports.select-date') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                                Tạo báo cáo đầu tiên
                            </a>
                        </div>
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
.stat-item h3 {
    margin-bottom: 0;
    font-weight: bold;
}

.progress {
    height: 8px;
}

.week-section {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    background-color: #f8f9fa;
}

.week-title {
    color: #495057;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 2px solid #dee2e6;
}

.report-content, .report-content-full {
    max-width: 300px;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.table-sm td, .table-sm th {
    padding: 0.5rem;
}
</style>
@endsection
