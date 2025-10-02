@extends('layouts.master')

@section('title', 'Sửa xe - HP Foods')

@section('content')
<style>
.form-card {
    border: none;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border-radius: 15px;
}
.form-control:focus, .form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}
</style>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card form-card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-pencil-square me-2"></i>Sửa xe: {{ $car->license_plate }}
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('rental.cars.update', $car) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="license_plate" class="form-label">Biển số xe <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('license_plate') is-invalid @enderror" 
                                           name="license_plate" id="license_plate" 
                                           value="{{ old('license_plate', $car->license_plate) }}" 
                                           placeholder="VD: 30A-12345" required>
                                    @error('license_plate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="weight" class="form-label">Trọng lượng (kg) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control @error('weight') is-invalid @enderror" 
                                           name="weight" id="weight" 
                                           value="{{ old('weight', $car->weight) }}" 
                                           placeholder="VD: 1500.00" required>
                                    @error('weight')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="car_type" class="form-label">Loại xe <span class="text-danger">*</span></label>
                                    <select class="form-select @error('car_type') is-invalid @enderror" 
                                            name="car_type" id="car_type" required>
                                        <option value="">-- Chọn loại xe --</option>
                                        <option value="Sedan" {{ old('car_type', $car->car_type) == 'Sedan' ? 'selected' : '' }}>Sedan</option>
                                        <option value="SUV" {{ old('car_type', $car->car_type) == 'SUV' ? 'selected' : '' }}>SUV</option>
                                        <option value="Hatchback" {{ old('car_type', $car->car_type) == 'Hatchback' ? 'selected' : '' }}>Hatchback</option>
                                        <option value="Crossover" {{ old('car_type', $car->car_type) == 'Crossover' ? 'selected' : '' }}>Crossover</option>
                                        <option value="Compact" {{ old('car_type', $car->car_type) == 'Compact' ? 'selected' : '' }}>Compact</option>
                                        <option value="Pickup" {{ old('car_type', $car->car_type) == 'Pickup' ? 'selected' : '' }}>Pickup</option>
                                        <option value="Van" {{ old('car_type', $car->car_type) == 'Van' ? 'selected' : '' }}>Van</option>
                                        <option value="Other" {{ old('car_type', $car->car_type) == 'Other' ? 'selected' : '' }}>Khác</option>
                                    </select>
                                    @error('car_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="color" class="form-label">Màu sắc <span class="text-danger">*</span></label>
                                    <select class="form-select @error('color') is-invalid @enderror" 
                                            name="color" id="color" required>
                                        <option value="">-- Chọn màu sắc --</option>
                                        <option value="Trắng" {{ old('color', $car->color) == 'Trắng' ? 'selected' : '' }}>Trắng</option>
                                        <option value="Đen" {{ old('color', $car->color) == 'Đen' ? 'selected' : '' }}>Đen</option>
                                        <option value="Bạc" {{ old('color', $car->color) == 'Bạc' ? 'selected' : '' }}>Bạc</option>
                                        <option value="Xám" {{ old('color', $car->color) == 'Xám' ? 'selected' : '' }}>Xám</option>
                                        <option value="Xanh" {{ old('color', $car->color) == 'Xanh' ? 'selected' : '' }}>Xanh</option>
                                        <option value="Đỏ" {{ old('color', $car->color) == 'Đỏ' ? 'selected' : '' }}>Đỏ</option>
                                        <option value="Vàng" {{ old('color', $car->color) == 'Vàng' ? 'selected' : '' }}>Vàng</option>
                                        <option value="Nâu" {{ old('color', $car->color) == 'Nâu' ? 'selected' : '' }}>Nâu</option>
                                        <option value="Other" {{ old('color', $car->color) == 'Other' ? 'selected' : '' }}>Khác</option>
                                    </select>
                                    @error('color')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      name="description" id="description" rows="4" 
                                      placeholder="Mô tả chi tiết về xe, tính năng, tình trạng...">{{ old('description', $car->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    name="status" id="status" required>
                                <option value="active" {{ old('status', $car->status) == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="inactive" {{ old('status', $car->status) == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Chọn "Hoạt động" để xe có thể được mượn
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('rental.cars.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Quay lại
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Cập nhật xe
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
