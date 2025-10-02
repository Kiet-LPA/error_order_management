@extends('layouts.master')

@section('title', 'Chi tiết mượn xe - HP Foods')

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
.status-badge {
    font-size: 1rem;
    padding: 0.5rem 1rem;
}
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="bi bi-eye me-2"></i>Chi tiết mượn xe
                </h2>
                <div>
                    <a href="{{ route('rental.my-rentals') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Quay lại
                    </a>
                    @if(auth()->user()->canManageCars())
                        <a href="{{ route('rental.admin') }}" class="btn btn-outline-primary">
                            <i class="bi bi-tools me-1"></i>Quản lý
                        </a>
                    @endif
                </div>
            </div>

            <div class="row">
                <!-- Rental Information -->
                <div class="col-12 col-lg-8">
                    <div class="card detail-card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-info-circle me-2"></i>Thông tin mượn xe
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-row">
                                        <strong>Biển số xe:</strong><br>
                                        <span class="text-primary fs-5">{{ $rental->car->license_plate }}</span>
                                    </div>
                                    <div class="info-row">
                                        <strong>Loại xe:</strong><br>
                                        <span class="text-muted">{{ $rental->car->car_type }}</span>
                                    </div>
                                    <div class="info-row">
                                        <strong>Màu sắc:</strong><br>
                                        <span class="text-muted">{{ $rental->car->color }}</span>
                                    </div>
                                    <div class="info-row">
                                        <strong>Trọng lượng:</strong><br>
                                        <span class="text-muted">{{ $rental->car->weight }}kg</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-row">
                                        <strong>Người mượn:</strong><br>
                                        <span class="text-muted">{{ $rental->user->name }}</span>
                                    </div>
                                    <div class="info-row">
                                        <strong>Vai trò:</strong><br>
                                        <span class="text-muted">{{ ucfirst($rental->user->role) }}</span>
                                    </div>
                                    <div class="info-row">
                                        <strong>Thời gian tạo:</strong><br>
                                        <span class="text-muted">{{ $rental->created_at->format('d/m/Y H:i:s') }}</span>
                                    </div>
                                    <div class="info-row">
                                        <strong>Trạng thái:</strong><br>
                                        <span class="status-badge badge 
                                            @if($rental->status === 'active') 
                                                @if($rental->is_overdue) bg-danger
                                                @else bg-success
                                                @endif
                                            @elseif($rental->status === 'completed') bg-secondary
                                            @else bg-warning
                                            @endif">
                                            @if($rental->status === 'active')
                                                @if($rental->is_overdue) Quá hạn
                                                @else Đang mượn
                                                @endif
                                            @elseif($rental->status === 'completed') Hoàn thành
                                            @else Đã hủy
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Rental Period -->
                            <div class="mt-4 p-3 bg-light rounded">
                                <h6 class="mb-3">
                                    <i class="bi bi-calendar-range me-2"></i>Thời gian mượn xe
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Thời gian bắt đầu:</strong><br>
                                        <span class="text-success fs-5">{{ $rental->rental_start->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Thời gian kết thúc:</strong><br>
                                        <span class="text-danger fs-5">{{ $rental->rental_end->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>
                                @if($rental->status === 'active')
                                    <div class="mt-3 text-center">
                                        <strong>Thời gian còn lại:</strong><br>
                                        <span class="badge 
                                            @if($rental->is_overdue) bg-danger
                                            @else bg-success
                                            @endif fs-6 p-2">
                                            {{ $rental->time_remaining }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <!-- Notes -->
                            @if($rental->notes)
                                <div class="mt-4 p-3 bg-light rounded">
                                    <h6 class="mb-2">
                                        <i class="bi bi-sticky me-2"></i>Ghi chú
                                    </h6>
                                    <p class="text-muted mb-0">{{ $rental->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Car Details & Actions -->
                <div class="col-12 col-lg-4">
                    <!-- Car Details -->
                    <div class="card detail-card mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-car-front me-2"></i>Thông tin xe
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($rental->car->description)
                                <p class="text-muted">{{ $rental->car->description }}</p>
                            @endif
                            
                            <div class="d-grid gap-2">
                                @if(auth()->user()->canManageCars())
                                    <a href="{{ route('rental.cars.show', $rental->car) }}" class="btn btn-outline-info">
                                        <i class="bi bi-car-front me-1"></i>Xem chi tiết xe
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card detail-card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-gear me-2"></i>Thao tác
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($rental->status === 'active' && $rental->canRequestExtension())
                                <button class="btn btn-warning w-100 mb-2" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#extensionModal">
                                    <i class="bi bi-clock-history me-1"></i>Yêu cầu gia hạn
                                </button>
                            @endif


                            <a href="{{ route('rental.my-rentals') }}" class="btn btn-outline-secondary w-100 mt-2">
                                <i class="bi bi-arrow-left me-1"></i>Quay lại danh sách
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Extensions History -->
            @if($rental->extensions->count() > 0)
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card detail-card">
                            <div class="card-header bg-warning text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-clock-history me-2"></i>Lịch sử gia hạn ({{ $rental->extensions->count() }})
                                </h5>
                            </div>
                            <div class="card-body">
                                @foreach($rental->extensions as $extension)
                                    <div class="row mb-3 p-3 bg-light rounded">
                                        <div class="col-md-8">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <strong>Ngày yêu cầu:</strong><br>
                                                    <span class="text-muted">{{ $extension->created_at->format('d/m/Y H:i:s') }}</span>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Thời gian trả mới:</strong><br>
                                                    <span class="text-muted">{{ $extension->new_rental_end->format('d/m/Y H:i') }}</span>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <strong>Lý do:</strong><br>
                                                <span class="text-muted">{{ $extension->reason }}</span>
                                            </div>
                                            @if($extension->rejection_reason)
                                                <div class="mt-2">
                                                    <strong>Lý do từ chối:</strong><br>
                                                    <span class="text-danger">{{ $extension->rejection_reason }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <span class="badge 
                                                @if($extension->status === 'approved') bg-success
                                                @elseif($extension->status === 'rejected') bg-danger
                                                @else bg-warning
                                                @endif fs-6 p-2">
                                                @if($extension->status === 'approved') Đã duyệt
                                                @elseif($extension->status === 'rejected') Từ chối
                                                @else Chờ duyệt
                                                @endif
                                            </span>
                                            @if($extension->approvedBy)
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        Duyệt bởi: {{ $extension->approvedBy->name }}<br>
                                                        {{ $extension->approved_at->format('d/m/Y H:i') }}
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Extension Modal -->
@if($rental->status === 'active' && $rental->canRequestExtension())
<div class="modal fade" id="extensionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-clock-history me-2"></i>Yêu cầu gia hạn
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('rental.request-extension', $rental) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Xe</label>
                        <input type="text" class="form-control" 
                               value="{{ $rental->car->license_plate }} - {{ $rental->car->car_type }}" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Thời gian trả hiện tại</label>
                        <input type="text" class="form-control" 
                               value="{{ $rental->rental_end->format('d/m/Y H:i') }}" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_rental_end" class="form-label">Thời gian trả mới</label>
                        <input type="datetime-local" class="form-control @error('new_rental_end') is-invalid @enderror" 
                               name="new_rental_end" id="new_rental_end" 
                               min="{{ $rental->rental_end->format('Y-m-d\TH:i') }}" required>
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
@endsection
