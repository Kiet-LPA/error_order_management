<div class="dropdown">
    <button class="btn btn-link position-relative" type="button" id="notificationDropdown" aria-expanded="false">
        <i class="bi bi-bell fs-5"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge" id="notificationBadge" style="display: none;">
            0
        </span>
    </button>
    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="margin-left: 45px; width: 350px; max-height: 400px; overflow-y: auto;">
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
            <h6 class="mb-0">Thông báo</h6>
            <div class="d-flex align-items-center">
                <span class="badge bg-primary me-2" id="notificationCount">0 Mới</span>
                <!-- <i class="bi bi-envelope"></i> -->
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

/* Responsive notification dropdown */
@media (max-width: 768px) {
    /* Notification dropdown specific */
    #notificationDropdown + .dropdown-menu {
        width: 90vw !important;
        max-width: 350px !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        right: auto !important;
        margin-top: 10px;
        position: fixed !important;
        z-index: 1051 !important;
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        pointer-events: auto !important;
    }
    
    /* User dropdown specific - keep normal positioning */
    #userDropdown + .dropdown-menu {
        width: auto !important;
        max-width: 200px !important;
        left: auto !important;
        transform: none !important;
        right: 0 !important;
        margin-top: 5px;
        position: absolute !important;
        z-index: 1052 !important;
    }
    
    .dropdown-menu::before {
        content: '';
        position: absolute;
        top: -8px;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 8px solid transparent;
        border-right: 8px solid transparent;
        border-bottom: 8px solid white;
    }
    
    /* Force show dropdown when Bootstrap adds show class */
    .dropdown-menu.show {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
}

@media (max-width: 576px) {
    .dropdown-menu {
        width: 95vw !important;
        max-height: 60vh !important;
    }
    
    .notification-item {
        padding: 0.75rem !important;
    }
    
    .notification-item h6 {
        font-size: 0.9rem;
    }
    
    .notification-item p {
        font-size: 0.8rem;
    }
}

/* Ensure dropdown doesn't interfere with main content */
.dropdown-menu {
    position: absolute;
    z-index: 1050;
}

/* Specific z-index for notification dropdown */
#notificationDropdown + .dropdown-menu {
    z-index: 1051 !important;
}

/* Specific z-index for user dropdown */
#userDropdown + .dropdown-menu {
    z-index: 1052 !important;
}

/* Desktop dropdown styling */
@media (min-width: 769px) {
    .dropdown-menu {
        position: absolute !important;
        z-index: 1050 !important;
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
    }
    
    .dropdown-menu.show {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
}

/* Force hide notification dropdown by default */
#notificationDropdown + .dropdown-menu {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    pointer-events: none !important;
    position: absolute !important;
    z-index: 1051 !important;
}

#notificationDropdown + .dropdown-menu.show {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    pointer-events: auto !important;
    position: absolute !important;
    z-index: 1051 !important;
}

/* Override Bootstrap dropdown behavior - ONLY for notification dropdown */
#notificationDropdown + .dropdown-menu:not(.show) {
    display: none !important;
}

#notificationDropdown + .dropdown-menu.show {
    display: block !important;
}

/* Ensure user dropdown works normally with Bootstrap */
#userDropdown + .dropdown-menu {
    display: none;
    position: absolute;
    z-index: 1052;
}

#userDropdown + .dropdown-menu.show {
    display: block;
    position: absolute;
    z-index: 1052;
}

/* Ensure user dropdown doesn't interfere with notification */
#userDropdown {
    position: relative;
    z-index: 1052;
}

/* Allow normal Bootstrap behavior for all other dropdowns */
.dropdown:not(#notificationDropdown) .dropdown-menu {
    display: none;
}

.dropdown:not(#notificationDropdown) .dropdown-menu.show {
    display: block;
}

/* Ensure pagination works normally */
.pagination {
    display: flex !important;
    padding-left: 0 !important;
    list-style: none !important;
    border-radius: 0.375rem !important;
    margin: 0 !important;
}

.pagination .page-item {
    margin: 0 2px !important;
}

.pagination .page-link {
    padding: 0.375rem 0.75rem !important;
    font-size: 0.875rem !important;
    line-height: 1.5 !important;
    border: 1px solid #dee2e6 !important;
    background-color: #fff !important;
    color: #0d6efd !important;
    text-decoration: none !important;
    display: block !important;
    cursor: pointer !important;
}

.pagination .page-link:hover {
    background-color: #e9ecef !important;
    border-color: #dee2e6 !important;
    color: #0a58ca !important;
}

.pagination .page-item.active .page-link {
    background-color: #0d6efd !important;
    border-color: #0d6efd !important;
    color: #fff !important;
}

