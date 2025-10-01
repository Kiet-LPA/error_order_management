<section>
    <header class="mb-4">
        <h6 class="text-muted mb-2">Cập nhật thông tin cá nhân và địa chỉ email</h6>
    </header>

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
                    Email của bạn chưa được xác thực.
                </p>
            </div>
        @endif
    </div>
</section>
