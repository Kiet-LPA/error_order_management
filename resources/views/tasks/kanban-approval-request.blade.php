@php
    $creatorName = $approvalRequest->creator ? $approvalRequest->creator->name : 'Không xác định';
    $currentApproverName = $approvalRequest->currentApprover ? $approvalRequest->currentApprover->name : 'Chưa giao';
    $approvedByName = $approvalRequest->approvedBy ? $approvalRequest->approvedBy->name : null;
    $rejectedByName = $approvalRequest->rejectedBy ? $approvalRequest->rejectedBy->name : null;
    
    // Lấy thông tin phòng ban từ creator
    $departmentName = $approvalRequest->creator && $approvalRequest->creator->department 
        ? $approvalRequest->creator->department->name 
        : null;
    
    // Format form type
    $formTypeText = match($approvalRequest->form_type) {
        'leave_request' => 'Đơn xin nghỉ phép',
        'overtime_request' => 'Đơn xin làm thêm giờ',
        'expense_request' => 'Đơn xin thanh toán',
        'equipment_request' => 'Đơn xin thiết bị',
        'travel_request' => 'Đơn xin công tác',
        default => 'Yêu cầu phê duyệt'
    };
    
    // Format status
    $statusText = match($approvalRequest->approval_status) {
        'pending' => 'Chờ phê duyệt',
        'approved' => 'Đã phê duyệt',
        'rejected' => 'Đã từ chối',
        default => 'Không xác định'
    };
    
    $statusClass = match($approvalRequest->approval_status) {
        'pending' => 'warning',
        'approved' => 'success',
        'rejected' => 'danger',
        default => 'secondary'
    };
@endphp

<div class="kanban-approval-request {{ $approvalRequest->approval_status }}" data-approval-id="{{ $approvalRequest->id }}">
    @if($departmentName)
        <div class="task-meta">
            <div class="task-department">
                <i class="fas fa-building me-1"></i>
                <span class="department-name">{{ $departmentName }}</span>
            </div>
        </div>
    @endif
    
    <div class="approval-title">{{ $formTypeText }}</div>
    
    <div class="task-meta">
        <div class="approval-creator">
            <i class="fas fa-user me-1"></i>
            <span>{{ $creatorName }}</span>
        </div>
        @if($approvalRequest->approval_status === 'pending')
            <div class="approval-approver">
                <i class="fas fa-user-check me-1"></i>
                <span>{{ $currentApproverName }}</span>
            </div>
        @elseif($approvalRequest->approval_status === 'approved' && $approvedByName)
            <div class="approval-approved">
                <i class="fas fa-check-circle me-1"></i>
                <span>{{ $approvedByName }}</span>
            </div>
        @elseif($approvalRequest->approval_status === 'rejected' && $rejectedByName)
            <div class="approval-rejected">
                <i class="fas fa-times-circle me-1"></i>
                <span>{{ $rejectedByName }}</span>
            </div>
        @endif
    </div>
    
    <div class="task-meta">
        <span class="badge bg-{{ $statusClass }}">
            {{ $statusText }}
        </span>
        @if($approvalRequest->created_at)
            <small class="text-muted">
                <i class="fas fa-clock me-1"></i>
                {{ $approvalRequest->created_at->format('d/m/Y') }}
            </small>
        @endif
    </div>
    
    @if($approvalRequest->form_data && isset($approvalRequest->form_data['reason']))
        <div class="task-description mt-2">
            <small class="text-muted">{{ Str::limit($approvalRequest->form_data['reason'], 80) }}</small>
        </div>
    @endif
    
    <div class="task-actions">
        <a href="{{ route('approval.show', $approvalRequest->id) }}" class="task-action-btn btn-view">
            <i class="fas fa-eye me-1"></i>
            Xem
        </a>
        @if($approvalRequest->approval_status === 'pending' && auth()->user()->isManager())
            <a href="{{ route('approval.edit', $approvalRequest->id) }}" class="task-action-btn btn-edit">
                <i class="fas fa-check me-1"></i>
                Phê duyệt
            </a>
        @endif
    </div>
</div>
