@extends('layouts.master')

@section('title', 'Kanban Board')

@push('styles')
<style>
.kanban-board {
    display: flex;
    gap: 20px;
    padding: 20px;
    min-height: calc(100vh - 200px);
    overflow-x: auto;
    align-items: stretch; /* Đảm bảo các cột có chiều cao bằng nhau */
}

.kanban-column {
    flex: 1;
    min-width: 300px;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    height: 100%;
}

.kanban-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #dee2e6;
    flex-shrink: 0; /* Không co lại */
}

.kanban-title {
    font-weight: 600;
    font-size: 16px;
    color: #495057;
}

.kanban-count {
    background: #6c757d;
    color: white;
    border-radius: 12px;
    padding: 4px 8px;
    font-size: 12px;
    font-weight: 500;
}

/* CSS đã được di chuyển lên trên */

.kanban-tasks {
    min-height: 200px;
    max-height: calc(3 * 140px + 20px); /* 3 tasks với chiều cao tự nhiên + padding */
    padding: 10px 0;
    overflow-y: auto;
    overflow-x: hidden;
    scrollbar-width: thin;
    scrollbar-color: #c1c1c1 #f1f1f1;
    flex: 1;
    display: block; /* Thay đổi từ flex sang block để tránh đè lên nhau */
}

/* Custom scrollbar cho webkit browsers */
.kanban-tasks::-webkit-scrollbar {
    width: 6px;
}

.kanban-tasks::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.kanban-tasks::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.kanban-tasks::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Giới hạn hiển thị 3 task mỗi cột */
.kanban-tasks {
    position: relative;
}

/* Visual indicator cho scroll */
.kanban-tasks::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 20px;
    background: linear-gradient(transparent, rgba(248, 249, 250, 0.8));
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.kanban-tasks.scrollable::after {
    opacity: 1;
}

/* Task card styling improvements */
.kanban-task {
    background: white;
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    cursor: move;
    transition: all 0.2s ease;
    border-left: 4px solid #007bff;
    min-height: 100px;
    word-wrap: break-word;
    overflow-wrap: break-word;
    display: block; /* Đảm bảo task hiển thị dạng block */
    width: 100%; /* Chiếm toàn bộ chiều rộng */
    position: relative; /* Để tránh đè lên nhau */
}

.kanban-task:last-child {
    margin-bottom: 0;
}

/* Task content styling */
.kanban-task .task-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    line-height: 1.3;
}

.kanban-task .task-meta {
    font-size: 12px;
    color: #666;
    margin-bottom: 4px;
    line-height: 1.4;
}

.kanban-task .task-meta:last-child {
    margin-bottom: 0;
}

.kanban-task .task-description {
    font-size: 11px;
    color: #777;
    margin-top: 6px;
    line-height: 1.3;
    max-height: 2.6em;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

/* Task count indicator */
.task-count-indicator {
    position: absolute;
    bottom: 10px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(85, 142, 193, 0.9);
    color: white;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 11px;
    font-weight: 500;
    z-index: 10;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 0.7; }
    50% { opacity: 1; }
    100% { opacity: 0.7; }
}

.kanban-task.readonly {
    cursor: default;
}

.kanban-task:hover {
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    transform: translateY(-1px);
}

.kanban-task.dragging {
    opacity: 0.5;
    transform: rotate(5deg);
}

.task-title {
    font-weight: 600;
    color: #212529;
    margin-bottom: 8px;
    font-size: 14px;
    line-height: 1.4;
}

.task-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 8px;
}

.task-assignee {
    display: flex;
    align-items: center;
    gap: 5px;
}

.task-assignee-avatar {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #007bff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 600;
}

.task-deadline {
    color: #dc3545;
    font-weight: 500;
}

.task-deadline.past {
    color: #dc3545;
}

.task-deadline.future {
    color: #28a745;
}

.task-department {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #6c757d;
    font-size: 12px;
}

.department-name {
    font-weight: 500;
    color: #495057;
}

.department-tooltip {
    cursor: pointer;
    border-bottom: 1px dotted #6c757d;
    transition: all 0.2s ease;
}

.department-tooltip:hover {
    color: #007bff;
    border-bottom-color: #007bff;
}

.task-actions {
    display: flex;
    gap: 5px;
    margin-top: 8px;
}