.pagination .page-item.disabled .page-link {
    background-color: #fff !important;
    border-color: #dee2e6 !important;
    color: #6c757d !important;
    pointer-events: none !important;
}

/* Responsive pagination */
@media (max-width: 576px) {
    .pagination {
        flex-wrap: wrap !important;
        justify-content: center !important;
    }
    
    .pagination .page-item {
        margin: 2px !important;
    }
    
    .pagination .page-link {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.8rem !important;
    }
}

/* Pagination trong card-footer */
.card-footer .pagination {
    margin-bottom: 0 !important;
}

.card-footer .pagination .page-link {
    border-radius: 0.25rem !important;
    font-size: 0.875rem !important;
    padding: 0.5rem 0.75rem !important;
}

/* Ensure Bootstrap dropdown toggle works for user menu */
#userDropdown[data-bs-toggle="dropdown"] {
    cursor: pointer;
}

/* Force Bootstrap to work for user dropdown */
.dropdown:not(#notificationDropdown) .dropdown-toggle[data-bs-toggle="dropdown"] {
    cursor: pointer;
}

/* Ensure user dropdown menu is properly positioned */
#userDropdown + .dropdown-menu {
    right: 0;
    left: auto;
    top: 100%;
    margin-top: 0.125rem;
}

/* Notification dropdown specific styling */
#notificationDropdown + .dropdown-menu {
    transition: all 0.3s ease;
}

#notificationDropdown + .dropdown-menu.show {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

/* Fix for mobile viewport */
@media (max-width: 768px) {
    body {
        overflow-x: hidden;
    }
}

/* Ensure notification button is clickable */
#notificationDropdown {
    cursor: pointer !important;
    pointer-events: auto !important;
    z-index: 1060 !important;
    position: relative !important;
}

#notificationDropdown:hover {
    background-color: rgba(0, 0, 0, 0.05) !important;
}

#notificationDropdown:active {
    background-color: rgba(0, 0, 0, 0.1) !important;
}

/* Prevent other elements from interfering */
.nav-link {
    pointer-events: auto !important;
}

/* Ensure notification dropdown is properly positioned */
#notificationDropdown + .dropdown-menu {
    position: absolute !important;
    z-index: 1051 !important;
}

/* Notification item clickable styling */
.notification-item {
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    user-select: none !important;
}

.notification-item:hover {
    background-color: #f8f9fa !important;
    transform: translateX(2px) !important;
}

.notification-item:active {
    background-color: #e9ecef !important;
    transform: translateX(1px) !important;
}

/* Unread notification styling */
.notification-item.bg-light {
    background-color: #e3f2fd !important;
    border-left: 3px solid #2196f3 !important;
}

.notification-item.bg-light:hover {
    background-color: #bbdefb !important;
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
    
    container.innerHTML = notifications.map(notification => {
        const dataJson = JSON.stringify(notification.data || {});
        return `
        <div class="notification-item p-3 border-bottom ${notification.is_read ? '' : 'bg-light'}" 
             data-notification-id="${notification.id}"
             data-notification-type="${notification.type}"
             data-notification-data='${dataJson}'>
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
                            <button class="btn btn-sm btn-link text-muted p-0 delete-notification-btn" data-notification-id="${notification.id}">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        `;
    }).join('');
    
    // Add event listeners after rendering
    addNotificationEventListeners();
}

