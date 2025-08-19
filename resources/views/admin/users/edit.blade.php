@extends('layouts.master')

@section('title', 'Cập nhật nhân viên')

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
        <h5 class="mb-0">Cập nhật nhân viên</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
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
                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Xác nhận mật khẩu</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
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
            <button type="submit" class="btn" style="background:#5DA444; color:#fff; border-color:#5DA444;">Cập nhật</button>
            <a href="{{ route('users.index') }}" class="btn" style="background:#558EC1; color:#fff; border-color:#558EC1;">Quay lại</a>
        </form>
    </div>
</div>
@endsection
