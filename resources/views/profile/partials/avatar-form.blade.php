<div class="avatar-section">
    <!-- Current Avatar -->
    <div class="mb-3">
        <div class="avatar-container position-relative d-inline-block">
            <img id="avatar-preview" 
                 src="{{ isset($user) ? $user->avatar_url : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwIiBoZWlnaHQ9IjEyMCIgdmlld0JveD0iMCAwIDEyMCAxMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iNjAiIGN5PSI2MCIgcj0iNjAiIGZpbGw9IiNmOGY5ZmEiLz48Y2lyY2xlIGN4PSI2MCIgY3k9IjQ1IiByPSIxOCIgZmlsbD0iIzZkNzM4MCIvPjxwYXRoIGQ9Ik0zMCA5MEMzMCA3NS42NDEgNDIuNjQxIDYzIDU3IDYzSDYzQzc3LjM1OSA2MyA5MCA3NS42NDEgOTAgOTBWMTAySDMwVjkwWiIgZmlsbD0iIzZkNzM4MCIvPjwvc3ZnPg==' }}" 
                 alt="Avatar" 
                 class="rounded-circle border border-3 border-primary" 
                 style="width: 120px; height: 120px; object-fit: cover;"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
            
            <i class="bi bi-person-circle fs-1 text-primary position-absolute top-50 start-50 translate-middle" 
               style="display: none; z-index: 1;"></i>
            
            <!-- Loading overlay -->
            <div id="avatar-loading" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 rounded-circle d-none align-items-center justify-content-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Đang tải...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Form -->
    <form id="avatar-form" enctype="multipart/form-data" onsubmit="return false;">
        @csrf
        <div class="mb-3">
            <input type="file" 
                   id="avatar-input" 
                   name="avatar" 
                   accept="image/*" 
                   class="form-control @error('avatar') is-invalid @enderror"
                   style="display: none;">
            @error('avatar')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid gap-2">
            <button type="button" 
                    id="upload-btn" 
                    class="btn btn-primary btn-sm">
                <i class="bi bi-camera me-1"></i>Chọn ảnh
            </button>
            
            @if(isset($user) && $user->avatar)
                <button type="button" 
                        id="remove-btn" 
                        class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-trash me-1"></i>Xóa ảnh
                </button>
            @endif
        </div>
    </form>

    <!-- Help text -->
    <small class="text-muted d-block mt-2">
        <i class="bi bi-info-circle me-1"></i>
        Định dạng: JPG, PNG, GIF. Tối đa 2MB
    </small>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const avatarInput = document.getElementById('avatar-input');
    const uploadBtn = document.getElementById('upload-btn');
    const removeBtn = document.getElementById('remove-btn');
    const avatarPreview = document.getElementById('avatar-preview');
    const avatarLoading = document.getElementById('avatar-loading');
    const avatarForm = document.getElementById('avatar-form');

    // Click upload button to trigger file input
    uploadBtn.addEventListener('click', function() {
        avatarInput.click();
    });

    // Handle file selection
    avatarInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('Kích thước file không được vượt quá 2MB');
                return;
            }

            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('Vui lòng chọn file ảnh');
                return;
            }

            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                avatarPreview.src = e.target.result;
            };
            reader.readAsDataURL(file);

            // Upload file
            uploadAvatar(file);
        }
    });

    // Handle remove button
    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            if (confirm('Bạn có chắc chắn muốn xóa ảnh đại diện?')) {
                removeAvatar();
            }
        });
    }

    // Upload avatar function
    function uploadAvatar(file) {
        const formData = new FormData();
        formData.append('avatar', file);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        // Show loading
        avatarLoading.classList.remove('d-none');
        uploadBtn.disabled = true;

        fetch('{{ isset($user) && $user->id !== auth()->id() ? route("avatar.upload.user", $user->id) : route("avatar.upload") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update preview with new URL
                avatarPreview.src = data.avatar_url;
                
                // Show remove button if not exists
                if (!removeBtn) {
                    const newRemoveBtn = document.createElement('button');
                    newRemoveBtn.type = 'button';
                    newRemoveBtn.id = 'remove-btn';
                    newRemoveBtn.className = 'btn btn-outline-danger btn-sm';
                    newRemoveBtn.innerHTML = '<i class="bi bi-trash me-1"></i>Xóa ảnh';
                    newRemoveBtn.addEventListener('click', function() {
                        if (confirm('Bạn có chắc chắn muốn xóa ảnh đại diện?')) {
                            removeAvatar();
                        }
                    });
                    uploadBtn.parentNode.appendChild(newRemoveBtn);
                }

                // Show success message
                showAlert('success', data.message);
            } else {
                showAlert('error', data.message || 'Có lỗi xảy ra khi tải lên ảnh');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Có lỗi xảy ra khi tải lên ảnh');
        })
        .finally(() => {
            // Hide loading
            avatarLoading.classList.add('d-none');
            uploadBtn.disabled = false;
        });
    }

    // Remove avatar function
    function removeAvatar() {
        // Show loading
        avatarLoading.classList.remove('d-none');
        uploadBtn.disabled = true;

        fetch('{{ isset($user) && $user->id !== auth()->id() ? route("avatar.remove.user", $user->id) : route("avatar.remove") }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update preview with default avatar
                avatarPreview.src = data.avatar_url;
                
                // Hide remove button
                if (removeBtn) {
                    removeBtn.remove();
                }

                // Show success message
                showAlert('success', data.message);
            } else {
                showAlert('error', data.message || 'Có lỗi xảy ra khi xóa ảnh');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Có lỗi xảy ra khi xóa ảnh');
        })
        .finally(() => {
            // Hide loading
            avatarLoading.classList.add('d-none');
            uploadBtn.disabled = false;
        });
    }

    // Show alert function
    function showAlert(type, message) {
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.alert-custom');
        existingAlerts.forEach(alert => alert.remove());
        
        // Create alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show alert-custom`;
        alertDiv.style.position = 'fixed';
        alertDiv.style.top = '20px';
        alertDiv.style.right = '20px';
        alertDiv.style.zIndex = '9999';
        alertDiv.style.minWidth = '300px';
        alertDiv.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
});
</script>
