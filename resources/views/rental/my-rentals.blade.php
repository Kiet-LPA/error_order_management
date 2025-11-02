@extends('layouts.master')

@section('title', 'Lịch sử mượn xe của tôi - HP Foods')

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
.status-active {
    border-left: 4px solid #28a745;
}
.status-completed {
    border-left: 4px solid #6c757d;
}
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="bi bi-clock-history me-2"></i>Lịch sử mượn xe của tôi
                </h2>
                <a href="{{ route('rental.index') }}" class="btn btn-primary">
                    <i class="bi bi-car-front me-1"></i>Mượn xe mới
                </a>
            </div>

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

            @if($rentals->count() > 0)
                <div class="row">
                    @foreach($rentals as $rental)
                        <div class="col-12 col-lg-6 col-xl-4 mb-4">
                            <div class="card rental-card 
                                @if($rental->status === 'active') status-active
                                @elseif($rental->status === 'completed') status-completed
                                @endif">
                                <div class="card-header bg-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="bi bi-car-front me-2"></i>{{ $rental->car->license_plate }}
                                        </h5>
                                        <span class="badge 
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
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <strong>Loại xe:</strong><br>
                                            <span class="text-muted">{{ $rental->car->car_type }}</span>
                                        </div>
                                        <div class="col-6">
                                            <strong>Màu sắc:</strong><br>
                                            <span class="text-muted">{{ $rental->car->color }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <strong>Thời gian mượn:</strong><br>
                                            <small class="text-muted">
                                                {{ $rental->rental_start->format('d/m/Y H:i') }} - 
                                                {{ $rental->rental_end->format('d/m/Y H:i') }}
                                            </small>
                                        </div>
                                    </div>

                                    @if($rental->status === 'active')
                                        <div class="row mb-3">
                                            <div class="col-12">
                                                <strong>Thời gian còn lại:</strong><br>
                                                <span class="badge 
                                                    @if($rental->is_overdue) bg-danger
                                                    @else bg-success
                                                    @endif fs-6">
                                                    {{ $rental->time_remaining }}
                                                </span>
                                            </div>
                                        </div>
                                    @endif

                                    @if($rental->notes)
                                        <div class="mb-3">
                                            <strong>Ghi chú:</strong><br>
                                            <small class="text-muted">{{ $rental->notes }}</small>
                                        </div>
                                    @endif

                                    <!-- Extensions -->
                                    @if($rental->extensions->count() > 0)
                                        <div class="mb-3">
                                            <strong>Lịch sử gia hạn:</strong>
                                            @foreach($rental->extensions as $extension)
                                                <div class="mt-2 p-2 bg-light rounded">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <small class="text-muted">
                                                                {{ $extension->created_at->format('d/m/Y H:i') }}
                                                            </small><br>
                                                            <strong>Lý do:</strong> {{ $extension->reason }}
                                                        </div>
                                                        <span class="badge 
                                                            @if($extension->status === 'approved') bg-success
                                                            @elseif($extension->status === 'rejected') bg-danger
                                                            @else bg-warning
                                                            @endif">
                                                            @if($extension->status === 'approved') Đã duyệt
                                                            @elseif($extension->status === 'rejected') Từ chối
                                                            @else Chờ duyệt
                                                            @endif
                                                        </span>
                                                    </div>
                                                    @if($extension->status === 'approved')
                                                        <small class="text-success">
                                                            <strong>Thời gian mới:</strong> {{ $extension->new_rental_end->format('d/m/Y H:i') }}
                                                        </small>
                                                    @elseif($extension->status === 'rejected' && $extension->rejection_reason)
                                                        <small class="text-danger">
                                                            <strong>Lý do từ chối:</strong> {{ $extension->rejection_reason }}
                                                        </small>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('rental.show', $rental) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye me-1"></i>Chi tiết
                                        </a>
                                        
                                        <div class="d-flex gap-2">
                                            @if($rental->status === 'active')
                                                <button class="btn btn-danger btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#returnModal{{ $rental->id }}">
                                                    <i class="bi bi-arrow-return-left me-1"></i>Trả xe
                                                </button>
                                            @endif
                                            
                                            @if($rental->status === 'active' && $rental->canRequestExtension())
                                                <button class="btn btn-warning btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#extensionModal{{ $rental->id }}">
                                                    <i class="bi bi-clock-history me-1"></i>Gia hạn
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Extension Modal for each rental -->
                        @if($rental->status === 'active' && $rental->canRequestExtension())
                        <div class="modal fade" id="extensionModal{{ $rental->id }}" tabindex="-1">
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
                                                <label for="new_rental_end{{ $rental->id }}" class="form-label">Thời gian trả mới</label>
                                                <input type="datetime-local" class="form-control @error('new_rental_end') is-invalid @enderror" 
                                                       name="new_rental_end" id="new_rental_end{{ $rental->id }}" 
                                                       min="{{ $rental->rental_end->format('Y-m-d\TH:i') }}" required>
                                                @error('new_rental_end')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="reason{{ $rental->id }}" class="form-label">Lý do gia hạn</label>
                                                <textarea class="form-control @error('reason') is-invalid @enderror" 
                                                          name="reason" id="reason{{ $rental->id }}" rows="3" 
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

                        <!-- Return Modal for each rental -->
                        @if($rental->status === 'active')
                        <div class="modal fade" id="returnModal{{ $rental->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="bi bi-arrow-return-left me-2"></i>Trả xe sớm
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('rental.return-car', $rental->id) }}" method="POST">
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
                                                       value="{{ $rental->car->license_plate }} - {{ $rental->car->car_type }}" readonly>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Thời gian trả theo đăng ký</label>
                                                <input type="text" class="form-control" 
                                                       value="{{ $rental->rental_end->format('d/m/Y H:i') }}" readonly>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="actual_return_time{{ $rental->id }}" class="form-label">Thời gian trả thực tế</label>
                                                <input type="datetime-local" class="form-control @error('actual_return_time') is-invalid @enderror" 
                                                       name="actual_return_time" id="actual_return_time{{ $rental->id }}" 
                                                       max="{{ now()->format('Y-m-d\TH:i') }}" required>
                                                @error('actual_return_time')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="refueled" id="refueled{{ $rental->id }}" 
                                                           onchange="toggleFuelAmount({{ $rental->id }})">
                                                    <label class="form-check-label" for="refueled{{ $rental->id }}">
                                                        Đổ đầy nhiên liệu
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3" id="fuelAmountDiv{{ $rental->id }}" style="display: none;">
                                                <label for="fuel_amount{{ $rental->id }}" class="form-label">Số tiền đã dùng để đổ nhiên liệu (VNĐ)</label>
                                                <input type="number" class="form-control @error('fuel_amount') is-invalid @enderror" 
                                                       name="fuel_amount" id="fuel_amount{{ $rental->id }}" 
                                                       placeholder="Nhập số tiền đã đổ nhiên liệu..." min="0" step="1000">
                                                @error('fuel_amount')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="return_notes{{ $rental->id }}" class="form-label">Ghi chú (tùy chọn)</label>
                                                <textarea class="form-control @error('return_notes') is-invalid @enderror" 
                                                          name="return_notes" id="return_notes{{ $rental->id }}" rows="3" 
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
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $rentals->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-clock-history display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">Chưa có lịch sử mượn xe</h4>
                    <p class="text-muted">Bạn chưa mượn xe nào. Hãy bắt đầu mượn xe đầu tiên của bạn!</p>
                    <a href="{{ route('rental.index') }}" class="btn btn-primary">
                        <i class="bi bi-car-front me-1"></i>Mượn xe ngay
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function toggleFuelAmount(rentalId) {
    const checkbox = document.getElementById('refueled' + rentalId);
    const fuelAmountDiv = document.getElementById('fuelAmountDiv' + rentalId);
    
    if (checkbox.checked) {
        fuelAmountDiv.style.display = 'block';
    } else {
        fuelAmountDiv.style.display = 'none';
        // Clear the fuel amount input when unchecked
        document.getElementById('fuel_amount' + rentalId).value = '';
    }
}
</script>
@endsection
