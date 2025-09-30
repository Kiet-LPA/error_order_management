@extends('layouts.master')

@section('title', 'Thuê xe - HPFoods')

@section('content')
<style>
.rental-card {
    transition: transform 0.2s ease-in-out;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.rental-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}
.car-card {
    border-left: 4px solid #007bff;
}
.status-active {
    background: #198754;
    color: white;
}
.status-pending {
    background: #ffc107;
    color: white;
}
.status-overdue {
    background: #dc3545;
    color: white;
}
.btn-rent {
    background: #007bff;
    border: none;
    color: white;
    transition: all 0.3s ease;
}
.btn-rent:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,123,255,0.3);
    color: white;
}
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card rental-card">
                <div class="card-header status-active">
                    <h4 class="mb-0">
                        <i class="bi bi-car-front me-2"></i>Hệ thống thuê xe HPFoods
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Active Rental Status -->
                    @if($activeRental)
                        <div class="alert alert-info">
                            <h5><i class="bi bi-info-circle me-2"></i>Thuê xe hiện tại</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Xe:</strong> {{ $activeRental->car->license_plate }} - {{ $activeRental->car->car_type }}</p>
                                    <p><strong>Thời gian thuê:</strong> {{ $activeRental->rental_start->format('d/m/Y H:i') }}</p>
                                    <p><strong>Thời gian trả:</strong> {{ $activeRental->rental_end->format('d/m/Y H:i') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Thời gian còn lại:</strong> 
                                        <span class="badge {{ $activeRental->is_overdue ? 'bg-danger' : 'bg-success' }}">
                                            {{ $activeRental->time_remaining }}
                                        </span>
                                    </p>
                                    <div class="d-flex gap-2">
                                        @if($activeRental->canRequestExtension())
                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#extensionModal">
                                                <i class="bi bi-clock-history me-1"></i>Yêu cầu gia hạn
                                            </button>
                                        @endif
                                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#returnModal">
                                            <i class="bi bi-arrow-return-left me-1"></i>Trả xe
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Pending Extension -->
                    @if($pendingExtension)
                        <div class="alert alert-warning">
                            <h5><i class="bi bi-hourglass-split me-2"></i>Yêu cầu gia hạn đang chờ</h5>
                            <p><strong>Lý do:</strong> {{ $pendingExtension->reason }}</p>
                            <p><strong>Thời gian trả mới:</strong> {{ $pendingExtension->new_rental_end->format('d/m/Y H:i') }}</p>
                            <p><strong>Trạng thái:</strong> Chờ duyệt</p>
                        </div>
                    @endif

                    <div class="row">
                        <!-- Available Cars -->
                        <div class="col-12 col-lg-8">
                            <h5 class="mb-3">
                                <i class="bi bi-car-front-fill me-2"></i>Xe có sẵn ({{ $availableCars->count() }} xe)
                            </h5>
                            
                            @if($availableCars->count() > 0)
                                <div class="row">
                                    @foreach($availableCars as $car)
                                        <div class="col-12 col-sm-6 col-lg-4 mb-3">
                                            <div class="card car-card rental-card">
                                                <div class="card-body">
                                                    <h6 class="card-title">
                                                        <i class="bi bi-car-front me-1"></i>{{ $car->license_plate }}
                                                    </h6>
                                                    <p class="card-text">
                                                        <strong>Loại:</strong> {{ $car->car_type }}<br>
                                                        <strong>Màu:</strong> {{ $car->color }}<br>
                                                        <strong>Trọng lượng:</strong> {{ $car->weight }}kg
                                                    </p>
                                                    @if($car->description)
                                                        <p class="card-text">
                                                            <small class="text-muted">{{ Str::limit($car->description, 80) }}</small>
                                                        </p>
                                                    @endif
                                                    
                                                    @if(!$activeRental)
                                                        <button class="btn btn-rent btn-sm w-100" 
                                                                onclick="openRentModal({{ $car->id }}, '{{ $car->license_plate }}', '{{ $car->car_type }}')">
                                                            <i class="bi bi-calendar-plus me-1"></i>Thuê xe này
                                                        </button>
                                                    @else
                                                        <button class="btn btn-secondary btn-sm w-100" disabled>
                                                            <i class="bi bi-x-circle me-1"></i>Bạn đang có thuê xe chưa trả
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="bi bi-car-front display-1 text-muted"></i>
                                    <h5 class="text-muted mt-3">Không có xe nào có sẵn</h5>
                                    <p class="text-muted">Vui lòng quay lại sau hoặc liên hệ quản lý</p>
                                </div>
                            @endif
                        </div>

                        <!-- Recent Rentals -->
                        <div class="col-12 col-lg-4">
                            <h5 class="mb-3">
                                <i class="bi bi-clock-history me-2"></i>Lịch sử gần đây
                            </h5>
                            
                            @if($recentRentals->count() > 0)
                                @foreach($recentRentals as $rental)
                                    <div class="card mb-2">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">{{ $rental->car->license_plate }}</h6>
                                                    <small class="text-muted">{{ $rental->car->car_type }}</small>
                                                </div>
                                                <span class="badge 
                                                    @if($rental->status === 'active') bg-success
                                                    @elseif($rental->status === 'completed') bg-secondary
                                                    @else bg-danger
                                                    @endif">
                                                    {{ ucfirst($rental->status) }}
                                                </span>
                                            </div>
                                            <small class="text-muted">
                                                {{ $rental->rental_start->format('d/m/Y') }} - 
                                                {{ $rental->rental_end->format('d/m/Y') }}
                                            </small>
                                        </div>
                                    </div>
                                @endforeach
                                
                                <div class="text-center mt-3">
                                    <a href="{{ route('rental.my-rentals') }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-list-ul me-1"></i>Xem tất cả
                                    </a>
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <i class="bi bi-clock-history display-6 text-muted"></i>
                                    <p class="text-muted mt-2 mb-0">Chưa có lịch sử thuê xe</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rent Car Modal -->
<div class="modal fade" id="rentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-plus me-2"></i>Thuê xe
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('rental.rent') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Xe thuê</label>
                        <input type="text" class="form-control" id="rentCarInfo" readonly>
                        <input type="hidden" name="car_id" id="rentCarId">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="rental_start" class="form-label">Thời gian bắt đầu</label>
                                <input type="datetime-local" class="form-control @error('rental_start') is-invalid @enderror" 
                                       name="rental_start" id="rental_start" 
                                       value="{{ old('rental_start', now()->format('Y-m-d\TH:i')) }}" required>
                                @error('rental_start')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="rental_end" class="form-label">Thời gian trả</label>
                                <input type="datetime-local" class="form-control @error('rental_end') is-invalid @enderror" 
                                       name="rental_end" id="rental_end" required>
                                @error('rental_end')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Ghi chú (tùy chọn)</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  name="notes" id="notes" rows="3" 
                                  placeholder="Mục đích sử dụng xe...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Xác nhận thuê xe
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Extension Modal -->
@if($activeRental)
<div class="modal fade" id="extensionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-clock-history me-2"></i>Yêu cầu gia hạn
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('rental.request-extension', $activeRental) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Xe hiện tại</label>
                        <input type="text" class="form-control" 
                               value="{{ $activeRental->car->license_plate }} - {{ $activeRental->car->car_type }}" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Thời gian trả hiện tại</label>
                        <input type="text" class="form-control" 
                               value="{{ $activeRental->rental_end->format('d/m/Y H:i') }}" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_rental_end" class="form-label">Thời gian trả mới</label>
                        <input type="datetime-local" class="form-control @error('new_rental_end') is-invalid @enderror" 
                               name="new_rental_end" id="new_rental_end" 
                               min="{{ $activeRental->rental_end->format('Y-m-d\TH:i') }}" required>
                        @error('new_rental_end')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="reason" class="form-label">Lý do gia hạn</label>
                        <textarea class="form-control @error('reason') is-invalid @enderror" 
                                  name="reason" id="reason" rows="3" 
                                  placeholder="Vui lòng nêu lý do cần gia hạn..." required>{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-send me-1"></i>Gửi yêu cầu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Return Car Modal -->
@if($activeRental)
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-return-left me-2"></i>Trả xe sớm
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('rental.return-car', $activeRental) }}" method="POST">
                @csrf
                @method('POST')
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Bạn đang yêu cầu trả xe sớm hơn thời hạn đã đăng ký.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Xe hiện tại</label>
                        <input type="text" class="form-control" 
                               value="{{ $activeRental->car->license_plate }} - {{ $activeRental->car->car_type }}" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Thời gian trả theo đăng ký</label>
                        <input type="text" class="form-control" 
                               value="{{ $activeRental->rental_end->format('d/m/Y H:i') }}" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="actual_return_time" class="form-label">Thời gian trả thực tế</label>
                        <input type="datetime-local" class="form-control @error('actual_return_time') is-invalid @enderror" 
                               name="actual_return_time" id="actual_return_time" 
                               max="{{ now()->format('Y-m-d\TH:i') }}" required>
                        @error('actual_return_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="return_notes" class="form-label">Ghi chú (tùy chọn)</label>
                        <textarea class="form-control @error('return_notes') is-invalid @enderror" 
                                  name="return_notes" id="return_notes" rows="3" 
                                  placeholder="Ghi chú về việc trả xe...">{{ old('return_notes') }}</textarea>
                        @error('return_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-check-circle me-1"></i>Xác nhận trả xe
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
function openRentModal(carId, licensePlate, carType) {
    document.getElementById('rentCarId').value = carId;
    document.getElementById('rentCarInfo').value = licensePlate + ' - ' + carType;
    
    // Set default end time to 1 hour from now
    const now = new Date();
    now.setHours(now.getHours() + 1);
    document.getElementById('rental_end').value = now.toISOString().slice(0, 16);
    
    new bootstrap.Modal(document.getElementById('rentModal')).show();
}
</script>
@endsection
