@extends('layouts.master')
@section('title', $task->title)

@section('content')
<style>
.task-header-gradient {
    background: linear-gradient(90deg, #558EC1 0%, #5DA444 100%);
    color: #fff;
    border-radius: 18px;
    padding: 32px 32px 24px 32px;
    margin-bottom: 24px;
    position: relative;
    box-shadow: 0 4px 24px rgba(85, 142, 193, 0.15);
}
.task-header-gradient h2 {
    font-size: 2.2rem;
    font-weight: 700;
    margin-bottom: 12px;
}
.badge-priority {
    font-size: 1rem;
    padding: 6px 16px;
    border-radius: 12px;
    margin-right: 8px;
}
.card-custom {
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    margin-bottom: 24px;
}
.file-attachment {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 8px 12px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    font-size: 1rem;
}
.file-attachment i {
    color: #e83e8c;
    margin-right: 8px;
}
.action-btn {
    font-size: 1rem;
    font-weight: 500;
    margin-bottom: 10px;
    border-radius: 8px;
    padding: 10px 0;
}
.action-btn-green { background: #5DA444; color: #fff; }
.action-btn-blue { background: #558EC1; color: #fff; }
.action-btn-yellow { background: #facc15; color: #333; }
.action-btn-outline { border: 1px solid #558EC1; color: #558EC1; background: #fff; }
.action-btn-success { background: #5DA444; color: #fff; }
.action-btn-red { background: #dc2626; color: #fff; }
.action-btn:hover { opacity: 0.9; }
.comment-section {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    padding: 24px;
}
.comment-item {
    border-left: 4px solid #558EC1;
    margin-bottom: 18px;
    padding-left: 12px;
}
.comment-item strong { color: #5DA444; }

/* Tooltip styling
.creator-tooltip:hover {
    cursor: help !important;
    transition: opacity 0.2s ease !important;
} */

/* Modal styling */
.modal-header {
    background: linear-gradient(90deg, #558EC1 0%, #5DA444 100%);
    color: #fff;
    border-bottom: none;
}
.modal-header .btn-close {
    filter: invert(1);
}
.modal-title {
    color: #fff;
}

/* Form controls */
.form-control:focus {
    border-color: #558EC1;
    box-shadow: 0 0 0 0.2rem rgba(85, 142, 193, 0.25);
}
.form-label {
    color: #374151;
    font-weight: 500;
}

/* Card styling */
.card-custom {
    border: 1px solid rgba(85, 142, 193, 0.1);
}
.card-custom:hover {
    box-shadow: 0 4px 20px rgba(85, 142, 193, 0.1);
}

/* File attachment styling */
.file-attachment {
    border: 1px solid rgba(85, 142, 193, 0.2);
}
.file-attachment:hover {
    background: rgba(85, 142, 193, 0.05);
}

/* Badge styling */
.badge-priority {
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Button hover effects */
.action-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.2s ease;
}

/* Modal animation */
.modal.fade .modal-dialog {
    transition: transform 0.3s ease-out;
}
.modal.show .modal-dialog {
    transform: none;
}

/* Alert styling */
.alert {
    border-radius: 8px;
    border-left: 4px solid;
    padding: 12px 16px;
}
.alert-danger {
    background: #fef2f2 !important;
    border-color: #558EC1 !important;
    color: #1e40af !important;
}
.alert-success {
    background: #f0fdf4 !important;
    border-color: #5DA444 !important;
    color: #166534 !important;
}
.alert-info {
    background: #dbeafe !important;
    border-color: #5DA444 !important;
    color: #166534 !important;
}
</style>

<div class="task-header-gradient d-flex flex-column flex-md-row align-items-md-center justify-content-between">
    <div>
        <h2 class="mb-2">{{ $task->title }}</h2>
        <span class="badge badge-priority me-2" style="
            @if($task->status == 'in_progress') background:#3b82f6; color:#fff;
            @elseif($task->status == 'completed') background:#f59e0b; color:#fff;
            @elseif($task->status == 'rejected') background:#ef4444; color:#fff;
            @elseif($task->status == 'overdue') background:#dc2626; color:#fff;
            @elseif($task->status == 'finished') background:#059669; color:#fff;
            @elseif($task->status == 'pending_approval') background:#8b5cf6; color:#fff;
            @else background:#6b7280; color:#fff; @endif">
            @if($task->status == 'in_progress')
                Đang làm
            @elseif($task->status == 'completed')
                Chờ duyệt
            @elseif($task->status == 'rejected')
                Từ chối
            @elseif($task->status == 'overdue')
                Trễ hạn
            @elseif($task->status == 'finished')
                Hoàn thành
            @elseif($task->status == 'pending_approval')
                Chờ phê duyệt
            @else
                {{ strtoupper($task->status) }}
            @endif
        </span>
        <span class="badge badge-priority bg-warning text-dark" style="background:#5DA444; color:#fff;">Độ ưu tiên: 
            @if($task->priority == 'high')
                Cao
            @elseif($task->priority == 'medium')
                Trung bình
            @elseif($task->priority == 'low')
                Thấp
            @else
                Không rõ
            @endif
        </span>
    </div>
    <a href="{{ route('dashboard') }}" class="btn btn-light" style="position:absolute;top:24px;right:32px; background:#558EC1; color:#fff; border-color:#558EC1;">&larr; Quay lại</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card card-custom p-4 mb-4">
            <h5 class="mb-3"><i class="bi bi-info-circle me-2"></i>Thông tin chung</h5>
            <div class="row mb-2">
                {{-- Cột bên trái --}}
                <div class="col-md-6 mb-2">
                    <i class="bi bi-person-badge me-1"></i> <strong>Người giao:</strong>
                    <span class="badge bg-success creator-tooltip" 
                          data-bs-toggle="tooltip" 
                          data-bs-placement="top" 
                          title="Người tạo: {{ $task->creator->name }}">
                        {{ $task->creator->name }}
                    </span>
                </div>
                <div class="col-md-6 mb-2"><i class="bi bi-calendar-date me-1"></i> <strong>Ngày giao:</strong> {{ $task->created_at->format('d/m/Y') }}</div>
                
                <div class="col-md-6 mb-2">
                    <i class="bi bi-person me-1"></i> <strong>Người nhận:</strong>
                    @if($task->assignees->count() > 0)
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            @foreach($task->assignees as $assignee)
                                @php
                                    $isFromOtherDept = auth()->user()->isManager() && 
                                                      !auth()->user()->departments->contains('id', $assignee->department_id);
                                @endphp
                                <span class="badge {{ $isFromOtherDept ? 'bg-secondary' : 'bg-primary' }}" 
                                      title="{{ $isFromOtherDept ? 'Phòng ban khác' : 'Cùng phòng ban' }}">
                                    {{ $assignee->name }}
                                    @if($isFromOtherDept)
                                        <i class="bi bi-lock-fill ms-1"></i>
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    @elseif($task->assignee)
                        @php
                            $isFromOtherDept = auth()->user()->isManager() && 
                                              !auth()->user()->departments->contains('id', $task->assignee->department_id);
                        @endphp
                        <span class="badge {{ $isFromOtherDept ? 'bg-secondary' : 'bg-primary' }}"
                              title="{{ $isFromOtherDept ? 'Phòng ban khác' : 'Cùng phòng ban' }}">
                            {{ $task->assignee->name }}
                            @if($isFromOtherDept)
                                <i class="bi bi-lock-fill ms-1"></i>
                            @endif
                        </span>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </div>
                <div class="col-md-6 mb-2"><i class="bi bi-calendar2-week me-1"></i> <strong>Hạn cuối:</strong> {{ $task->deadline? $task->deadline->format('d/m/Y'):'—' }}</div>
                
                @if($task->forwarded_to)
                <div class="col-12 mb-2">
                    <div class="alert alert-info">
                        <i class="bi bi-arrow-right-circle me-2"></i>
                        <strong>Task đã được forward:</strong> 
                        Từ {{ $task->forwardedBy->name ?? 'Người dùng đã xóa' }} 
                        đến {{ $task->forwardedTo->name ?? 'Người dùng đã xóa' }}
                        @if($task->forward_reason)
                            <br><small class="text-muted">Lý do: {{ $task->forward_reason }}</small>
                        @endif
                        @if($task->forwarded_at)
                            <br><small class="text-muted">Thời gian: {{ $task->forwarded_at->format('d/m/Y H:i') }}</small>
                        @endif
                    </div>
                </div>
                @endif
                
                @php
                    $forwardActivities = $task->activities->where('action', 'forwarded');
                @endphp
                
                @if($forwardActivities->count() > 0)
                <div class="col-12 mb-2">
                    <div class="alert alert-warning">
                        <i class="bi bi-clock-history me-2"></i>
                        <strong>Lịch sử forward:</strong>
                        <div class="mt-2">
                            @foreach($forwardActivities->sortByDesc('created_at') as $activity)
                                <div class="border-start border-3 border-warning ps-3 mb-2">
                                    <small class="text-muted">{{ $activity->created_at->format('d/m/Y H:i') }}</small>
                                    <div class="fw-bold">{{ $activity->description }}</div>
                                    @if(isset($activity->metadata['forward_reason']))
                                        <small class="text-muted">Lý do: {{ $activity->metadata['forward_reason'] }}</small>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
                
                <div class="col-md-6 mb-2"><i class="bi bi-building me-1"></i> <strong>Phòng ban:</strong> 
                    @php
                        $currentDepartments = $task->getCurrentDepartments();
                    @endphp
                    @if($currentDepartments->count() > 0)
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            @foreach($currentDepartments as $department)
                                <span class="badge bg-info">{{ $department->name }}</span>
                            @endforeach
                        </div>
                        @if($task->is_multi_department)
                            <small class="text-muted d-block mt-1">
                                <i class="bi bi-info-circle me-1"></i>Đa phòng ban (tự động phát hiện)
                            </small>
                        @endif
                    @else
                        <span class="text-muted">Chưa phân phòng ban</span>
                    @endif
                </div>
                <div class="col-md-6 mb-2"><i class="bi bi-exclamation-triangle me-1"></i> <strong>Độ ưu tiên:</strong> 
                    @if($task->priority == 'high')
                        <span class="text-danger">Cao</span>
                    @elseif($task->priority == 'medium')
                        <span class="text-warning">Trung bình</span>
                    @elseif($task->priority == 'low')
                        <span class="text-success">Thấp</span>
                    @else
                        <span class="text-muted">Không rõ</span>
                    @endif
                </div>
                
                <div class="col-md-6 mb-2">
                    <i class="bi bi-eye me-1"></i> <strong>Người theo dõi:</strong>
                    @if($task->followers->count() > 0)
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            @foreach($task->followers as $follower)
                                <span class="badge bg-secondary">{{ $follower->name }}</span>
                            @endforeach
                        </div>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </div>
                <div class="col-md-6 mb-2"><i class="bi bi-check2-circle me-1"></i> <strong>Trạng thái:</strong> 
                                @if($task->status == 'in_progress')
                <span class="text-primary">Đang làm</span>
            @elseif($task->status == 'completed')
                <span class="text-warning">Chờ duyệt</span>
            @elseif($task->status == 'rejected')
                <span class="text-danger">Từ chối</span>
            @elseif($task->status == 'overdue')
                <span class="text-danger">Trễ hạn</span>
            @elseif($task->status == 'finished')
                <span class="text-success">Hoàn thành</span>
            @elseif($task->status == 'pending_approval')
                <span class="text-info">Chờ phê duyệt</span>
            @else
                <span class="text-muted">{{ strtoupper($task->status) }}</span>
            @endif
                </div>
                
                {{-- Thông báo task lặp lại --}}
                @if($task->is_recurring)
                <div class="col-12 mb-3">
                    <div class="recurring-notification">
                        <div class="recurring-content">
                            <i class="bi bi-arrow-repeat recurring-icon"></i>
                            <div class="recurring-text">
                                <strong>Lặp lại:</strong> 
                                <span>Công việc sẽ lặp lại mỗi {{ $task->recurring_days }} ngày từ ngày {{ $task->recurring_start_date ? $task->recurring_start_date->format('d/m/Y') : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                {{-- Hiển thị lý do từ chối nếu có --}}
                @if($task->status == 'rejected' && $task->rejection_reason)
                    <div class="col-12 mb-2">
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Lý do từ chối:</strong> {{ $task->rejection_reason }}
                        </div>
                    </div>
                @endif
                
                {{-- Hiển thị ghi chú kết thúc nếu có --}}
                @if($task->status == 'finished' && $task->finish_note)
                    <div class="col-12 mb-2">
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>Ghi chú kết thúc:</strong> {{ $task->finish_note }}
                        </div>
                    </div>
                @endif
                
                {{-- Cảnh báo task trễ hạn --}}
                @if($task->status == 'overdue')
                    <div class="col-12 mb-2">
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Cảnh báo:</strong> Task này đã quá hạn deadline. 
                            @if($task->deadline && $task->deadline->isPast())
                                Để chuyển về trạng thái "Đang làm", vui lòng cập nhật deadline thành ngày trong tương lai.
                            @endif
                        </div>
                    </div>
                @endif
    </div>
</div>

<script>
function editComment(commentId) {
    const contentDiv = document.getElementById(`comment-content-${commentId}`);
    const currentContent = contentDiv.querySelector('p').textContent;
    const commentItem = contentDiv.closest('.comment-item');
    
    // Add editing class to show delete buttons
    commentItem.classList.add('editing');
    
    contentDiv.innerHTML = `
        <textarea class="form-control mb-2" rows="3" id="edit-textarea-${commentId}">${currentContent}</textarea>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-primary" onclick="saveComment(${commentId})">Lưu</button>
            <button class="btn btn-sm btn-secondary" onclick="cancelEdit(${commentId})">Hủy</button>
        </div>
    `;
}

function saveComment(commentId) {
    const textarea = document.getElementById(`edit-textarea-${commentId}`);
    const content = textarea.value;
    
    fetch(`/comments/${commentId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ content: content })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Có lỗi xảy ra khi cập nhật bình luận');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi cập nhật bình luận');
    });
}

function cancelEdit(commentId) {
    location.reload();
}

function deleteComment(commentId) {
    if (confirm('Bạn có chắc muốn xóa bình luận này?')) {
        fetch(`/comments/${commentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Có lỗi xảy ra khi xóa bình luận');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi xóa bình luận');
        });
    }
}

function removeFile(fileIndex, fileName) {
    if (confirm(`Bạn có chắc muốn xóa file "${fileName}"?`)) {
        fetch(`{{ route('tasks.removeFile', $task) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                file_index: fileIndex
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Có lỗi xảy ra khi xóa file: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi xóa file');
        });
    }
}

function deleteAttachment(attachmentId, fileName) {
    if (confirm(`Bạn có chắc muốn xóa file "${fileName}"?`)) {
        fetch(`/comment-attachments/${attachmentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the attachment item from DOM
                const attachmentItem = document.querySelector(`[data-attachment-id="${attachmentId}"]`);
                if (attachmentItem) {
                    attachmentItem.remove();
                }
                
                // Check if no attachments left, hide the section
                const attachmentList = attachmentItem?.closest('.attachment-list');
                if (attachmentList && attachmentList.children.length === 0) {
                    attachmentList.closest('.comment-attachments').remove();
                }
            } else {
                alert('Có lỗi xảy ra khi xóa file: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi xóa file');
        });
    }
}
</script>
        <div class="comment-section mb-4">
            <h5 class="mb-3"><i class="bi bi-chat-dots me-2"></i>Thảo luận</h5>
            <form class="mb-4" action="{{ route('comments.store',$task) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <textarea name="content" class="form-control mb-2" rows="3" placeholder="Viết bình luận..." maxlength="1000" id="commentTextarea"></textarea>
                
                <!-- Compact file attachment section -->
                <div class="compact-file-section mb-3">
                  <div class="file-input-container">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="fileUploadBtn">
                      <i class="bi bi-paperclip me-1"></i>Chọn tệp
                    </button>
                    <span id="fileCount" class="file-count">Không có tệp nào được chọn</span>
                  </div>
                  <small class="text-muted d-block mt-1">
                    <i class="bi bi-info-circle me-1"></i>
                    Hỗ trợ: PDF, Word, Excel, PowerPoint, hình ảnh, video, nén. Tối đa 1GB/file.
                  </small>
                  
                  <!-- File preview -->
                  <div id="filePreview" class="mt-2" style="display: none;">
                    <div class="d-flex align-items-center mb-2">
                      <i class="bi bi-file-earmark me-1"></i>
                      <span class="small fw-medium">File đã chọn:</span>
                    </div>
                    <div id="fileList" class="d-flex flex-wrap gap-2"></div>
                  </div>
                </div>
                
                <input type="file" name="attachments[]" id="fileInput" multiple accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar" style="display: none;">
                
                <div class="d-flex justify-content-between align-items-center">
                  <small class="text-muted">Tối đa 1000 ký tự | Ctrl+V để paste ảnh | File tối đa 1GB</small>
                  <button type="submit" class="btn btn-sm" style="background:#558EC1; color:#fff; border-color:#558EC1;">Gửi bình luận</button>
                </div>
            </form>
            @forelse($task->comments()->topLevel()->withReplies()->get() as $comment)
                <div class="comment-item" data-comment-id="{{ $comment->id }}">
                    <div class="d-flex align-items-center mb-2">
                        <div class="d-flex align-items-center">
                            <div class="avatar me-2" style="width: 32px; height: 32px; background: linear-gradient(135deg, #558EC1, #5DA444); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 14px;">
                                {{ substr($comment->user->name, 0, 1) }}
                            </div>
                            <div>
                                <strong class="text-primary d-block">{{ $comment->user->name }}</strong>
                                <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                @if($comment->is_edited)
                                    <small class="text-muted ms-2">(đã chỉnh sửa)</small>
                                @endif
                            </div>
                        </div>
                        <div class="ms-auto">
                            @if($comment->canEdit(auth()->user()))
                                <button class="btn btn-sm btn-outline-primary" onclick="editComment({{ $comment->id }})" title="Chỉnh sửa">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            @endif
                            @if($comment->canDelete(auth()->user()))
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteComment({{ $comment->id }})" title="Xóa">
                                    <i class="bi bi-trash"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="comment-content" id="comment-content-{{ $comment->id }}">
                        <p class="mb-3">{{ $comment->content }}</p>
                        
                        @if($comment->attachments->count() > 0)
                          <div class="comment-attachments">
                            <div class="attachment-section">
                              <h6 class="attachment-title mb-2">
                                <i class="bi bi-paperclip me-1"></i>📎 File đính kèm
                              </h6>
                              <div class="attachment-list">
                                @foreach($comment->attachments as $attachment)
                                  <div class="attachment-item" data-attachment-id="{{ $attachment->id }}">
                                    @if($attachment->isImage())
                                      <div class="attachment-thumbnail" onclick="openImageModal('{{ $attachment->file_url }}', '{{ $attachment->original_name }}')" title="Click để xem ảnh">
                                        <img src="{{ $attachment->file_url }}" alt="{{ $attachment->original_name }}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="fallback-icon d-none">
                                          <i class="bi bi-image"></i>
                                        </div>
                                        <!-- Delete button for edit mode -->
                                        <button type="button" class="attachment-delete-btn" onclick="event.stopPropagation(); deleteAttachment({{ $attachment->id }}, '{{ $attachment->original_name }}')" title="Xóa file">
                                          <i class="bi bi-x"></i>
                                        </button>
                                      </div>
                                    @else
                                      <div class="attachment-thumbnail">
                                        <i class="bi {{ $attachment->getIconClass() }}"></i>
                                        <!-- Delete button for edit mode -->
                                        <button type="button" class="attachment-delete-btn" onclick="event.stopPropagation(); deleteAttachment({{ $attachment->id }}, '{{ $attachment->original_name }}')" title="Xóa file">
                                          <i class="bi bi-x"></i>
                                        </button>
                                      </div>
                                    @endif
                                    <div class="attachment-details">
                                      @if($attachment->isImage())
                                        <a href="{{ $attachment->file_url }}" target="_blank" class="file-name" title="{{ $attachment->original_name }}">
                                          {{ $attachment->original_name }}
                                        </a>
                                      @else
                                        <a href="{{ $attachment->file_url }}" download="{{ $attachment->original_name }}" class="file-name" title="{{ $attachment->original_name }}">
                                          {{ $attachment->original_name }}
                                        </a>
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
                </div>
            @empty
                <div class="text-muted">Chưa có bình luận.</div>
            @endforelse
        </div>
    </div>
    
    <!-- Image Preview Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header" style="background: linear-gradient(135deg, #558EC1 0%, #5DA444 100%); color: white;">
            <h5 class="modal-title" id="imageModalLabel">
              <i class="bi bi-image me-2"></i>Xem hình ảnh
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center p-0">
            <img id="modalImage" src="" alt="" class="img-fluid" style="max-height: 70vh; object-fit: contain;">
          </div>
          <div class="modal-footer">
            <a id="downloadLink" href="" download="" class="btn btn-primary">
              <i class="bi bi-download me-1"></i>Tải xuống
            </a>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-custom p-4 mb-4">
            <h5 class="mb-3"><i class="bi bi-paperclip me-2"></i>File đính kèm</h5>
            
            @php
                $images = collect($task->attachments ?? [])->filter(function($file) {
                    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                });
                $videos = collect($task->attachments ?? [])->filter(function($file) {
                    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    return in_array($extension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm']);
                });
                $documents = collect($task->attachments ?? [])->filter(function($file) {
                    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    return in_array($extension, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']);
                });
                $others = collect($task->attachments ?? [])->filter(function($file) {
                    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    return !in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']);
                });
            @endphp
            
            {{-- Hiển thị hình ảnh theo hàng ngang --}}
            @if($images->count() > 0)
                <div class="mb-4">
                    <h6 class="mb-3"><i class="bi bi-image me-2"></i>Hình ảnh</h6>
                    <div class="row g-3">
                        @foreach($images as $file)
                            @php
                                $fileIndex = array_search($file, $task->attachments);
                            @endphp
                            <div class="col-md-3 col-sm-4 col-6">
                                <div class="position-relative">
                                    <img src="{{ $file['url'] }}" alt="{{ $file['name'] }}" 
                                         class="img-fluid rounded w-100" style="height: 150px; object-fit: cover; cursor: pointer;"
                                         onclick="openImageModal('{{ $file['url'] }}', '{{ $file['name'] }}')">
                                    <div class="position-absolute top-0 end-0 p-1">
                                        <button class="btn btn-sm btn-danger" onclick="removeFile({{ $fileIndex }}, '{{ $file['name'] }}')" title="Xóa file">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                    <div class="mt-1">
                                        <small class="text-muted d-block text-truncate">{{ $file['name'] }}</small>
                                        <small class="text-muted">({{ number_format($file['size'] / 1024, 1) }} KB)</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            {{-- Hiển thị video theo hàng ngang --}}
            @if($videos->count() > 0)
                <div class="mb-4">
                    <h6 class="mb-3"><i class="bi bi-camera-video me-2"></i>Video</h6>
                    <div class="row g-3">
                        @foreach($videos as $file)
                            @php
                                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                                $fileIndex = array_search($file, $task->attachments);
                            @endphp
                            <div class="col-md-4 col-sm-6">
                                <div class="position-relative">
                                    <video controls class="w-100 rounded" style="height: 150px; object-fit: cover;">
                                        <source src="{{ $file['url'] }}" type="video/{{ $extension }}">
                                        Trình duyệt không hỗ trợ video.
                                    </video>
                                    <div class="position-absolute top-0 end-0 p-1">
                                        <button class="btn btn-sm btn-danger" onclick="removeFile({{ $fileIndex }}, '{{ $file['name'] }}')" title="Xóa file">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                    <div class="mt-1">
                                        <small class="text-muted d-block text-truncate">{{ $file['name'] }}</small>
                                        <small class="text-muted">({{ number_format($file['size'] / 1024, 1) }} KB)</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            {{-- Hiển thị documents --}}
            @if($documents->count() > 0)
                <div class="mb-4">
                    <h6 class="mb-3"><i class="bi bi-file-earmark-text me-2"></i>Tài liệu</h6>
                    @foreach($documents as $file)
                        @php
                            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            $fileIndex = array_search($file, $task->attachments);
                        @endphp
                        <div class="file-attachment d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-file-earmark-{{ $extension == 'pdf' ? 'pdf' : 'text' }} me-2"></i>
                                <a href="{{ $file['url'] }}" target="_blank" class="text-decoration-none">{{ $file['name'] }}</a>
                            </div>
                            <div class="d-flex align-items-center">
                                <small class="text-muted me-2">({{ number_format($file['size'] / 1024, 1) }} KB)</small>
                                <button class="btn btn-sm btn-outline-danger" onclick="removeFile({{ $fileIndex }}, '{{ $file['name'] }}')" title="Xóa file">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            
            {{-- Hiển thị file khác --}}
            @if($others->count() > 0)
                <div class="mb-4">
                    <h6 class="mb-3"><i class="bi bi-file-earmark me-2"></i>File khác</h6>
                    @foreach($others as $file)
                        @php
                            $fileIndex = array_search($file, $task->attachments);
                        @endphp
                        <div class="file-attachment d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-file-earmark me-2"></i>
                                <a href="{{ $file['url'] }}" target="_blank" class="text-decoration-none">{{ $file['name'] }}</a>
                            </div>
                            <div class="d-flex align-items-center">
                                <small class="text-muted me-2">({{ number_format($file['size'] / 1024, 1) }} KB)</small>
                                <button class="btn btn-sm btn-outline-danger" onclick="removeFile({{ $fileIndex }}, '{{ $file['name'] }}')" title="Xóa file">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            
            @if(collect($task->attachments ?? [])->count() == 0)
                <div class="text-muted">Chưa có tệp.</div>
            @endif
        </div>
        <div class="card card-custom p-4">
            <h5 class="mb-3"><i class="bi bi-lightning me-2"></i>Hành động</h5>
            
            {{-- Hiển thị nút theo trạng thái và role --}}
            @if($task->status == 'in_progress')
                @if($task->assignee_id == auth()->id())
                    <a href="{{ route('tasks.updateStatus',[$task,'status'=>'completed']) }}" class="btn action-btn action-btn-green w-100 mb-2">✅ Hoàn thành & gửi duyệt</a>
                @endif
                @if(auth()->user()->isAdmin() || auth()->user()->isDirector() || auth()->user()->isManager())
                    <a href="{{ route('tasks.updateStatus',[$task,'status'=>'completed']) }}" class="btn action-btn action-btn-green w-100 mb-2">✅ Chuyển sang chờ duyệt</a>
                @endif
            @endif
            
            @if($task->status == 'completed' || $task->status == 'pending_approval')
                @if(auth()->user()->isAdmin() || auth()->user()->isDirector() || auth()->user()->isManager())
                    <button type="button" class="btn action-btn action-btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#finishModal">🏁 Hoàn thành</button>
                    <button type="button" class="btn action-btn action-btn-red w-100 mb-2" data-bs-toggle="modal" data-bs-target="#rejectModal">❌ Từ chối</button>
                @endif
            @endif
            
            @if($task->status == 'rejected')
                @if($task->assignee_id == auth()->id())
                    <a href="{{ route('tasks.updateStatus',[$task,'status'=>'completed']) }}" class="btn action-btn action-btn-green w-100 mb-2">🔄 Đã làm lại & gửi duyệt</a>
                @endif
            @endif
            
            {{-- Nút Forward Task --}}
            @if(auth()->user()->isAdmin() || auth()->user()->isDirector() || auth()->user()->isManager())
                @php
                    $canForward = false;
                    if (auth()->user()->isAdmin() || auth()->user()->isDirector()) {
                        $canForward = true; // Admin và Director luôn có thể forward
                    } elseif (auth()->user()->isManager()) {
                        $canForward = auth()->user()->canViewTask($task); // Manager chỉ cần có quyền xem task
                    }
                @endphp
                @if($canForward)
                    <a href="{{ route('tasks.forward.form', $task) }}" class="btn action-btn action-btn-warning w-100 mb-2">
                        <i class="bi bi-arrow-right-circle me-2"></i>Forward Task
                    </a>
                @endif
            @endif
            
            @if($task->status == 'overdue')
                @if($task->deadline && $task->deadline->isFuture())
                    {{-- Chỉ hiển thị nút khi deadline đã được cập nhật thành tương lai --}}
                    @if($task->assignee_id == auth()->id())
                        <a href="{{ route('tasks.updateStatus',[$task,'status'=>'in_progress']) }}" class="btn action-btn action-btn-blue w-100 mb-2">🚀 Bắt đầu làm</a>
                    @endif
                    @if(auth()->user()->isAdmin() || auth()->user()->isDirector() || auth()->user()->isManager())
                        <a href="{{ route('tasks.updateStatus',[$task,'status'=>'in_progress']) }}" class="btn action-btn action-btn-blue w-100 mb-2">🔄 Chuyển sang đang làm</a>
                    @endif
                @else
                    {{-- Hiển thị thông báo khi deadline vẫn trong quá khứ --}}
                    <div class="alert alert-warning mb-2">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Không thể chuyển trạng thái:</strong> Deadline vẫn trong quá khứ. Vui lòng cập nhật deadline trước.
                    </div>
                @endif
            @endif
            
            {{-- Chỉ hiển thị cho admin/director/manager --}}
            @if(auth()->user()->isAdmin() || auth()->user()->isDirector() || auth()->user()->isManager())
                <a href="{{ route('tasks.edit', $task) }}" class="btn action-btn action-btn-yellow w-100 mb-2">✏️ Chỉnh sửa</a>
            @endif
            
            <a href="{{ route('tasks.history',$task) }}" class="btn action-btn action-btn-outline w-100">👁 Xem lịch sử</a>
            
            {{-- Nút hoàn tác chỉ hiển thị khi có thể hoàn tác và cho người được giao việc --}}
            @if($task->canUndo() && $task->assignee_id == auth()->id())
                <form action="{{ route('tasks.undo-completion', $task) }}" method="POST" class="mt-2" onsubmit="return confirm('Bạn có chắc chắn muốn hoàn tác công việc này?')">
                    @csrf
                    <button type="submit" class="btn btn-undo w-100">
                        <i class="bi bi-arrow-counterclockwise me-2"></i>Hoàn tác
                    </button>
                </form>
            @endif
        </div>
        
        {{-- Modal kết thúc --}}
        <div class="modal fade" id="finishModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Hoàn thành công việc</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('tasks.updateStatus', $task) }}" method="GET">
                        <input type="hidden" name="status" value="finished">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Ghi chú kết thúc <span class="text-muted">(tùy chọn)</span></label>
                                <textarea name="finish_note" id="finishNoteTextarea" class="form-control" rows="3" placeholder="Nhập ghi chú khi kết thúc công việc..." maxlength="500"></textarea>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <small class="text-muted">Tối đa 500 ký tự</small>
                                    <small class="text-muted" id="finishNoteCounter">0/500</small>
                                </div>
                            </div>
                            <div class="alert" style="background:#dbeafe; border-color:#5DA444; color:#166534;">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Lưu ý:</strong> Công việc sẽ được đánh dấu là hoàn thành và không thể thay đổi trạng thái nữa.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn" style="background:#5DA444; color:#fff; border-color:#5DA444;">Hoàn thành</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        {{-- Modal từ chối --}}
        <div class="modal fade" id="rejectModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Từ chối công việc</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('tasks.updateStatus', $task) }}" method="GET">
                        <input type="hidden" name="status" value="rejected">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
                                <textarea name="rejection_reason" id="rejectReasonTextarea" class="form-control" rows="3" required placeholder="Nhập lý do từ chối..." maxlength="500"></textarea>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <small class="text-muted">Tối đa 500 ký tự</small>
                                    <small class="text-muted" id="rejectReasonCounter">0/500</small>
                                </div>
                            </div>
                            <div class="alert" style="background:#fef3c7; border-color:#558EC1; color:#1e40af;">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Lưu ý:</strong> Công việc sẽ được trả lại cho nhân viên để làm lại.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn" style="background:#dc2626; color:#fff; border-color:#dc2626;">Từ chối</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        

    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Hiển thị thông báo thành công
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="bi bi-check"></i> Copied!';
        button.classList.remove('btn-outline-primary');
        button.classList.add('btn-success');
        
        setTimeout(function() {
            button.innerHTML = originalText;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-primary');
        }, 2000);
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
        alert('Không thể copy mã tracking. Vui lòng copy thủ công.');
    });
}

// Validation for modal textareas
document.addEventListener('DOMContentLoaded', function() {
    // Validation for finish note modal
    const finishNoteTextarea = document.getElementById('finishNoteTextarea');
    const finishNoteCounter = document.getElementById('finishNoteCounter');
    const finishModal = document.getElementById('finishModal');
    
    if (finishNoteTextarea && finishNoteCounter) {
        validateModalTextarea(finishNoteTextarea, finishNoteCounter, 500, 'finishModal');
    }
    
    // Validation for rejection reason modal
    const rejectReasonTextarea = document.getElementById('rejectReasonTextarea');
    const rejectReasonCounter = document.getElementById('rejectReasonCounter');
    const rejectModal = document.getElementById('rejectModal');
    
    if (rejectReasonTextarea && rejectReasonCounter) {
        validateModalTextarea(rejectReasonTextarea, rejectReasonCounter, 500, 'rejectModal');
    }
});

// Function to validate modal textarea
function validateModalTextarea(textarea, counter, maxLength, modalId) {
    textarea.addEventListener('input', function() {
        const text = this.value;
        const words = text.split(/\s+/);
        let hasLongWord = false;
        
        // Check each word
        for (let word of words) {
            if (word.length > 45) {
                hasLongWord = true;
                break;
            }
        }
        
        // Update counter
        counter.textContent = `${text.length}/${maxLength}`;
        
        // Visual feedback for long words
        if (hasLongWord) {
            this.style.borderColor = '#dc3545';
            this.style.backgroundColor = '#fff5f5';
            
            // Disable submit button in modal
            const modal = document.getElementById(modalId);
            const submitBtn = modal.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Từ quá dài (>45 ký tự)';
                submitBtn.classList.add('btn-danger');
                submitBtn.classList.remove('btn-success');
            }
        } else {
            this.style.borderColor = '';
            this.style.backgroundColor = '';
            
            // Enable submit button in modal
            const modal = document.getElementById(modalId);
            const submitBtn = modal.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = false;
                if (modalId === 'finishModal') {
                    submitBtn.innerHTML = 'Hoàn thành';
                    submitBtn.classList.remove('btn-danger');
                    submitBtn.style.background = '#5DA444';
                } else if (modalId === 'rejectModal') {
                    submitBtn.innerHTML = 'Từ chối';
                    submitBtn.classList.remove('btn-danger');
                    submitBtn.style.background = '#dc2626';
                }
            }
        }
    });
    
    // Form validation
    textarea.closest('form').addEventListener('submit', function(e) {
        const text = textarea.value;
        const words = text.split(/\s+/);
        
        for (let word of words) {
            if (word.length > 45) {
                e.preventDefault();
                alert('Không được phép nhập từ dài hơn 45 ký tự!');
                return false;
            }
        }
    });
    
    // Initialize counter
    counter.textContent = `${textarea.value.length}/${maxLength}`;
}
</script>

<style>
/* Button undo styling */
.btn-undo {
    background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 12px 20px;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
}

.btn-undo:hover {
    background: linear-gradient(135deg, #d97706 0%, #ea580c 100%);
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
}

.btn-undo:active {
    transform: translateY(0);
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
}

/* Task recurring notification styling - giống hệt như trong ảnh */
.recurring-notification {
    background: linear-gradient(135deg, #dbeafe 0%, #e0f2fe 100%);
    border: 2px solid #10b981;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.15);
    position: relative;
    overflow: hidden;
}

.recurring-notification::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(16, 185, 129, 0.05) 0%, rgba(14, 165, 233, 0.05) 100%);
    pointer-events: none;
}

.recurring-content {
    display: flex;
    align-items: center;
    position: relative;
    z-index: 1;
}

.recurring-icon {
    font-size: 1.8rem;
    color: #10b981;
    margin-right: 16px;
    animation: pulse 2s infinite;
    filter: drop-shadow(0 2px 4px rgba(16, 185, 129, 0.3));
}

.recurring-text {
    flex: 1;
    color: #1e40af;
    font-size: 0.95rem;
    line-height: 1.5;
}

.recurring-text strong {
    color: #10b981;
    font-weight: 600;
    margin-right: 8px;
}

.recurring-text span {
    color: #374151;
    font-weight: 400;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

/* Hover effect */
.recurring-notification:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(16, 185, 129, 0.25);
    transition: all 0.3s ease;
}

/* Compact file section styling */
.compact-file-section {
  border: 1px solid #dee2e6;
  border-radius: 6px;
  padding: 0.75rem;
  background: #f8f9fa;
}

.file-input-container {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem;
  background: white;
  border: 1px solid #ced4da;
  border-radius: 4px;
}

.file-count {
  color: #6c757d;
  font-size: 0.875rem;
}

.compact-file-section .btn-outline-primary {
  border-color: #558EC1;
  color: #558EC1;
  font-size: 0.875rem;
  padding: 0.375rem 0.75rem;
}

.compact-file-section .btn-outline-primary:hover {
  background-color: #558EC1;
  border-color: #558EC1;
  color: white;
}

/* Compact file preview styling */
.compact-file-preview {
  display: inline-block;
  margin-right: 0.5rem;
  margin-bottom: 0.5rem;
}

.file-preview-card {
  background: white;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  padding: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  max-width: 250px;
  position: relative;
  transition: all 0.3s ease;
}

.file-preview-card:hover {
  border-color: #558EC1;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.file-thumbnail {
  width: 40px;
  height: 40px;
  object-fit: cover;
  border-radius: 4px;
  border: 1px solid #dee2e6;
  flex-shrink: 0;
}

.file-icon {
  width: 40px;
  height: 40px;
  background: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.file-icon i {
  font-size: 1.2rem;
}

.file-details {
  flex: 1;
  min-width: 0;
}

.file-details .file-name {
  font-size: 0.75rem;
  font-weight: 500;
  color: #495057;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 0.25rem;
}

.file-details .file-size {
  font-size: 0.7rem;
  color: #6c757d;
}

.remove-file-btn {
  background: none;
  border: none;
  color: #dc3545;
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 3px;
  font-size: 0.875rem;
  flex-shrink: 0;
}

.remove-file-btn:hover {
  background: #f8d7da;
  color: #dc3545;
}

.file-preview-item .file-info {
  flex: 1;
  min-width: 0;
}

.file-preview-item .file-name {
  font-size: 0.875rem;
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.file-preview-item .file-size {
  font-size: 0.75rem;
  color: #6c757d;
}

.file-preview-item .remove-file {
  color: #dc3545;
  cursor: pointer;
  padding: 0.25rem;
}

.file-preview-item .remove-file:hover {
  background: #f8d7da;
  border-radius: 4px;
}

/* Comment attachments styling */
.attachment-item {
  display: inline-block;
  text-align: center;
  margin-right: 1rem;
  margin-bottom: 1rem;
}

.attachment-link {
  text-decoration: none;
  color: inherit;
}

.attachment-link:hover {
  text-decoration: none;
  color: inherit;
}

.attachment-preview {
  border: 1px solid #dee2e6;
  transition: all 0.3s ease;
}

.attachment-preview:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Enhanced comment styling */
.comment-item {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  transition: all 0.3s ease;
  padding: 1rem;
  margin-bottom: 1rem;
}

.comment-item:hover {
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.attachment-item {
  transition: all 0.3s ease;
  border: 1px solid #e9ecef !important;
}

.attachment-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  border-color: #558EC1 !important;
}

.attachment-preview img {
  transition: all 0.3s ease;
}

.attachment-preview:hover img {
  transform: scale(1.02);
}

.avatar {
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.attachment-info {
  max-width: 80px;
  overflow: hidden;
}

.attachment-info small {
  display: block;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* New attachment list styling */
.attachment-section {
  margin-top: 1rem;
}

.attachment-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #6c757d;
  margin-bottom: 0.75rem;
}

.attachment-list {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 0.75rem;
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.attachment-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem;
  border-radius: 6px;
  transition: all 0.2s ease;
  min-width: 200px;
  max-width: 300px;
  background: white;
  border: 1px solid #e9ecef;
}

.attachment-item:hover {
  background: #e9ecef;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
}

.fallback-icon i {
  font-size: 1.5rem;
  color: #6c757d;
}

.attachment-details {
  flex: 1;
  min-width: 0;
}

.file-name {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: #495057;
  text-decoration: none;
  margin-bottom: 0.25rem;
  line-height: 1.3;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.file-name:hover {
  color: #558EC1;
  text-decoration: underline;
}

.file-size {
  font-size: 0.75rem;
  color: #6c757d;
  line-height: 1.2;
}

/* Assignee badges styling */
.badge.bg-primary {
  background: linear-gradient(135deg, #558EC1 0%, #5DA444 100%) !important;
  border: none;
  font-weight: 500;
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
}

/* Delete button styling */
.attachment-delete-btn {
  position: absolute;
  top: -6px;
  right: -6px;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: #dc3545;
  color: white;
  border: 2px solid white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  cursor: pointer;
  opacity: 0;
  transition: all 0.2s ease;
  z-index: 100;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.attachment-thumbnail {
  position: relative;
}

.attachment-item:hover .attachment-delete-btn {
  opacity: 1;
}

.attachment-delete-btn:hover {
  background: #c82333;
  transform: scale(1.1);
}

/* Show delete button when comment is in edit mode */
.comment-item.editing .attachment-delete-btn {
  opacity: 1;
}
</style>

<script>
// File upload functionality
document.addEventListener('DOMContentLoaded', function() {
    const fileUploadBtn = document.getElementById('fileUploadBtn');
    const fileInput = document.getElementById('fileInput');
    const filePreview = document.getElementById('filePreview');
    const fileList = document.getElementById('fileList');
    const commentTextarea = document.getElementById('commentTextarea');
    const MAX_FILE_SIZE = 1024 * 1024 * 1024; // 1GB
    const MAX_TOTAL_SIZE = 1024 * 1024 * 1024; // 1GB total
    
    let selectedFiles = [];
    let totalSize = 0;
    
    // Click to select files
    fileUploadBtn.addEventListener('click', function() {
        fileInput.click();
    });
    
    // Handle file selection
    fileInput.addEventListener('change', function(e) {
        handleFiles(e.target.files);
    });
    
    // Paste image from clipboard
    commentTextarea.addEventListener('paste', function(e) {
        const items = e.clipboardData.items;
        for (let item of items) {
            if (item.type.indexOf('image') !== -1) {
                const file = item.getAsFile();
                if (file) {
                    handleFiles([file]);
                    e.preventDefault();
                    break;
                }
            }
        }
    });
    
    function handleFiles(files) {
        for (let file of files) {
            // Check file size
            if (file.size > MAX_FILE_SIZE) {
                alert(`File "${file.name}" quá lớn. Kích thước tối đa là 1GB.`);
                continue;
            }
            
            // Check total size
            if (totalSize + file.size > MAX_TOTAL_SIZE) {
                alert(`Tổng kích thước file vượt quá 1GB.`);
                continue;
            }
            
            // Check file type
            const allowedTypes = [
                'image/', 'video/', 
                'application/pdf', 
                'application/msword', 
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            ];
            
            let isValidType = false;
            for (let type of allowedTypes) {
                if (file.type.startsWith(type)) {
                    isValidType = true;
                    break;
                }
            }
            
            if (!isValidType) {
                alert(`File "${file.name}" không được hỗ trợ.`);
                continue;
            }
            
            // Add file to list
            selectedFiles.push(file);
            totalSize += file.size;
            addFilePreview(file);
        }
        
        updateFileInput();
        updateFilePreviewVisibility();
    }
    
    function addFilePreview(file) {
        const fileItem = document.createElement('div');
        fileItem.className = 'compact-file-preview';
        fileItem.dataset.fileName = file.name;
        
        let previewContent = '';
        let clickHandler = '';
        
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                fileItem.querySelector('img').src = e.target.result;
            };
            reader.readAsDataURL(file);
            previewContent = '<img src="" alt="Preview" class="file-thumbnail">';
            clickHandler = `onclick="openImageModal(this.querySelector('img').src, '${file.name}')"`;
        } else if (file.type.startsWith('video/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                fileItem.querySelector('video').src = e.target.result;
            };
            reader.readAsDataURL(file);
            previewContent = '<video src="" muted class="file-thumbnail"></video>';
        } else {
            // Document files
            const iconMap = {
                'application/pdf': 'bi-file-pdf text-danger',
                'application/msword': 'bi-file-word text-primary',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'bi-file-word text-primary',
                'application/vnd.ms-excel': 'bi-file-excel text-success',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'bi-file-excel text-success',
                'application/vnd.ms-powerpoint': 'bi-file-ppt text-warning',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation': 'bi-file-ppt text-warning',
                'application/zip': 'bi-file-zip text-secondary',
                'application/x-rar-compressed': 'bi-file-zip text-secondary'
            };
            const icon = iconMap[file.type] || 'bi-file-earmark text-muted';
            previewContent = `<div class="file-icon"><i class="bi ${icon}"></i></div>`;
        }
        
        fileItem.innerHTML = `
            <div class="file-preview-card" ${clickHandler}>
                ${previewContent}
                <div class="file-details">
                    <div class="file-name">${file.name.length > 30 ? file.name.substring(0, 30) + '...' : file.name}</div>
                    <div class="file-size">${formatFileSize(file.size)}</div>
                </div>
                <button type="button" class="remove-file-btn" onclick="removeFile('${file.name}')" title="Xóa file">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        `;
        
        fileList.appendChild(fileItem);
    }
    
    function removeFile(fileName) {
        const fileIndex = selectedFiles.findIndex(f => f.name === fileName);
        if (fileIndex > -1) {
            totalSize -= selectedFiles[fileIndex].size;
            selectedFiles.splice(fileIndex, 1);
            
            const fileItem = fileList.querySelector(`[data-file-name="${fileName}"]`);
            if (fileItem) {
                fileItem.remove();
            }
            
            updateFileInput();
            updateFilePreviewVisibility();
        }
    }
    
    function updateFileInput() {
        // Create new FileList-like object
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        fileInput.files = dt.files;
    }
    
    function updateFilePreviewVisibility() {
        const fileCount = document.getElementById('fileCount');
        if (selectedFiles.length > 0) {
            filePreview.style.display = 'block';
            fileCount.textContent = `${selectedFiles.length} tệp`;
        } else {
            filePreview.style.display = 'none';
            fileCount.textContent = 'Không có tệp nào được chọn';
        }
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Image modal functions
    function openImageModal(imageUrl, fileName) {
        const modalImage = document.getElementById('modalImage');
        const downloadLink = document.getElementById('downloadLink');
        
        modalImage.src = imageUrl;
        modalImage.alt = fileName;
        downloadLink.href = imageUrl;
        downloadLink.download = fileName;
        
        // Show modal
        const modal = document.getElementById('imageModal');
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
    }
    
    // Make functions global
    window.openImageModal = openImageModal;
    window.removeFile = removeFile;
});
</script>
@endsection
