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
                        
                        <h2 class="text-center mb-4 fw-bold text-dark">Đặt lại mật khẩu</h2>
                        
                        @if(session('success'))
                            <div class="alert alert-success text-center mb-4">
                                <i class="bi bi-check-circle me-2"></i>
                                {{ session('success') }}
                            </div>
                        @endif

                        <!-- Validation Errors -->
                        <x-auth-validation-errors class="mb-4" :errors="$errors" />

                        <form method="POST" action="{{ route('password.reset.update') }}">
                            @csrf

                            <!-- Password -->
                            <div class="mb-4">
                                <x-input-label for="password" :value="__('Mật khẩu mới')" class="form-label fw-semibold" />
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-lock text-muted"></i>
                                    </span>
                                    <x-text-input id="password" 
                                                 class="form-control border-start-0 ps-0" 
                                                 type="password" 
                                                 name="password" 
                                                 required 
                                                 autocomplete="new-password" 
                                                 placeholder="Nhập mật khẩu mới" />
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password', 'passwordToggle', 'passwordIcon')">
                                        <i class="fas fa-eye" id="passwordIcon"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Mật khẩu phải có ít nhất 8 ký tự</small>
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-4">
                                <x-input-label for="password_confirmation" :value="__('Xác nhận mật khẩu')" class="form-label fw-semibold" />
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-shield-lock text-muted"></i>
                                    </span>
                                    <x-text-input id="password_confirmation" 
                                                 class="form-control border-start-0 ps-0" 
                                                 type="password" 
                                                 name="password_confirmation" 
                                                 required 
                                                 autocomplete="new-password" 
                                                 placeholder="Nhập lại mật khẩu mới" />
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password_confirmation', 'passwordConfirmToggle', 'passwordConfirmIcon')">
                                        <i class="fas fa-eye" id="passwordConfirmIcon"></i>
                                    </button>
                                </div>
                                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                            </div>

                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Đặt lại mật khẩu
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

.alert {
    border-radius: 10px;
    border: none;
}
</style>
@endsection
