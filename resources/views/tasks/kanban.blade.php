@extends('layouts.master')

@section('title', 'Kanban Board')

@push('styles')
<style>
/* Page header improvements */
.kanban-page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 25px 30px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    color: white;
}

.kanban-page-header h2 {
    margin: 0;
    font-weight: 700;
    font-size: 28px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.kanban-page-header .btn-primary {
    background: white;
    color: #667eea;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.kanban-page-header .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    background: #f8f9fa;
}

.kanban-board {
    display: flex;
    gap: 24px;
    padding: 20px 10px;
    min-height: calc(100vh - 250px);
    overflow-x: auto;
    align-items: stretch;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f7fafc;
}

.kanban-board::-webkit-scrollbar {
    height: 10px;
}

.kanban-board::-webkit-scrollbar-track {
    background: #f7fafc;
    border-radius: 10px;
}

.kanban-board::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #cbd5e0 0%, #a0aec0 100%);
    border-radius: 10px;
}

.kanban-column {
    flex: 1;
    min-width: 320px;
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    display: flex;
    flex-direction: column;
    height: 100%;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.kanban-column:hover {
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}

.kanban-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding: 15px;
    border-radius: 12px;
    flex-shrink: 0;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.kanban-title {
    font-weight: 700;
    font-size: 17px;
    color: #2d3748;
    display: flex;
    align-items: center;
    gap: 8px;
}

.kanban-title i {
    font-size: 20px;
}

.kanban-count {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    color: white;
    border-radius: 20px;
    padding: 6px 12px;
    font-size: 13px;
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    min-width: 32px;
    text-align: center;
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
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 14px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    cursor: move;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-left: 5px solid #007bff;
    min-height: 100px;
    word-wrap: break-word;
    overflow-wrap: break-word;
    display: block;
    width: 100%;
    position: relative;
    border: 1px solid rgba(0,0,0,0.05);
}

.kanban-task:last-child {
    margin-bottom: 0;
}

/* Task content styling */
.kanban-task .task-title {
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 10px;
    line-height: 1.4;
    font-size: 15px;
}

.kanban-task .task-meta {
    font-size: 13px;
    color: #4a5568;
    margin-bottom: 6px;
    line-height: 1.5;
}

.kanban-task .task-meta:last-child {
    margin-bottom: 0;
}

.kanban-task .task-description {
    font-size: 12px;
    color: #718096;
    margin-top: 8px;
    line-height: 1.4;
    max-height: 2.8em;
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
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    transform: translateY(-3px) scale(1.02);
    border-left-width: 6px;
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
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
    border: 2px solid white;
}

.task-deadline {
    color: #ef4444;
    font-weight: 600;
    font-size: 12px;
}

.task-deadline.past {
    color: white;
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    padding: 4px 10px;
    border-radius: 8px;
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
    font-size: 11px;
}

.task-deadline.future {
    color: #10b981;
    font-weight: 600;
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

/* Status specific colors with gradients */
.kanban-column.in-progress {
    border-top: 4px solid #3b82f6;
}

.kanban-column.in-progress .kanban-header {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
}

.kanban-column.in-progress .kanban-title {
    color: #1e40af;
}

.kanban-column.in-progress .kanban-count {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
}

.kanban-column.pending-approval {
    border-top: 4px solid #8b5cf6;
}

.kanban-column.pending-approval .kanban-header {
    background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
}

.kanban-column.pending-approval .kanban-title {
    color: #6d28d9;
}

.kanban-column.pending-approval .kanban-count {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
}

.kanban-column.rejected {
    border-top: 4px solid #ef4444;
}

.kanban-column.rejected .kanban-header {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
}

.kanban-column.rejected .kanban-title {
    color: #b91c1c;
}

.kanban-column.rejected .kanban-count {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.kanban-column.overdue {
    border-top: 4px solid #f97316;
}

.kanban-column.overdue .kanban-header {
    background: linear-gradient(135deg, #ffedd5 0%, #fed7aa 100%);
}

.kanban-column.overdue .kanban-title {
    color: #c2410c;
}

.kanban-column.overdue .kanban-count {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
}

.kanban-column.finished {
    border-top: 4px solid #10b981;
}

.kanban-column.finished .kanban-header {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
}

.kanban-column.finished .kanban-title {
    color: #047857;
}

.kanban-column.finished .kanban-count {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

/* Task border colors by status */
.kanban-task.in-progress {
    border-left-color: #3b82f6;
    background: linear-gradient(135deg, #ffffff 0%, #eff6ff 100%);
}

.kanban-task.pending-approval {
    border-left-color: #8b5cf6;
    background: linear-gradient(135deg, #ffffff 0%, #faf5ff 100%);
}

.kanban-task.rejected {
    border-left-color: #ef4444;
    background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
}

.kanban-task.overdue {
    border-left-color: #f97316;
    background: linear-gradient(135deg, #ffffff 0%, #fff7ed 100%);
}

.kanban-task.finished {
    border-left-color: #10b981;
    background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
    opacity: 0.85;
}

/* Drop zone styling */
.kanban-column.drag-over {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border: 3px dashed #2196f3;
    transform: scale(1.02);
}

/* Empty state styling */
.kanban-tasks .text-center {
    padding: 40px 20px;
}

.kanban-tasks .text-center i {
    opacity: 0.3;
    margin-bottom: 10px;
}

.kanban-tasks .text-center p {
    color: #94a3b8;
    font-weight: 500;
}

/* Animations */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.kanban-column {
    animation: slideIn 0.4s ease-out;
}

.kanban-column:nth-child(1) { animation-delay: 0.1s; }
.kanban-column:nth-child(2) { animation-delay: 0.2s; }
.kanban-column:nth-child(3) { animation-delay: 0.3s; }
.kanban-column:nth-child(4) { animation-delay: 0.4s; }
.kanban-column:nth-child(5) { animation-delay: 0.5s; }

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
    .kanban-page-header {
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 20px;
    }
    
    .kanban-page-header h2 {
        font-size: 22px;
    }
    
    .kanban-page-header .btn-primary {
        padding: 8px 16px;
        font-size: 14px;
    }
    
    .kanban-board {
        flex-direction: column;
        gap: 20px;
        padding: 15px 5px;
    }
    
    .kanban-column {
        min-width: auto;
        padding: 16px;
        border-radius: 12px;
    }
    
    .kanban-header {
        padding: 12px;
    }
    
    /* Mobile Kanban improvements */
    .kanban-tasks {
        max-height: calc(3 * 120px + 20px);
        display: block;
    }
    
    .kanban-task {
        padding: 12px;
        margin-bottom: 12px;
        min-height: 80px;
        word-wrap: break-word;
        overflow-wrap: break-word;
        display: block;
        width: 100%;
        position: relative;
    }
    
    .kanban-task .task-title {
        font-size: 14px;
        line-height: 1.3;
    }
    
    .kanban-task .task-meta {
        font-size: 12px;
        line-height: 1.4;
    }
    
    .kanban-task .task-description {
        font-size: 11px;
        line-height: 1.3;
        max-height: 2.6em;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid" style="padding-top: 20px;">
    <div class="kanban-page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2>
                <i class="fas fa-th-large me-2"></i>
                Trang Chủ
            </h2>
            @if(auth()->user()->isAdmin() || auth()->user()->isDirector() || auth()->user()->isManager())
            <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>
                Tạo công việc
            </a>
            @endif
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
    // function addTaskCountIndicator(column, totalTasks) {
    //     if (column.querySelector('.task-count-indicator')) return;
        
    //     const indicator = document.createElement('div');
    //     indicator.className = 'task-count-indicator';
    //     // indicator.innerHTML = `+${totalTasks - 3} công việc khác`;
    //     column.parentElement.appendChild(indicator);
    // }
    
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
    // Tạo container dưới navbar để tránh bị che
    let alertContainer = document.getElementById('global-alert-container');
    if (!alertContainer) {
        alertContainer = document.createElement('div');
        alertContainer.id = 'global-alert-container';
        alertContainer.style.position = 'fixed';
        alertContainer.style.right = '20px';
        alertContainer.style.left = '20px';
        alertContainer.style.zIndex = '100000';
        const navbar = document.querySelector('.navbar');
        const topOffset = (navbar ? navbar.offsetHeight : 0) + 10;
        alertContainer.style.top = `${topOffset}px`;
        document.body.appendChild(alertContainer);
    }

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
    notification.style.cssText = 'min-width: 300px; max-width: 600px; margin-left: auto;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}
</script>
@endpush
