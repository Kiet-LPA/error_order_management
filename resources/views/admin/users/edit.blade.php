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

            <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data" id="edit-user-form">
                @csrf
                @method('PUT')
                
                <!-- Avatar Section - Centered -->
                <div class="row justify-content-center mb-4">
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-image me-2"></i>Ảnh đại diện
                                </h5>
                            </div>
                            <div class="card-body text-center">
                                @include('profile.partials.avatar-form')
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Sections -->
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
                                            <i class="bi bi-eye" id="passwordIcon"></i>
                                        </button>
                                    </div>
                                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Xác nhận mật khẩu</label>
                                    <div class="input-group">
                                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password_confirmation', 'passwordConfirmToggle', 'passwordConfirmIcon')">
                                            <i class="bi bi-eye" id="passwordConfirmIcon"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="role" class="form-label">Vai trò</label>
                                    <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                                                                        <option value="admin" {{ old('role', $user->role)=='admin'?'selected':'' }}>Admin</option>
                                <option value="director" {{ old('role', $user->role)=='director'?'selected':'' }}>Director</option>
                                <option value="manager" {{ old('role', $user->role)=='manager'?'selected':'' }}>Manager</option>
                                <option value="employee" {{ old('role', $user->role)=='employee'?'selected':'' }}>Employee</option>
                                    </select>
                                    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="account_status" class="form-label">Trạng thái tài khoản</label>
                                    <select name="account_status" id="account_status" class="form-select @error('account_status') is-invalid @enderror" required>
                                        <option value="active" {{ old('account_status', $user->account_status)=='active'?'selected':'' }}>Hoạt động</option>
                                        <option value="inactive" {{ old('account_status', $user->account_status)=='inactive'?'selected':'' }}>Không hoạt động</option>
                                    </select>
                                    @error('account_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Phòng ban</label>
                                    <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                        @foreach($departments as $department)
                                            @php
                                                $isChecked = in_array($department->id, old('department_ids', $user->departments->pluck('id')->toArray()));
                                            @endphp
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="department_ids[]" value="{{ $department->id }}" 
                                                       id="department_{{ $department->id }}" 
                                                       {{ $isChecked ? 'checked' : '' }}
                                                <label class="form-check-label" for="department_{{ $department->id }}">
                                                    {{ $department->name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="form-text">
                                        <strong>Manager/Employee:</strong> Bắt buộc chọn ít nhất một phòng ban.<br>
                                        <strong>Director/Admin:</strong> Nếu không chọn phòng ban nào, sẽ mặc định quản lý tất cả phòng ban.
                                    </div>
                                    @error('department_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    @error('department_ids.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    
                                    
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
                                    <select name="position" id="position" class="form-select @error('position') is-invalid @enderror">
                                        <option value="">Chọn chức vụ</option>
                                        <option value="Nhân Viên Chính Thức" {{ old('position', $user->position) == 'Nhân Viên Chính Thức' ? 'selected' : '' }}>Nhân Viên Chính Thức</option>
                                        <option value="Nhân Viên Thử Việc" {{ old('position', $user->position) == 'Nhân Viên Thử Việc' ? 'selected' : '' }}>Nhân Viên Thử Việc</option>
                                        <option value="Nhân Viên Remote" {{ old('position', $user->position) == 'Nhân Viên Remote' ? 'selected' : '' }}>Nhân Viên Remote</option>
                                    </select>
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
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thông tin hợp đồng chính thức -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-file-earmark-text me-2"></i>Thông tin hợp đồng chính thức
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="contract_salary" class="form-label">Lương chính thức (VNĐ)</label>
                                            <input type="number" name="contract_salary" id="contract_salary" class="form-control @error('contract_salary') is-invalid @enderror" value="{{ old('contract_salary', $user->activeContract ? $user->activeContract->probation_salary : '') }}" placeholder="VD: 40000000">
                                            @error('contract_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="contract_start_date" class="form-label">Ngày bắt đầu hợp đồng</label>
                                            <input type="date" name="contract_start_date" id="contract_start_date" class="form-control @error('contract_start_date') is-invalid @enderror" value="{{ old('contract_start_date', $user->activeContract ? $user->activeContract->start_date->format('Y-m-d') : '') }}">
                                            @error('contract_start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="contract_period" class="form-label">Thời gian hợp đồng (tháng)</label>
                                            <select name="contract_period" id="contract_period" class="form-select @error('contract_period') is-invalid @enderror">
                                                <option value="">Chọn thời gian</option>
                                                @for($i = 1; $i <= 60; $i++)
                                                    <option value="{{ $i }}" {{ old('contract_period', $user->activeContract ? $user->activeContract->probation_period : '') == $i ? 'selected' : '' }}>{{ $i }} tháng</option>
                                                @endfor
                                            </select>
                                            @error('contract_period')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="contract_status" class="form-label">Trạng thái hợp đồng</label>
                                            <select name="contract_status" id="contract_status" class="form-select @error('contract_status') is-invalid @enderror">
                                                <option value="active" {{ old('contract_status', $user->activeContract ? $user->activeContract->status : '') == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                                                <option value="completed" {{ old('contract_status', $user->activeContract ? $user->activeContract->status : '') == 'completed' ? 'selected' : '' }}>Đã hoàn thành</option>
                                                <option value="terminated" {{ old('contract_status', $user->activeContract ? $user->activeContract->status : '') == 'terminated' ? 'selected' : '' }}>Đã chấm dứt</option>
                                            </select>
                                            @error('contract_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Bổ sung hợp đồng cho nhân viên chưa có -->
                                @if(!$user->activeContract)
                                <div class="mt-4">
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        <strong>Lưu ý:</strong> Nhân viên này chưa có hợp đồng. Vui lòng điền đầy đủ thông tin hợp đồng bên trên và tải lên hình ảnh hợp đồng để tạo hợp đồng mới.
                                    </div>
                                    
                                    <div class="d-flex justify-content-center">
                                        <button type="button" class="btn btn-primary" onclick="createNewContract()">
                                            <i class="bi bi-plus-circle me-2"></i>Tạo hợp đồng mới
                                        </button>
                                    </div>
                                </div>
                                @else
                                <div class="mt-4">
                                    <div class="alert alert-success">
                                        <i class="bi bi-check-circle me-2"></i>
                                        <strong>Thông báo:</strong> Nhân viên này đã có hợp đồng. Bạn có thể cập nhật thông tin hợp đồng bên trên.
                                    </div>
                                </div>
                                @endif
                                
                                <!-- Hình ảnh hợp đồng (cho tất cả nhân viên) -->
                                <div class="mt-4">
                                    <h6 class="text-info mb-3">
                                        <i class="bi bi-images me-2"></i>Hình ảnh hợp đồng
                                    </h6>
                                    
                                    <div class="mb-3">
                                        <label for="contract_images" class="form-label">Tải lên hình ảnh hợp đồng</label>
                                        <input type="file" class="form-control @error('contract_images.*') is-invalid @enderror" 
                                               id="contract_images" name="contract_images[]" multiple 
                                               accept="image/*">
                                        <div class="form-text">Có thể chọn nhiều file. Hỗ trợ: JPG, PNG, GIF. Tối đa 2MB mỗi file.</div>
                                        @error('contract_images.*')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div id="imagePreview" class="row g-2"></div>
                                    
                                    <!-- Hiển thị hình ảnh hiện có -->
                                    @if($user->activeContract && $user->activeContract->images->count() > 0)
                                    <div class="mt-3">
                                        <h6>Hình ảnh hợp đồng hiện tại:</h6>
                                        <div class="row g-2">
                                            @foreach($user->activeContract->images as $image)
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

// Function tạo hợp đồng mới
function createNewContract() {
    // Kiểm tra xem có thông tin hợp đồng chưa
    const salary = document.getElementById('contract_salary').value;
    const startDate = document.getElementById('contract_start_date').value;
    const period = document.getElementById('contract_period').value;
    const status = document.getElementById('contract_status').value;
    
    if (!salary || !startDate || !period || !status) {
        alert('Vui lòng điền đầy đủ thông tin hợp đồng (Lương, Ngày bắt đầu, Thời gian, Trạng thái) trước khi tạo hợp đồng.');
        return;
    }
    
    // Kiểm tra xem có hình ảnh hợp đồng chưa
    const contractImages = document.getElementById('contract_images').files;
    if (contractImages.length === 0) {
        alert('Vui lòng tải lên ít nhất một hình ảnh hợp đồng trước khi tạo hợp đồng.');
        return;
    }
    
    // Nếu đã đủ thông tin, submit form
    if (confirm('Bạn có chắc chắn muốn tạo hợp đồng mới cho nhân viên này?')) {
        document.querySelector('form').submit();
    }
}

</script>
@endpush
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

// Debug form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('edit-user-form');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    console.log('Form found:', form);
    console.log('Submit button found:', submitBtn);
    
    // Debug form submission - chỉ log, không can thiệp
    form.addEventListener('submit', function(e) {
        console.log('Form submit event triggered');
    });
    
    // Debug button click
    submitBtn.addEventListener('click', function(e) {
        console.log('Submit button clicked');
    });
});
</script>
