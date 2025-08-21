@extends('layouts.master')

@section('title', 'Cập nhật thông tin nhân viên')

@section('content')
<style>
.card-header {
    background: linear-gradient(90deg, #558EC1 0%, #5DA444 100%);
    color: #fff;
    border-bottom: none;
}
.card-header h5 {
    color: #fff;
}

/* Form controls */
.form-control:focus {
    border-color: #558EC1;
    box-shadow: 0 0 0 0.2rem rgba(85, 142, 193, 0.25);
}
.form-select:focus {
    border-color: #558EC1;
    box-shadow: 0 0 0 0.2rem rgba(85, 142, 193, 0.25);
}
.form-label {
    color: #374151;
    font-weight: 500;
}
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="bi bi-person-circle me-2"></i>
                    Cập nhật thông tin nhân viên
                </h2>
                <div>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Quay lại
                    </a>
                </div>
            </div>

            <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <!-- Thông tin cơ bản -->
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-person me-2"></i>Thông tin cơ bản
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Tên</label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-muted">(tùy chọn nếu có số điện thoại)</span></label>
                                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}">
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Số điện thoại <span class="text-muted">(tùy chọn nếu có email)</span></label>
                                    <input type="tel" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" placeholder="0123456789">
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Mật khẩu mới (bỏ trống nếu không đổi)</label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password', 'passwordToggle', 'passwordIcon')">
                                            <i class="fas fa-eye" id="passwordIcon"></i>
                                        </button>
                                    </div>
                                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Xác nhận mật khẩu</label>
                                    <div class="input-group">
                                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password_confirmation', 'passwordConfirmToggle', 'passwordConfirmIcon')">
                                            <i class="fas fa-eye" id="passwordConfirmIcon"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="role" class="form-label">Vai trò</label>
                                    <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                                        <option value="admin" {{ old('role', $user->role)=='admin'?'selected':'' }}>Admin</option>
                                        <option value="manager" {{ old('role', $user->role)=='manager'?'selected':'' }}>Manager</option>
                                        <option value="employee" {{ old('role', $user->role)=='employee'?'selected':'' }}>Employee</option>
                                    </select>
                                    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="department_id" class="form-label">Phòng ban (nếu có)</label>
                                    <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror">
                                        <option value="">-- Không chọn --</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ old('department_id', $user->department_id)==$department->id?'selected':'' }}>{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin bổ sung -->
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-file-earmark-text me-2"></i>Thông tin bổ sung
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="position" class="form-label">Chức vụ</label>
                                    <input type="text" name="position" id="position" class="form-control @error('position') is-invalid @enderror" value="{{ old('position', $user->position) }}">
                                    @error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="social_insurance_number" class="form-label">Mã số BHXH</label>
                                    <input type="text" name="social_insurance_number" id="social_insurance_number" class="form-control @error('social_insurance_number') is-invalid @enderror" value="{{ old('social_insurance_number', $user->social_insurance_number) }}">
                                    @error('social_insurance_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="health_insurance_number" class="form-label">Mã số BHYT</label>
                                    <input type="text" name="health_insurance_number" id="health_insurance_number" class="form-control @error('health_insurance_number') is-invalid @enderror" value="{{ old('health_insurance_number', $user->health_insurance_number) }}">
                                    @error('health_insurance_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="personal_identification_number" class="form-label">Mã số định danh cá nhân</label>
                                    <input type="text" name="personal_identification_number" id="personal_identification_number" class="form-control @error('personal_identification_number') is-invalid @enderror" value="{{ old('personal_identification_number', $user->personal_identification_number) }}">
                                    @error('personal_identification_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                
                                <!-- Hình ảnh hợp đồng (chỉ cho nhân viên chính thức) -->
                                @if($user->employee_type == 'official')
                                <div class="mt-4">
                                    <h6 class="text-info mb-3">
                                        <i class="bi bi-images me-2"></i>Hình ảnh hợp đồng
                                    </h6>
                                    
                                    <div class="mb-3">
                                        <label for="contract_images" class="form-label">Tải lên hình ảnh hợp đồng</label>
                                        <input type="file" class="form-control @error('contract_images.*') is-invalid @enderror" 
                                               id="contract_images" name="contract_images[]" multiple 
                                               accept="image/*">
                                        <div class="form-text">Có thể chọn nhiều file. Hỗ trợ: JPG, PNG, GIF</div>
                                        @error('contract_images.*')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div id="imagePreview" class="row g-2"></div>
                                    
                                    <!-- Hiển thị hình ảnh hiện có -->
                                    @if($user->contracts && $user->contracts->where('status', 'active')->first() && $user->contracts->where('status', 'active')->first()->images->count() > 0)
                                    <div class="mt-3">
                                        <h6>Hình ảnh hợp đồng hiện tại:</h6>
                                        <div class="row g-2">
                                            @foreach($user->contracts->where('status', 'active')->first()->images as $image)
                                            <div class="col-md-6 col-sm-6 col-6">
                                                <div class="card">
                                                    <img src="{{ $image->image_path }}" class="card-img-top" style="height: 120px; object-fit: cover;">
                                                    <div class="card-body p-2">
                                                        <small class="text-muted">Trang {{ $image->page_number }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nút submit -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn" style="background:#5DA444; color:#fff; border-color:#5DA444;">
                                <i class="bi bi-check-circle me-1"></i>Cập nhật
                            </button>
                            <a href="{{ route('users.index') }}" class="btn" style="background:#558EC1; color:#fff; border-color:#558EC1;">
                                <i class="bi bi-x-circle me-1"></i>Hủy
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@if($user->employee_type == 'official')
@push('scripts')
<script>
document.getElementById('contract_images').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    
    for (let i = 0; i < e.target.files.length; i++) {
        const file = e.target.files[i];
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const col = document.createElement('div');
                col.className = 'col-md-6 col-sm-6 col-6';
                col.innerHTML = `
                    <div class="card">
                        <img src="${e.target.result}" class="card-img-top" style="height: 120px; object-fit: cover;">
                        <div class="card-body p-2">
                            <small class="text-muted">Trang ${i + 1}</small>
                        </div>
                    </div>
                `;
                preview.appendChild(col);
            };
            reader.readAsDataURL(file);
        }
    }
});
</script>
@endpush
@endif
@endsection

<script>
function togglePasswordVisibility(inputId, buttonId, iconId) {
    const input = document.getElementById(inputId);
    const button = document.getElementById(buttonId);
    const icon = document.getElementById(iconId);
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        button.classList.remove('btn-outline-secondary');
        button.classList.add('btn-secondary');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        button.classList.remove('btn-secondary');
        button.classList.add('btn-outline-secondary');
    }
}
</script>
