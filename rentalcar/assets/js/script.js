// Custom JavaScript functions

// Confirm delete action
function confirmDelete(message = 'Bạn có chắc chắn muốn xóa?') {
    return confirm(message);
}

// Show loading spinner
function showLoading(button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Đang xử lý...';
    button.disabled = true;
    return originalText;
}

// Hide loading spinner
function hideLoading(button, originalText) {
    button.innerHTML = originalText;
    button.disabled = false;
}

// Format date for input
function formatDateForInput(dateString) {
    const date = new Date(dateString);
    return date.toISOString().slice(0, 16);
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });
});

// Form validation
function validateRentalForm() {
    const startDate = new Date(document.getElementById('rental_start').value);
    const endDate = new Date(document.getElementById('rental_end').value);
    const now = new Date();
    
    if (startDate < now) {
        alert('Thời gian bắt đầu thuê phải sau thời điểm hiện tại!');
        return false;
    }
    
    if (endDate <= startDate) {
        alert('Thời gian kết thúc phải sau thời gian bắt đầu!');
        return false;
    }
    
    return true;
}

// Extension form validation
function validateExtensionForm() {
    const newEndDate = new Date(document.getElementById('new_rental_end').value);
    const now = new Date();
    
    if (newEndDate <= now) {
        alert('Thời gian gia hạn phải sau thời điểm hiện tại!');
        return false;
    }
    
    return true;
}

// Set minimum date for date inputs
document.addEventListener('DOMContentLoaded', function() {
    const dateInputs = document.querySelectorAll('input[type="datetime-local"]');
    const now = new Date();
    const minDate = now.toISOString().slice(0, 16);
    
    dateInputs.forEach(function(input) {
        input.min = minDate;
    });
});

// Toggle password visibility
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.querySelector(`[onclick="togglePassword('${inputId}')"] i`);
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

