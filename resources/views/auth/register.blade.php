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
                        
                        <h2 class="text-center mb-4 fw-bold text-dark">Đăng ký tài khoản</h2>
                        
                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <!-- Name -->
                            <div class="mb-3">
                                <x-input-label for="name" :value="__('Họ và tên')" class="form-label fw-semibold" />
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-person text-muted"></i>
                                    </span>
                                    <x-text-input id="name" class="form-control border-start-0 ps-0" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Nhập họ và tên" />
                                </div>
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <!-- Phone -->
                            <div class="mb-3">
                                <x-input-label for="phone" :value="__('Số điện thoại')" class="form-label fw-semibold" />
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-telephone text-muted"></i>
                                    </span>
                                    <x-text-input id="phone" class="form-control border-start-0 ps-0" type="text" name="phone" :value="old('phone')" autocomplete="tel" placeholder="Nhập số điện thoại" />
                                </div>
                                <small class="text-muted">Có thể đăng ký bằng email hoặc số điện thoại.</small>
                                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                            </div>

                            <!-- Email Address (optional if phone) -->
                            <div class="mb-3">
                                <x-input-label for="email" :value="__('Email (tuỳ chọn)')" class="form-label fw-semibold" />
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-envelope text-muted"></i>
                                    </span>
                                    <x-text-input id="email" class="form-control border-start-0 ps-0" type="email" name="email" :value="old('email')" autocomplete="username" placeholder="Nhập email của bạn (nếu có)" />
                                </div>
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <x-input-label for="password" :value="__('Mật khẩu')" class="form-label fw-semibold" />
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-lock text-muted"></i>
                                    </span>
                                    <x-text-input id="password" class="form-control border-start-0 ps-0" type="password" name="password" required autocomplete="new-password" placeholder="Nhập mật khẩu" />
                                </div>
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-3">
                                <x-input-label for="password_confirmation" :value="__('Xác nhận mật khẩu')" class="form-label fw-semibold" />
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-shield-lock text-muted"></i>
                                    </span>
                                    <x-text-input id="password_confirmation" class="form-control border-start-0 ps-0" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Nhập lại mật khẩu" />
                                </div>
                                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                            </div>

                            <!-- Terms -->
                            <div class="mb-4">
                                <div class="form-check">
                                    <input id="terms" type="checkbox" class="form-check-input" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        <span class="ms-2 text-sm text-gray-600">
                                            Tôi đồng ý với <a href="#" class="text-primary text-decoration-none">điều khoản sử dụng</a> và <a href="#" class="text-primary text-decoration-none">chính sách bảo mật</a>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                                    <i class="bi bi-person-plus me-2"></i>
                                    {{ __('Đăng ký') }}
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="text-muted mb-0">
                                Đã có tài khoản? 
                                <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">Đăng nhập ngay</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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

.form-control {
    border-radius: 0 8px 8px 0;
    border: 1px solid #dee2e6;
    padding: 12px 16px;
    font-size: 16px;
}

.form-control:focus {
    border-color: #558EC1;
    box-shadow: 0 0 0 0.2rem rgba(85, 142, 193, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #558EC1 0%, #5DA444 100%);
    border: none;
    border-radius: 8px;
    padding: 12px 24px;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(85, 142, 193, 0.3);
}

.form-check-input:checked {
    background-color: #558EC1;
    border-color: #558EC1;
}

.text-primary {
    color: #558EC1 !important;
}

.fw-semibold {
    font-weight: 600;
}
</style>
@endsection
