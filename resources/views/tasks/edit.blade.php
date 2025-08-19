@extends('layouts.edit')
@section('title','Chỉnh sửa công việc')

@push('styles')
<style>
/* Container and layout */
.edit-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.form-control, .form-select {
    transition: all 0.3s ease;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    padding: 12px 16px;
    font-size: 16px;
}

.form-control:focus, .form-select:focus {
    border-color: #558EC1;
    box-shadow: 0 0 0 0.2rem rgba(85, 142, 193, 0.25);
}

/* File upload area */
.file-drop-zone {
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    padding: 40px 20px;
    text-align: center;
    background: #f8f9fa;
    transition: all 0.3s ease;
    cursor: pointer;
    min-height: 150px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.file-drop-zone:hover {
    background-color: #e9ecef !important;
    border-color: #558EC1 !important;
    transform: translateY(-2px);
}

.file-drop-zone.dragover {
    background-color: #e3f2fd !important;
    border-color: #558EC1 !important;
}

/* Priority buttons */
.priority-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.priority-btn {
    flex: 1;
    min-width: 120px;
    padding: 12px 20px;
    border: 2px solid transparent;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
    text-align: center;
}

.priority-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.priority-btn.active {
    border-color: currentColor;
    transform: scale(1.05);
}

.priority-low {
    background: #d4edda;
    color: #155724;
}

.priority-medium {
    background: #fff3cd;
    color: #856404;
}

.priority-high {
    background: #f8d7da;
    color: #721c24;
}

/* Submit button */
.btn-submit {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
    border-radius: 10px;
    padding: 15px 30px;
    font-size: 18px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.btn-submit:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
}

/* Rejection reason styling */
#rejection_reason_group {
    transition: all 0.3s ease;
    border-left: 4px solid #558EC1;
    padding-left: 15px;
    background: rgba(85, 142, 193, 0.05);
    border-radius: 8px;
    margin-top: 10px;
}

#rejection_reason_group label {
    color: #558EC1;
    font-weight: 600;
}

#rejection_reason_group textarea {
    border-color: #558EC1;
}

#rejection_reason_group textarea:focus {
    border-color: #558EC1;
    box-shadow: 0 0 0 0.2rem rgba(85, 142, 193, 0.25);
}

/* Card styling */
.card {
    border-radius: 15px;
    border: none;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, #558EC1 0%, #5DA444 100%);
    color: white;
    border: none;
    padding: 20px 25px;
}

/* Form groups */
.form-group {
    margin-bottom: 25px;
}

.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
    display: block;
}

/* Responsive */
@media (max-width: 768px) {
    .edit-container {
        padding: 15px;
    }
    
    .priority-buttons {
        flex-direction: column;
    }
    
    .priority-btn {
        min-width: auto;
    }
    
    .card-body {
        padding: 20px;
    }
}

/* Fix datetime-local input */
input[type="datetime-local"] {
    z-index: 9999 !important;
    position: relative !important;
    background-color: white !important;
    cursor: pointer !important;
    pointer-events: auto !important;
}

input[type="datetime-local"]::-webkit-calendar-picker-indicator {
    cursor: pointer !important;
    opacity: 1 !important;
    pointer-events: auto !important;
}