.task-action-btn {
    padding: 4px 8px;
    border: none;
    border-radius: 4px;
    font-size: 11px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-view {
    background: #17a2b8;
    color: white;
}

.btn-edit {
    background: #ffc107;
    color: #212529;
}

.task-action-btn:hover {
    opacity: 0.8;
    transform: scale(1.05);
}

/* Status specific colors */
.kanban-column.in-progress .kanban-header {
    border-bottom-color: #007bff;
}

.kanban-column.in-progress .kanban-count {
    background: #007bff;
}

.kanban-column.completed .kanban-header {
    border-bottom-color: #ffc107;
}

.kanban-column.completed .kanban-count {
    background: #ffc107;
    color: #212529;
}

.kanban-column.pending-approval .kanban-header {
    border-bottom-color: #8b5cf6;
}

.kanban-column.pending-approval .kanban-count {
    background: #8b5cf6;
}

.kanban-column.rejected .kanban-header {
    border-bottom-color: #dc3545;
}

.kanban-column.rejected .kanban-count {
    background: #dc3545;
}

.kanban-column.overdue .kanban-header {
    border-bottom-color: #dc3545;
}

.kanban-column.overdue .kanban-count {
    background: #dc3545;
}

.kanban-column.finished .kanban-header {
    border-bottom-color: #28a745;
}

.kanban-column.finished .kanban-count {
    background: #28a745;
}

/* Task border colors by status */
.kanban-task.in-progress {
    border-left-color: #007bff;
}

.kanban-task.completed {
    border-left-color: #ffc107;
}

.kanban-task.pending-approval {
    border-left-color: #8b5cf6;
}

.kanban-task.rejected {
    border-left-color: #dc3545;
}

.kanban-task.overdue {
    border-left-color: #dc3545;
}

.kanban-task.finished {
    border-left-color: #28a745;
}

/* Drop zone styling */
.kanban-column.drag-over {
    background: #e3f2fd;
    border: 2px dashed #2196f3;
}

/* Sidebar collapsed adjustments */
.main-content.expanded .kanban-board {
    width: calc(100% - 60px) !important;
    max-width: calc(100% - 60px) !important;
    margin: 0 !important;
    padding: 20px !important;
}

.main-content.expanded .kanban-column {
    flex: 1;
    min-width: 280px;
}

/* Responsive */
@media (max-width: 768px) {
    .kanban-board {
        flex-direction: column;
        gap: 15px;
    }
    
    .kanban-column {
        min-width: auto;
    }
    
    /* Mobile Kanban improvements */
    .kanban-tasks {
      max-height: calc(3 * 120px + 20px); /* 3 tasks on mobile với chiều cao tự nhiên */
      display: block; /* Đảm bảo mobile cũng hiển thị đúng */
    }
    
    .kanban-task {
        padding: 10px;
        margin-bottom: 10px;
        min-height: 80px;
        word-wrap: break-word;
        overflow-wrap: break-word;
        display: block; /* Đảm bảo task mobile hiển thị đúng */
        width: 100%;
        position: relative;
    }
    
    .kanban-task .task-title {
        font-size: 13px;
        line-height: 1.2;
    }
    
    .kanban-task .task-meta {
        font-size: 11px;
        line-height: 1.3;
    }
    
    .kanban-task .task-description {
        font-size: 10px;
        line-height: 1.2;
        max-height: 2.4em;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-columns me-2"></i>
                    Trang Chủ
                </h2>
                <div>
                    <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Tạo công việc
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="kanban-board" id="kanbanBoard">
        <!-- Đang làm -->
        <div class="kanban-column in-progress" data-status="in_progress">
            <div class="kanban-header">
                <div class="kanban-title">
                    <i class="fas fa-play-circle me-2"></i>
                    Đang làm
                </div>
                <div class="kanban-count">{{ $kanbanData['in_progress']->count() }}</div>
            </div>
            <div class="kanban-tasks" data-status="in_progress">
                @forelse($kanbanData['in_progress'] as $task)
                    @include('tasks.kanban-task', ['task' => $task])
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>Không có công việc nào</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Chờ phê duyệt -->
        <div class="kanban-column pending-approval" data-status="pending_approval">
            <div class="kanban-header">
                <div class="kanban-title">
                    <i class="fas fa-clock me-2"></i>
                    Chờ phê duyệt
                </div>
                <div class="kanban-count">{{ $kanbanData['pending_approval']->count() }}</div>
            </div>
            <div class="kanban-tasks" data-status="pending_approval">
                @forelse($kanbanData['pending_approval'] as $task)
                    @include('tasks.kanban-task', ['task' => $task])
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>Không có công việc nào</p>
                    </div>
                @endforelse
            </div>
        </div>


        <!-- Từ chối -->
        <div class="kanban-column rejected" data-status="rejected">
            <div class="kanban-header">
                <div class="kanban-title">
                    <i class="fas fa-times-circle me-2"></i>
                    Từ chối
                </div>
                <div class="kanban-count">{{ $kanbanData['rejected']->count() }}</div>
            </div>
            <div class="kanban-tasks" data-status="rejected">
                @forelse($kanbanData['rejected'] as $task)
                    @include('tasks.kanban-task', ['task' => $task])
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>Không có công việc nào</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Trễ hạn -->
        <div class="kanban-column overdue" data-status="overdue">
            <div class="kanban-header">
                <div class="kanban-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Trễ hạn
                </div>
                <div class="kanban-count">{{ $kanbanData['overdue']->count() }}</div>
            </div>
            <div class="kanban-tasks" data-status="overdue">
                @forelse($kanbanData['overdue'] as $task)
                    @include('tasks.kanban-task', ['task' => $task])
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>Không có công việc nào</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Hoàn thành -->
        <div class="kanban-column finished" data-status="finished">
            <div class="kanban-header">
                <div class="kanban-title">
                    <i class="fas fa-flag-checkered me-2"></i>
                    Hoàn thành
                </div>
                <div class="kanban-count">{{ $kanbanData['finished']->count() }}</div>
            </div>
            <div class="kanban-tasks" data-status="finished">
                @forelse($kanbanData['finished'] as $task)
                    @include('tasks.kanban-task', ['task' => $task])
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>Không có công việc nào</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const kanbanBoard = document.getElementById('kanbanBoard');
    const columns = document.querySelectorAll('.kanban-tasks');
    
    // Initialize tooltips with click trigger
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'click',
            placement: 'top'
        });
    });
    
    // Close tooltip when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.department-tooltip')) {
            tooltipList.forEach(tooltip => {
                if (tooltip) {
                    tooltip.hide();
                }
            });
        }
    });
    
    // Add scrollable class to columns with more than 3 tasks
    function checkScrollableColumns() {
        const columns = document.querySelectorAll('.kanban-tasks');
        columns.forEach(column => {
            const tasks = column.querySelectorAll('.kanban-task');
            console.log('Column has', tasks.length, 'tasks'); // Debug log
            if (tasks.length > 3) {
                column.classList.add('scrollable');
                // Add task count indicator (chỉ hiển thị từ task thứ 4 trở đi)
                addTaskCountIndicator(column, tasks.length);
            } else {
                column.classList.remove('scrollable');
                removeTaskCountIndicator(column);
            }
        });
    }
    
    // Add task count indicator
    function addTaskCountIndicator(column, totalTasks) {
        if (column.querySelector('.task-count-indicator')) return;
        
        const indicator = document.createElement('div');
        indicator.className = 'task-count-indicator';
        indicator.innerHTML = `+${totalTasks - 3} công việc khác`;
        column.parentElement.appendChild(indicator);
    }
    
    // Remove task count indicator
    function removeTaskCountIndicator(column) {
        const indicator = column.parentElement.querySelector('.task-count-indicator');
        if (indicator) {
            indicator.remove();
        }
    }
    
    // Check on page load
    checkScrollableColumns();
    
    // Check if user can drag & drop (Admin/Director/Manager only)
    const canDragDrop = {{ auth()->user()->isAdmin() || auth()->user()->isDirector() || auth()->user()->isManager() ? 'true' : 'false' }};
    
    // Add readonly class to task cards for Employee
    if (!canDragDrop) {
        document.querySelectorAll('.kanban-task').forEach(task => {
            task.classList.add('readonly');
        });
    }
    
    // Initialize Sortable for each column
    columns.forEach(column => {
        new Sortable(column, {
            group: canDragDrop ? 'kanban' : false, // Disable drag & drop for Employee
            animation: 150,
            ghostClass: 'kanban-task-ghost',
            chosenClass: 'kanban-task-chosen',
            dragClass: 'kanban-task-dragging',
            disabled: !canDragDrop, // Disable for Employee
            onEnd: function(evt) {
                if (!canDragDrop) return; // Double check
                
                const taskId = evt.item.dataset.taskId;
                const newStatus = evt.to.dataset.status;
                const oldStatus = evt.from.dataset.status;
                
                // Don't update if dropped in the same column
                if (newStatus === oldStatus) {
                    return;
                }
                
                // Update task status via AJAX
                updateTaskStatus(taskId, newStatus, evt.item);
            }
        });
    });
    
    // Add drag over effects
    columns.forEach(column => {
        column.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.parentElement.classList.add('drag-over');
        });
        
        column.addEventListener('dragleave', function(e) {
            this.parentElement.classList.remove('drag-over');
        });
        
        column.addEventListener('drop', function(e) {
            this.parentElement.classList.remove('drag-over');
        });
    });
});

function updateTaskStatus(taskId, newStatus, taskElement) {
    // Show loading state
    taskElement.style.opacity = '0.5';
    
    fetch(`/tasks/${taskId}/update-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            status: newStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update task element classes
            taskElement.className = taskElement.className.replace(/kanban-task-\w+/g, '');
            taskElement.classList.add('kanban-task', newStatus.replace('_', '-'));
            
            // Update column counts
            updateColumnCounts();
            
            // Show success message
            showNotification('success', data.message);
        } else {
            // Revert the drag if failed
            location.reload();
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        location.reload();
        showNotification('error', 'Có lỗi xảy ra khi cập nhật trạng thái');
    })
    .finally(() => {
        taskElement.style.opacity = '1';
    });
}

function updateColumnCounts() {
    const columns = document.querySelectorAll('.kanban-column');
    columns.forEach(column => {
        const status = column.dataset.status;
        const count = column.querySelectorAll('.kanban-task').length;
        const countElement = column.querySelector('.kanban-count');
        countElement.textContent = count;
    });
}

function showNotification(type, message) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}
</script>
@endpush
