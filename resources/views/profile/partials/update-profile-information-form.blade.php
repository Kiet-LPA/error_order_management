<section>
    <header class="mb-4">
        <h6 class="text-muted mb-2">Cập nhật thông tin cá nhân và địa chỉ email</h6>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="form-label">Họ tên</label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-sm text-warning">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        {{ __('Email của bạn chưa được xác thực.') }}

                        <button form="send-verification" class="btn btn-link btn-sm p-0 text-decoration-none">
                            {{ __('Nhấn vào đây để gửi lại email xác thực.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <div class="alert alert-success alert-sm mt-2">
                            <i class="bi bi-check-circle me-1"></i>
                            {{ __('Email xác thực mới đã được gửi đến địa chỉ email của bạn.') }}
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i>Lưu thay đổi
            </button>

            @if (session('status') === 'profile-updated')
                <div class="alert alert-success alert-sm mb-0" 
                     x-data="{ show: true }"
                     x-show="show"
                     x-transition
                     x-init="setTimeout(() => show = false, 3000)">
                    <i class="bi bi-check-circle me-1"></i>Đã lưu thành công!
                </div>
            @endif
        </div>
    </form>
</section>
