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

.kanban-task .task-department {
    font-size: 12px;
    color: #6c757d;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 4px;
}

.kanban-task .task-department i {
    font-size: 11px;
    color: #6c757d;
}

.kanban-task .task-department .department-name {
    font-size: 12px;
    color: #6c757d;
}

.kanban-task .task-department .department-tooltip {
    cursor: help;
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

/* Status specific colors - màu đơn sắc như approval system */
.kanban-column.in-progress {
    border-top: 4px solid #4A90E2;
}

.kanban-column.in-progress .kanban-header {
    background: #4A90E2;
}

.kanban-column.in-progress .kanban-title {
    color: #ffffff;
}

.kanban-column.in-progress .kanban-count {
    background: #4A90E2;
}

.kanban-column.pending-approval {
    border-top: 4px solid #ed8712;
}

.kanban-column.pending-approval .kanban-header {
    background: #ed8712;
}

.kanban-column.pending-approval .kanban-title {
    color: #ffffff;
}

.kanban-column.pending-approval .kanban-count {
    background: #ed8712;
}

.kanban-column.rejected {
    border-top: 4px solid #F23005;
}

.kanban-column.rejected .kanban-header {
    background: #F23005;
}

.kanban-column.rejected .kanban-title {
    color: #ffffff;
}

.kanban-column.rejected .kanban-count {
    background: #F23005;
}

.kanban-column.overdue {
    border-top: 4px solid #ed8712;
}

.kanban-column.overdue .kanban-header {
    background: #ed8712;
}

.kanban-column.overdue .kanban-title {
    color: #ffffff;
}

.kanban-column.overdue .kanban-count {
    background: #ed8712;
}

.kanban-column.finished {
    border-top: 4px solid #50a344;
}

.kanban-column.finished .kanban-header {
    background: #50a344;
}

.kanban-column.finished .kanban-title {
    color: #ffffff;
}

.kanban-column.finished .kanban-count {
    background: #50a344;
}

/* Approval Request Column Colors */
.kanban-column.approval-pending {
    border-top: 4px solid #ed8712;
}

.kanban-column.approval-pending .kanban-header {
    background: #ed8712;
}

.kanban-column.approval-pending .kanban-title {
    color: #ffffff;
}

.kanban-column.approval-pending .kanban-count {
    background: #ed8712;
}

.kanban-column.approval-approved {
    border-top: 4px solid #50a344;
}

.kanban-column.approval-approved .kanban-header {
    background: #50a344;
}

.kanban-column.approval-approved .kanban-title {
    color: #ffffff;
}

.kanban-column.approval-approved .kanban-count {
    background: #50a344;
}

.kanban-column.approval-rejected {
    border-top: 4px solid #F23005;
}

.kanban-column.approval-rejected .kanban-header {
    background: #F23005;
}

.kanban-column.approval-rejected .kanban-title {
    color: #ffffff;
}

.kanban-column.approval-rejected .kanban-count {
    background: #F23005;
}

/* Task border colors by status - màu đơn sắc như approval system */
.kanban-task.in-progress {
    border-left-color: #4A90E2;
    background: #ffffff;
}

.kanban-task.pending-approval {
    border-left-color: #ed8712;
    background: #ffffff;
}

.kanban-task.rejected {
    border-left-color: #F23005;
    background: #ffffff;
}

.kanban-task.overdue {
    border-left-color: #ed8712;
    background: #ffffff;
}

.kanban-task.finished {
    border-left-color: #50a344;
    background: #ffffff;
    opacity: 0.9;
}

/* Approval Request Card Styling */
.kanban-approval-request {
    background: #ffffff;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 14px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    cursor: move;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-left: 5px solid #6c757d;
    min-height: 100px;
    word-wrap: break-word;
    overflow-wrap: break-word;
    display: block;
    width: 100%;
    position: relative;
    border: 1px solid rgba(0,0,0,0.05);
}

.kanban-approval-request:last-child {
    margin-bottom: 0;
}

.kanban-approval-request .approval-title {
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 10px;
    line-height: 1.4;
    font-size: 15px;
}

.kanban-approval-request .approval-creator,
.kanban-approval-request .approval-approver,
.kanban-approval-request .approval-approved,
.kanban-approval-request .approval-rejected {
    font-size: 13px;
    color: #4a5568;
    margin-bottom: 6px;
    line-height: 1.5;
    display: flex;
    align-items: center;
    gap: 4px;
}

.kanban-approval-request .approval-approved {
    color: #50a344;
}

.kanban-approval-request .approval-rejected {
    color: #F23005;
}

.kanban-approval-request:hover {
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    transform: translateY(-3px) scale(1.02);
    border-left-width: 6px;
}

/* Approval Request Status Colors */
.kanban-approval-request.pending {
    border-left-color: #ed8712;
    background: #ffffff;
}

.kanban-approval-request.approved {
    border-left-color: #50a344;
    background: #ffffff;
}

.kanban-approval-request.rejected {
    border-left-color: #F23005;
    background: #ffffff;
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

/* Tooltip improvements */
.kanban-task .task-department .department-tooltip {
    position: relative;
}

.kanban-task .task-department .department-tooltip:hover::after {
    content: attr(title);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.9);
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 12px;
    white-space: nowrap;
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    margin-bottom: 5px;
}

.kanban-task .task-department .department-tooltip:hover::before {
    content: '';
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 5px solid transparent;
    border-top-color: rgba(0, 0, 0, 0.9);
    z-index: 1000;
    margin-bottom: -5px;
}

/* Responsive adjustments */
@media (max-width: 480px) {
    .kanban-task .task-department {
        font-size: 11px;
    }
}

/* Section styling */
.kanban-section {
    margin-bottom: 40px;
}

.section-title {
    color: #2d3748;
    font-weight: 700;
    font-size: 20px;
    border-bottom: 3px solid #667eea;
    padding-bottom: 10px;
    display: inline-block;
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

    <!-- Tasks Section -->
    <div class="kanban-section">
        <h4 class="section-title mb-3">
            <i class="fas fa-tasks me-2"></i>
            Quản lý công việc
        </h4>
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

            @if(auth()->user()->isDirector())
                <!-- Director: Đang làm -> Hoàn thành -> Từ chối -> Trễ hạn -->
                
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
            @else
                <!-- Các role khác: Đang làm -> Chờ phê duyệt -> Hoàn thành -> Từ chối -> Trễ hạn -->
                
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
            @endif
        </div>
    </div>

    <!-- Approval Requests Section -->
    <div class="kanban-section mt-5">
        <h4 class="section-title mb-3">
            <i class="fas fa-clipboard-check me-2"></i>
            Quản lý yêu cầu phê duyệt
        </h4>
        <div class="kanban-board" id="approvalKanbanBoard">
            <!-- Yêu cầu chờ phê duyệt -->
            <div class="kanban-column approval-pending" data-status="approval_pending">
                <div class="kanban-header">
                    <div class="kanban-title">
                        <i class="fas fa-hourglass-half me-2"></i>
                        Yêu cầu chờ phê duyệt
                    </div>
                    <div class="kanban-count">{{ $approvalKanbanData['pending_approval_requests']->count() }}</div>
                </div>
                <div class="kanban-tasks" data-status="approval_pending">
                    @forelse($approvalKanbanData['pending_approval_requests'] as $approvalRequest)
                        @include('tasks.kanban-approval-request', ['approvalRequest' => $approvalRequest])
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>Không có yêu cầu nào</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Yêu cầu đã phê duyệt -->
            <div class="kanban-column approval-approved" data-status="approval_approved">
                <div class="kanban-header">
                    <div class="kanban-title">
                        <i class="fas fa-check-circle me-2"></i>
                        Yêu cầu đã phê duyệt
                    </div>
                    <div class="kanban-count">{{ $approvalKanbanData['approved_requests']->count() }}</div>
                </div>
                <div class="kanban-tasks" data-status="approval_approved">
                    @forelse($approvalKanbanData['approved_requests'] as $approvalRequest)
                        @include('tasks.kanban-approval-request', ['approvalRequest' => $approvalRequest])
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>Không có yêu cầu nào</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Yêu cầu bị từ chối -->
            <div class="kanban-column approval-rejected" data-status="approval_rejected">
                <div class="kanban-header">
                    <div class="kanban-title">
                        <i class="fas fa-times-circle me-2"></i>
                        Yêu cầu bị từ chối
                    </div>
                    <div class="kanban-count">{{ $approvalKanbanData['rejected_requests']->count() }}</div>
                </div>
                <div class="kanban-tasks" data-status="approval_rejected">
                    @forelse($approvalKanbanData['rejected_requests'] as $approvalRequest)
                        @include('tasks.kanban-approval-request', ['approvalRequest' => $approvalRequest])
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>Không có yêu cầu nào</p>
                        </div>
                    @endforelse
                </div>
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
    const approvalKanbanBoard = document.getElementById('approvalKanbanBoard');
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
            const tasks = column.querySelectorAll('.kanban-task, .kanban-approval-request');
            console.log('Column has', tasks.length, 'items'); // Debug log
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
        document.querySelectorAll('.kanban-task, .kanban-approval-request').forEach(item => {
            item.classList.add('readonly');
        });
    }
    
    // Initialize Sortable for tasks columns
    const taskColumns = document.querySelectorAll('#kanbanBoard .kanban-tasks');
    taskColumns.forEach(column => {
        new Sortable(column, {
            group: canDragDrop ? 'tasks' : false, // Separate group for tasks
            animation: 150,
            ghostClass: 'kanban-task-ghost',
            chosenClass: 'kanban-task-chosen',
            dragClass: 'kanban-task-dragging',
            disabled: !canDragDrop, // Disable for Employee
            onEnd: function(evt) {
                if (!canDragDrop) return; // Double check
                
                const newStatus = evt.to.dataset.status;
                const oldStatus = evt.from.dataset.status;
                
                // Don't update if dropped in the same column
                if (newStatus === oldStatus) {
                    return;
                }
                
                // It's a task
                const taskId = evt.item.dataset.taskId;
                updateTaskStatus(taskId, newStatus, evt.item);
            }
        });
    });
    
    // Initialize Sortable for approval request columns
    const approvalColumns = document.querySelectorAll('#approvalKanbanBoard .kanban-tasks');
    approvalColumns.forEach(column => {
        new Sortable(column, {
            group: canDragDrop ? 'approvals' : false, // Separate group for approvals
            animation: 150,
            ghostClass: 'kanban-task-ghost',
            chosenClass: 'kanban-task-chosen',
            dragClass: 'kanban-task-dragging',
            disabled: !canDragDrop, // Disable for Employee
            onEnd: function(evt) {
                if (!canDragDrop) return; // Double check
                
                const newStatus = evt.to.dataset.status;
                const oldStatus = evt.from.dataset.status;
                
                // Don't update if dropped in the same column
                if (newStatus === oldStatus) {
                    return;
                }
                
                // It's an approval request
                const approvalId = evt.item.dataset.approvalId;
                updateApprovalStatus(approvalId, newStatus, evt.item);
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

function updateApprovalStatus(approvalId, newStatus, approvalElement) {
    // Map Kanban status to approval request status
    const statusMap = {
        'approval_pending': 'pending',
        'approval_approved': 'approved',
        'approval_rejected': 'rejected'
    };
    
    const approvalStatus = statusMap[newStatus];
    if (!approvalStatus) {
        console.error('Invalid status mapping:', newStatus);
        return;
    }
    
    approvalElement.style.opacity = '0.5';
    
    fetch(`/approval-requests/${approvalId}/update-status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            approval_status: approvalStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            // Update the card's status class
            approvalElement.className = approvalElement.className.replace(/pending|approved|rejected/g, approvalStatus);
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        location.reload();
        showNotification('error', 'Có lỗi xảy ra khi cập nhật trạng thái');
    })
    .finally(() => {
        approvalElement.style.opacity = '1';
    });
}

function updateColumnCounts() {
    const columns = document.querySelectorAll('.kanban-column');
    columns.forEach(column => {
        const status = column.dataset.status;
        const count = column.querySelectorAll('.kanban-task, .kanban-approval-request').length;
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
