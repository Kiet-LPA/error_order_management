@extends('layouts.guest')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center bg-gradient-primary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        {{-- Logo --}}
                        <div class="text-center mb-4">
                            <x-application-logo />
                        </div>
                        
                        <h2 class="text-center mb-4 fw-bold text-dark">Quên mật khẩu</h2>
                        
                        <p class="text-center text-muted mb-4">
                            Nhập email hoặc số điện thoại của bạn để đặt lại mật khẩu
                        </p>

                        <!-- Session Status -->
                        <x-auth-session-status class="mb-4" :status="session('status')" />

                        <!-- Validation Errors -->
                        <x-auth-validation-errors class="mb-4" :errors="$errors" />

                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf

                            <!-- Email or Phone -->
                            <div class="mb-4">
                                <x-input-label for="email_or_phone" :value="__('Email hoặc Số điện thoại')" class="form-label fw-semibold" />
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-person-lines-fill text-muted"></i>
                                    </span>
                                    <x-text-input id="email_or_phone" 
                                                 class="form-control border-start-0 ps-0" 
                                                 type="text" 
                                                 name="email_or_phone" 
                                                 :value="old('email_or_phone')" 
                                                 required 
                                                 autofocus 
                                                 placeholder="Nhập email hoặc số điện thoại" />
                                </div>
                                <small class="text-muted">Bạn có thể sử dụng email hoặc số điện thoại đã đăng ký</small>
                            </div>

                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                                    <i class="bi bi-envelope me-2"></i>
                                    Gửi link đặt lại mật khẩu
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center">
                            <a href="{{ route('login') }}" class="text-decoration-none text-primary">
                                <i class="bi bi-arrow-left me-1"></i>
                                Quay lại đăng nhập
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #558EC1 0%, #5DA444 100%);
}

.card {
    border-radius: 15px;
    backdrop-filter: blur(10px);
}

.input-group-text {
    border-radius: 8px 0 0 8px;
    border: 1px solid #dee2e6;
}

.form-control:focus {
    border-color: #558EC1;
    box-shadow: 0 0 0 0.2rem rgba(85, 142, 193, 0.25);
}
</style>
@endsection