// Add event listeners for notifications
function addNotificationEventListeners() {
    // Add click listeners to notification items
    const notificationItems = document.querySelectorAll('.notification-item');
    notificationItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Don't trigger if clicking delete button
            if (e.target.closest('.delete-notification-btn')) {
                return;
            }
            
            const notificationId = this.dataset.notificationId;
            const notificationType = this.dataset.notificationType;
            const notificationData = this.dataset.notificationData;
            
            handleNotificationClick(e, notificationId, notificationType, notificationData);
        });
    });
    
    // Add click listeners to delete buttons
    const deleteButtons = document.querySelectorAll('.delete-notification-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const notificationId = this.dataset.notificationId;
            deleteNotification(notificationId);
        });
    });
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
    // Update UI immediately for better UX
    const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
    if (notificationElement) {
        notificationElement.classList.remove('bg-light');
        notificationElement.style.borderLeft = 'none';
        
        // Remove unread badge
        const badge = notificationElement.querySelector('.badge.bg-primary');
        if (badge) {
            badge.remove();
        }
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
            // Don't reload notifications to avoid UI flicker
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
function handleNotificationClick(event, notificationId, type, dataString) {
    // Prevent event bubbling
    event.stopPropagation();
    
    console.log('Notification clicked:', { notificationId, type, dataString });
    
    // Parse data safely
    let data = {};
    try {
        data = JSON.parse(dataString || '{}');
    } catch (e) {
        console.error('Error parsing notification data:', e);
        data = {};
    }
    
    // Mark as read first
    markAsRead(notificationId);
    
    // Close dropdown
    const notificationDropdown = document.querySelector('#notificationDropdown + .dropdown-menu');
    if (notificationDropdown) {
        notificationDropdown.style.display = 'none';
        notificationDropdown.style.visibility = 'hidden';
        notificationDropdown.style.opacity = '0';
        notificationDropdown.classList.remove('show');
    }
    
    // Navigate based on notification type
    switch(type) {
        case 'task_assigned':
        case 'task_updated':
        case 'task_followed':
        case 'task_forwarded':
            if (data.task_id) {
                window.location.href = `/task-detail/${data.task_id}`;
            } else {
                window.location.href = '{{ route("dashboard") }}';
            }
            break;
        case 'work_report_submitted':
        case 'work_report_approved':
        case 'work_report_rejected':
            if (data.report_id) {
                // Chuyển đến trang xem báo cáo cụ thể
                window.location.href = `/work-reports/${data.report_id}`;
            } else if (data.work_report_id) {
                // Fallback nếu dùng work_report_id
                window.location.href = `/work-reports/${data.work_report_id}`;
            } else {
                // Nếu không có ID, chuyển đến trang tạo báo cáo mới với ngày hiện tại
                window.location.href = '{{ route("work-reports.create") }}?selected_date={{ now()->format("Y-m-d") }}';
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
            } else {
                window.location.href = '{{ route("support-requests.index") }}';
            }
            break;
        case 'approval_request_created':
        case 'approval_request_approved':
        case 'approval_request_rejected':
        case 'approval_request_cancelled':
            if (data.approval_request_id) {
                window.location.href = `/approval/${data.approval_request_id}`;
            } else if (data.approval_id) {
                // For task approval notifications
                window.location.href = `/task-approvals/${data.approval_id}`;
            } else {
                window.location.href = '{{ route("approval.index") }}';
            }
            break;
        case 'user_created':
        case 'user_updated':
        case 'user_deleted':
            window.location.href = '{{ route("users.index") }}';
            break;
        case 'department_created':
        case 'department_updated':
        case 'department_deleted':
            window.location.href = '{{ route("departments.index") }}';
            break;
        default:
            console.log('Unknown notification type:', type);
            // Default to dashboard
            window.location.href = '{{ route("dashboard") }}';
    }
}

// Format time
function formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diffInMinutes = Math.floor((now - date) / (1000 * 60));
    
    if (diffInMinutes < 1) return 'Vừa xong';
    if (diffInMinutes < 60) return `${diffInMinutes} phút trước`;
    
    const diffInHours = Math.floor(diffInMinutes / 60);
    if (diffInHours < 24) return `${diffInHours} tiếng trước`;
    
    const diffInDays = Math.floor(diffInHours / 24);
    if (diffInDays < 7) return `${diffInDays} ngày trước`;
    
    return date.toLocaleDateString();
}

