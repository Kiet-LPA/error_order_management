@php
    $requesterName = $supportRequest->requester ? $supportRequest->requester->name : 'Không xác định';
    $approverName = $supportRequest->approver ? $supportRequest->approver->name : null;
    
    // Lấy thông tin phòng ban
    $departmentName = $supportRequest->department ? $supportRequest->department->name : null;
    $sourceDepartmentName = $supportRequest->sourceDepartment ? $supportRequest->sourceDepartment->name : null;
    
    // Format status
    switch($supportRequest->status) {
        case 'pending':
            $statusText = 'Chờ phê duyệt';
            break;
        case 'approved':
            $statusText = 'Đã phê duyệt';
            break;
        case 'rejected':
            $statusText = 'Đã từ chối';
            break;
        case 'forwarded':
            $statusText = 'Đã chuyển tiếp';
            break;
        case 'cancelled':
            $statusText = 'Đã hủy';
            break;
        default:
            $statusText = 'Không xác định';
    }
    
    switch($supportRequest->status) {
        case 'pending':
            $statusClass = 'warning';
            break;
        case 'approved':
            $statusClass = 'success';
            break;
        case 'rejected':
            $statusClass = 'danger';
            break;
        case 'forwarded':
            $statusClass = 'info';
            break;
        case 'cancelled':
            $statusClass = 'secondary';
            break;
        default:
            $statusClass = 'secondary';
    }
    
    // Format priority
    switch($supportRequest->priority) {
        case 'low':
            $priorityText = 'Thấp';
            break;
        case 'medium':
            $priorityText = 'Trung bình';
            break;
        case 'high':
            $priorityText = 'Cao';
            break;
        case 'urgent':
            $priorityText = 'Khẩn cấp';
            break;
        default:
            $priorityText = 'Không xác định';
    }
    
    switch($supportRequest->priority) {
        case 'low':
            $priorityClass = 'success';
            break;
        case 'medium':
            $priorityClass = 'warning';
            break;
        case 'high':
            $priorityClass = 'danger';
            break;
        case 'urgent':
            $priorityClass = 'danger';
            break;
        default:
            $priorityClass = 'secondary';
    }
@endphp

<div class="kanban-support-request {{ $supportRequest->status }}" data-support-id="{{ $supportRequest->id }}">
    @if($departmentName)
        <div class="task-meta">
            <div class="task-department">
                <i class="fas fa-building me-1"></i>
                <span class="department-name">{{ $departmentName }}</span>
            </div>
        </div>
    @endif
    
    <div class="support-title">{{ $supportRequest->title }}</div>
    
    <div class="task-meta">
        <div class="support-requester">
            <i class="fas fa-user me-1"></i>
            <span>{{ $requesterName }}</span>
        </div>
    </div>
    
    <div class="task-meta">
        <span class="badge bg-{{ $statusClass }}">
            {{ $statusText }}
        </span>
        @if($supportRequest->priority)
            <span class="badge bg-{{ $priorityClass }}">
                {{ $priorityText }}
            </span>
        @endif
        @if($supportRequest->created_at)
            <small class="text-muted">
                <i class="fas fa-clock me-1"></i>
                {{ $supportRequest->created_at->format('d/m/Y') }}
            </small>
        @endif
    </div>
    
    @if($supportRequest->description)
        <div class="support-description">
            {{ strlen($supportRequest->description) > 100 ? substr($supportRequest->description, 0, 100) . '...' : $supportRequest->description }}
        </div>
    @endif
    
    @if($supportRequest->is_urgent)
        <div class="support-urgent">
            <i class="fas fa-exclamation-triangle me-1"></i>
            <span>Khẩn cấp</span>
        </div>
    @endif
    
    <div class="task-actions mt-2">
        <a href="{{ route('support-requests.show', $supportRequest->id) }}" 
           class="btn btn-sm btn-outline-primary" 
           title="Xem chi tiết">
            <i class="fas fa-eye me-1"></i>
            Xem
        </a>
    </div>
</div>