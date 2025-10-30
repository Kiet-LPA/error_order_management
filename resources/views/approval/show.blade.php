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
                                
                                <!-- Nút xóa - Cho người có quyền -->
                                @if($approvalRequest->canBeDeletedBy(auth()->user()))
                                    <button type="button" class="btn btn-danger" onclick="deleteRequest({{ $approvalRequest->id }})">
                                        <i class="bi bi-trash"></i> Xóa đề xuất
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
                                <form method="POST" action="{{ route('approval.comment', $approvalRequest->id) }}" enctype="multipart/form-data" id="commentForm">
                                    @csrf
                                    <div class="form-group">
                                        <textarea name="comment" class="form-control" placeholder="Thêm bình luận..." required></textarea>
                                    </div>
                                    
                                    <!-- File upload section -->
                                    <div class="file-upload-section mb-3">
                                        <label for="fileInput" class="upload-area" id="uploadArea">
                                            <div class="upload-content">
                                                <i class="bi bi-cloud-upload fs-1 text-muted mb-2"></i>
                                                <p class="mb-1">Kéo thả file vào đây hoặc <span class="text-primary">chọn file</span></p>
                                                <small class="text-muted">Hỗ trợ: Ảnh, Video, PDF, Word, Excel, PowerPoint (Tối đa 5MB mỗi file)</small>
                                            </div>
                                            <input type="file" name="attachments[]" id="fileInput" multiple accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar" style="display: none;">
                                        </label>
                                        
                                    <!-- File preview -->
                                    <div id="filePreview" class="mt-2" style="display: none;">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-file-earmark me-1"></i>
                                            <span class="small fw-medium">File đã chọn:</span>
                                        </div>
                                        <div id="fileList" class="d-flex flex-wrap gap-2"></div>
                                    </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">Tối đa 1000 ký tự | File tối đa 5MB mỗi file</small>
                                        <div>
                                            <button type="submit" class="btn btn-primary">Gửi bình luận</button>
                                        </div>
                                    </div>
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
                                            
                                            @if($comment->attachments->count() > 0)
                                                <div class="comment-attachments mt-2">
                                                    <div class="attachment-section">
                                                        <h6 class="attachment-title mb-2">
                                                            <i class="bi bi-paperclip me-1"></i> File đính kèm
                                                        </h6>
                                                        <div class="attachment-list">
                                                            @foreach($comment->attachments as $attachment)
                                                                <div class="attachment-item" data-attachment-id="{{ $attachment->id }}">
                                                                    @if($attachment->isImage())
                                                                        <div class="attachment-thumbnail" onclick="openImageModal('{{ route('approval.attachment.view', $attachment) }}', '{{ $attachment->original_name }}')" title="Click để xem ảnh">
                                                                            <img src="{{ route('approval.attachment.view', $attachment) }}" alt="{{ $attachment->original_name }}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                                            <div class="fallback-icon d-none">
                                                                                <i class="bi bi-image"></i>
                                                                            </div>
                                                                            @if(auth()->user()->isAdmin() || auth()->user()->isDirector() || $comment->user_id === auth()->id())
                                                                                <button type="button" class="attachment-delete-btn" onclick="event.stopPropagation(); deleteApprovalAttachment({{ $attachment->id }}, '{{ $attachment->original_name }}')" title="Xóa file">
                                                                                    <i class="bi bi-x"></i>
                                                                                </button>
                                                                            @endif
                                                                        </div>
                                                                    @elseif($attachment->isVideo())
                                                                        <div class="attachment-thumbnail video-thumbnail-container" 
                                                                            style="width: 60px; height: 60px; border-radius: 4px; cursor: pointer; position: relative; overflow: hidden; transition: all 0.3s ease;"
                                                                            data-video-url="{{ route('approval.attachment.view', $attachment) }}"
                                                                            data-video-name="{{ $attachment->original_name }}"
                                                                            data-video-id="{{ $attachment->id }}"
                                                                            title="Click để xem video"
                                                                            onclick="openVideoModal(this)">
                                                                            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center;">
                                                                                <i class="bi bi-play-circle text-white" style="font-size: 1.5rem;"></i>
                                                                            </div>
                                                                            <div style="position: absolute; bottom: 2px; right: 2px; background: rgba(0,0,0,0.7); color: white; font-size: 8px; padding: 1px 3px; border-radius: 2px; pointer-events: none;">
                                                                                VIDEO
                                                                            </div>
                                                                            @if(auth()->user()->isAdmin() || auth()->user()->isDirector() || $comment->user_id === auth()->id())
                                                                                <button type="button" class="attachment-delete-btn" onclick="event.stopPropagation(); deleteApprovalAttachment({{ $attachment->id }}, '{{ $attachment->original_name }}')" title="Xóa file">
                                                                                    <i class="bi bi-x"></i>
                                                                                </button>
                                                                            @endif
                                                                        </div>
                                                                    @else
                                                                        <a href="{{ route('approval.attachment.download', $attachment) }}" class="attachment-thumbnail" style="text-decoration: none; color: inherit;" title="Click để tải về">
                                                                            <i class="bi {{ $attachment->getIconClass() }}"></i>
                                                                            @if(auth()->user()->isAdmin() || auth()->user()->isDirector() || $comment->user_id === auth()->id())
                                                                                <button type="button" class="attachment-delete-btn" onclick="event.stopPropagation(); event.preventDefault(); deleteApprovalAttachment({{ $attachment->id }}, '{{ $attachment->original_name }}')" title="Xóa file">
                                                                                    <i class="bi bi-x"></i>
                                                                                </button>
                                                                            @endif
                                                                        </a>
                                                                    @endif
                                                                    <div class="attachment-details">
                                                                        @if($attachment->isImage())
                                                                            <a href="{{ route('approval.attachment.view', $attachment) }}" target="_blank" class="file-name" title="{{ $attachment->original_name }}">
                                                                                {{ $attachment->original_name }}
                                                                            </a>
                                                                        @else
                                                                            <span class="file-name" title="{{ $attachment->original_name }}">
                                                                                {{ $attachment->original_name }}
                                                                            </span>
                                                                        @endif
                                                                        <div class="file-size">{{ $attachment->getFormattedSize() }}</div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
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
<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Xem ảnh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="" class="img-fluid" style="max-height: 70vh; border-radius: 8px;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <a id="downloadLink" href="" download="" class="btn btn-primary">
                    <i class="bi bi-download"></i> Tải về
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Video Modal -->
<div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="videoModalLabel">Xem video</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <video id="modalVideo" controls class="img-fluid" style="max-height: 70vh; border-radius: 8px;">
                    Trình duyệt của bạn không hỗ trợ video.
                </video>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <a id="videoDownloadLink" href="" download="" class="btn btn-primary">
                    <i class="bi bi-download"></i> Tải về
                </a>
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
        if (forwardBtn) {
            forwardBtn.disabled = checkedBoxes.length === 0;
        }
    }
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateForwardButton);
    });
    
    // Initial check
    updateForwardButton();
    
    // File upload functionality
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('fileInput');
    const filePreview = document.getElementById('filePreview');
    const fileList = document.getElementById('fileList');
    const selectedFiles = [];
    
    // Check if upload elements exist
    if (!uploadArea || !fileInput || !filePreview || !fileList) {
        console.error('Upload elements not found in DOM', {
            uploadArea: !!uploadArea,
            fileInput: !!fileInput,
            filePreview: !!filePreview,
            fileList: !!fileList
        });
        return;
    }
    
    console.log('Upload elements found, initializing file upload functionality...');
    
    // Form submission handler
    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        commentForm.addEventListener('submit', (e) => {
            e.preventDefault(); // Ngăn chặn submit mặc định
            console.log('Form submitting...');
            
            const formData = new FormData(commentForm);
            
            // Append selected files to FormData
            selectedFiles.forEach((file, index) => {
                formData.append(`attachments[${index}]`, file);
            });
            
            console.log('Form data after appending files:');
            for (let [key, value] of formData.entries()) {
                console.log(key, value);
            }
            
            // Submit the form manually
            fetch(commentForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (response.ok) {
                    console.log('Form submission successful');
                    window.location.reload(); // Reload trang để hiển thị comment mới
                } else {
                    throw new Error('Network response was not ok');
                }
            })
            .catch(error => {
                console.error('Form submission error:', error);
                alert('Có lỗi xảy ra khi gửi bình luận.');
            });
        });
    }
    
    
    // Click to select files (handled by label for attribute, no need for explicit click listener)
    // uploadArea.addEventListener('click', (e) => {
    //     console.log('Upload area clicked');
    //     e.preventDefault();
    //     e.stopPropagation(); // Ngăn event bubbling
    //     console.log('Triggering file input click...');
    //     fileInput.click();
    // });
    
    // File input change
    fileInput.addEventListener('change', (e) => {
        console.log('File input changed', e.target.files);
        console.log('Event target:', e.target);
        console.log('Files length:', e.target.files.length);
        
        if (e.target.files.length > 0) {
            console.log('Processing files...');
            handleFiles(e);
        } else {
            console.log('No files selected');
        }
    });
    
    // Drag and drop functionality
    uploadArea.addEventListener('dragover', (e) => {
        console.log('Drag over');
        e.preventDefault();
        e.stopPropagation();
        uploadArea.classList.add('drag-over');
    });
    
    uploadArea.addEventListener('dragleave', (e) => {
        console.log('Drag leave');
        e.stopPropagation();
        uploadArea.classList.remove('drag-over');
    });
    
    uploadArea.addEventListener('drop', (e) => {
        console.log('Files dropped', e.dataTransfer.files);
        e.preventDefault();
        e.stopPropagation();
        uploadArea.classList.remove('drag-over');
        const files = e.dataTransfer.files;
        handleFiles({ target: { files } });
    });
    
    function handleFiles(event) {
        console.log('handleFiles called', event);
        const newFiles = Array.from(event.target.files);
        console.log('New files to process:', newFiles);
        console.log('Current selectedFiles before adding:', selectedFiles);
        
        const maxSize = 5 * 1024 * 1024; // 5MB
        const allowedTypes = [
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
            'video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/flv', 'video/webm',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'application/zip',
            'application/x-rar-compressed'
        ];
        
        // Validate and add new files to selectedFiles, filtering out duplicates
        newFiles.forEach(file => {
            console.log('Processing file:', file.name, 'Type:', file.type, 'Size:', file.size);
            
            // Check if file already exists
            if (selectedFiles.some(existingFile => existingFile.name === file.name && existingFile.size === file.size)) {
                console.log('File already exists, skipping:', file.name);
                return;
            }
            
            // Validate file size
            if (file.size > maxSize) {
                alert(`File "${file.name}" vượt quá 5MB. Vui lòng chọn file nhỏ hơn.`);
                return;
            }
            
            // Validate file type
            if (!allowedTypes.includes(file.type)) {
                alert(`File "${file.name}" không được hỗ trợ. Vui lòng chọn file khác.`);
                return;
            }
            
            // Add file to selectedFiles
            selectedFiles.push(file);
            console.log('Added file to selectedFiles:', file.name);
        });
        
        console.log('selectedFiles after processing:', selectedFiles);
        console.log('Total selected files:', selectedFiles.length);
        updateFilePreview();
    }
    
    function updateFilePreview() {
        console.log('updateFilePreview called, selectedFiles:', selectedFiles);
        console.log('filePreview element:', filePreview);
        console.log('fileList element:', fileList);
        
        if (selectedFiles.length === 0) {
            if (filePreview) {
                filePreview.style.display = 'none';
            }
            return;
        }
        
        if (filePreview && fileList) {
            filePreview.style.display = 'block';
            fileList.innerHTML = '';
            
            selectedFiles.forEach((file, index) => {
                console.log('Creating preview for file:', file.name);
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item d-flex align-items-center bg-light p-2 rounded border';
                fileItem.style.cssText = 'margin-bottom: 5px; min-width: 200px;';
                
                // Determine file type icon
                let iconClass = 'bi-file-earmark';
                if (file.type.startsWith('image/')) {
                    iconClass = 'bi-image text-primary';
                } else if (file.type.startsWith('video/')) {
                    iconClass = 'bi-play-circle text-danger';
                } else if (file.type === 'application/pdf') {
                    iconClass = 'bi-file-pdf text-danger';
                } else if (file.type.includes('word')) {
                    iconClass = 'bi-file-word text-primary';
                } else if (file.type.includes('excel') || file.type.includes('spreadsheet')) {
                    iconClass = 'bi-file-excel text-success';
                } else if (file.type.includes('powerpoint') || file.type.includes('presentation')) {
                    iconClass = 'bi-file-ppt text-warning';
                } else if (file.type.includes('zip') || file.type.includes('rar')) {
                    iconClass = 'bi-file-zip text-secondary';
                }
                
                fileItem.innerHTML = `
                    <i class="bi ${iconClass} me-2"></i>
                    <div class="flex-grow-1">
                        <div class="fw-medium" style="font-size: 0.85rem;">${file.name}</div>
                        <small class="text-muted">${formatFileSize(file.size)}</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile(${index})" title="Xóa file">
                        <i class="bi bi-x"></i>
                    </button>
                `;
                fileList.appendChild(fileItem);
            });
        } else {
            console.error('filePreview or fileList element not found!');
        }
    }
    
    function getFileIcon(type) {
        if (type.startsWith('image/')) {
            return '<i class="bi bi-image text-primary"></i>';
        } else if (type.startsWith('video/')) {
            return '<i class="bi bi-play-circle text-danger"></i>';
        } else if (type === 'application/pdf') {
            return '<i class="bi bi-file-pdf text-danger"></i>';
        } else if (type.includes('word')) {
            return '<i class="bi bi-file-word text-primary"></i>';
        } else if (type.includes('excel') || type.includes('sheet')) {
            return '<i class="bi bi-file-excel text-success"></i>';
        } else if (type.includes('powerpoint') || type.includes('presentation')) {
            return '<i class="bi bi-file-ppt text-warning"></i>';
        } else if (type.includes('zip') || type.includes('rar')) {
            return '<i class="bi bi-file-zip text-secondary"></i>';
        } else {
            return '<i class="bi bi-file-earmark text-muted"></i>';
        }
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }
    
    window.removeFile = function(index) {
        selectedFiles.splice(index, 1);
        updateFilePreview();
    };
    
    // Image modal functionality (from task comment)
    function openImageModal(imageUrl, fileName) {
        const modalImage = document.getElementById('modalImage');
        const downloadLink = document.getElementById('downloadLink');
        
        // Reset image để tránh hiển thị ảnh cũ
        modalImage.src = '';
        
        // Set image source
        modalImage.src = imageUrl;
        modalImage.alt = fileName;
        
        // Set download link
        downloadLink.href = imageUrl;
        downloadLink.download = fileName;
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('imageModal'));
        modal.show();
    }
    
    // Video modal functionality
    function openVideoModal(element) {
        const videoUrl = element.getAttribute('data-video-url');
        const videoName = element.getAttribute('data-video-name');
        
        const modalVideo = document.getElementById('modalVideo');
        const downloadLink = document.getElementById('videoDownloadLink');
        
        // Reset video
        modalVideo.src = '';
        
        // Set video source
        modalVideo.src = videoUrl;
        modalVideo.setAttribute('data-video-name', videoName);
        
        // Set download link
        downloadLink.href = videoUrl;
        downloadLink.download = videoName;
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('videoModal'));
        modal.show();
    }
    
    // Delete approval attachment function
    function deleteApprovalAttachment(attachmentId, fileName) {
        if (confirm(`Bạn có chắc muốn xóa file "${fileName}"?`)) {
            fetch(`/approval/attachment/${attachmentId}/delete`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => {
                if (response.ok) {
                    // Remove attachment from DOM
                    const attachmentItem = document.querySelector(`[data-attachment-id="${attachmentId}"]`);
                    if (attachmentItem) {
                        attachmentItem.remove();
                    }
                    // Show success message
                    showNotification('Đã xóa file thành công', 'success');
                } else {
                    showNotification('Có lỗi xảy ra khi xóa file', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Có lỗi xảy ra khi xóa file', 'error');
            });
        }
    }
    
    
    // Make functions global
    window.openImageModal = openImageModal;
    window.openVideoModal = openVideoModal;
    window.deleteApprovalAttachment = deleteApprovalAttachment;
});
</script>

