<section>
    <header class="mb-4">
        <h6 class="text-muted mb-2">Đảm bảo tài khoản của bạn sử dụng mật khẩu dài và ngẫu nhiên để bảo mật</h6>
    </header>

    <div class="mb-3">
        <label for="update_password_current_password" class="form-label">Mật khẩu hiện tại</label>
        <div class="input-group flex-nowrap">
            <input type="password" name="current_password" id="update_password_current_password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password">
            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('update_password_current_password', 'currentPasswordToggle', 'currentPasswordIcon')">
                <i class="bi bi-eye" id="currentPasswordIcon"></i>
            </button>
        </div>
        @error('current_password', 'updatePassword')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="update_password_password" class="form-label">Mật khẩu mới (bỏ trống nếu không đổi)</label>
        <div class="input-group flex-nowrap">
            <input type="password" name="password" id="update_password_password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('update_password_password', 'newPasswordToggle', 'newPasswordIcon')">
                <i class="bi bi-eye" id="newPasswordIcon"></i>
            </button>
        </div>
        @error('password', 'updatePassword')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="update_password_password_confirmation" class="form-label">Xác nhận mật khẩu mới</label>
        <div class="input-group flex-nowrap">
            <input type="password" name="password_confirmation" id="update_password_password_confirmation" class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('update_password_password_confirmation', 'confirmPasswordToggle', 'confirmPasswordIcon')">
                <i class="bi bi-eye" id="confirmPasswordIcon"></i>
            </button>
        </div>
        @error('password_confirmation', 'updatePassword')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-1"></i>
        <small>Bỏ trống nếu không muốn đổi mật khẩu</small>
    </div>
</section>

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
