<div class="avatar-section">
    <!-- Current Avatar Display -->
    <div class="mb-3 text-center">
        <div class="avatar-container position-relative d-inline-block">
            <img id="avatar-preview" 
                 src="{{ isset($user) ? $user->avatar_url : '' }}" 
                 data-default-avatar="{{ isset($user) ? $user->avatar_url : '' }}"
                 alt="Avatar" 
                 class="rounded-circle border border-3" 
                 style="width: 120px; height: 120px; object-fit: cover; box-shadow: 0 4px 15px rgba(0,0,0,0.15); transition: all 0.3s ease;">
        </div>
    </div>

    <!-- Avatar Input (No Form) -->
    <div class="mb-3">
        <label for="avatar-input" class="form-label fw-bold">
            <i class="bi bi-cloud-upload me-1"></i>Chọn ảnh mới
        </label>
        <input type="file" 
               id="avatar-input" 
               name="avatar" 
               accept="image/jpeg,image/png,image/jpg,image/gif" 
               class="form-control @error('avatar') is-invalid @enderror">
        @error('avatar')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted d-block mt-1">
            <i class="bi bi-info-circle me-1"></i>
            Chọn ảnh rồi nhấn "Lưu tất cả thay đổi" ở cuối trang
        </small>
        <div id="avatar-file-name" class="mt-2"></div>
    </div>

    <!-- Remove Avatar Checkbox (Optional) -->
    @if(isset($user) && $user->avatar)
    <div class="mb-3">
        <div class="form-check">
            <input class="form-check-input" 
                   type="checkbox" 
                   id="remove-avatar" 
                   name="remove_avatar" 
                   value="1">
            <label class="form-check-label text-danger" for="remove-avatar">
                <i class="bi bi-trash me-1"></i>Xóa ảnh đại diện hiện tại
            </label>
        </div>
    </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const avatarInput = document.getElementById('avatar-input');
    const avatarPreview = document.getElementById('avatar-preview');
    const removeAvatarCheckbox = document.getElementById('remove-avatar');

    // Handle file selection and preview
    avatarInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        const fileNameDisplay = document.getElementById('avatar-file-name');
        
        if (file) {
            // Validate file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('Kích thước file không được vượt quá 2MB');
                avatarInput.value = '';
                if (fileNameDisplay) fileNameDisplay.innerHTML = '';
                return;
            }

            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('Vui lòng chọn file ảnh');
                avatarInput.value = '';
                if (fileNameDisplay) fileNameDisplay.innerHTML = '';
                return;
            }

            // Show file name
            if (fileNameDisplay) {
                fileNameDisplay.innerHTML = '<div class="alert alert-success py-2"><i class="bi bi-check-circle me-1"></i><strong>Đã chọn:</strong> ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)</div>';
            }

            // Show preview using FileReader
            const reader = new FileReader();
            reader.onload = function(e) {
                avatarPreview.src = e.target.result;
                avatarPreview.style.transform = 'scale(1.05)';
                setTimeout(() => {
                    avatarPreview.style.transform = 'scale(1)';
                }, 300);
            };
            reader.readAsDataURL(file);

            // Uncheck remove avatar if user selects new file
            if (removeAvatarCheckbox) {
                removeAvatarCheckbox.checked = false;
            }
        } else {
            if (fileNameDisplay) fileNameDisplay.innerHTML = '';
        }
    });

    // Handle remove avatar checkbox
    if (removeAvatarCheckbox) {
        removeAvatarCheckbox.addEventListener('change', function(e) {
            if (e.target.checked) {
                // Clear file input
                avatarInput.value = '';
                // Reload page để hiển thị avatar mặc định mới
                avatarPreview.src = avatarPreview.getAttribute('data-default-avatar');
            }
        });
    }
});
</script>