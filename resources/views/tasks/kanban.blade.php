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
}

.kanban-column {
    flex: 1;
    min-width: 300px;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.kanban-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #dee2e6;
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

.kanban-tasks {
    min-height: 200px;
    padding: 10px 0;
}

.kanban-task {
    background: white;
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    cursor: move;
    transition: all 0.2s ease;
    border-left: 4px solid #007bff;
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

/* Responsive */
@media (max-width: 768px) {
    .kanban-board {
        flex-direction: column;
        gap: 15px;
    }
    
    .kanban-column {
        min-width: auto;
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
                    Kanban Board
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

        <!-- Hoàn thành -->
        <div class="kanban-column completed" data-status="completed">
            <div class="kanban-header">
                <div class="kanban-title">
                    <i class="fas fa-check-circle me-2"></i>
                    Hoàn thành
                </div>
                <div class="kanban-count">{{ $kanbanData['completed']->count() }}</div>
            </div>
            <div class="kanban-tasks" data-status="completed">
                @forelse($kanbanData['completed'] as $task)
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

        <!-- Kết thúc -->
        <div class="kanban-column finished" data-status="finished">
            <div class="kanban-header">
                <div class="kanban-title">
                    <i class="fas fa-flag-checkered me-2"></i>
                    Kết thúc
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
