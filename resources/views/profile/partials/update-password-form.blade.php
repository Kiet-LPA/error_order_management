<section>
    <header class="mb-4">
        <h6 class="text-muted mb-2">Đảm bảo tài khoản của bạn sử dụng mật khẩu dài và ngẫu nhiên để bảo mật</h6>
    </header>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="update_password_current_password" class="form-label">Mật khẩu hiện tại</label>
            <input type="password" name="current_password" id="update_password_current_password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password">
            @error('current_password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="update_password_password" class="form-label">Mật khẩu mới</label>
            <input type="password" name="password" id="update_password_password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
            @error('password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="update_password_password_confirmation" class="form-label">Xác nhận mật khẩu mới</label>
            <input type="password" name="password_confirmation" id="update_password_password_confirmation" class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
            @error('password_confirmation', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-shield-check me-1"></i>Lưu mật khẩu
            </button>

            @if (session('status') === 'password-updated')
                <div class="alert alert-success alert-sm mb-0" 
                     x-data="{ show: true }"
                     x-show="show"
                     x-transition
                     x-init="setTimeout(() => show = false, 3000)">
                    <i class="bi bi-check-circle me-1"></i>Mật khẩu đã được cập nhật!
                </div>
            @endif
        </div>
    </form>
</section>
