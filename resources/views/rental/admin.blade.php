@extends('layouts.master')

@section('title', 'Quản lý xe - HPFoods')

@section('content')
<style>
.stat-card {
    background: #007bff;
    color: white;
    border-radius: 15px;
    transition: transform 0.2s ease-in-out;
}

.stat-card.card-1 {
    background: #667eea;
}

.stat-card.card-2 {
    background: #f5576c;
}

.stat-card.card-3 {
    background: #4facfe;
}

.stat-card.card-4 {
    background: #43e97b;
}

.stat-card.card-5 {
    background: #fa709a;
}

.stat-card.card-6 {
    background: #a8edea;
    color: #333;
}
.stat-card:hover {
    transform: translateY(-5px);
}
.stat-icon {
    font-size: 2.5rem;
    opacity: 0.8;
}
.overdue-card {
    border-left: 4px solid #dc3545;
}
.recent-card {
    border-left: 4px solid #007bff;
}
.btn-action {
    border-radius: 20px;
    padding: 0.5rem 1.5rem;
}
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="bi bi-tools me-2"></i>Quản lý xe HPFoods
                </h2>
                <div>
                    @if(auth()->user()->canManageCars())
                    <div class="dropdown d-inline-block me-2">
                        <button class="btn btn-success btn-action dropdown-toggle" type="button" id="managementDropdown" data-bs-toggle="dropdown" data-bs-auto-close="false" aria-expanded="false">
                            <i class="bi bi-gear me-1"></i>Quản lý
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="managementDropdown" style="min-width: 200px;">
                            <li class="dropdown-header">Quản lý hệ thống</li>
                            <li>
                                <a class="dropdown-item" href="{{ route('rental.cars.index') }}">
                                    <i class="bi bi-car-front me-2"></i>Quản lý xe
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('users.index') }}">
                                    <i class="bi bi-people me-2"></i>Quản lý người dùng
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('rental.admin') }}">
                                    <i class="bi bi-list-ul me-2"></i>Quản lý thuê xe
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('rental.extensions.index') }}">
                                    <i class="bi bi-clock-history me-2"></i>Duyệt gia hạn
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endif
                    
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-info btn-action dropdown-toggle" type="button" id="rentalDropdown" data-bs-toggle="dropdown" data-bs-auto-close="false" aria-expanded="false">
                            <i class="bi bi-car-front me-1"></i>Thuê xe
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="rentalDropdown" style="min-width: 180px;">
                            <li class="dropdown-header">Thuê xe</li>
                            <li>
                                <a class="dropdown-item" href="{{ route('rental.index') }}">
                                    <i class="bi bi-car-front me-2"></i>Thuê xe mới
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('rental.my-rentals') }}">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Lịch sử thuê xe
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-6 col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="card stat-card card-1">
                        <div class="card-body text-center">
                            <div class="stat-icon">
                                <i class="bi bi-car-front"></i>
                            </div>
                            <h3 class="mb-0">{{ $stats['total_cars'] }}</h3>
                            <small>Tổng xe</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="card stat-card card-2">
                        <div class="card-body text-center">
                            <div class="stat-icon">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <h3 class="mb-0">{{ $stats['available_cars'] }}</h3>
                            <small>Có sẵn</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="card stat-card card-3">
                        <div class="card-body text-center">
                            <div class="stat-icon">
                                <i class="bi bi-car-front-fill"></i>
                            </div>
                            <h3 class="mb-0">{{ $stats['rented_cars'] }}</h3>
                            <small>Đang thuê</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="card stat-card card-4">
                        <div class="card-body text-center">
                            <div class="stat-icon">
                                <i class="bi bi-people"></i>
                            </div>
                            <h3 class="mb-0">{{ $stats['total_users'] }}</h3>
                            <small>Người dùng</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="card stat-card card-5">
                        <div class="card-body text-center">
                            <div class="stat-icon">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <h3 class="mb-0">{{ $stats['active_rentals'] }}</h3>
                            <small>Thuê active</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="card stat-card card-6">
                        <div class="card-body text-center">
                            <div class="stat-icon">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                            <h3 class="mb-0">{{ $stats['pending_extensions'] }}</h3>
                            <small>Chờ duyệt</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- My Active Rental -->
                @if($myActiveRental)
                <div class="col-12 col-lg-6 mb-4">
                    <div class="card recent-card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-car-front-fill me-2"></i>Thuê xe của tôi
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Xe:</strong> {{ $myActiveRental->car->license_plate }}</p>
                                    <p><strong>Loại:</strong> {{ $myActiveRental->car->car_type }}</p>
                                    <p><strong>Màu:</strong> {{ $myActiveRental->car->color }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Bắt đầu:</strong> {{ $myActiveRental->rental_start->format('d/m/Y H:i') }}</p>
                                    <p><strong>Kết thúc:</strong> {{ $myActiveRental->rental_end->format('d/m/Y H:i') }}</p>
                                    <p><strong>Còn lại:</strong> 
                                        <span class="badge {{ $myActiveRental->is_overdue ? 'bg-danger' : 'bg-success' }}">
                                            {{ $myActiveRental->time_remaining }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            @if($myActiveRental->notes)
                                <div class="mt-3">
                                    <strong>Ghi chú:</strong>
                                    <p class="text-muted">{{ $myActiveRental->notes }}</p>
                                </div>
                            @endif
                            <div class="mt-3">
                                <a href="{{ route('rental.show', $myActiveRental) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye me-1"></i>Chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Overdue Rentals -->
                @if($overdueRentals->count() > 0)
                <div class="col-12 col-lg-6 mb-4">
                    <div class="card overdue-card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>Thuê xe quá hạn ({{ $overdueRentals->count() }})
                            </h5>
                        </div>
                        <div class="card-body">
                            @foreach($overdueRentals->take(3) as $rental)
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                    <div>
                                        <strong>{{ $rental->car->license_plate }}</strong><br>
                                        <small class="text-muted">{{ $rental->user->name }}</small>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-danger">
                                            Quá hạn: {{ $rental->rental_end->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                            @if($overdueRentals->count() > 3)
                                <div class="text-center mt-2">
                                    <small class="text-muted">Và {{ $overdueRentals->count() - 3 }} xe khác...</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Recent Rentals -->
            <div class="row">
                <div class="col-12">
                    <div class="card recent-card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-clock-history me-2"></i>Thuê xe gần đây
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($recentRentals->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Xe</th>
                                                <th>Người thuê</th>
                                                <th>Thời gian</th>
                                                <th>Trạng thái</th>
                                                <th>Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentRentals as $rental)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $rental->car->license_plate }}</strong><br>
                                                        <small class="text-muted">{{ $rental->car->car_type }}</small>
                                                    </td>
                                                    <td>
                                                        <strong>{{ $rental->user->name }}</strong><br>
                                                        <small class="text-muted">{{ $rental->user->role }}</small>
                                                    </td>
                                                    <td>
                                                        <strong>Bắt đầu:</strong> {{ $rental->rental_start->format('d/m/Y H:i') }}<br>
                                                        <strong>Kết thúc:</strong> {{ $rental->rental_end->format('d/m/Y H:i') }}
                                                        @if($rental->is_active)
                                                            <br><small class="text-muted">
                                                                Còn lại: {{ $rental->time_remaining }}
                                                            </small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($rental->status === 'active')
                                                            @if($rental->is_overdue)
                                                                <span class="badge bg-danger">Quá hạn</span>
                                                            @else
                                                                <span class="badge bg-success">Đang thuê</span>
                                                            @endif
                                                        @elseif($rental->status === 'completed')
                                                            <span class="badge bg-secondary">Hoàn thành</span>
                                                        @else
                                                            <span class="badge bg-warning">Đã hủy</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="{{ route('rental.show', $rental) }}" class="btn btn-outline-primary btn-sm">
                                                                <i class="bi bi-eye me-1"></i>Chi tiết
                                                            </a>
                                                            @if($rental->status === 'active')
                                                                <button type="button" class="btn btn-outline-warning btn-sm" 
                                                                        onclick="openCancelModal({{ $rental->id }}, '{{ $rental->car->license_plate }}', '{{ $rental->user->name }}')">
                                                                    <i class="bi bi-x-circle me-1"></i>Hủy
                                                                </button>
                                                                <button type="button" class="btn btn-outline-success btn-sm" 
                                                                        onclick="openCompleteModal({{ $rental->id }}, '{{ $rental->car->license_plate }}', '{{ $rental->user->name }}')">
                                                                    <i class="bi bi-check-circle me-1"></i>Kết thúc
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bi bi-car-front display-1 text-muted"></i>
                                    <h5 class="text-muted mt-3">Chưa có thuê xe nào</h5>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
        </div>
    </div>
</div>

<!-- Cancel Rental Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    <i class="bi bi-x-circle me-2"></i>Hủy thuê xe
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="cancelForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Bạn có chắc chắn muốn hủy thuê xe này không?
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Thông tin thuê xe:</label>
                        <div class="border rounded p-3 bg-light">
                            <strong>Xe:</strong> <span id="cancelCarInfo"></span><br>
                            <strong>Người thuê:</strong> <span id="cancelUserInfo"></span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="cancelReason" class="form-label">Lý do hủy <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="reason" id="cancelReason" rows="3" 
                                  placeholder="Nhập lý do hủy thuê xe..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-x-circle me-1"></i>Hủy thuê xe
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Complete Rental Early Modal -->
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle me-2"></i>Kết thúc thuê xe sớm
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="completeForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Kết thúc thuê xe sớm sẽ làm xe trở về trạng thái có sẵn ngay lập tức.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Thông tin thuê xe:</label>
                        <div class="border rounded p-3 bg-light">
                            <strong>Xe:</strong> <span id="completeCarInfo"></span><br>
                            <strong>Người thuê:</strong> <span id="completeUserInfo"></span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="actualEndTime" class="form-label">Thời gian kết thúc thực tế <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" name="actual_end_time" id="actualEndTime" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="completeReason" class="form-label">Lý do kết thúc sớm <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="reason" id="completeReason" rows="3" 
                                  placeholder="Nhập lý do kết thúc thuê xe sớm..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>Kết thúc thuê xe
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCancelModal(rentalId, carInfo, userInfo) {
    document.getElementById('cancelCarInfo').textContent = carInfo;
    document.getElementById('cancelUserInfo').textContent = userInfo;
    document.getElementById('cancelForm').action = `/rental/rentals/${rentalId}/cancel`;
    new bootstrap.Modal(document.getElementById('cancelModal')).show();
}

function openCompleteModal(rentalId, carInfo, userInfo) {
    document.getElementById('completeCarInfo').textContent = carInfo;
    document.getElementById('completeUserInfo').textContent = userInfo;
    document.getElementById('completeForm').action = `/rental/rentals/${rentalId}/complete-early`;
    
    // Set current time as default
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    document.getElementById('actualEndTime').value = now.toISOString().slice(0, 16);
    
    new bootstrap.Modal(document.getElementById('completeModal')).show();
}
</script>
@endsection
