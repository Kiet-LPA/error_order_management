@extends('layouts.master')

@section('title', 'Quản lý xe - HP Foods')

@section('content')
<style>
.car-card {
    transition: transform 0.2s ease-in-out;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.car-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}
.status-active {
    border-left: 4px solid #28a745;
}
.status-inactive {
    border-left: 4px solid #6c757d;
}
.status-rented {
    border-left: 4px solid #007bff;
}
.btn-action {
    border-radius: 20px;
    padding: 0.5rem 1rem;
}
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="bi bi-car-front me-2"></i>Quản lý xe HP Foods
                </h2>
                <div>
                    <a href="{{ route('rental.cars.create') }}" class="btn btn-success btn-action">
                        <i class="bi bi-plus-circle me-1"></i>Thêm xe mới
                    </a>
                    <a href="{{ route('rental.admin') }}" class="btn btn-outline-primary btn-action">
                        <i class="bi bi-arrow-left me-1"></i>Quay lại
                    </a>
                </div>
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

            @if($cars->count() > 0)
                <div class="row">
                    @foreach($cars as $car)
                        <div class="col-12 col-sm-6 col-lg-4 mb-4">
                            <div class="card car-card 
                                @if($car->status === 'active') status-active
                                @elseif($car->status === 'inactive') status-inactive
                                @else status-rented
                                @endif">
                                <div class="card-header bg-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="bi bi-car-front me-2"></i>{{ $car->license_plate }}
                                        </h5>
                                        <span class="badge 
                                            @if($car->status === 'active') bg-success
                                            @elseif($car->status === 'inactive') bg-secondary
                                            @else bg-primary
                                            @endif">
                                            @if($car->status === 'active') Có sẵn
                                            @elseif($car->status === 'inactive') Không hoạt động
                                            @else Đang mượn
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <strong>Loại xe:</strong><br>
                                            <span class="text-muted">{{ $car->car_type }}</span>
                                        </div>
                                        <div class="col-6">
                                            <strong>Màu sắc:</strong><br>
                                            <span class="text-muted">{{ $car->color }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <strong>Trọng lượng:</strong><br>
                                            <span class="text-muted">{{ $car->weight }}kg</span>
                                        </div>
                                        <div class="col-6">
                                            <strong>Trạng thái:</strong><br>
                                            @if($car->status === 'rented' && $car->activeRental)
                                                <small class="text-primary">
                                                    Mượn bởi: {{ $car->activeRental->user->name }}<br>
                                                    Đến: {{ $car->available_from->format('d/m/Y H:i') }}
                                                </small>
                                            @else
                                                <span class="text-success">Sẵn sàng</span>
                                            @endif
                                        </div>
                                    </div>

                                    @if($car->description)
                                        <div class="mb-3">
                                            <strong>Mô tả:</strong><br>
                                            <small class="text-muted">{{ Str::limit($car->description, 100) }}</small>
                                        </div>
                                    @endif

                                    @if($car->available_from && $car->status === 'rented')
                                        <div class="mb-3">
                                            <strong>Thời gian có sẵn:</strong><br>
                                            <small class="text-info">
                                                <i class="bi bi-clock me-1"></i>
                                                {{ $car->available_from->format('d/m/Y H:i') }}
                                                ({{ $car->available_from->diffForHumans() }})
                                            </small>
                                        </div>
                                    @endif

                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="{{ route('rental.cars.show', $car) }}" class="btn btn-outline-info btn-sm">
                                            <i class="bi bi-eye me-1"></i>Chi tiết
                                        </a>
                                        
                                        @if($car->status !== 'rented')
                                            <a href="{{ route('rental.cars.edit', $car) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-pencil me-1"></i>Sửa
                                            </a>
                                            
                                            <form action="{{ route('rental.cars.destroy', $car) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" 
                                                        onclick="return confirm('Bạn có chắc chắn muốn xóa xe này?')">
                                                    <i class="bi bi-trash me-1"></i>Xóa
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-secondary btn-sm" disabled>
                                                <i class="bi bi-lock me-1"></i>Đang mượn
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $cars->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-car-front display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">Chưa có xe nào</h4>
                    <p class="text-muted">Hãy thêm xe đầu tiên để bắt đầu quản lý!</p>
                    <a href="{{ route('rental.cars.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Thêm xe đầu tiên
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
