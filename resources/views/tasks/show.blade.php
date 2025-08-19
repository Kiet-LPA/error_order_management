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
                Kết thúc
            @else
                {{ strtoupper($task->status) }}
            @endif
        </span>
        <span class="badge badge-priority bg-warning text-dark" style="background:#5DA444; color:#fff;">Độ ưu tiên: {{ ucfirst($task->priority ?? 'Không rõ') }}</span>
    </div>
    <a href="{{ route('dashboard') }}" class="btn btn-light" style="position:absolute;top:24px;right:32px; background:#558EC1; color:#fff; border-color:#558EC1;">&larr; Quay lại</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card card-custom p-4 mb-4">
            <h5 class="mb-3"><i class="bi bi-info-circle me-2"></i>Thông tin chung</h5>
            <div class="row mb-2">
                <div class="col-md-6 mb-2"><i class="bi bi-person-badge me-1"></i> <strong>Người giao:</strong> {{ $task->creator->name }}</div>
                <div class="col-md-6 mb-2"><i class="bi bi-calendar-date me-1"></i> <strong>Ngày giao:</strong> {{ $task->created_at->format('d/m/Y') }}</div>
                <div class="col-md-6 mb-2"><i class="bi bi-person me-1"></i> <strong>Người nhận:</strong> {{ $task->assignee?->name ?? '—' }}</div>
                <div class="col-md-6 mb-2"><i class="bi bi-calendar2-week me-1"></i> <strong>Deadline:</strong> {{ $task->deadline? $task->deadline->format('d/m/Y'):'—' }}</div>
                <div class="col-md-6 mb-2"><i class="bi bi-exclamation-triangle me-1"></i> <strong>Độ ưu tiên:</strong> <span class="text-danger">{{ ucfirst($task->priority ?? 'Không rõ') }}</span></div>
                <div class="col-md-6 mb-2"><i class="bi bi-check2-circle me-1"></i> <strong>Trạng thái:</strong> <span class="text-dark">
                    @if($task->status == 'in_progress')
                        Đang làm
                    @elseif($task->status == 'completed')
                        Chờ duyệt
                    @elseif($task->status == 'rejected')
                        Từ chối
                    @elseif($task->status == 'overdue')
                        Trễ hạn
                    @elseif($task->status == 'finished')
                        Kết thúc
                    @else
                        {{ strtoupper($task->status) }}
                    @endif
                </span></div>
                
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
    </div>
</div>

<!-- Modal xem hình ảnh full size -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Xem hình ảnh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="" class="img-fluid" style="max-height: 70vh;">
            </div>
            <div class="modal-footer">
                <a id="downloadLink" href="" target="_blank" class="btn" style="background:#558EC1; color:#fff; border-color:#558EC1;">Tải xuống</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
function openImageModal(imageUrl, fileName) {
    document.getElementById('modalImage').src = imageUrl;
    document.getElementById('modalImage').alt = fileName;
    document.getElementById('downloadLink').href = imageUrl;
    document.getElementById('downloadLink').download = fileName;
    new bootstrap.Modal(document.getElementById('imageModal')).show();
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
</script>
        <div class="comment-section mb-4">
            <h5 class="mb-3"><i class="bi bi-chat-dots me-2"></i>Thảo luận</h5>
            <form class="mb-4" action="{{ route('tasks.comment',$task) }}" method="POST">
                @csrf
                <textarea name="content" class="form-control mb-2" rows="3" placeholder="Viết bình luận..."></textarea>
                <button class="btn btn-sm" style="background:#558EC1; color:#fff; border-color:#558EC1;">Gửi bình luận</button>
            </form>
            @forelse($task->activities as $act)
                <div class="comment-item">
                    <strong>{{ $act->user->name }}</strong>
                    <small class="text-muted ms-2">{{ $act->created_at->diffForHumans() }}</small>
                    <div>{{ $act->meta }}</div>
                </div>
            @empty
                <div class="text-muted">Chưa có bình luận.</div>
            @endforelse
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
                @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                    <a href="{{ route('tasks.updateStatus',[$task,'status'=>'completed']) }}" class="btn action-btn action-btn-green w-100 mb-2">✅ Chuyển sang chờ duyệt</a>
                @endif
            @endif
            
            @if($task->status == 'completed')
                @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                    <button type="button" class="btn action-btn action-btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#finishModal">🏁 Kết thúc</button>
                    <button type="button" class="btn action-btn action-btn-red w-100 mb-2" data-bs-toggle="modal" data-bs-target="#rejectModal">❌ Từ chối</button>
                @endif
            @endif
            
            @if($task->status == 'rejected')
                @if($task->assignee_id == auth()->id())
                    <a href="{{ route('tasks.updateStatus',[$task,'status'=>'completed']) }}" class="btn action-btn action-btn-green w-100 mb-2">🔄 Đã làm lại & gửi duyệt</a>
                @endif
            @endif
            
            @if($task->status == 'overdue')
                @if($task->assignee_id == auth()->id())
                    <a href="{{ route('tasks.updateStatus',[$task,'status'=>'in_progress']) }}" class="btn action-btn action-btn-blue w-100 mb-2">🚀 Bắt đầu làm</a>
                @endif
                @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                    <a href="{{ route('tasks.updateStatus',[$task,'status'=>'in_progress']) }}" class="btn action-btn action-btn-blue w-100 mb-2">🔄 Chuyển sang đang làm</a>
                @endif
            @endif
            
            {{-- Chỉ hiển thị cho admin/manager --}}
            @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                <a href="{{ route('tasks.edit', $task) }}" class="btn action-btn action-btn-yellow w-100 mb-2">✏️ Chỉnh sửa</a>
            @endif
            
            <a href="{{ route('tasks.history',$task) }}" class="btn action-btn action-btn-outline w-100">👁 Xem lịch sử</a>
        </div>
        
        {{-- Modal kết thúc --}}
        <div class="modal fade" id="finishModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Kết thúc công việc</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('tasks.updateStatus', $task) }}" method="GET">
                        <input type="hidden" name="status" value="finished">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Ghi chú kết thúc <span class="text-muted">(tùy chọn)</span></label>
                                <textarea name="finish_note" class="form-control" rows="3" placeholder="Nhập ghi chú khi kết thúc công việc..."></textarea>
                            </div>
                            <div class="alert" style="background:#dbeafe; border-color:#5DA444; color:#166534;">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Lưu ý:</strong> Công việc sẽ được đánh dấu là hoàn thành và không thể thay đổi trạng thái nữa.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn" style="background:#5DA444; color:#fff; border-color:#5DA444;">Kết thúc</button>
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
                                <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Nhập lý do từ chối..."></textarea>
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
@endsection
