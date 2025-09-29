@extends('layouts.master')
@section('title', 'Thêm phòng ban')
@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">Thêm phòng ban mới</h5></div>
    <div class="card-body">
        <form action="{{ route('departments.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Tên phòng ban <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Địa chỉ</label>
                <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address') }}</textarea>
                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="latitude" class="form-label">Vĩ độ (Latitude)</label>
                        <input type="number" name="latitude" id="latitude" class="form-control @error('latitude') is-invalid @enderror" 
                               value="{{ old('latitude') }}" step="0.00000001" min="-90" max="90" placeholder="Ví dụ: 10.0259">
                        <small class="form-text text-muted">Tọa độ GPS để điểm danh</small>
                        @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="longitude" class="form-label">Kinh độ (Longitude)</label>
                        <input type="number" name="longitude" id="longitude" class="form-control @error('longitude') is-invalid @enderror" 
                               value="{{ old('longitude') }}" step="0.00000001" min="-180" max="180" placeholder="Ví dụ: 105.7692">
                        <small class="form-text text-muted">Tọa độ GPS để điểm danh</small>
                        @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="radius_meters" class="form-label">Bán kính cho phép (mét)</label>
                <input type="number" name="radius_meters" id="radius_meters" class="form-control @error('radius_meters') is-invalid @enderror" 
                       value="{{ old('radius_meters', 200) }}" min="1" max="10000">
                <small class="form-text text-muted">Khoảng cách tối đa từ tọa độ GPS để điểm danh thành công</small>
                @error('radius_meters')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Lưu ý:</strong> Để nhân viên có thể điểm danh, phòng ban cần có tọa độ GPS (Vĩ độ, Kinh độ) và bán kính cho phép.
            </div>

            <button type="submit" class="btn btn-primary">Lưu</button>
            <a href="{{ route('departments.index') }}" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
</div>
@endsection
