@extends('layouts.master')
@section('title', 'Quản lý điểm danh')

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

.stats-card {
    background: #007bff;
    color: white;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
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

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-success {
    background: #d4edda;
    color: #155724;
}

.status-failed {
    background: #f8d7da;
    color: #721c24;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}
</style>

{{-- Statistics Section --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <h3>{{ $stats['total_checkins'] }}</h3>
            <p><i class="bi bi-calendar-check me-2"></i>Tổng điểm danh</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card" style="background: #dc3545;">
            <h3>{{ $stats['today_checkins'] }}</h3>
            <p><i class="bi bi-calendar-day me-2"></i>Hôm nay</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card" style="background: #198754;">
            <h3>{{ $stats['today_success'] }}</h3>
            <p><i class="bi bi-check-circle me-2"></i>Thành công hôm nay</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card" style="background: #ffc107; color: #000;">
            <h3>{{ $stats['success_rate'] }}%</h3>
            <p><i class="bi bi-graph-up me-2"></i>Tỷ lệ thành công</p>
        </div>
    </div>
</div>

{{-- Quick Actions --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">⚡ Thao tác nhanh</h5>
    </div>
    <div class="card-body">
        <div class="quick-actions">
            <a href="{{ route('admin.checkin.manage') }}" class="btn btn-primary">
                <i class="bi bi-list-ul me-1"></i>Quản lý điểm danh
            </a>
            <a href="{{ route('admin.checkin.gps-requests') }}" class="btn btn-warning">
                <i class="bi bi-geo-alt me-1"></i>GPS Requests ({{ $pendingGpsRequests->count() }})
            </a>
            <a href="{{ route('admin.checkin.reports') }}" class="btn btn-success">
                <i class="bi bi-graph-up me-1"></i>Báo cáo
            </a>
            @if($user->isAdmin() || $user->isDirector())
                <button onclick="getCurrentLocationAdmin()" class="btn btn-info">
                    <i class="bi bi-geo me-1"></i>Lấy GPS hiện tại
                </button>
            @endif
        </div>
        
        <!-- GPS Tool for Admin -->
        <div id="adminGpsResult" style="margin-top: 1rem; display: none;"></div>
    </div>
</div>

{{-- Recent Check-ins --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">📋 Điểm danh gần đây (7 ngày)</h5>
    </div>
    <div class="card-body">
        @if($recentCheckins->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nhân viên</th>
                            <th>Phòng ban</th>
                            <th>Ngày</th>
                            <th>Ca</th>
                            <th>Giờ</th>
                            <th>Khoảng cách</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentCheckins as $checkin)
                        <tr>
                            <td>
                                <strong>{{ $checkin->user->name }}</strong><br>
                                <small>{{ $checkin->user->email }}</small>
                            </td>
                            <td>{{ $checkin->department->name ?? 'N/A' }}</td>
                            <td>{{ \Carbon\Carbon::parse($checkin->checkin_date)->format('d/m/Y') }}</td>
                            <td>{{ $checkin->session === 'morning' ? '🌅 Sáng' : '🌆 Chiều' }}</td>
                            <td>{{ \Carbon\Carbon::parse($checkin->checkin_time)->format('H:i') }}</td>
                            <td>{{ $checkin->distance_meters ? round($checkin->distance_meters) . 'm' : 'N/A' }}</td>
                            <td>
                                <span class="status-badge status-{{ $checkin->status }}">
                                    {{ $checkin->status === 'success' ? '✅ Thành công' : '❌ Thất bại' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-center text-muted">Chưa có dữ liệu điểm danh gần đây.</p>
        @endif
    </div>
</div>

{{-- Pending GPS Requests --}}
@if($pendingGpsRequests->count() > 0)
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">⏳ Các yêu cầu GPS chờ duyệt</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nhân viên</th>
                        <th>Phòng ban</th>
                        <th>Ngày</th>
                        <th>Khoảng cách</th>
                        <th>Mã GPS</th>
                        <th>Thời gian</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingGpsRequests as $gpsRequest)
                    <tr>
                        <td>
                            <strong>{{ $gpsRequest->user->name }}</strong><br>
                            <small>{{ $gpsRequest->user->email }}</small>
                        </td>
                        <td>{{ $gpsRequest->department->name ?? 'N/A' }}</td>
                        <td>{{ \Carbon\Carbon::parse($gpsRequest->request_date)->format('d/m/Y') }}</td>
                        <td>{{ round($gpsRequest->distance_meters) }}m</td>
                        <td><code>{{ $gpsRequest->gps_code }}</code></td>
                        <td>{{ \Carbon\Carbon::parse($gpsRequest->created_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <form action="{{ route('admin.checkin.approve-gps', $gpsRequest) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Duyệt GPS request này?')">
                                        <i class="bi bi-check"></i> Duyệt
                                    </button>
                                </form>
                                <form action="{{ route('admin.checkin.approve-gps', $gpsRequest) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Từ chối GPS request này?')">
                                        <i class="bi bi-x"></i> Từ chối
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="text-center mt-3">
            <a href="{{ route('admin.checkin.gps-requests') }}" class="btn btn-primary">Xem tất cả GPS Requests</a>
        </div>
    </div>
</div>
@endif

<script>
// Get current location for Admin (to create regions)
async function getCurrentLocationAdmin() {
    const resultDiv = document.getElementById('adminGpsResult');
    resultDiv.style.display = 'block';
    
    if (!navigator.geolocation) {
        resultDiv.innerHTML = '<div class="alert alert-danger">❌ GPS không được hỗ trợ trên thiết bị này</div>';
        return;
    }

        resultDiv.innerHTML = `
            <div class="alert alert-info">
                <h6>🔄 Đang lấy vị trí GPS chính xác...</h6>
                <p>Vui lòng cho phép truy cập vị trí khi trình duyệt hỏi.</p>
                <p><strong>Lưu ý:</strong> Để có kết quả chính xác nhất, hãy di chuyển ra ngoài trời và bật GPS/WiFi.</p>
            </div>
        `;

        try {
            const position = await new Promise((resolve, reject) => {
                navigator.geolocation.getCurrentPosition(resolve, reject, {
                    enableHighAccuracy: true,
                    timeout: 30000,
                    maximumAge: 0
                });
            });

        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        const accuracy = position.coords.accuracy;
        
        const mapsUrl = `https://www.google.com/maps?q=${lat},${lng}&t=satellite&z=18`;
        
        resultDiv.innerHTML = `
            <div class="alert alert-success">
                <h5>✅ Lấy GPS thành công!</h5>
                <p><strong>Tọa độ hiện tại:</strong></p>
                <div class="bg-light p-3 rounded mb-3">
                    <strong>Vĩ độ:</strong> ${lat.toFixed(8)}<br>
                    <strong>Kinh độ:</strong> ${lng.toFixed(8)}
                </div>
                <p><strong>Độ chính xác:</strong> ±${Math.round(accuracy)}m</p>
                <p><strong>Thời gian:</strong> ${new Date().toLocaleString('vi-VN')}</p>
                ${accuracy > 50 ? '<p class="text-warning">⚠️ Độ chính xác thấp, thử di chuyển ra ngoài trời</p>' : ''}
                
                <div class="mt-3">
                    <a href="${mapsUrl}" target="_blank" class="btn btn-primary me-2">
                        🗺️ Xem trên Google Maps
                    </a>
                    <a href="{{ route('departments.create') }}?lat=${lat.toFixed(8)}&lng=${lng.toFixed(8)}" class="btn btn-success">
                        ➕ Tạo phòng ban tại đây
                    </a>
                </div>
            </div>
        `;
        
    } catch (error) {
        let message = 'Không thể lấy vị trí GPS';
        switch (error.code) {
            case error.PERMISSION_DENIED:
                message = 'Bạn đã từ chối truy cập GPS. Vui lòng cho phép truy cập vị trí trong trình duyệt.';
                break;
            case error.POSITION_UNAVAILABLE:
                message = 'GPS không khả dụng. Vui lòng kiểm tra GPS và thử lại.';
                break;
            case error.TIMEOUT:
                message = 'Hết thời gian chờ GPS. Vui lòng thử lại.';
                break;
        }
        resultDiv.innerHTML = `<div class="alert alert-danger">❌ ${message}</div>`;
    }
}
</script>
@endsection