<style>
.upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.upload-area:hover {
    border-color: #007bff;
    background-color: #f8f9fa;
}

.upload-area.drag-over {
    border-color: #007bff;
    background-color: #e3f2fd;
}

.upload-content {
    pointer-events: none;
}

.upload-area {
    position: relative;
    z-index: 10;
}

.attachment-item {
    text-align: center;
    max-width: 80px;
    position: relative;
}

.attachment-preview {
    border: 1px solid #dee2e6;
    transition: transform 0.2s ease;
}

.attachment-preview:hover {
    transform: scale(1.05);
}

/* Attachment styles from task comment */
.attachment-section {
    margin-top: 10px;
}

.attachment-title {
    font-size: 0.9rem;
    color: #6c757d;
    font-weight: 500;
}

.attachment-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.attachment-item {
    position: relative;
    display: inline-block;
}

.attachment-thumbnail {
    width: 70px;
    height: 70px;
    border-radius: 6px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    flex-shrink: 0;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.attachment-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.attachment-thumbnail i {
    font-size: 1.5rem;
    color: #6c757d;
}

.fallback-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    background: #f8f9fa;
    color: #6c757d;
}

.attachment-details {
    margin-top: 5px;
    text-align: center;
}

.file-name {
    display: block;
    font-size: 0.75rem;
    color: #495057;
    text-decoration: none;
    max-width: 70px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.file-name:hover {
    color: #007bff;
    text-decoration: underline;
}

.file-size {
    font-size: 0.7rem;
    color: #6c757d;
    margin-top: 2px;
}

.attachment-delete-btn {
    position: absolute;
    top: -5px;
    right: -5px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #dc3545;
    color: white;
    border: none;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    opacity: 0;
    transition: all 0.2s ease;
    z-index: 100;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.attachment-item:hover .attachment-delete-btn {
    opacity: 1;
}

.attachment-item:hover .attachment-thumbnail,
.attachment-item:hover .video-thumbnail-container {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.attachment-thumbnail:hover,
.video-thumbnail-container:hover {
    border-color: #558EC1;
}

.attachment-link {
    text-decoration: none;
    color: inherit;
}

/* File preview styles */
.file-item {
    transition: all 0.2s ease;
}

.file-item:hover {
    background-color: #e9ecef !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.attachment-link:hover {
    text-decoration: none;
    color: inherit;
}

.file-item {
    border: 1px solid #dee2e6;
    border-radius: 6px;
}

.file-item:hover {
    background-color: #f8f9fa !important;
}
</style>
@endpush

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

function deleteRequest(requestId) {
    if (confirm('Bạn có chắc chắn muốn XÓA HOÀN TOÀN đề xuất này?\n\n⚠️ Hành động này không thể hoàn tác!')) {
        // Tạo form để gửi request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("approval.destroy", ":id") }}'.replace(':id', requestId);
        
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
