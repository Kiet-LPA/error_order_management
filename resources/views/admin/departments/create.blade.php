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
                <button type="button" class="btn btn-outline-primary" id="btnGetGps">
                    📍 Lấy GPS từ thiết bị
                </button>
                <small class="text-muted ms-2" id="gpsStatus" style="display:none;"></small>
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

@push('scripts')
<script src="{{ asset('js/location-name.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('btnGetGps');
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const statusEl = document.getElementById('gpsStatus');

    if (!btn) return;

    btn.addEventListener('click', function() {
        if (!navigator.geolocation) {
            alert('Trình duyệt không hỗ trợ GPS.');
            return;
        }
        statusEl.style.display = 'inline';
        statusEl.textContent = 'Đang lấy vị trí...';
        btn.disabled = true;

        navigator.geolocation.getCurrentPosition(function(pos) {
            const { latitude, longitude, accuracy } = pos.coords;
            // Điền vào form với 6-8 chữ số thập phân
            const ACC_THRESHOLD = 100; // mét
            const roundedAcc = Math.round(accuracy || 0);
            if (accuracy && accuracy > ACC_THRESHOLD) {
                const proceed = confirm(`Độ chính xác hiện tại ~${roundedAcc}m (> ${ACC_THRESHOLD}m).\nBạn vẫn muốn dùng tọa độ này không?`);
                if (!proceed) {
                    statusEl.textContent = `Hủy do độ chính xác thấp (~${roundedAcc}m). Hãy thử lại ở nơi thoáng hoặc bật High accuracy.`;
                    btn.disabled = false;
                    return;
                }
            }
            latInput.value = latitude.toFixed(8);
            lngInput.value = longitude.toFixed(8);
            statusEl.textContent = `Đã lấy vị trí (độ chính xác ~${roundedAcc}m)…`;
            if (window.LocationName) {
                window.LocationName.resolve(latitude, longitude).then(function(name) {
                    statusEl.textContent = name
                        ? `Đã lấy: ${name} (±${roundedAcc}m)`
                        : `Đã lấy vị trí (độ chính xác ~${roundedAcc}m)`;
                }).catch(function() {
                    statusEl.textContent = `Đã lấy vị trí (độ chính xác ~${roundedAcc}m)`;
                });
            } else {
                statusEl.textContent = `Đã lấy vị trí (độ chính xác ~${roundedAcc}m)`;
            }
            btn.disabled = false;
        }, function(err) {
            alert('Không lấy được vị trí: ' + (err.message || 'Lỗi không xác định'));
            statusEl.style.display = 'none';
            btn.disabled = false;
        }, {
            enableHighAccuracy: true,
            timeout: 30000,
            maximumAge: 0
        });
    });
});
</script>
@endpush
@endsection