input[type="datetime-local"]::-webkit-inner-spin-button,
input[type="datetime-local"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>
@endpush

@section('content')
<div class="edit-container">
    {{-- Header --}}
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="bi bi-pencil-square me-2"></i>
                    Chỉnh sửa công việc
                </h2>
                <a href="{{ route('task-detail', $task) }}" class="btn btn-outline-light">
                    <i class="bi bi-arrow-left me-2"></i>
                    Quay lại
                </a>
            </div>
        </div>
    </div>

    {{-- Form --}}
    <div class="card">
        <div class="card-body p-4">
            <form action="{{ route('tasks.update', $task) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Title --}}
                <div class="form-group">
                    <label for="title" class="form-label">
                        <i class="bi bi-type me-1"></i>Tiêu đề <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" 
                           value="{{ old('title', $task->title) }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="form-group">
                    <label for="description" class="form-label">
                        <i class="bi bi-text-paragraph me-1"></i>Mô tả
                    </label>
                    <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror" 
                              placeholder="Mô tả chi tiết công việc...">{{ old('description', $task->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- File Upload --}}
                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-paperclip me-1"></i>File đính kèm
                    </label>
                    <div class="file-drop-zone" onclick="document.getElementById('files').click()">
                        <i class="bi bi-cloud-upload display-4 text-muted mb-3"></i>
                        <p class="mb-2 fw-semibold">Kéo thả file vào đây hoặc click để chọn</p>
                        <small class="text-muted">
                            Hỗ trợ: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, GIF, WEBP, MP4, AVI, MOV, WMV, FLV, WEBM (Tối đa 50MB)
                        </small>
                    </div>
                    <input type="file" name="files[]" id="files" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.webp,.mp4,.avi,.mov,.wmv,.flv,.webm" 
                           class="d-none" onchange="handleFileSelect(this)">
                    @error('files.*')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Assignee --}}
                <div class="form-group">
                    <label for="assignee_id" class="form-label">
                        <i class="bi bi-person me-1"></i>Người phụ trách
                    </label>
                    <select name="assignee_id" id="assignee_id" class="form-select @error('assignee_id') is-invalid @enderror">
                        <option value="">Chọn người phụ trách</option>
                        @foreach($users as $user)
                            @if($user)
                                <option value="{{ $user->id }}" {{ old('assignee_id', $task->assignee_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name ?? 'Không có tên' }} @if($user->department) ({{ $user->department->name }}) @endif
                                </option>
                            @endif
                        @endforeach
                    </select>
                    @error('assignee_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Deadline --}}
                <div class="form-group">
                    <label for="deadline" class="form-label">
                        <i class="bi bi-calendar-event me-1"></i>Deadline
                    </label>
                    <input type="datetime-local" name="deadline" id="deadline" 
                           class="form-control @error('deadline') is-invalid @enderror"
                           value="{{ old('deadline', $task->deadline ? $task->deadline->format('Y-m-d\TH:i') : '') }}"
                           placeholder="dd/mm/yyyy --:--">
                    @error('deadline')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Priority --}}
                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-flag me-1"></i>Độ ưu tiên
                    </label>
                    <div class="priority-buttons">
                        <input type="radio" name="priority" value="low" id="priority_low" 
                               {{ old('priority', $task->priority) == 'low' ? 'checked' : '' }} class="d-none">
                        <label for="priority_low" class="priority-btn priority-low">
                            <i class="bi bi-flag me-1"></i>Thấp
                        </label>

                        <input type="radio" name="priority" value="medium" id="priority_medium" 
                               {{ old('priority', $task->priority) == 'medium' ? 'checked' : '' }} class="d-none">
                        <label for="priority_medium" class="priority-btn priority-medium">
                            <i class="bi bi-flag me-1"></i>Trung bình
                        </label>

                        <input type="radio" name="priority" value="high" id="priority_high" 
                               {{ old('priority', $task->priority) == 'high' ? 'checked' : '' }} class="d-none">
                        <label for="priority_high" class="priority-btn priority-high">
                            <i class="bi bi-flag me-1"></i>Cao
                        </label>
                    </div>
                    @error('priority')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Status --}}
                <div class="form-group">
                    <label for="status" class="form-label">
                        <i class="bi bi-check2-circle me-1"></i>Trạng thái <span class="text-danger">*</span>
                    </label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="in_progress" {{ old('status', $task->status) == 'in_progress' ? 'selected' : '' }}>Đang làm</option>
                        <option value="completed" {{ old('status', $task->status) == 'completed' ? 'selected' : '' }}>Chờ duyệt</option>
                        <option value="rejected" {{ old('status', $task->status) == 'rejected' ? 'selected' : '' }}>Từ chối</option>
                        <option value="overdue" {{ old('status', $task->status) == 'overdue' ? 'selected' : '' }}>Trễ hạn</option>
                        <option value="finished" {{ old('status', $task->status) == 'finished' ? 'selected' : '' }}>Kết thúc</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Rejection Reason (Conditional) --}}
                <div class="form-group" id="rejection_reason_group" style="display: none;">
                    <label for="rejection_reason" class="form-label">
                        <i class="bi bi-exclamation-triangle me-1"></i>Lý do từ chối <span class="text-danger">*</span>
                    </label>
                    <textarea name="rejection_reason" id="rejection_reason" rows="3" 
                              class="form-control @error('rejection_reason') is-invalid @enderror" 
                              placeholder="Nhập lý do từ chối công việc...">{{ old('rejection_reason', $task->rejection_reason) }}</textarea>
                    @error('rejection_reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Submit Button --}}
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-submit">
                        <i class="bi bi-check-circle me-2"></i>
                        Cập nhật công việc
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Priority button selection
    const priorityBtns = document.querySelectorAll('.priority-btn');
    priorityBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            priorityBtns.forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
        });
    });

    // File drop zone functionality
    const dropZone = document.querySelector('.file-drop-zone');
    const fileInput = document.getElementById('files');

    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        const files = e.dataTransfer.files;
        fileInput.files = files;
        handleFileSelect(fileInput);
    });

    // Deadline input
    const deadlineInput = document.querySelector('input[name="deadline"]');
    if (deadlineInput) {
        deadlineInput.addEventListener('click', function() {
            this.showPicker && this.showPicker();
        });
    }

    // Status change handler
    const statusSelect = document.getElementById('status');
    const rejectionReasonGroup = document.getElementById('rejection_reason_group');

    // Show/hide rejection reason based on current status
    if (statusSelect.value === 'rejected') {
        rejectionReasonGroup.style.display = 'block';
    }

    statusSelect.addEventListener('change', function() {
        if (this.value === 'rejected') {
            rejectionReasonGroup.style.display = 'block';
            // Make rejection reason required when status is rejected
            document.getElementById('rejection_reason').required = true;
        } else {
            rejectionReasonGroup.style.display = 'none';
            // Remove required when status is not rejected
            document.getElementById('rejection_reason').required = false;
            // Clear rejection reason when status is not rejected
            document.getElementById('rejection_reason').value = '';
        }
    });
});

function handleFileSelect(input) {
    const files = input.files;
    if (files.length > 0) {
        const dropZone = document.querySelector('.file-drop-zone');
        dropZone.innerHTML = `
            <i class="bi bi-check-circle text-success display-6 mb-2"></i>
            <p class="mb-0 fw-semibold text-success">Đã chọn ${files.length} file</p>
            <small class="text-muted">Click để thay đổi</small>
        `;
    }
}
</script>
@endsection
