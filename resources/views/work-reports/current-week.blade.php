@extends('layouts.master')
@section('title', 'Báo cáo tuần này')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="fas fa-calendar-week text-primary"></i>
                    Báo cáo tuần này
                </h2>
                <a href="{{ route('work-reports.select-date') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Tạo báo cáo mới
                </a>
            </div>
        </div>
    </div>

    <!-- Thông tin tuần -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-info-circle text-info"></i>
                        Thông tin tuần
                    </h5>
                    <div class="row">
                        <div class="col-6">
                            <p class="mb-1"><strong>Tuần:</strong> {{ $weekInfo['week_of_year'] }}</p>
                            <p class="mb-1"><strong>Năm:</strong> {{ $weekInfo['year'] }}</p>
                            <p class="mb-1"><strong>Tháng:</strong> {{ $weekInfo['month'] }}</p>
                        </div>
                        <div class="col-6">
                            <p class="mb-1"><strong>Bắt đầu:</strong> {{ $weekDates['start_formatted'] }}</p>
                            <p class="mb-1"><strong>Kết thúc:</strong> {{ $weekDates['end_formatted'] }}</p>
                            <p class="mb-0"><strong>Tuần thứ:</strong> {{ $weekInfo['week_of_month'] }} của tháng</p>
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
                        Thống kê tuần
                    </h5>
                                         <div class="row text-center">
                         <div class="col-6">
                             <div class="stat-item">
                                 <h3 class="text-primary">{{ $totalReports }}</h3>
                                 <small>Báo cáo đã tạo</small>
                             </div>
                         </div>
                         <div class="col-6">
                             <div class="stat-item">
                                 <h3 class="text-success">{{ $completedDays }}</h3>
                                 <small>Ngày có báo cáo</small>
                             </div>
                         </div>
                     </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Danh sách báo cáo -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list"></i>
                        Báo cáo trong tuần này
                    </h5>
                </div>
                <div class="card-body">
                    @if($reports->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Ngày</th>
                                        <th>Nội dung công việc</th>
                                        <th>Thời gian tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reports as $report)
                                        <tr>
                                            <td>
                                                <strong>{{ \Carbon\Carbon::parse($report->report_date)->format('d/m/Y') }}</strong>
                                                <br>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($report->report_date)->format('l') }}</small>
                                            </td>
                                            <td>
                                                <div class="report-content">
                                                    {{ Str::limit($report->daily_work, 100) }}
                                                    @if(strlen($report->daily_work) > 100)
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
                                                                        <td>
                                <small class="text-muted">
                                    {{ $report->created_at->format('d/m/Y H:i') }}
                                </small>
                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('work-reports.edit', $report) }}" 
                                                       class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger btn-sm"
                                                            onclick="deleteReport({{ $report->id }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có báo cáo nào trong tuần này</h5>
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

.report-content, .report-content-full {
    max-width: 400px;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>
@endsection