// Load notifications when dropdown is shown
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, looking for notification button...');
    const notificationButton = document.getElementById('notificationDropdown');
    
    if (notificationButton) {
        console.log('Notification button found, adding click listener...');
        
        // Disable Bootstrap dropdown behavior
        notificationButton.removeAttribute('data-bs-toggle');
        notificationButton.removeAttribute('data-bs-auto-close');
        
        // Ensure dropdown is hidden by default
        const notificationDropdown = document.querySelector('#notificationDropdown + .dropdown-menu');
        if (notificationDropdown) {
            notificationDropdown.style.display = 'none';
            notificationDropdown.style.visibility = 'hidden';
            notificationDropdown.style.opacity = '0';
            notificationDropdown.style.pointerEvents = 'none';
            notificationDropdown.classList.remove('show');
            console.log('Notification dropdown hidden by default');
        }
        
        // Remove any existing event listeners
        notificationButton.removeEventListener('click', handleNotificationClick);
        
        // Add new event listener
        notificationButton.addEventListener('click', handleNotificationClick);
        
        function handleNotificationClick(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('Notification button clicked!');
            
            const notificationDropdown = document.querySelector('#notificationDropdown + .dropdown-menu');
            if (!notificationDropdown) {
                console.error('Notification dropdown not found!');
                return;
            }
            
            // Check if dropdown is currently visible
            const isVisible = notificationDropdown.classList.contains('show') && 
                             notificationDropdown.style.display === 'block' && 
                             notificationDropdown.style.visibility === 'visible' && 
                             notificationDropdown.style.opacity === '1';
            
            console.log('Dropdown is visible:', isVisible);
            
            if (isVisible) {
                // Close dropdown
                notificationDropdown.style.display = 'none';
                notificationDropdown.style.visibility = 'hidden';
                notificationDropdown.style.opacity = '0';
                notificationDropdown.style.pointerEvents = 'none';
                notificationDropdown.classList.remove('show');
                notificationButton.setAttribute('aria-expanded', 'false');
                console.log('Notification dropdown closed');
            } else {
                // Open dropdown
                loadNotifications();
                
                // Force show dropdown with all necessary properties
                notificationDropdown.style.display = 'block';
                notificationDropdown.style.visibility = 'visible';
                notificationDropdown.style.opacity = '1';
                notificationDropdown.style.pointerEvents = 'auto';
                notificationDropdown.style.position = 'absolute';
                notificationDropdown.style.zIndex = '1051';
                notificationDropdown.classList.add('show');
                notificationButton.setAttribute('aria-expanded', 'true');
                
                // Handle responsive positioning
                if (window.innerWidth <= 768) {
                    // Mobile - center
                    notificationDropdown.style.left = '50%';
                    notificationDropdown.style.transform = 'translateX(-50%)';
                    notificationDropdown.style.right = 'auto';
                    notificationDropdown.style.position = 'fixed';
                    console.log('Mobile notification dropdown positioned and shown');
                } else {
                    // Desktop - right align
                    notificationDropdown.style.left = 'auto';
                    notificationDropdown.style.transform = 'none';
                    notificationDropdown.style.right = '0';
                    notificationDropdown.style.position = 'absolute';
                    console.log('Desktop notification dropdown shown');
                }
                
                console.log('Dropdown should be visible now');
            }
        }
    } else {
        console.error('Notification button not found!');
    }
});

// Close notification dropdown when clicking outside
document.addEventListener('click', function(event) {
    const notificationDropdown = document.querySelector('#notificationDropdown + .dropdown-menu');
    const notificationButton = document.getElementById('notificationDropdown');
    
    // Only close notification dropdown if clicking outside notification dropdown and its button
    if (notificationDropdown && 
        !notificationDropdown.contains(event.target) && 
        !notificationButton.contains(event.target)) {
        
        // Close notification dropdown
        notificationDropdown.style.display = 'none';
        notificationDropdown.style.visibility = 'hidden';
        notificationDropdown.style.opacity = '0';
        notificationDropdown.style.pointerEvents = 'none';
        notificationDropdown.classList.remove('show');
        notificationButton.setAttribute('aria-expanded', 'false');
        console.log('Notification dropdown closed by clicking outside');
    }
});

// Force close dropdown on page load
window.addEventListener('load', function() {
    const notificationDropdown = document.querySelector('#notificationDropdown + .dropdown-menu');
    if (notificationDropdown) {
        notificationDropdown.style.display = 'none';
        notificationDropdown.style.visibility = 'hidden';
        notificationDropdown.style.opacity = '0';
        notificationDropdown.classList.remove('show');
        console.log('Notification dropdown force closed on page load');
    }
});

// Handle dropdown toggle events
document.addEventListener('DOMContentLoaded', function() {
    const button = document.getElementById('notificationDropdown');
    if (button) {
        button.addEventListener('shown.bs.dropdown', function() {
            console.log('Dropdown shown');
            const dropdown = document.querySelector('.dropdown-menu');
            if (dropdown && window.innerWidth <= 768) {
                // Mobile - ensure it's visible
                dropdown.style.display = 'block';
                dropdown.style.visibility = 'visible';
                dropdown.style.opacity = '1';
            }
        });
        
        button.addEventListener('hidden.bs.dropdown', function() {
            console.log('Dropdown hidden');
        });
    }
});

// Handle window resize
window.addEventListener('resize', function() {
    const dropdown = document.querySelector('.dropdown-menu');
    if (dropdown && window.innerWidth <= 768) {
        dropdown.style.left = '50%';
        dropdown.style.transform = 'translateX(-50%)';
        dropdown.style.right = 'auto';
    } else if (dropdown) {
        dropdown.style.left = 'auto';
        dropdown.style.transform = 'none';
        dropdown.style.right = '0';
    }
});

// Debug click events
document.addEventListener('click', function(e) {
    console.log('Click detected on:', e.target);
    console.log('Click target class:', e.target.className);
    console.log('Click target id:', e.target.id);
    console.log('Click target tag:', e.target.tagName);
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
