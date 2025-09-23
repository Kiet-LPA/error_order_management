<div class="avatar-section">
    <!-- Current Avatar Display -->
    <div class="mb-3 text-center">
        <div class="avatar-container position-relative d-inline-block">
            <img id="avatar-preview" 
                 src="{{ isset($user) ? $user->avatar_url : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwIiBoZWlnaHQ9IjEyMCIgdmlld0JveD0iMCAwIDEyMCAxMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iNjAiIGN5PSI2MCIgcj0iNjAiIGZpbGw9IiNmOGY5ZmEiLz48Y2lyY2xlIGN4PSI2MCIgY3k9IjQ1IiByPSIxOCIgZmlsbD0iIzZkNzM4MCIvPjxwYXRoIGQ9Ik0zMCA5MEMzMCA3NS42NDEgNDIuNjQxIDYzIDU3IDYzSDYzQzc3LjM1OSA2MyA5MCA3NS42NDEgOTAgOTBWMTAySDMwVjkwWiIgZmlsbD0iIzZkNzM4MCIvPjwvc3ZnPg==' }}" 
                 alt="Avatar" 
                 class="rounded-circle border border-3 border-primary" 
                 style="width: 120px; height: 120px; object-fit: cover;"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
            
            <i class="bi bi-person-circle fs-1 text-primary position-absolute top-50 start-50 translate-middle" 
               style="display: none; z-index: 1;"></i>
        </div>
    </div>

    <!-- Avatar Input (No Form) -->
    <div class="mb-3">
        <label for="avatar-input" class="form-label">Ảnh đại diện</label>
        <input type="file" 
               id="avatar-input" 
               name="avatar" 
               accept="image/*" 
               class="form-control @error('avatar') is-invalid @enderror">
        @error('avatar')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
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

    <!-- Help text -->
    <small class="text-muted d-block mt-2">
        <i class="bi bi-info-circle me-1"></i>
        Định dạng: JPG, PNG, GIF. Tối đa 2MB
    </small>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const avatarInput = document.getElementById('avatar-input');
    const avatarPreview = document.getElementById('avatar-preview');
    const removeAvatarCheckbox = document.getElementById('remove-avatar');

    // Handle file selection and preview
    avatarInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('Kích thước file không được vượt quá 2MB');
                avatarInput.value = ''; // Clear input
                return;
            }

            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('Vui lòng chọn file ảnh');
                avatarInput.value = ''; // Clear input
                return;
            }

            // Show preview using FileReader
            const reader = new FileReader();
            reader.onload = function(e) {
                avatarPreview.src = e.target.result;
                avatarPreview.style.display = 'block';
                // Hide fallback icon
                const fallbackIcon = avatarPreview.nextElementSibling;
                if (fallbackIcon) {
                    fallbackIcon.style.display = 'none';
                }
            };
            reader.readAsDataURL(file);

            // Uncheck remove avatar if user selects new file
            if (removeAvatarCheckbox) {
                removeAvatarCheckbox.checked = false;
            }
        }
    });

    // Handle remove avatar checkbox
    if (removeAvatarCheckbox) {
        removeAvatarCheckbox.addEventListener('change', function(e) {
            if (e.target.checked) {
                // Clear file input
                avatarInput.value = '';
                // Show default avatar
                avatarPreview.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwIiBoZWlnaHQ9IjEyMCIgdmlld0JveD0iMCAwIDEyMCAxMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iNjAiIGN5PSI2MCIgcj0iNjAiIGZpbGw9IiNmOGY5ZmEiLz48Y2lyY2xlIGN4PSI2MCIgY3k9IjQ1IiByPSIxOCIgZmlsbD0iIzZkNzM4MCIvPjxwYXRoIGQ9Ik0zMCA5MEMzMCA3NS42NDEgNDIuNjQxIDYzIDU3IDYzSDYzQzc3LjM1OSA2MyA5MCA3NS42NDEgOTAgOTBWMTAySDMwVjkwWiIgZmlsbD0iIzZkNzM4MCIvPjwvc3ZnPg==';
                avatarPreview.style.display = 'block';
                // Show fallback icon
                const fallbackIcon = avatarPreview.nextElementSibling;
                if (fallbackIcon) {
                    fallbackIcon.style.display = 'block';
                }
            }
        });
    }
});
</script>