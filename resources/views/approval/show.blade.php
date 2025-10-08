    @extends('layouts.master')

@section('title', 'Chi tiết đề xuất #' . $approvalRequest->id)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-clipboard-check"></i> {{ $approvalRequest->form_data['title'] ?? ($formConfig ? $formConfig->form_name : 'Đề xuất') }} #{{ $approvalRequest->id }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('approval.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Status Info -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="bi bi-person"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Người tạo</span>
                                    <span class="info-box-number">{{ $approvalRequest->creator->name }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="bi bi-clock"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Trạng thái</span>
                                    <span class="info-box-number">
                                        <span class="badge badge-{{ $approvalRequest->approval_status === 'approved' ? 'success' : ($approvalRequest->approval_status === 'rejected' ? 'danger' : 'warning') }}">
                                            {{ $approvalRequest->getApprovalStatusText() }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Approval Signatures -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="bi bi-check-circle"></i> Chữ ký phê duyệt</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Người phê duyệt -->
                                <div class="col-md-6">
                                    <div class="text-center border p-3">
                                        <h6 class="text-muted mb-2">Người phê duyệt</h6>
                                        @if($approvalRequest->approved_by_id)
                                            {{-- Đã có người phê duyệt --}}
                                            <div class="fw-bold">{{ $approvalRequest->approvedBy->name ?? 'N/A' }}</div>
                                            <div class="text-muted small">
                                                @if($approvalRequest->approvedBy->role === 'manager')
                                                    Quản lý
                                                @elseif($approvalRequest->approvedBy->role === 'director')
                                                    Giám đốc
                                                @else
                                                    {{ ucfirst($approvalRequest->approvedBy->role ?? 'N/A') }}
                                                @endif
                                            </div>
                                            @if($approvalRequest->approved_at)
                                                <div class="text-muted small">{{ $approvalRequest->approved_at->format('d/m/Y H:i') }}</div>
                                            @endif
                                        @elseif($approvalRequest->current_approver_id)
                                            {{-- Chưa phê duyệt, hiển thị người sẽ phê duyệt --}}
                                            <div class="fw-bold">{{ $approvalRequest->currentApprover->name ?? 'N/A' }}</div>
                                            <div class="text-muted small">
                                                @if($approvalRequest->currentApprover->role === 'manager')
                                                    Quản lý
                                                @elseif($approvalRequest->currentApprover->role === 'director')
                                                    Giám đốc
                                                @else
                                                    {{ ucfirst($approvalRequest->currentApprover->role ?? 'N/A') }}
                                                @endif
                                            </div>
                                            <div class="text-muted small">Chờ phê duyệt</div>
                                        @else
                                            <div class="text-muted">Chưa có người phê duyệt</div>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Người làm đơn -->
                                <div class="col-md-6">
                                    <div class="text-center border p-3">
                                        <h6 class="text-muted mb-2">Người làm đơn</h6>
                                        <div class="fw-bold">{{ $approvalRequest->creator->name }}</div>
                                        <div class="text-muted small">
                                            @if($approvalRequest->creator->role === 'employee')
                                                Nhân viên
                                            @elseif($approvalRequest->creator->role === 'manager')
                                                Quản lý
                                            @elseif($approvalRequest->creator->role === 'director')
                                                Giám đốc
                                            @else
                                                {{ ucfirst($approvalRequest->creator->role) }}
                                            @endif
                                        </div>
                                        <div class="text-muted small">{{ $approvalRequest->created_at->format('d/m/Y H:i') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Controls - Chỉ người tạo mới thấy -->
                    @if($approvalRequest->canChangeStatus(auth()->id()))
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <form method="POST" action="{{ route('approval.update-discussion-status', $approvalRequest->id) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <div class="form-group">
                                        <label>Trạng thái thảo luận:</label>
                                        <select name="discussion_status" onchange="this.form.submit()" class="form-control">
                                            <option value="open" {{ $approvalRequest->discussion_status === 'open' ? 'selected' : '' }}>
                                                Có thể thảo luận
                                            </option>
                                            <option value="closed" {{ $approvalRequest->discussion_status === 'closed' ? 'selected' : '' }}>
                                                Không thể thảo luận
                                            </option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <form method="POST" action="{{ route('approval.update-edit-status', $approvalRequest->id) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <div class="form-group">
                                        <label>Trạng thái chỉnh sửa:</label>
                                        <select name="edit_status" onchange="this.form.submit()" class="form-control">
                                            <option value="editable" {{ $approvalRequest->edit_status === 'editable' ? 'selected' : '' }}>
                                                Có thể chỉnh sửa
                                            </option>
                                            <option value="locked" {{ $approvalRequest->edit_status === 'locked' ? 'selected' : '' }}>
                                                Không thể chỉnh sửa
                                            </option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif

                    <!-- Form Content -->
                    <div class="card">
                        <div class="card-header">
                            <h4>Nội dung đề xuất</h4>
                        </div>
                        <div class="card-body">
                            <!-- Các trường cơ bản -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Tiêu đề đề xuất:</label>
                                        <div class="form-control-plaintext">{{ $approvalRequest->form_data['title'] ?? '' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Phòng ban:</label>
                                        <div class="form-control-plaintext">
                                            @if(!empty($approvalRequest->form_data['department']))
                                                @php
                                                    $department = \App\Models\Department::find($approvalRequest->form_data['department']);
                                                @endphp
                                                {{ $department ? $department->name : 'Không xác định' }}
                                            @else
                                                Gửi cho Director/Admin
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Mô tả:</label>
                                        <div class="form-control-plaintext">{{ $approvalRequest->form_data['description'] ?? '' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Người phê duyệt:</label>
                                        <div class="form-control-plaintext">
                                            @if(!empty($approvalRequest->form_data['manager']))
                                                @php
                                                    $manager = \App\Models\User::find($approvalRequest->form_data['manager']);
                                                @endphp
                                                {{ $manager ? $manager->name : 'Không xác định' }}
                                            @else
                                                Director/Admin
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Phương thức thanh toán:</label>
                                        <div class="form-control-plaintext">
                                            @if($approvalRequest->form_data['payment_method'] === 'bank_transfer')
                                                Chuyển khoản
                                            @elseif($approvalRequest->form_data['payment_method'] === 'cash')
                                                Tiền mặt
                                            @else
                                                {{ $approvalRequest->form_data['payment_method'] ?? '' }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Số tiền:</label>
                                        <div class="form-control-plaintext">{{ number_format($approvalRequest->form_data['amount'] ?? 0) }} VNĐ</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Thông tin ngân hàng -->
                            @if(!empty($approvalRequest->form_data['payment_method']) && ($approvalRequest->form_data['payment_method'] === 'Chuyển khoản' || $approvalRequest->form_data['payment_method'] === 'bank_transfer'))
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Thông tin ngân hàng:</label>
                                        <div class="form-control-plaintext">
                                            <strong>Số tài khoản:</strong> {{ $approvalRequest->form_data['bank_account'] ?? '' }}<br>
                                            <strong>Tên ngân hàng:</strong> {{ $approvalRequest->form_data['bank_name'] ?? '' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            
                            @if($formConfig && $formConfig->form_fields)
                                @foreach($formConfig->form_fields as $field)
                                    @if($field['name'] !== 'title' && $field['name'] !== 'description' && $field['name'] !== 'department' && $field['name'] !== 'amount' && $field['name'] !== 'payment_method' && $field['name'] !== 'bank_account' && $field['name'] !== 'bank_name' && $field['type'] !== 'approver_select')
                                <div class="form-group">
                                    <label class="font-weight-bold">{{ $field['label'] }}:</label>
                                    <div class="form-control-plaintext">
                                        @if($field['type'] === 'table' || $field['type'] === 'dynamic_table')
                                            @if(!empty($approvalRequest->form_data[$field['name']]))
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            @foreach($field['columns'] as $index => $column)
                                                                <th>
                                                                    @if(!empty($column['label']))
                                                                        {{ $column['label'] }}
                                                                    @else
                                                                        Cột {{ $index + 1 }}
                                                                    @endif
                                                                </th>
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($approvalRequest->form_data[$field['name']] as $row)
                                                            <tr>
                                                                @foreach($field['columns'] as $column)
                                                                    <td>{{ $row[$column['name']] ?? '' }}</td>
                                                                @endforeach
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @else
                                                <em>Không có dữ liệu</em>
                                            @endif
                                        @else
                                            {{ $approvalRequest->getFormFieldValue($field['name'], $field) }}
                                        @endif
                                    </div>
                                </div>
                                    @endif
                                @endforeach
                            @else
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i> Không tìm thấy cấu hình form
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mb-5 mt-4">
                        <div class="col-12">
                            <div class="btn-group" role="group">
                                <!-- Nút hủy yêu cầu - Chỉ người gửi mới thấy -->
                                @if($approvalRequest->created_by_id === auth()->id() && $approvalRequest->approval_status === 'pending')
                                    <button type="button" class="btn btn-warning" onclick="cancelRequest({{ $approvalRequest->id }})">
                                        <i class="bi bi-x-circle"></i> Hủy yêu cầu
                                    </button>
                                @endif
                                
                                
                                <!-- Nút preview và in -->
                                <a href="{{ route('approval.preview', $approvalRequest->id) }}" target="_blank" class="btn btn-info">
                                    <i class="bi bi-eye"></i> Xem trước
                                </a>
                                <a href="{{ route('approval.print', $approvalRequest->id) }}" target="_blank" class="btn btn-success">
                                    <i class="bi bi-printer"></i> In
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Approval Actions - Hiển thị cho người có quyền phê duyệt -->
                    @if($approvalRequest->approval_status === 'pending' && 
                        $approvalRequest->created_by_id !== auth()->id() &&
                        $approvalRequest->canBeApprovedBy(auth()->user()))
                        <div class="card mt-5">
                            <div class="card-header">
                                <h4>Phê duyệt đề xuất</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <form method="POST" action="{{ route('approval.approve', $approvalRequest->id) }}">
                                            @csrf
                                            <div class="form-group">
                                                <label>Ghi chú phê duyệt:</label>
                                                <textarea name="note" class="form-control" placeholder="Ghi chú khi phê duyệt (tùy chọn)"></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-success">
                                                <i class="bi bi-check-circle"></i> Phê duyệt
                                            </button>
                                        </form>
                                    </div>
                                    <div class="col-md-6">
                                        <form method="POST" action="{{ route('approval.reject', $approvalRequest->id) }}">
                                            @csrf
                                            <div class="form-group">
                                                <label>Lý do từ chối: <span class="text-danger">*</span></label>
                                                <textarea name="note" class="form-control" placeholder="Lý do từ chối đề xuất" required></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="bi bi-x-circle"></i> Từ chối
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endcan

                    <!-- Forward Section -->
                    {{-- Chỉ manager/director mới được chuyển tiếp --}}
                    @if(in_array(auth()->user()->role, ['manager', 'director']) && $approvalRequest->approval_status === 'pending')
                        <div class="card mt-4">
                            <div class="card-header">
                                <h4>Chuyển tiếp đề xuất</h4>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('approval.forward', $approvalRequest->id) }}" id="forwardForm">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Chuyển đến (có thể chọn nhiều người):</label>
                                                <div class="form-check-group" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                                    @foreach($availableUsers as $user)
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="forwarded_to_ids[]" value="{{ $user->id }}" id="user_{{ $user->id }}">
                                                            <label class="form-check-label" for="user_{{ $user->id }}">
                                                                {{ $user->name }} 
                                                                @if($user->role === 'manager')
                                                                    (Quản lý)
                                                                @elseif($user->role === 'director')
                                                                    (Giám đốc)
                                                                @else
                                                                    ({{ ucfirst($user->role) }})
                                                                @endif
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <small class="form-text text-muted">Chọn một hoặc nhiều người để chuyển tiếp</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Ghi chú:</label>
                                                <textarea name="message" class="form-control" placeholder="Lý do chuyển tiếp..."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-warning" id="forwardBtn" disabled>
                                        <i class="bi bi-arrow-right"></i> Chuyển tiếp
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endcan

                    <!-- Forwarded Users List -->
                    @if($approvalRequest->forwardedRequests->count() > 0)
                        <div class="card mt-4">
                            <div class="card-header">
                                <h4>Danh sách người được chuyển tiếp</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Người chuyển tiếp</th>
                                                <th>Người nhận</th>
                                                <th>Thời gian</th>
                                                <th>Ghi chú</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($approvalRequest->forwardedRequests as $forward)
                                                <tr>
                                                    <td>{{ $forward->forwardedBy->name }}</td>
                                                    <td>{{ $forward->forwardedTo->name }}</td>
                                                    <td>{{ $forward->forwarded_at->format('d/m/Y H:i') }}</td>
                                                    <td>{{ $forward->message ?? 'Không có ghi chú' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Approval History -->
                    @if($approvalRequest->approvalActions->count() > 0)
                        <div class="card mt-4">
                            <div class="card-header">
                                <h4>Lịch sử phê duyệt</h4>
                            </div>
                            <div class="card-body">
                                @foreach($approvalRequest->approvalActions as $action)
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-{{ $action->getActionClass() }}"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">
                                                {{ $action->user->name }} - {{ $action->getActionText() }}
                                            </h6>
                                            <p class="timeline-text">{{ $action->action_at->format('d/m/Y H:i') }}</p>
                                            @if($action->note)
                                                <p class="timeline-text">{{ $action->note }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Comments Section -->
                    @if($approvalRequest->discussion_status === 'open')
                        <div class="card mt-4">
                            <div class="card-header">
                                <h4>Thảo luận</h4>
                            </div>
                            <div class="card-body">
                                <!-- Add Comment Form -->
                                <form method="POST" action="{{ route('approval.comment', $approvalRequest->id) }}">
                                    @csrf
                                    <div class="form-group">
                                        <textarea name="comment" class="form-control" placeholder="Thêm bình luận..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Gửi bình luận</button>
                                </form>

                                <!-- Comments List -->
                                <div class="mt-4">
                                    @foreach($approvalRequest->comments as $comment)
                                        <div class="comment-item border-bottom pb-3 mb-3">
                                            <div class="comment-header">
                                                <strong>{{ $comment->user->name }}</strong>
                                                <span class="text-muted small">{{ $comment->created_at->format('d/m/Y H:i') }}</span>
                                            </div>
                                            <div class="comment-body mt-2">{{ $comment->comment }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Thảo luận đã bị đóng bởi người tạo
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enable/disable forward button based on checkbox selection
    const checkboxes = document.querySelectorAll('input[name="forwarded_to_ids[]"]');
    const forwardBtn = document.getElementById('forwardBtn');
    
    function updateForwardButton() {
        const checkedBoxes = document.querySelectorAll('input[name="forwarded_to_ids[]"]:checked');
        forwardBtn.disabled = checkedBoxes.length === 0;
    }
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateForwardButton);
    });
    
    // Initial check
    updateForwardButton();
});
</script>
@endpush

@push('scripts')
<script>
function cancelRequest(requestId) {
    if (confirm('Bạn có chắc chắn muốn hủy yêu cầu này?')) {
        // Tạo form để gửi request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("approval.cancel", ":id") }}'.replace(':id', requestId);
        
        // Thêm CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Thêm method DELETE
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
