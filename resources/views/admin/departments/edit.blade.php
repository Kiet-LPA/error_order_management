@extends('layouts.master')
@section('title', 'Sửa phòng ban')
@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">Sửa phòng ban: {{ $department->name }}</h5></div>
    <div class="card-body">
        <form action="{{ route('departments.update', $department) }}" method="POST">
            @csrf @method('PUT')
            <div class="mb-3">
                <label for="name" class="form-label">Tên phòng ban <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $department->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Địa chỉ</label>
                <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address', $department->address) }}</textarea>
                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="latitude" class="form-label">Vĩ độ (Latitude)</label>
                        <input type="number" name="latitude" id="latitude" class="form-control @error('latitude') is-invalid @enderror" 
                               value="{{ old('latitude', $department->latitude) }}" step="0.00000001" min="-90" max="90" placeholder="Ví dụ: 10.0259">
                        <small class="form-text text-muted">Tọa độ GPS để điểm danh</small>
                        @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="longitude" class="form-label">Kinh độ (Longitude)</label>
                        <input type="number" name="longitude" id="longitude" class="form-control @error('longitude') is-invalid @enderror" 
                               value="{{ old('longitude', $department->longitude) }}" step="0.00000001" min="-180" max="180" placeholder="Ví dụ: 105.7692">
                        <small class="form-text text-muted">Tọa độ GPS để điểm danh</small>
                        @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="radius_meters" class="form-label">Bán kính cho phép (mét)</label>
                <input type="number" name="radius_meters" id="radius_meters" class="form-control @error('radius_meters') is-invalid @enderror" 
                       value="{{ old('radius_meters', $department->radius_meters ?? 200) }}" min="1" max="10000">
                <small class="form-text text-muted">Khoảng cách tối đa từ tọa độ GPS để điểm danh thành công</small>
                @error('radius_meters')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @if($department->hasGpsConfig())
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <strong>Đã cấu hình GPS:</strong> Nhân viên trong phòng ban này có thể điểm danh.
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Chưa cấu hình GPS:</strong> Nhân viên trong phòng ban này không thể điểm danh. Vui lòng thêm tọa độ GPS.
                </div>
            @endif

            <button type="submit" class="btn btn-primary">Cập nhật</button>
            <a href="{{ route('departments.index') }}" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
</div>
@endsection
