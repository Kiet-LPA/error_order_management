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

        <!-- Xóa tài khoản -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm border-danger">
                    <div class="card-header bg-danger">
                        <h5 class="mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>Xóa tài khoản
                        </h5>
                    </div>
                    <div class="card-body">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Alpine.js for JavaScript functionality -->
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection
