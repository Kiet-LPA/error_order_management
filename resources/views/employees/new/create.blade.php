@extends('layouts.master')

@section('title', 'Thêm nhân viên mới')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="bi bi-person-plus me-2"></i>
                    Thêm nhân viên mới
                </h2>
                <a href="{{ route('employees.new.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Quay lại
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Thông tin nhân viên mới</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('employees.new.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
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
                        
                        <!-- Thông tin cơ bản -->
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="bi bi-person me-2"></i>Thông tin cơ bản
                                </h6>
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Họ tên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Số điện thoại</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                               id="password" name="password" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password', 'passwordToggle', 'passwordIcon')">
                                            <i class="bi bi-eye" id="passwordIcon"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" 
                                               id="password_confirmation" name="password_confirmation" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password_confirmation', 'passwordConfirmToggle', 'passwordConfirmIcon')">
                                            <i class="bi bi-eye" id="passwordConfirmIcon"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h6 class="text-success mb-3">
                                    <i class="bi bi-building me-2"></i>Thông tin phòng ban
                                </h6>

                                <div class="mb-3">
                                    <label class="form-label">Phòng ban <span class="text-danger">*</span></label>
                                    <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                        @foreach($departments as $department)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="department_ids[]" value="{{ $department->id }}" 
                                                       id="department_{{ $department->id }}" 
                                                       {{ in_array($department->id, old('department_ids', [])) ? 'checked' : '' }}
                                                       onchange="updatePrimaryDepartment()">
                                                <label class="form-check-label" for="department_{{ $department->id }}">
                                                    {{ $department->name }}
                                                    <span class="badge bg-primary ms-2" id="primary_badge_{{ $department->id }}" style="display: none;">Chính</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="form-text">Chọn một hoặc nhiều phòng ban. Phòng ban đầu tiên sẽ là phòng ban chính.</div>
                                    @error('department_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    @error('department_ids.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    
                                    <!-- Hidden input for primary department -->
                                    <input type="hidden" name="primary_department_id" id="primary_department_id" value="{{ old('primary_department_id') }}">
                                </div>

                                <div class="mb-3">
                                    <label for="position" class="form-label">Chức vụ</label>
                                    <select name="position" id="position" class="form-select @error('position') is-invalid @enderror">
                                        <option value="">Chọn chức vụ</option>
                                        <option value="Nhân Viên Chính Thức" {{ old('position') == 'Nhân Viên Chính Thức' ? 'selected' : '' }}>Nhân Viên Chính Thức</option>
                                        <option value="Nhân Viên Thử Việc" {{ old('position') == 'Nhân Viên Thử Việc' ? 'selected' : '' }}>Nhân Viên Thử Việc</option>
                                        <option value="Nhân Viên Remote" {{ old('position') == 'Nhân Viên Remote' ? 'selected' : '' }}>Nhân Viên Remote</option>
                                    </select>
                                    @error('position')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <h6 class="text-warning mb-3">
                                    <i class="bi bi-file-earmark-text me-2"></i>Thông tin hợp đồng thử việc
                                </h6>

                                <div class="mb-3">
                                    <label for="probation_salary" class="form-label">Lương thử việc (VNĐ) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('probation_salary') is-invalid @enderror" 
                                           id="probation_salary" name="probation_salary" value="{{ old('probation_salary') }}" 
                                           min="0" step="1000000" required>
                                    @error('probation_salary')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="probation_period" class="form-label">Thời gian thử việc (tháng) <span class="text-danger">*</span></label>
                                    <select class="form-select @error('probation_period') is-invalid @enderror" 
                                            id="probation_period" name="probation_period" required>
                                        <option value="">Chọn thời gian</option>
                                        <option value="1" {{ old('probation_period') == 1 ? 'selected' : '' }}>1 tháng</option>
                                        <option value="2" {{ old('probation_period') == 2 ? 'selected' : '' }}>2 tháng</option>
                                        <option value="3" {{ old('probation_period') == 3 ? 'selected' : '' }}>3 tháng</option>
                                        <option value="6" {{ old('probation_period') == 6 ? 'selected' : '' }}>6 tháng</option>
                                    </select>
                                    @error('probation_period')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Ngày bắt đầu thử việc <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                           id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>



                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('employees.new.index') }}" class="btn btn-secondary">Hủy</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i>Thêm nhân viên
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

<script>
// Function để cập nhật phòng ban chính
function updatePrimaryDepartment() {
    const checkboxes = document.querySelectorAll('input[name="department_ids[]"]:checked');
    const primaryInput = document.getElementById('primary_department_id');
    
    // Ẩn tất cả badge "Chính"
    document.querySelectorAll('[id^="primary_badge_"]').forEach(badge => {
        badge.style.display = 'none';
    });
    
    if (checkboxes.length > 0) {
        // Nếu có phòng ban được chọn, phòng ban đầu tiên sẽ là chính
        const firstChecked = checkboxes[0];
        const departmentId = firstChecked.value;
        primaryInput.value = departmentId;
        
        // Hiển thị badge "Chính" cho phòng ban đầu tiên
        const primaryBadge = document.getElementById('primary_badge_' + departmentId);
        if (primaryBadge) {
            primaryBadge.style.display = 'inline';
        }
    } else {
        primaryInput.value = '';
    }
}

// Khởi tạo khi trang load
document.addEventListener('DOMContentLoaded', function() {
    updatePrimaryDepartment();
});

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
