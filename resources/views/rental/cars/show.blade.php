@extends('layouts.master')

@section('title', 'Chi tiết xe - HPFoods')

@section('content')
<style>
.detail-card {
    border: none;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    border-radius: 15px;
}
.info-row {
    border-bottom: 1px solid #eee;
    padding: 1rem 0;
}
.info-row:last-child {
    border-bottom: none;
}
.rental-history-card {
    border-left: 4px solid #007bff;
}
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="bi bi-car-front me-2"></i>Chi tiết xe: {{ $car->license_plate }}
                </h2>
                <div>
                    <a href="{{ route('rental.cars.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Quay lại
                    </a>
                    @if($car->status !== 'rented')
                        <a href="{{ route('rental.cars.edit', $car) }}" class="btn btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i>Sửa xe
                        </a>
                    @endif
                </div>
            </div>

            <div class="row">
                <!-- Car Information -->
                <div class="col-lg-8">
                    <div class="card detail-card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-info-circle me-2"></i>Thông tin xe
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-row">
                                        <strong>Biển số xe:</strong><br>
                                        <span class="text-primary fs-5">{{ $car->license_plate }}</span>
                                    </div>
                                    <div class="info-row">
                                        <strong>Loại xe:</strong><br>
                                        <span class="text-muted">{{ $car->car_type }}</span>
                                    </div>
                                    <div class="info-row">
                                        <strong>Màu sắc:</strong><br>
                                        <span class="text-muted">{{ $car->color }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-row">
                                        <strong>Trọng lượng:</strong><br>
                                        <span class="text-muted">{{ $car->weight }}kg</span>
                                    </div>
                                    <div class="info-row">
                                        <strong>Trạng thái:</strong><br>
                                        <span class="badge 
                                            @if($car->status === 'active') bg-success
                                            @elseif($car->status === 'inactive') bg-secondary
                                            @else bg-primary
                                            @endif fs-6 p-2">
                                            @if($car->status === 'active') Có sẵn
                                            @elseif($car->status === 'inactive') Không hoạt động
                                            @else Đang thuê
                                            @endif
                                        </span>
                                    </div>
                                    <div class="info-row">
                                        <strong>Ngày tạo:</strong><br>
                                        <span class="text-muted">{{ $car->created_at->format('d/m/Y H:i:s') }}</span>
                                    </div>
                                </div>
                            </div>

                            @if($car->description)
                                <div class="mt-4 p-3 bg-light rounded">
                                    <h6 class="mb-2">
                                        <i class="bi bi-card-text me-2"></i>Mô tả
                                    </h6>
                                    <p class="text-muted mb-0">{{ $car->description }}</p>
                                </div>
                            @endif

                            @if($car->status === 'rented' && $car->activeRental)
                                <div class="mt-4 p-3 bg-warning bg-opacity-10 rounded">
                                    <h6 class="mb-3">
                                        <i class="bi bi-exclamation-triangle me-2"></i>Thông tin thuê hiện tại
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Người thuê:</strong> {{ $car->activeRental->user->name }}</p>
                                            <p><strong>Vai trò:</strong> {{ ucfirst($car->activeRental->user->role) }}</p>
                                            <p><strong>Bắt đầu:</strong> {{ $car->activeRental->rental_start->format('d/m/Y H:i') }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Kết thúc:</strong> {{ $car->activeRental->rental_end->format('d/m/Y H:i') }}</p>
                                            <p><strong>Thời gian có sẵn:</strong> {{ $car->available_from->format('d/m/Y H:i') }}</p>
                                            <p><strong>Còn lại:</strong> 
                                                <span class="badge {{ $car->activeRental->is_overdue ? 'bg-danger' : 'bg-success' }}">
                                                    {{ $car->activeRental->time_remaining }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                    @if($car->activeRental->notes)
                                        <div class="mt-3">
                                            <strong>Ghi chú:</strong>
                                            <p class="text-muted">{{ $car->activeRental->notes }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Actions & Status -->
                <div class="col-lg-4">
                    <!-- Current Status -->
                    <div class="card detail-card mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-gear me-2"></i>Trạng thái hiện tại
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($car->status === 'active')
                                <div class="text-center">
                                    <i class="bi bi-check-circle text-success display-4"></i>
                                    <h5 class="text-success mt-2">Có sẵn</h5>
                                    <p class="text-muted">Xe đang sẵn sàng để thuê</p>
                                </div>
                            @elseif($car->status === 'inactive')
                                <div class="text-center">
                                    <i class="bi bi-pause-circle text-secondary display-4"></i>
                                    <h5 class="text-secondary mt-2">Không hoạt động</h5>
                                    <p class="text-muted">Xe tạm thời không thể thuê</p>
                                </div>
                            @else
                                <div class="text-center">
                                    <i class="bi bi-car-front-fill text-primary display-4"></i>
                                    <h5 class="text-primary mt-2">Đang thuê</h5>
                                    <p class="text-muted">Xe đang được sử dụng</p>
                                    @if($car->available_from)
                                        <small class="text-info">
                                            Có sẵn từ: {{ $car->available_from->format('d/m/Y H:i') }}
                                        </small>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card detail-card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-tools me-2"></i>Thao tác
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @if($car->status !== 'rented')
                                    <a href="{{ route('rental.cars.edit', $car) }}" class="btn btn-primary">
                                        <i class="bi bi-pencil me-1"></i>Sửa thông tin xe
                                    </a>
                                @endif

                                @if($car->status !== 'rented')
                                    <form action="{{ route('rental.cars.destroy', $car) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger w-100" 
                                                onclick="return confirm('Bạn có chắc chắn muốn xóa xe này?')">
                                            <i class="bi bi-trash me-1"></i>Xóa xe
                                        </button>
                                    </form>
                                @else
                                    <button class="btn btn-secondary" disabled>
                                        <i class="bi bi-lock me-1"></i>Không thể sửa (đang thuê)
                                    </button>
                                @endif

                                <a href="{{ route('rental.cars.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-list-ul me-1"></i>Danh sách xe
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rental History -->
            @if($car->rentals->count() > 0)
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card rental-history-card detail-card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-clock-history me-2"></i>Lịch sử thuê xe ({{ $car->rentals->count() }})
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Người thuê</th>
                                                <th>Thời gian thuê</th>
                                                <th>Trạng thái</th>
                                                <th>Ghi chú</th>
                                                <th>Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($car->rentals as $rental)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $rental->user->name }}</strong><br>
                                                        <small class="text-muted">{{ ucfirst($rental->user->role) }}</small>
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
                                                        @if($rental->notes)
                                                            <small class="text-muted">{{ Str::limit($rental->notes, 50) }}</small>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('rental.show', $rental) }}" class="btn btn-outline-primary btn-sm">
                                                            <i class="bi bi-eye me-1"></i>Chi tiết
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card rental-history-card detail-card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-clock-history me-2"></i>Lịch sử thuê xe
                                </h5>
                            </div>
                            <div class="card-body text-center py-4">
                                <i class="bi bi-car-front display-1 text-muted"></i>
                                <h5 class="text-muted mt-3">Chưa có lịch sử thuê xe</h5>
                                <p class="text-muted">Xe này chưa được thuê lần nào</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
