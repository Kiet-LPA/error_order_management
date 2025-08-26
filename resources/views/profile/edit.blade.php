@extends('layouts.master')

@section('title', 'Hồ sơ cá nhân')

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

.form-control:focus {
    border-color: #558EC1;
    box-shadow: 0 0 0 0.2rem rgba(85, 142, 193, 0.25);
}
.form-label {
    color: #374151;
    font-weight: 500;
}

.btn-primary {
    background: linear-gradient(90deg, #558EC1 0%, #5DA444 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(90deg, #4a7ba8 0%, #52903a 100%);
    transform: translateY(-1px);
    box-shadow: 0 0.25rem 0.5rem rgba(85, 142, 193, 0.25);
}

.btn-danger {
    background: linear-gradient(90deg, #dc3545 0%, #c82333 100%);
    border: none;
}

.btn-danger:hover {
    background: linear-gradient(90deg, #c82333 0%, #a71e2a 100%);
    transform: translateY(-1px);
    box-shadow: 0 0.25rem 0.5rem rgba(220, 53, 69, 0.25);
}

/* Fix for mobile layout */
@media (max-width: 768px) {
    .col-md-6 {
        margin-bottom: 1rem;
    }
    
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    .d-flex.justify-content-between .btn {
        align-self: flex-start;
    }
    
    .card {
        margin-bottom: 1rem;
    }
    
    .alert-sm {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }
}

/* Ensure proper spacing */
.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-body {
    padding: 1.5rem;
}

/* Fix modal on mobile */
@media (max-width: 576px) {
    .modal-dialog {
        margin: 0.5rem;
    }
    
    .modal-content {
        border-radius: 0.5rem;
    }
}
</style>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">
                <i class="bi bi-person-circle me-2"></i>
                Hồ sơ cá nhân
            </h2>
            <div>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Quay lại
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Thông tin cá nhân -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-person me-2"></i>Thông tin cá nhân
                        </h5>
                    </div>
                    <div class="card-body">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
            </div>

            <!-- Đổi mật khẩu -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-shield-lock me-2"></i>Đổi mật khẩu
                        </h5>
                    </div>
                    <div class="card-body">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>
        </div>

        <!-- Thông tin hợp đồng và phòng ban -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-file-earmark-text me-2"></i>Thông tin hợp đồng và phòng ban
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Thông tin cơ bản -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Vai trò</label>
                                    <div class="form-control-plaintext">
                                        @switch($user->role)
                                            @case('admin')
                                                <span class="badge bg-danger">Quản trị viên</span>
                                                @break
                                            @case('manager')
                                                <span class="badge bg-warning">Quản lý</span>
                                                @break
                                            @case('employee')
                                                <span class="badge bg-primary">Nhân viên</span>
                                                @break
                                        @endswitch
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Loại nhân viên</label>
                                    <div class="form-control-plaintext">
                                        @if($user->employee_type == 'new')
                                            <span class="badge bg-info">Nhân viên mới</span>
                                        @else
                                            <span class="badge bg-success">Nhân viên chính thức</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Phòng ban</label>
                                    <div class="form-control-plaintext">
                                        @if($user->department)
                                            {{ $user->department->name }}
                                        @else
                                            <span class="text-muted">Chưa được phân công</span>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($user->position)
                                <div class="mb-3">
                                    <label class="form-label">Chức vụ</label>
                                    <div class="form-control-plaintext">
                                        {{ $user->position }}
                                    </div>
                                </div>
                                @endif
                            </div>
                            
                            <!-- Thông tin hợp đồng -->
                            <div class="col-md-6">
                                @if($user->activeContract)
                                    @php $contract = $user->activeContract; @endphp
                                    <div class="mb-3">
                                        <label class="form-label">Lương thử việc</label>
                                        <div class="form-control-plaintext">
                                            {{ number_format($contract->probation_salary) }} VNĐ
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Thời gian thử việc</label>
                                        <div class="form-control-plaintext">
                                            {{ $contract->probation_period }} tháng
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Ngày bắt đầu</label>
                                        <div class="form-control-plaintext">
                                            {{ $contract->start_date->format('d/m/Y') }}
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Ngày kết thúc</label>
                                        <div class="form-control-plaintext">
                                            {{ $contract->end_date->format('d/m/Y') }}
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Trạng thái hợp đồng</label>
                                        <div class="form-control-plaintext">
                                            @switch($contract->status)
                                                @case('active')
                                                    <span class="badge bg-success">Đang thử việc</span>
                                                    @break
                                                @case('completed')
                                                    <span class="badge bg-info">Hoàn thành</span>
                                                    @break
                                                @case('terminated')
                                                    <span class="badge bg-danger">Đã chấm dứt</span>
                                                    @break
                                            @endswitch
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        <small>
                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                            Bạn chưa có hợp đồng thử việc.
                                        </small>
                                    </div>
                                @endif
                                
                                @if($user->social_insurance_number || $user->health_insurance_number || $user->personal_identification_number)
                                    <hr class="my-3">
                                    <h6 class="text-muted mb-3">Thông tin bảo hiểm</h6>
                                    
                                    @if($user->social_insurance_number)
                                    <div class="mb-3">
                                        <label class="form-label">Số bảo hiểm xã hội</label>
                                        <div class="form-control-plaintext">
                                            {{ $user->social_insurance_number }}
                                        </div>
                                    </div>
                                    @endif
                                    
                                    @if($user->health_insurance_number)
                                    <div class="mb-3">
                                        <label class="form-label">Số bảo hiểm y tế</label>
                                        <div class="form-control-plaintext">
                                            {{ $user->health_insurance_number }}
                                        </div>
                                    </div>
                                    @endif
                                    
                                    @if($user->personal_identification_number)
                                    <div class="mb-3">
                                        <label class="form-label">Số CMND/CCCD</label>
                                        <div class="form-control-plaintext">
                                            {{ $user->personal_identification_number }}
                                        </div>
                                    </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Alpine.js for JavaScript functionality -->
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection
