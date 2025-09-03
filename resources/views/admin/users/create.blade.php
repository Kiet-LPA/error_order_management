@extends('layouts.master')

@section('title', 'Thêm nhân viên mới')

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
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Thêm nhân viên mới</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Tên</label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email <span class="text-muted">(tùy chọn nếu có số điện thoại)</span></label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Số điện thoại <span class="text-muted">(tùy chọn nếu có email)</span></label>
                <input type="tel" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="0123456789">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password', 'passwordToggle', 'passwordIcon')">
                        <i class="bi bi-eye" id="passwordIcon"></i>
                    </button>
                </div>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Xác nhận mật khẩu</label>
                <div class="input-group">
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password_confirmation', 'passwordConfirmToggle', 'passwordConfirmIcon')">
                        <i class="bi bi-eye" id="passwordConfirmIcon"></i>
                    </button>
                </div>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Vai trò</label>
                <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                    <option value="">-- Chọn vai trò --</option>
                                                    <option value="admin" {{ old('role')=='admin'?'selected':'' }}>Admin</option>
                                <option value="director" {{ old('role')=='director'?'selected':'' }}>Director</option>
                                <option value="manager" {{ old('role')=='manager'?'selected':'' }}>Manager</option>
                                <option value="employee" {{ old('role')=='employee'?'selected':'' }}>Employee</option>
                </select>
                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="department_id" class="form-label">Phòng ban (nếu có)</label>
                <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror">
                    <option value="">-- Không chọn --</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ old('department_id')==$department->id?'selected':'' }}>{{ $department->name }}</option>
                    @endforeach
                </select>
                @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn" style="background:#5DA444; color:#fff; border-color:#5DA444;">Lưu</button>
            <a href="{{ route('users.index') }}" class="btn" style="background:#558EC1; color:#fff; border-color:#558EC1;">Quay lại</a>
        </form>
    </div>
</div>

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
@endsection
