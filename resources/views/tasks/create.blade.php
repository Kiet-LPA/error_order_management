@extends('layouts.master')
@section('title','Tạo công việc')

@push('styles')
<style>
.form-control, .form-select {
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.file-drop-zone:hover {
    background-color: #e9ecef !important;
    border-color: #007bff !important;
}

.priority-badge {
    transition: all 0.3s ease;
}

.priority-badge:hover {
    transform: scale(1.05);
}

.btn-success {
    transition: all 0.3s ease;
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2) !important;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
}

/* Fix datetime-local input */
input[type="datetime-local"] {
    z-index: 1 !important;
    position: relative !important;
    background-color: white !important;
}
input[type="datetime-local"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
    opacity: 1;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
  <div class="row mb-4">
    <div class="col-6">
      <h2 class="text-primary mb-0">
        <i class="fas fa-plus-circle me-2"></i>
        + Tạo công việc mới
      </h2>
    </div>
    <div class="col-6 text-end">
      <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>
        ← Quay lại
      </a>
    </div>
  </div>

  <div class="card shadow-sm border-0">
    <div class="card-body p-4">
      <form action="{{ route('tasks.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-4">
          {{-- Cột bên trái --}}
          <div class="col-lg-6">
            <div class="mb-4">
              <label class="form-label fw-bold text-dark">
                Tiêu đề <span class="text-danger">*</span>
              </label>
              <input
                type="text"
                name="title"
                class="form-control form-control-lg border-2"
                placeholder="Nhập tiêu đề công việc"
                required
                value="{{ old('title') }}"
              >
            </div>

            <div class="mb-4">
              <label class="form-label fw-bold text-dark">Mô tả</label>
              <textarea
                name="description"
                rows="6"
                class="form-control border-2"
                placeholder="Mô tả chi tiết công việc..."
              >{{ old('description') }}</textarea>
            </div>

            <div class="mb-4">
              <label class="form-label fw-bold text-dark">File đính kèm</label>
              <div class="file-drop-zone border-2 border-dashed rounded-3 p-4 text-center"
                   id="fileDropZone"
                   style="border-color: #dee2e6; background-color: #f8f9fa; cursor: pointer; transition: all 0.3s ease;">
                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                <p class="mb-1 fw-medium">Kéo & thả file vào đây</p>
                <small class="text-muted">hoặc click để chọn file</small>
                <div class="mt-2">
                  <small class="text-muted">Hỗ trợ: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, GIF, WEBP, MP4, AVI, MOV, WMV, FLV, WEBM (Tối đa 50MB)</small>
                </div>
                <div id="fileList" class="mt-3"></div>
              </div>
              <input type="file" name="files[]" id="fileInput" class="d-none" multiple>
            </div>

          </div>

          {{-- Cột bên phải --}}
          <div class="col-lg-6">
            <div class="mb-4">
              <label class="form-label fw-bold text-dark">Người nhận</label>
              <select name="assignee_id" class="form-select form-select-lg border-2">
                <option value="">Chọn người nhận</option>
                @foreach($users as $u)
                  <option value="{{ $u->id }}" @selected(old('assignee_id')==$u->id)>
                    {{ $u->name }}
                    @if($u->department_id)
                      <span class="text-muted">({{ \App\Models\Department::find($u->department_id)->name ?? 'N/A' }})</span>
                    @endif
                  </option>
                @endforeach
              </select>
              @if(auth()->user()->isManager())
                <div class="form-text text-info">
                  <i class="fas fa-info-circle me-1"></i>
                  Bạn chỉ có thể giao việc cho nhân viên cùng phòng ban
                </div>
              @endif
            </div>

            <div class="mb-4">
              <label class="form-label fw-bold text-dark">Deadline</label>
              <input
                type="datetime-local"
                name="deadline"
                class="form-control form-control-lg border-2"
                value="{{ old('deadline') }}"
                style="z-index: 9999; position: relative; background-color: white; cursor: pointer;"
              >
            </div>

            <div class="mb-4">
              <label class="form-label fw-bold text-dark d-block mb-3">Độ ưu tiên</label>
              <div class="d-flex gap-3">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="priority" value="low" id="priorityLow" {{ old('priority') == 'low' ? 'checked' : '' }}>
                  <label class="form-check-label fw-medium" for="priorityLow">
                    <span class="badge bg-success px-3 py-2">Thấp</span>
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="priority" value="medium" id="priorityMedium" {{ old('priority') == 'medium' || !old('priority') ? 'checked' : '' }}>
                  <label class="form-check-label fw-medium" for="priorityMedium">
                    <span class="badge bg-warning px-3 py-2">Trung bình</span>
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="priority" value="high" id="priorityHigh" {{ old('priority') == 'high' ? 'checked' : '' }}>
                  <label class="form-check-label fw-medium" for="priorityHigh">
                    <span class="badge bg-danger px-3 py-2">Cao</span>
                  </label>
                </div>
              </div>
            </div>
            <div class="mb-4">
              <label class="form-label fw-bold text-dark">
                Mã Tracking
              </label>
              <input 
                type="text" 
                name="tracking_code" 
                class="form-control form-control-lg border-2" 
                placeholder="Nhập mã tracking từ QR code... (không bắt buộc)"
                value="{{ old('tracking_code') }}"
              >
              <div class="mt-2">
                <small class="text-muted">
                  <i class="fas fa-info-circle me-1"></i>
                  <a href="https://qrscanner.net/" target="_blank" class="text-primary text-decoration-none">
                    <i class="fas fa-qrcode me-1"></i>Quét mã QR
                  </a> 
                  - Nếu bạn có ảnh QR code, hãy quét tại đây và copy mã về
                </small>
              </div>
              @error('tracking_code')
                <div class="text-danger mt-2">
                  <i class="fas fa-exclamation-circle me-1"></i>
                  {{ $message }}
                </div>
              @enderror
            </div>
          </div>
        </div>

        {{-- Nút giao việc --}}
        <div class="row mt-5">
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-success btn-lg px-5 py-3 fw-bold shadow-sm">
              <i class="fas fa-rocket me-2"></i>
              🚀 Giao việc
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
// Fix datetime-local input
document.addEventListener('DOMContentLoaded', function() {
    const deadlineInput = document.querySelector('input[name="deadline"]');
    if (deadlineInput) {
        console.log('Create: Deadline input found');
        deadlineInput.addEventListener('click', function() {
            console.log('Create: Deadline input clicked');
            this.showPicker && this.showPicker();
        });
        deadlineInput.addEventListener('focus', function() {
            console.log('Create: Deadline input focused');
        });
    }

    // File upload handling
    const dropZone = document.getElementById('fileDropZone');
    const fileInput = document.getElementById('fileInput');
    const fileList = document.getElementById('fileList');

    dropZone.addEventListener('click', function() {
        fileInput.click();
    });

    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.style.background = '#e9ecef';
        dropZone.style.borderColor = '#007bff';
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropZone.style.background = '#f8f9fa';
        dropZone.style.borderColor = '#dee2e6';
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.style.background = '#f8f9fa';
        dropZone.style.borderColor = '#dee2e6';
        fileInput.files = e.dataTransfer.files;
        showFiles();
    });

    fileInput.addEventListener('change', showFiles);

    function showFiles() {
        fileList.innerHTML = '';
        if (fileInput.files.length > 0) {
            for (let i = 0; i < fileInput.files.length; i++) {
                const file = fileInput.files[i];
                const fileDiv = document.createElement('div');
                fileDiv.className = 'alert alert-info d-flex align-items-center justify-content-between mb-2';
                fileDiv.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="fas fa-file me-2"></i>
                        <span class="fw-medium">${file.name}</span>
                        <small class="text-muted ms-2">(${formatFileSize(file.size)})</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile(${i})">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                fileList.appendChild(fileDiv);
            }
        }
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    window.removeFile = function(index) {
        const dt = new DataTransfer();
        const { files } = fileInput;

        for (let i = 0; i < files.length; i++) {
            if (i !== index) {
                dt.items.add(files[i]);
            }
        }

        fileInput.files = dt.files;
        showFiles();
    };
});
</script>
@endpush
