@extends('layouts.master')

@section('title', 'Cập nhật thông tin nhân viên mới')

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
                    <i class="bi bi-person-plus me-2"></i>
                    Cập nhật thông tin nhân viên mới
                </h2>
                <div>
                    <a href="{{ route('employees.new.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Quay lại
                    </a>
                </div>
            </div>

            <!-- Cảnh báo khi Director cố gắng sửa Admin -->
            @if(auth()->user()->isDirector() && $user->role == 'admin')
                <div class="alert alert-warning mb-4">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Cảnh báo:</strong> Bạn không thể chỉnh sửa thông tin của Admin. Chỉ có thể xem thông tin.
                </div>
            @endif

            <form action="{{ route('employees.new.update', $user) }}" method="POST">
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
                                    <label for="name" class="form-label">Họ tên <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required
                                           @if(auth()->user()->isDirector() && $user->role == 'admin') disabled @endif>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required
                                           @if(auth()->user()->isDirector() && $user->role == 'admin') disabled @endif>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Số điện thoại</label>
                                    <input type="tel" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" placeholder="0123456789"
                                           @if(auth()->user()->isDirector() && $user->role == 'admin') disabled @endif>
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Mật khẩu mới (bỏ trống nếu không đổi)</label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror"
                                               @if(auth()->user()->isDirector() && $user->role == 'admin') disabled @endif>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password', 'passwordToggle', 'passwordIcon')"
                                                @if(auth()->user()->isDirector() && $user->role == 'admin') disabled @endif>
                                            <i class="bi bi-eye" id="passwordIcon"></i>
                                        </button>
                                    </div>
                                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Xác nhận mật khẩu</label>
                                    <div class="input-group">
                                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
                                               @if(auth()->user()->isDirector() && $user->role == 'admin') disabled @endif>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password_confirmation', 'passwordConfirmToggle', 'passwordConfirmIcon')"
                                                @if(auth()->user()->isDirector() && $user->role == 'admin') disabled @endif>
                                            <i class="bi bi-eye" id="passwordConfirmIcon"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin phòng ban và chức vụ -->
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-building me-2"></i>Thông tin phòng ban
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="department_id" class="form-label">Phòng ban <span class="text-danger">*</span></label>
                                    <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror" required
                                            @if(auth()->user()->isDirector() && $user->role == 'admin') disabled @endif>
                                        <option value="">-- Chọn phòng ban --</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ old('department_id', $user->department_id)==$department->id?'selected':'' }}>{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <!-- Chọn nhiều phòng ban cho Director -->
                                <div class="mb-3" id="managed_departments_section" style="display: none;">
                                    <label class="form-label">Phòng ban được quản lý (tùy chọn)</label>
                                    <div class="alert alert-info">
                                        <small>
                                            <i class="bi bi-info-circle me-1"></i>
                                            Chọn các phòng ban mà Director này sẽ quản lý. Nếu không chọn phòng ban nào, Director sẽ quản lý tất cả phòng ban.
                                        </small>
                                    </div>
                                    <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                        @foreach($departments as $department)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="managed_departments[]" 
                                                       value="{{ $department->id }}" 
                                                       id="dept_{{ $department->id }}"
                                                       {{ in_array($department->id, old('managed_departments', $user->managedDepartments->pluck('id')->toArray())) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="dept_{{ $department->id }}">
                                                    {{ $department->name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <small class="form-text text-muted">
                                        Tích vào các phòng ban mà Director sẽ quản lý
                                    </small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="role" class="form-label">Vai trò <span class="text-danger">*</span></label>
                                    <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required
                                            @if(auth()->user()->isDirector() && $user->role == 'admin') disabled @endif>
                                        <option value="">-- Chọn vai trò --</option>
                                        <option value="employee" {{ old('role', $user->role) == 'employee' ? 'selected' : '' }}>Nhân viên</option>
                                        @if(auth()->user()->isAdmin() || auth()->user()->isDirector() || auth()->user()->isManager())
                                            <option value="manager" {{ old('role', $user->role) == 'manager' ? 'selected' : '' }}>Quản lý</option>
                                        @endif
                                        @if(auth()->user()->isAdmin() || auth()->user()->isDirector())
                                            <option value="director" {{ old('role', $user->role) == 'director' ? 'selected' : '' }}>Director</option>
                                        @endif
                                        @if(auth()->user()->isAdmin())
                                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Quản trị viên</option>
                                        @endif
                                    </select>
                                    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <!-- Trạng thái tài khoản -->
                                <div class="mb-3">
                                    <label for="account_status" class="form-label">Trạng thái tài khoản <span class="text-danger">*</span></label>
                                    <select name="account_status" id="account_status" class="form-select @error('account_status') is-invalid @enderror" required
                                            @if($user->isAdmin() || $user->isDirector()) disabled @endif>
                                        <option value="inactive" {{ old('account_status', $user->account_status ?? 'inactive') == 'inactive' ? 'selected' : '' }}>
                                            Vô hiệu hóa
                                        </option>
                                        <option value="active" {{ old('account_status', $user->account_status ?? 'inactive') == 'active' ? 'selected' : '' }}>
                                            Kích hoạt
                                        </option>
                                    </select>
                                    @error('account_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <div class="form-text">
                                        <i class="bi bi-info-circle me-1"></i>
                                        <strong>Vô hiệu hóa:</strong> Tài khoản không thể đăng nhập<br>
                                        <strong>Kích hoạt:</strong> Tài khoản có thể hoạt động bình thường
                                        @if($user->isAdmin() || $user->isDirector())
                                            <br><strong class="text-warning">⚠️ Admin và Director luôn luôn active</strong>
                                        @endif
                                    </div>
                                </div>
                                


                                <!-- Thông tin hợp đồng thử việc -->
                                <div class="mt-4">
                                    <h6 class="text-info mb-3">
                                        <i class="bi bi-file-earmark-text me-2"></i>Thông tin hợp đồng thử việc
                                    </h6>
                                    
                                    @if($user->activeContract)
                                        <!-- Hiển thị thông tin hợp đồng hiện tại -->
                                        <div class="alert alert-info">
                                            <small>
                                                <i class="bi bi-info-circle me-1"></i>
                                                Nhân viên này đã có hợp đồng thử việc. Bạn có thể chỉnh sửa thông tin bên dưới.
                                            </small>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="probation_salary" class="form-label">Lương thử việc (VNĐ) <span class="text-danger">*</span></label>
                                                    <input type="number" name="probation_salary" id="probation_salary" class="form-control @error('probation_salary') is-invalid @enderror" value="{{ old('probation_salary', $user->activeContract->probation_salary) }}" min="0" step="1000000" placeholder="VD: 5000000">
                                                    @error('probation_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="probation_period" class="form-label">Thời gian thử việc (tháng) <span class="text-danger">*</span></label>
                                                    <select name="probation_period" id="probation_period" class="form-select @error('probation_period') is-invalid @enderror">
                                                        <option value="">Chọn thời gian</option>
                                                        <option value="1" {{ old('probation_period', $user->activeContract->probation_period) == '1' ? 'selected' : '' }}>1 tháng</option>
                                                        <option value="2" {{ old('probation_period', $user->activeContract->probation_period) == '2' ? 'selected' : '' }}>2 tháng</option>
                                                        <option value="3" {{ old('probation_period', $user->activeContract->probation_period) == '3' ? 'selected' : '' }}>3 tháng</option>
                                                        <option value="6" {{ old('probation_period', $user->activeContract->probation_period) == '6' ? 'selected' : '' }}>6 tháng</option>
                                                        <option value="12" {{ old('probation_period', $user->activeContract->probation_period) == '12' ? 'selected' : '' }}>12 tháng</option>
                                                    </select>
                                                    @error('probation_period')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="start_date" class="form-label">Ngày bắt đầu thử việc <span class="text-danger">*</span></label>
                                            <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $user->activeContract->start_date->format('Y-m-d')) }}">
                                            @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="contract_status" class="form-label">Trạng thái hợp đồng</label>
                                            <select name="contract_status" id="contract_status" class="form-select @error('contract_status') is-invalid @enderror">
                                                <option value="active" {{ old('contract_status', $user->activeContract->status) == 'active' ? 'selected' : '' }}>Đang thử việc</option>
                                                <option value="completed" {{ old('contract_status', $user->activeContract->status) == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                                                <option value="terminated" {{ old('contract_status', $user->activeContract->status) == 'terminated' ? 'selected' : '' }}>Đã chấm dứt</option>
                                            </select>
                                            @error('contract_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    @else
                                        <!-- Form thêm hợp đồng thử việc -->
                                        <div class="alert alert-warning">
                                            <small>
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                Nhân viên này chưa có hợp đồng thử việc.
                                            </small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="add_contract" name="add_contract" value="1" {{ old('add_contract') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="add_contract">
                                                    Thêm hợp đồng thử việc cho nhân viên này
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div id="contract_fields" style="display: none;">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="new_probation_salary" class="form-label">Lương thử việc (VNĐ) <span class="text-danger">*</span></label>
                                                        <input type="number" name="probation_salary" id="new_probation_salary" class="form-control @error('probation_salary') is-invalid @enderror" value="{{ old('probation_salary') }}" min="0" step="1000000" placeholder="VD: 5000000">
                                                        @error('probation_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="new_probation_period" class="form-label">Thời gian thử việc (tháng) <span class="text-danger">*</span></label>
                                                        <select name="probation_period" id="new_probation_period" class="form-select @error('probation_period') is-invalid @enderror">
                                                            <option value="">Chọn thời gian</option>
                                                            <option value="1" {{ old('probation_period') == '1' ? 'selected' : '' }}>1 tháng</option>
                                                            <option value="2" {{ old('probation_period') == '2' ? 'selected' : '' }}>2 tháng</option>
                                                            <option value="3" {{ old('probation_period') == '3' ? 'selected' : '' }}>3 tháng</option>
                                                            <option value="6" {{ old('probation_period') == '6' ? 'selected' : '' }}>6 tháng</option>
                                                            <option value="12" {{ old('probation_period') == '12' ? 'selected' : '' }}>12 tháng</option>
                                                        </select>
                                                        @error('probation_period')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="new_start_date" class="form-label">Ngày bắt đầu thử việc <span class="text-danger">*</span></label>
                                                <input type="date" name="start_date" id="new_start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', date('Y-m-d')) }}">
                                                @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                            @if(auth()->user()->isDirector() && $user->role == 'admin')
                                <button type="button" class="btn btn-secondary" disabled>
                                    <i class="bi bi-lock me-1"></i>Không thể cập nhật
                                </button>
                            @else
                                <button type="submit" class="btn" style="background:#5DA444; color:#fff; border-color:#5DA444;">
                                    <i class="bi bi-check-circle me-1"></i>Cập nhật
                                </button>
                            @endif
                            <a href="{{ route('employees.new.index') }}" class="btn" style="background:#558EC1; color:#fff; border-color:#558EC1;">
                                <i class="bi bi-x-circle me-1"></i>Hủy
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

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

// Hiển thị/ẩn các trường hợp đồng thử việc
document.addEventListener('DOMContentLoaded', function() {
    const addContractCheckbox = document.getElementById('add_contract');
    const contractFields = document.getElementById('contract_fields');
    
    if (addContractCheckbox && contractFields) {
        // Kiểm tra trạng thái ban đầu (bao gồm cả khi có lỗi validation)
        if (addContractCheckbox.checked || document.querySelector('.is-invalid[name="probation_salary"]')) {
            contractFields.style.display = 'block';
        }
        
        // Xử lý sự kiện thay đổi
        addContractCheckbox.addEventListener('change', function() {
            if (this.checked) {
                contractFields.style.display = 'block';
            } else {
                contractFields.style.display = 'none';
            }
        });
    }

    // Xử lý hiển thị/ẩn phần chọn nhiều phòng ban cho Director
    const roleSelect = document.getElementById('role');
    const managedDepartmentsSection = document.getElementById('managed_departments_section');
    const departmentSelect = document.getElementById('department_id');
    
    if (roleSelect && managedDepartmentsSection) {
        // Kiểm tra trạng thái ban đầu
        toggleManagedDepartmentsSection();
        
        // Xử lý sự kiện thay đổi role (chỉ khi không bị disable)
        if (!roleSelect.disabled) {
            roleSelect.addEventListener('change', toggleManagedDepartmentsSection);
        }
    }
    
    function toggleManagedDepartmentsSection() {
        const selectedRole = roleSelect.value;
        const isDirector = selectedRole === 'director';
        
        if (isDirector) {
            managedDepartmentsSection.style.display = 'block';
            // Làm cho phòng ban chính không bắt buộc khi là Director
            departmentSelect.required = false;
            departmentSelect.classList.remove('is-invalid');
            // Xóa validation error nếu có
            const errorElement = departmentSelect.parentNode.querySelector('.invalid-feedback');
            if (errorElement) {
                errorElement.remove();
            }
        } else {
            managedDepartmentsSection.style.display = 'none';
            // Làm cho phòng ban chính bắt buộc khi không phải Director
            departmentSelect.required = true;
        }
    }
    
    // Disable tất cả form fields khi Director không thể sửa
    function disableFormFields() {
        const isDirectorRestricted = {{ auth()->user()->isDirector() && $user->role == 'admin' ? 'true' : 'false' }};
        
        if (isDirectorRestricted) {
            const allInputs = document.querySelectorAll('input, select, textarea');
            allInputs.forEach(input => {
                input.disabled = true;
            });
            
            // Disable tất cả buttons trừ nút "Quay lại"
            const allButtons = document.querySelectorAll('button:not([href])');
            allButtons.forEach(button => {
                button.disabled = true;
            });
        }
    }
    
    // Gọi function khi trang load
    disableFormFields();
});
</script>
