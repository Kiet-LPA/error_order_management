@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 mb-0">
                    {{ __('Yêu cầu hỗ trợ') }}
                </h2>
                @if(auth()->user()->isEmployee())
                    <a href="{{ route('support-requests.create') }}" 
                       class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Tạo yêu cầu hỗ trợ
                    </a>
                @endif
            </div>

            <div class="card">
                <div class="card-body">
                    @if($supportRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Tiêu đề</th>
                                        <th>Người yêu cầu</th>
                                        <th>Phòng ban</th>
                                        <th>Độ ưu tiên</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($supportRequests as $request)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $request->title }}</div>
                                                @if($request->is_urgent)
                                                    <span class="badge bg-danger">Khẩn cấp</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>{{ $request->requester->name }}</div>
                                                <small class="text-muted">{{ $request->requester->email }}</small>
                                            </td>
                                            <td>{{ $request->department->name }}</td>
                                            <td>
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
                                                <span class="badge {{ $priorityColors[$request->priority] }}">
                                                    {{ $priorityLabels[$request->priority] }}
                                                </span>
                                            </td>
                                            <td>
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
                                                <span class="badge {{ $statusColors[$request->status] }}">
                                                    {{ $statusLabels[$request->status] }}
                                                </span>
                                            </td>
                                            <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('support-requests.show', $request) }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    
                                                    @if($request->canBeApprovedBy(auth()->user()) && $request->status === 'pending')
                                                        <form method="POST" action="{{ route('support-requests.approve', $request) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-success" 
                                                                    onclick="return confirm('Bạn có chắc chắn muốn phê duyệt yêu cầu này?')">
                                                                <i class="bi bi-check"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    
                                                    @if($request->canBeApprovedBy(auth()->user()) && $request->status === 'pending')
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="showRejectModal({{ $request->id }})">
                                                            <i class="bi bi-x"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    @if($request->canBeForwardedBy(auth()->user()) && $request->status === 'pending')
                                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                                onclick="showForwardModal({{ $request->id }})">
                                                            <i class="bi bi-arrow-right"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    @if($request->canBeDeletedBy(auth()->user()))
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="deleteSupportRequest({{ $request->id }}, '{{ $request->title }}')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted"></i>
                            <h5 class="mt-3">Chưa có yêu cầu hỗ trợ nào</h5>
                            @if(auth()->user()->isEmployee())
                                <p class="text-muted">Bạn có thể tạo yêu cầu hỗ trợ đầu tiên</p>
                                <a href="{{ route('support-requests.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Tạo yêu cầu mới
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal từ chối -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Từ chối yêu cầu hỗ trợ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
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
            <form id="forwardForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Người nhận mới <span class="text-danger">*</span></label>
                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                            @foreach(\App\Models\User::whereIn('role', ['manager', 'director'])->with('department')->get() as $user)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="new_recipients[]" 
                                           value="{{ $user->id }}" id="recipient_{{ $user->id }}">
                                    <label class="form-check-label" for="recipient_{{ $user->id }}">
                                        <strong>{{ $user->name }}</strong> 
                                        <span class="badge bg-info ms-1">{{ ucfirst($user->role) }}</span>
                                        <small class="text-muted d-block">{{ $user->department->name ?? 'N/A' }}</small>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <div class="form-text">Chọn một hoặc nhiều người nhận từ danh sách trên.</div>
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

<script>
function showRejectModal(requestId) {
    document.getElementById('rejectForm').action = `/support-requests/${requestId}/reject`;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function showForwardModal(requestId) {
    document.getElementById('forwardForm').action = `/support-requests/${requestId}/forward`;
    new bootstrap.Modal(document.getElementById('forwardModal')).show();
}

// Validation cho form chuyển tiếp
document.getElementById('forwardForm').addEventListener('submit', function(e) {
    const checkboxes = document.querySelectorAll('input[name="new_recipients[]"]:checked:not(:disabled)');
    if (checkboxes.length === 0) {
        e.preventDefault();
        alert('Vui lòng chọn ít nhất một người nhận.');
        return false;
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
