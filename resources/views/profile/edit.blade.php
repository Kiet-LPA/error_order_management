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

.btn-success {
    transition: all 0.3s ease;
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3) !important;
}

.btn-warning {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.02); }
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

.card.border-success {
    border: 3px solid #28a745 !important;
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.15) !important;
}

.card.border-success .card-body {
    background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
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

        <!-- Single Form for All Updates -->
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" id="profile-form">
            @csrf
            @method('PATCH')

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

            <!-- Single Save Button at Bottom -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow-sm border-success">
                        <div class="card-body text-center py-4">
                            <button type="submit" class="btn btn-success btn-lg px-5 py-3 fw-bold shadow-sm" id="save-all-btn">
                                <i class="bi bi-check-circle me-2"></i>
                                Lưu tất cả thay đổi
                            </button>
                            
                            @if (session('status') === 'profile-updated')
                                <div class="alert alert-success mt-3 mb-0" 
                                     x-data="{ show: true }"
                                     x-show="show"
                                     x-transition
                                     x-init="setTimeout(() => show = false, 4000)">
                                    <i class="bi bi-check-circle me-1"></i>
                                    {{ session('message', 'Cập nhật thành công!') }}
                                </div>
                            @endif
                            
                            @if ($errors->any())
                                <div class="alert alert-danger mt-3 mb-0">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Có lỗi xảy ra, vui lòng kiểm tra lại các trường
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>

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
                                        @if($user->departments->count() > 0)
                                            @foreach($user->departments as $department)
                                                <span class="badge bg-secondary me-1">
                                                    {{ $department->name }}
                                                </span>
                                            @endforeach
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('profile-form');
    const saveBtn = document.getElementById('save-all-btn');
    const originalBtnText = saveBtn.innerHTML;
    
    // Track changes
    let hasChanges = false;
    
    // Monitor form changes
    form.addEventListener('change', function() {
        hasChanges = true;
        saveBtn.classList.add('btn-warning');
        saveBtn.classList.remove('btn-success');
        saveBtn.innerHTML = '<i class="bi bi-exclamation-circle me-2"></i>Có thay đổi - Click để lưu';
    });
    
    // Monitor input changes
    form.addEventListener('input', function() {
        hasChanges = true;
        saveBtn.classList.add('btn-warning');
        saveBtn.classList.remove('btn-success');
        saveBtn.innerHTML = '<i class="bi bi-exclamation-circle me-2"></i>Có thay đổi - Click để lưu';
    });
    
    // Handle form submission
    form.addEventListener('submit', function(e) {
        // Debug: Check if avatar file is present
        const avatarInput = document.getElementById('avatar-input');
        if (avatarInput && avatarInput.files.length > 0) {
            console.log('Avatar file detected:', avatarInput.files[0].name);
        }
        
        // Debug: Check form data
        const formData = new FormData(form);
        console.log('Form data entries:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + (pair[1] instanceof File ? pair[1].name : pair[1]));
        }
        
        // Reset change tracking khi form được submit
        hasChanges = false;
        
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Đang lưu...';
    });
    
    // Warn before leaving if has unsaved changes
    window.addEventListener('beforeunload', function(e) {
        if (hasChanges) {
            e.preventDefault();
            e.returnValue = 'Bạn có thay đổi chưa lưu. Bạn có chắc muốn rời khỏi trang?';
        }
    });
});
</script>

@endsection
