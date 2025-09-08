<div class="dropdown">
    <button class="btn btn-link position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-bell fs-5"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge" id="notificationBadge" style="display: none;">
            0
        </span>
    </button>
    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="width: 350px; max-height: 400px; overflow-y: auto;">
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
            <h6 class="mb-0">Thông báo</h6>
            <div class="d-flex align-items-center">
                <span class="badge bg-primary me-2" id="notificationCount">0 Mới</span>
                <i class="bi bi-envelope"></i>
            </div>
        </div>
        <div class="p-2 border-bottom">
            <button class="btn btn-sm btn-outline-primary w-100" onclick="markAllAsRead()">
                Đánh dấu tất cả đã đọc
            </button>
        </div>
        <div id="notificationList" class="p-0">
            <!-- Notifications will be loaded here -->
        </div>
    </div>
</div>

<style>
.notification-item {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.notification-item:hover {
    background-color: #f8f9fa !important;
}

.notification-item:active {
    background-color: #e9ecef !important;
}
</style>

<script>
let notificationCount = 0;

// Load notifications
function loadNotifications() {
    fetch('{{ route("notifications.index") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayNotifications(data.notifications);
                updateNotificationBadge(data.unread_count);
            }
        })
        .catch(error => console.error('Error loading notifications:', error));
}

// Display notifications
function displayNotifications(notifications) {
    const container = document.getElementById('notificationList');
    
    if (notifications.length === 0) {
        container.innerHTML = `
            <div class="p-3 text-center text-muted">
                <i class="bi bi-bell-slash fs-1"></i>
                <p class="mb-0">Không có thông báo</p>
            </div>
        `;
        return;
    }
    
         container.innerHTML = notifications.map(notification => `
         <div class="notification-item p-3 border-bottom ${notification.is_read ? '' : 'bg-light'}" 
              data-notification-id="${notification.id}"
              onclick="handleNotificationClick(event, ${notification.id}, '${notification.type}', ${JSON.stringify(notification.data)})">
             <div class="d-flex align-items-start">
                 <div class="me-3">
                     <i class="bi bi-${getNotificationIcon(notification.type)} text-${getNotificationColor(notification.type)}"></i>
                 </div>
                 <div class="flex-grow-1">
                     <div class="d-flex justify-content-between align-items-start">
                         <div>
                             <h6 class="mb-1">${notification.title}</h6>
                             <p class="mb-1 small">${notification.message}</p>
                             <small class="text-muted">${formatTime(notification.created_at)}</small>
                         </div>
                         <div class="d-flex align-items-center">
                             ${!notification.is_read ? '<span class="badge bg-primary me-2"></span>' : ''}
                             <button class="btn btn-sm btn-link text-muted p-0" onclick="deleteNotification(${notification.id}); event.stopPropagation();">
                                 <i class="bi bi-x"></i>
                             </button>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
     `).join('');
}

// Get notification icon
function getNotificationIcon(type) {
    const icons = {
        'task_assigned': 'person-plus',
        'task_updated': 'pencil-square',
        'work_report_submitted': 'file-earmark-text',
        'task_followed': 'eye',
        'support_request_created': 'plus-circle',
        'support_request_approved': 'check-circle',
        'support_request_rejected': 'x-circle',
        'support_request_forwarded': 'arrow-right',
        'support_request_undone': 'arrow-counterclockwise',
        'support_request_cancelled': 'x-circle',
        'support_request_deleted': 'trash'
    };
    return icons[type] || 'bell';
}

// Get notification color
function getNotificationColor(type) {
    const colors = {
        'task_assigned': 'success',
        'task_updated': 'info',
        'work_report_submitted': 'warning',
        'task_followed': 'primary',
        'support_request_created': 'info',
        'support_request_approved': 'success',
        'support_request_rejected': 'danger',
        'support_request_forwarded': 'warning',
        'support_request_undone': 'secondary',
        'support_request_cancelled': 'danger',
        'support_request_deleted': 'danger'
    };
    return colors[type] || 'secondary';
}

