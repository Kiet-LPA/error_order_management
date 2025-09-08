@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 mb-0">
                    {{ __('Chi tiết yêu cầu hỗ trợ') }}
                </h2>
                <a href="{{ route('support-requests.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Quay lại
                </a>
            </div>

            <!-- Thông tin chính -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h3 class="h5 mb-3">{{ $supportRequest->title }}</h3>
                            
                            @if($supportRequest->description)
                                <div class="mb-3">
                                    <h6 class="fw-bold">Mô tả:</h6>
                                    <p class="text-muted">{{ $supportRequest->description }}</p>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-6">
                                    <span class="fw-bold">Trạng thái:</span>
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-warning',
                                            'approved' => 'bg-success',
                                            'rejected' => 'bg-danger'
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Chờ phê duyệt',
                                            'approved' => 'Đã phê duyệt',
                                            'rejected' => 'Bị từ chối'
                                        ];
                                    @endphp
                                    <span class="badge {{ $statusColors[$supportRequest->status] }} ms-2">
                                        {{ $statusLabels[$supportRequest->status] }}
                                    </span>
                                </div>

                                <div class="col-6">
                                    <span class="fw-bold">Độ ưu tiên:</span>
                                    @php
                                        $priorityColors = [
                                            'low' => 'bg-success',
                                            'medium' => 'bg-warning',
                                            'high' => 'bg-danger'
                                        ];
                                        $priorityLabels = [
                                            'low' => 'Thấp',
                                            'medium' => 'Trung bình',
                                            'high' => 'Cao'
                                        ];
                                    @endphp
                                    <span class="badge {{ $priorityColors[$supportRequest->priority] }} ms-2">
                                        {{ $priorityLabels[$supportRequest->priority] }}
                                    </span>
                                </div>
                            </div>

                            @if($supportRequest->is_urgent)
                                <div class="mt-3">
                                    <span class="badge bg-danger">Khẩn cấp</span>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <span class="fw-bold">Người yêu cầu:</span>
                                    <p class="mb-1">{{ $supportRequest->requester->name }}</p>
                                    <small class="text-muted">{{ $supportRequest->requester->email }}</small>
                                </div>

                                <div class="col-12 mb-3">
                                    <span class="fw-bold">Phòng ban:</span>
                                    <p class="mb-0">{{ $supportRequest->department->name }}</p>
                                </div>

                                @if($supportRequest->approver)
                                    <div class="col-12 mb-3">
                                        <span class="fw-bold">Người phê duyệt:</span>
                                        <p class="mb-1">{{ $supportRequest->approver->name }}</p>
                                        <small class="text-muted">{{ $supportRequest->approver->role }}</small>
                                    </div>
                                @endif

                                @if($supportRequest->deadline)
                                    <div class="col-12 mb-3">
                                        <span class="fw-bold">Deadline:</span>
                                        <p class="mb-0">{{ \Carbon\Carbon::parse($supportRequest->deadline)->format('d/m/Y') }}</p>
                                    </div>
                                @endif

                                <div class="col-12">
                                    <span class="fw-bold">Ngày tạo:</span>
                                    <p class="mb-0">{{ $supportRequest->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin người tham gia -->
                    <div class="mt-4">
                        <h6 class="fw-bold mb-3">Người tham gia:</h6>
                        <div class="row">
                            @php
                                // Thu thập tất cả người tham gia
                                $allParticipants = [];
                                
                                // Thêm người yêu cầu
                                if ($supportRequest->requester) {
                                    $allParticipants[] = [
                                        'name' => $supportRequest->requester->name,
                                        'role' => 'Người yêu cầu',
                                        'badge_class' => 'bg-primary'
                                    ];
                                }
                                
                                // Thêm người chuyển tiếp
                                if ($supportRequest->forwarded_by && $supportRequest->forwardedBy) {
                                    $allParticipants[] = [
                                        'name' => $supportRequest->forwardedBy->name,
                                        'role' => 'Người chuyển tiếp',
                                        'badge_class' => 'bg-info'
                                    ];
                                }
                                
                                // Thêm người nhận hiện tại
                                if ($supportRequest->recipients) {
                                    $recipientIds = is_string($supportRequest->recipients) 
                                        ? json_decode($supportRequest->recipients, true) 
                                        : $supportRequest->recipients;
                                    
                                    if (is_array($recipientIds)) {
                                        $recipients = \App\Models\User::whereIn('id', $recipientIds)->get();
                                        foreach ($recipients as $recipient) {
                                            $allParticipants[] = [
                                                'name' => $recipient->name,
                                                'role' => 'Người nhận',
                                                'badge_class' => 'bg-success'
                                            ];
                                        }
                                    }
                                }
                                
                                // Loại bỏ trùng lặp (nếu cùng một người có nhiều vai trò)
                                $uniqueParticipants = [];
                                $seenNames = [];
                                foreach ($allParticipants as $participant) {
                                    if (!in_array($participant['name'], $seenNames)) {
                                        $uniqueParticipants[] = $participant;
                                        $seenNames[] = $participant['name'];
                                    }
                                }
                            @endphp
                            
                            @if(count($uniqueParticipants) > 0)
                                <div class="col-12">
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($uniqueParticipants as $participant)
                                            <span class="badge {{ $participant['badge_class'] }} me-1 mb-1">
                                                {{ $participant['name'] }} 
                                                <small>({{ $participant['role'] }})</small>
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="col-12">
                                    <p class="text-muted mb-0">Chưa có người tham gia nào.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- File đính kèm -->
                    @if($supportRequest->attachments && count($supportRequest->attachments) > 0)
                        <div class="mt-4">
                            <h6 class="fw-bold mb-3">File đính kèm:</h6>
                            <div class="row">
                                @foreach($supportRequest->attachments as $attachment)
                                    <div class="col-md-6 mb-2">
                                        <a href="{{ $attachment['url'] }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-file-earmark me-2"></i>{{ $attachment['name'] }}
                                        </a>
                                        <small class="text-muted ms-2">{{ number_format($attachment['size'] / 1024, 2) }} KB</small>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Phê duyệt/Từ chối (chỉ cho approver) -->
            @if($supportRequest->status === 'pending' && $supportRequest->canBeApprovedBy(auth()->user()))
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Phê duyệt yêu cầu</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <form method="POST" action="{{ route('support-requests.approve', $supportRequest) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-circle me-2"></i>Phê duyệt
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                    <i class="bi bi-x-circle me-2"></i>Từ chối
                                </button>
                            </div>
                            @if($supportRequest->canBeForwardedBy(auth()->user()))
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#forwardModal">
                                        <i class="bi bi-arrow-right me-2"></i>Chuyển tiếp
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Modal từ chối -->
                <div class="modal fade" id="rejectModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Từ chối yêu cầu</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="{{ route('support-requests.reject', $supportRequest) }}">
                                @csrf
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="rejection_reason" class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
                                        <textarea name="rejection_reason" id="rejection_reason" rows="4" class="form-control" required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                    <button type="submit" class="btn btn-danger">Từ chối</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal chuyển tiếp -->
                <div class="modal fade" id="forwardModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Chuyển tiếp yêu cầu hỗ trợ</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="{{ route('support-requests.forward', $supportRequest) }}">
                                @csrf
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Người nhận mới <span class="text-danger">*</span></label>
                                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                            @php
                                                // Lấy danh sách người đã được forward hoặc đã có trong request
                                                $currentRecipients = [];
                                                if ($supportRequest->recipients) {
                                                    $currentRecipients = is_string($supportRequest->recipients) 
                                                        ? json_decode($supportRequest->recipients, true) 
                                                        : $supportRequest->recipients;
                                                }
                                                
                                                // Thêm người đã forward (nếu có)
                                                if ($supportRequest->forwarded_by) {
                                                    $currentRecipients[] = $supportRequest->forwarded_by;
                                                }
                                                
                                                // Thêm người tạo request
                                                if ($supportRequest->requester_id) {
                                                    $currentRecipients[] = $supportRequest->requester_id;
                                                }
                                                
                                                $currentRecipients = array_unique($currentRecipients);
                                            @endphp
                                            
                                            @foreach(\App\Models\User::whereIn('role', ['admin', 'director', 'manager'])->get() as $user)
                                                @php
                                                    $isDisabled = in_array($user->id, $currentRecipients);
                                                @endphp
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="new_recipients[]" 
                                                           value="{{ $user->id }}" 
                                                           id="recipient_{{ $user->id }}"
                                                           {{ $isDisabled ? 'disabled' : '' }}>
                                                    <label class="form-check-label {{ $isDisabled ? 'text-muted' : '' }}" 
                                                           for="recipient_{{ $user->id }}">
                                                        {{ $user->name }} ({{ ucfirst($user->role) }})
                                                        @if($isDisabled)
                                                            <small class="text-warning"> - Đã tham gia</small>
                                                        @endif
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="forwarding_reason" class="form-label">Lý do chuyển tiếp <span class="text-danger">*</span></label>
                                        <textarea name="forwarding_reason" id="forwarding_reason" rows="4" class="form-control" required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                    <button type="submit" class="btn btn-info">Chuyển tiếp</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Hoàn tác (undo) approve/reject -->
            @if($supportRequest->canBeUndone() && (auth()->user()->isManager() || auth()->user()->isDirector() || auth()->user()->isAdmin()))
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Hoàn tác</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            Bạn có thể hoàn tác {{ $supportRequest->status === 'approved' ? 'phê duyệt' : 'từ chối' }} này để chuyển yêu cầu về trạng thái chờ xử lý.
                        </p>
                        <form method="POST" action="{{ route('support-requests.undo', $supportRequest) }}" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn hoàn tác {{ $supportRequest->status === 'approved' ? 'phê duyệt' : 'từ chối' }} này?')">
                            @csrf
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-arrow-counterclockwise me-2"></i>Hoàn tác {{ $supportRequest->status === 'approved' ? 'phê duyệt' : 'từ chối' }}
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Hủy yêu cầu (chỉ Employee trong 3 giờ) -->
            @if($supportRequest->canBeCancelledByEmployee() && auth()->user()->isEmployee() && $supportRequest->requester_id === auth()->user()->id)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Hủy yêu cầu</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            Bạn có thể hủy yêu cầu này trong vòng 3 giờ sau khi gửi và khi chưa được xử lý.
                        </p>
                        <form method="POST" action="{{ route('support-requests.cancel', $supportRequest) }}" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn hủy yêu cầu hỗ trợ này?')">
                            @csrf
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-x-circle me-2"></i>Hủy yêu cầu
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Xóa yêu cầu (chỉ Admin và Director) -->
            @if($supportRequest->canBeDeletedBy(auth()->user()))
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Xóa yêu cầu</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            <strong>Cảnh báo:</strong> Hành động này sẽ xóa vĩnh viễn yêu cầu hỗ trợ và tất cả dữ liệu liên quan (bình luận, file đính kèm, lịch sử hoạt động). Hành động này không thể hoàn tác.
                        </p>
                        <button type="button" class="btn btn-danger" 
                                onclick="deleteSupportRequest({{ $supportRequest->id }}, '{{ $supportRequest->title }}')">
                            <i class="bi bi-trash me-2"></i>Xóa yêu cầu hỗ trợ
                        </button>
                    </div>
                </div>
            @endif

            <!-- Comments -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Bình luận</h5>
                </div>
                <div class="card-body">
                    @if($supportRequest->comments && $supportRequest->comments->count() > 0)
                        <div class="space-y-3">
                            @foreach($supportRequest->comments as $comment)
                                <div class="border-bottom pb-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <strong>{{ $comment->user->name }}</strong>
                                            <small class="text-muted ms-2">{{ $comment->created_at->format('d/m/Y H:i') }}</small>
                                        </div>
                                    </div>
                                    <p class="mb-2">{{ $comment->content }}</p>
                                    
                                    @if($comment->attachments && count($comment->attachments) > 0)
                                        <div class="mt-2">
                                            @foreach($comment->attachments as $attachment)
                                                <a href="{{ $attachment['url'] }}" target="_blank" class="btn btn-outline-primary btn-sm me-2">
                                                    <i class="bi bi-file-earmark me-1"></i>{{ $attachment['name'] }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-3">Chưa có bình luận nào.</p>
                    @endif

                    <!-- Form thêm comment -->
                    <form method="POST" action="{{ route('support-requests.comment', $supportRequest) }}" enctype="multipart/form-data" class="mt-4">
                        @csrf
                        <div class="mb-3">
                            <label for="content" class="form-label">Thêm bình luận</label>
                            <textarea name="content" id="content" rows="3" class="form-control" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="files" class="form-label">File đính kèm</label>
                            <input type="file" name="files[]" id="files" multiple class="form-control">
                            <div class="form-text">Có thể chọn nhiều file. Định dạng: PDF, DOC, XLS, PPT, JPG, PNG, GIF.</div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-2"></i>Gửi bình luận
                        </button>
                    </form>
                </div>
            </div>

            <!-- Lịch sử hoạt động -->
            @if($supportRequest->activities && $supportRequest->activities->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Lịch sử hoạt động</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @foreach($supportRequest->activities as $activity)
                                <div class="timeline-item mb-3">
                                    <div class="d-flex">
                                        <div class="timeline-marker me-3">
                                            <i class="bi bi-circle-fill text-primary"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="d-flex justify-content-between">
                                                <strong>{{ $activity->user->name }}</strong>
                                                <small class="text-muted">{{ $activity->created_at->format('d/m/Y H:i') }}</small>
                                            </div>
                                            <p class="mb-1">
                                                @switch($activity->action)
                                                    @case('created')
                                                        Đã tạo yêu cầu hỗ trợ
                                                        @break
                                                    @case('approved')
                                                        Đã phê duyệt yêu cầu
                                                        @break
                                                    @case('rejected')
                                                        Đã từ chối yêu cầu
                                                        @if(isset($activity->meta['reason']))
                                                            - Lý do: {{ $activity->meta['reason'] }}
                                                        @endif
                                                        @break
                                                    @case('commented')
                                                        Đã thêm bình luận
                                                        @break
                                                    @default
                                                        {{ $activity->action }}
                                                @endswitch
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
// Validation cho form chuyển tiếp
document.addEventListener('DOMContentLoaded', function() {
    const forwardForm = document.getElementById('forwardForm');
    if (forwardForm) {
        forwardForm.addEventListener('submit', function(e) {
            const checkboxes = document.querySelectorAll('input[name="new_recipients[]"]:checked:not(:disabled)');
            if (checkboxes.length === 0) {
                e.preventDefault();
                alert('Vui lòng chọn ít nhất một người nhận.');
                return false;
            }
        });
    }
});

// Function xóa support request
function deleteSupportRequest(requestId, requestTitle) {
    if (confirm(`Bạn có chắc chắn muốn xóa yêu cầu hỗ trợ "${requestTitle}"?\n\nHành động này không thể hoàn tác và sẽ xóa tất cả dữ liệu liên quan.`)) {
        // Tạo form để gửi DELETE request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/support-requests/${requestId}`;
        
        // Thêm CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Thêm method override
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        // Thêm form vào body và submit
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
