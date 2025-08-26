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
                    <form action="{{ route('employees.new.store') }}" method="POST">
                        @csrf
                        
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
                                            <i class="fas fa-eye" id="passwordIcon"></i>
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
                                            <i class="fas fa-eye" id="passwordConfirmIcon"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h6 class="text-success mb-3">
                                    <i class="bi bi-building me-2"></i>Thông tin phòng ban
                                </h6>

                                <div class="mb-3">
                                    <label for="department_id" class="form-label">Phòng ban <span class="text-danger">*</span></label>
                                    <select class="form-select @error('department_id') is-invalid @enderror" 
                                            id="department_id" name="department_id" required>
                                        <option value="">Chọn phòng ban</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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
