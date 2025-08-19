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
                        
                        <h2 class="text-center mb-4 fw-bold text-dark">Đăng nhập</h2>
                        
                        <!-- Session Status -->
                        <x-auth-session-status class="mb-4" :status="session('status')" />

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <!-- Email or Phone -->
                            <div class="mb-3">
                                <x-input-label for="login" :value="__('Email hoặc Số điện thoại')" class="form-label fw-semibold" />
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-person-lines-fill text-muted"></i>
                                    </span>
                                    <x-text-input id="login" class="form-control border-start-0 ps-0" type="text" name="login" :value="old('login')" required autofocus autocomplete="username" placeholder="Nhập email hoặc số điện thoại" />
                                </div>
                                <small class="text-muted">Bạn có thể đăng nhập bằng email hoặc số điện thoại đã đăng ký</small>
                                <x-input-error :messages="$errors->get('login')" class="mt-2" />
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <x-input-label for="password" :value="__('Mật khẩu')" class="form-label fw-semibold" />
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-lock text-muted"></i>
                                    </span>
                                    <x-text-input id="password" class="form-control border-start-0 ps-0" type="password" name="password" required autocomplete="current-password" placeholder="Nhập mật khẩu" />
                                </div>
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <!-- Remember Me -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                                    <label class="form-check-label" for="remember_me">
                                        <span class="ms-2 text-sm text-gray-600">{{ __('Ghi nhớ đăng nhập') }}</span>
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex items-center justify-content-between mb-4">
                                @if (Route::has('password.request'))
                                    <a class="text-decoration-none text-primary" href="{{ route('password.request') }}">
                                        {{ __('Quên mật khẩu?') }}
                                    </a>
                                @endif
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    {{ __('Đăng nhập') }}
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="text-muted mb-0">
                                Chưa có tài khoản? 
                                <a href="{{ route('register') }}" class="text-decoration-none fw-semibold">Đăng ký ngay</a>
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
