@php
    $assignee = $task->assignee ?? $task->assignees->first();
    $assigneeName = $assignee ? $assignee->name : 'Chưa giao';
    $assigneeInitials = $assignee ? strtoupper(substr($assignee->name, 0, 1)) : '?';
    
    $creatorName = $task->creator ? $task->creator->name : 'Không xác định';
    
    // Lấy thông tin phòng ban
    $departments = collect();
    if ($task->department) {
        $departments->push($task->department);
    }
    if ($task->departments && $task->departments->count() > 0) {
        $departments = $departments->merge($task->departments);
    }
    $departments = $departments->unique('id');
    
    $deadlineClass = '';
    $deadlineText = '';
    if ($task->deadline) {
        if ($task->deadline->isPast()) {
            $deadlineClass = 'past';
            $daysOverdue = $task->deadline->diffInDays(now());
            $deadlineText = 'Quá hạn ' . $daysOverdue . ' ngày: ' . $task->deadline->format('d/m/Y');
        } else {
            $deadlineClass = 'future';
            $deadlineText = 'Hạn: ' . $task->deadline->format('d/m/Y');
        }
    }
@endphp

<div class="kanban-task {{ $task->status }}" data-task-id="{{ $task->id }}">
    <div class="task-title">{{ $task->title }}</div>
    
    <div class="task-meta">
        <div class="task-assignee">
            <div class="task-assignee-avatar">{{ $assigneeInitials }}</div>
            <span>{{ $assigneeName }}</span>
        </div>
        @if($task->deadline)
            <div class="task-deadline {{ $deadlineClass }}">{{ $deadlineText }}</div>
        @endif
    </div>
    
    @if($departments->count() > 0)
        <div class="task-meta">
            <div class="task-department">
                <i class="fas fa-building me-1"></i>
                @if($departments->count() == 1)
                    <span class="department-name">Phòng: {{ $departments->first()->name }}</span>
                @else
                    <span class="department-name department-tooltip" 
                          data-bs-toggle="tooltip" 
                          data-bs-placement="top" 
                          title="{{ $departments->pluck('name')->join(', ') }}">
                        {{ $departments->count() }} phòng ban
                    </span>
                @endif
            </div>
        </div>
    @endif
    
    <div class="task-meta">
        <small class="text-muted">
            <i class="fas fa-user me-1"></i>
            Giao bởi: {{ $creatorName }}
        </small>
        @if($task->priority)
            <span class="badge bg-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning' : 'success') }}">
                {{ $task->priority === 'high' ? 'Cao' : ($task->priority === 'medium' ? 'Trung bình' : 'Thấp') }}
            </span>
        @endif
    </div>
    
    @if($task->description)
        <div class="task-description mt-2">
            <small class="text-muted">{{ Str::limit($task->description, 80) }}</small>
        </div>
    @endif
    
    <div class="task-actions">
        <a href="{{ route('task-detail', $task) }}" class="task-action-btn btn-view">
            <i class="fas fa-eye me-1"></i>
            Xem
        </a>
        <a href="{{ route('tasks.edit', $task) }}" class="task-action-btn btn-edit">
            <i class="fas fa-edit me-1"></i>
            Sửa
        </a>
    </div>
</div>