// Update notification badge
function updateNotificationBadge(count) {
    const badge = document.getElementById('notificationBadge');
    const countElement = document.getElementById('notificationCount');
    
    if (count > 0) {
        badge.style.display = 'block';
        badge.textContent = count;
        countElement.textContent = count + ' Mới';
    } else {
        badge.style.display = 'none';
        countElement.textContent = '0 Mới';
    }
}

// Mark notification as read
function markAsRead(notificationId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        console.error('CSRF token not found');
        return;
    }
    
    const token = csrfToken.getAttribute('content');
    if (!token) {
        console.error('CSRF token content is empty');
        return;
    }
    
    fetch('{{ route("notifications.mark-as-read") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({ notification_id: notificationId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateNotificationBadge(data.unread_count);
            loadNotifications();
        }
    })
    .catch(error => console.error('Error marking notification as read:', error));
}

// Mark all notifications as read
function markAllAsRead() {
    console.log('Marking all as read...');
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        console.error('CSRF token not found');
        return;
    }
    
    const token = csrfToken.getAttribute('content');
    if (!token) {
        console.error('CSRF token content is empty');
        return;
    }
    
    fetch('{{ route("notifications.mark-all-as-read") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            updateNotificationBadge(0);
            loadNotifications();
        }
    })
    .catch(error => console.error('Error marking all notifications as read:', error));
}

// Delete notification
function deleteNotification(notificationId) {
    if (!confirm('Bạn có chắc chắn muốn xóa thông báo này?')) {
        return;
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        console.error('CSRF token not found');
        return;
    }
    
    const token = csrfToken.getAttribute('content');
    if (!token) {
        console.error('CSRF token content is empty');
        return;
    }
    
    fetch('{{ route("notifications.delete") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({ notification_id: notificationId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateNotificationBadge(data.unread_count);
            loadNotifications();
        }
    })
    .catch(error => console.error('Error deleting notification:', error));
}

// Handle notification click
function handleNotificationClick(event, notificationId, type, data) {
    // Mark as read first
    markAsRead(notificationId);
    
    // Navigate based on notification type
    switch(type) {
        case 'task_assigned':
        case 'task_updated':
        case 'task_followed':
            if (data.task_id) {
                window.location.href = `/task-detail/${data.task_id}`;
            }
            break;
        case 'work_report_submitted':
            if (data.report_id) {
                // Navigate to work reports page
                window.location.href = '{{ route("work-reports.index") }}';
            }
            break;
        case 'support_request_created':
        case 'support_request_approved':
        case 'support_request_rejected':
        case 'support_request_forwarded':
        case 'support_request_undone':
        case 'support_request_cancelled':
        case 'support_request_deleted':
            if (data.support_request_id) {
                window.location.href = `/support-requests/${data.support_request_id}`;
            }
            break;
        default:
            console.log('Unknown notification type:', type);
    }
}

// Format time
function formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diffInMinutes = Math.floor((now - date) / (1000 * 60));
    
    if (diffInMinutes < 1) return 'Just now';
    if (diffInMinutes < 60) return `${diffInMinutes} minutes ago`;
    
    const diffInHours = Math.floor(diffInMinutes / 60);
    if (diffInHours < 24) return `${diffInHours} hours ago`;
    
    const diffInDays = Math.floor(diffInHours / 24);
    if (diffInDays < 7) return `${diffInDays} days ago`;
    
    return date.toLocaleDateString();
}

// Load notifications when dropdown is shown
document.getElementById('notificationDropdown').addEventListener('click', function() {
    loadNotifications();
});

// Load initial notification count
document.addEventListener('DOMContentLoaded', function() {
    fetch('{{ route("notifications.unread-count") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateNotificationBadge(data.unread_count);
            }
        })
        .catch(error => console.error('Error loading notification count:', error));
});
</script>
