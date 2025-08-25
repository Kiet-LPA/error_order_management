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
                placeholder="Nhập mô tả chi tiết công việc..."
              >{{ old('description') }}</textarea>
            </div>

            {{-- Multi-Department Selection --}}
            <div class="mb-4">
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="is_multi_department" name="is_multi_department" value="1" {{ old('is_multi_department') ? 'checked' : '' }}>
                <label class="form-check-label fw-bold text-dark" for="is_multi_department">
                  <i class="bi bi-diagram-3 me-2"></i>Công việc đa phòng ban
                </label>
              </div>
              
              <div id="multi_department_section" style="display: none;">
                <label class="form-label fw-bold text-dark">
                  <i class="bi bi-building me-2"></i>Chọn phòng ban tham gia
                </label>
                <div class="row">
                  @foreach($departments as $department)
                    <div class="col-md-6 mb-2">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" 
                               name="department_ids[]" 
                               value="{{ $department->id }}" 
                               id="dept_{{ $department->id }}"
                               {{ in_array($department->id, old('department_ids', [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="dept_{{ $department->id }}">
                          <i class="bi bi-building me-1"></i>{{ $department->name }}
                        </label>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
              
              <div id="single_department_section">
                <label class="form-label fw-bold text-dark">
                  <i class="bi bi-building me-2"></i>Phòng ban phụ trách
                </label>
                <select name="department_id" class="form-select border-2">
                  <option value="">Chọn phòng ban</option>
                  @foreach($departments as $department)
                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                      {{ $department->name }}
                    </option>
                  @endforeach
                </select>
              </div>
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
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="is_multi_user" name="is_multi_user" value="1" {{ old('is_multi_user') ? 'checked' : '' }}>
                <label class="form-check-label fw-bold text-dark" for="is_multi_user">
                  <i class="bi bi-people me-2"></i>Giao việc cho nhiều người
                </label>
              </div>
              
              <div id="multi_user_section" style="display: none;">
                <label class="form-label fw-bold text-dark">
                  <i class="bi bi-people me-2"></i>Chọn người nhận (có thể chọn nhiều)
                </label>
                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                  @foreach($users as $u)
                    <div class="form-check mb-2 user-option" data-department="{{ $u->department_id ?? '' }}">
                      <input class="form-check-input" type="checkbox" 
                             name="assignee_ids[]" 
                             value="{{ $u->id }}" 
                             id="user_{{ $u->id }}"
                             {{ in_array($u->id, old('assignee_ids', [])) ? 'checked' : '' }}>
                      <label class="form-check-label" for="user_{{ $u->id }}">
                        <strong>{{ $u->name }}</strong>
                        @if($u->department)
                          <span class="text-muted">({{ $u->department->name }})</span>
                        @endif
                      </label>
                    </div>
                  @endforeach
                </div>
              </div>
              
              <div id="single_user_section">
                <label class="form-label fw-bold text-dark">Người nhận</label>
                <select name="assignee_id" class="form-select form-select-lg border-2" id="assignee_select">
                  <option value="">Chọn người nhận</option>
                  @foreach($users as $u)
                    <option value="{{ $u->id }}" 
                            data-department="{{ $u->department_id ?? '' }}"
                            @selected(old('assignee_id')==$u->id)>
                      {{ $u->name }}
                      @if($u->department)
                        ({{ $u->department->name }})
                      @endif
                    </option>
                  @endforeach
                </select>
              </div>
              
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
                id="deadline"
                class="form-control form-control-lg border-2"
                value="{{ old('deadline') }}"
                style="z-index: 9999; position: relative; background-color: white; cursor: pointer;"
              >
            </div>
            
            <div class="mb-4">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_recurring" id="is_recurring" value="1" {{ old('is_recurring') ? 'checked' : '' }}>
                <label class="form-check-label fw-bold text-dark" for="is_recurring">
                  <i class="fas fa-redo me-2"></i>Lặp lại công việc
                </label>
              </div>
              <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Công việc sẽ được tự động tạo lại với deadline mới mỗi khi hoàn thành
              </small>
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
document.addEventListener('DOMContentLoaded', function() {
    // Multi-department toggle
    const multiDeptCheckbox = document.getElementById('is_multi_department');
    const multiDeptSection = document.getElementById('multi_department_section');
    const singleDeptSection = document.getElementById('single_department_section');
    
    if (multiDeptCheckbox) {
        multiDeptCheckbox.addEventListener('change', function() {
            if (this.checked) {
                multiDeptSection.style.display = 'block';
                singleDeptSection.style.display = 'none';
            } else {
                multiDeptSection.style.display = 'none';
                singleDeptSection.style.display = 'block';
            }
        });
        
        // Trigger on load if checked
        if (multiDeptCheckbox.checked) {
            multiDeptSection.style.display = 'block';
            singleDeptSection.style.display = 'none';
        }
    }
    
    // Multi-user toggle
    const multiUserCheckbox = document.getElementById('is_multi_user');
    const multiUserSection = document.getElementById('multi_user_section');
    const singleUserSection = document.getElementById('single_user_section');
    
    if (multiUserCheckbox) {
        multiUserCheckbox.addEventListener('change', function() {
            if (this.checked) {
                multiUserSection.style.display = 'block';
                singleUserSection.style.display = 'none';
            } else {
                multiUserSection.style.display = 'none';
                singleUserSection.style.display = 'block';
            }
        });
        
        // Trigger on load if checked
        if (multiUserCheckbox.checked) {
            multiUserSection.style.display = 'block';
            singleUserSection.style.display = 'none';
        }
    }

    // Filter users by selected departments
    function filterUsersByDepartments() {
        const selectedDepartments = [];
        const departmentCheckboxes = document.querySelectorAll('input[name="department_ids[]"]:checked');
        
        departmentCheckboxes.forEach(checkbox => {
            selectedDepartments.push(checkbox.value);
        });

        // Filter multi-user section
        const userOptions = document.querySelectorAll('.user-option');
        userOptions.forEach(option => {
            const departmentId = option.getAttribute('data-department');
            if (selectedDepartments.length === 0 || selectedDepartments.includes(departmentId)) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
                // Uncheck hidden options
                const checkbox = option.querySelector('input[type="checkbox"]');
                if (checkbox) checkbox.checked = false;
            }
        });

        // Filter single user dropdown
        const assigneeSelect = document.getElementById('assignee_select');
        if (assigneeSelect) {
            const options = assigneeSelect.querySelectorAll('option[data-department]');
            options.forEach(option => {
                const departmentId = option.getAttribute('data-department');
                if (selectedDepartments.length === 0 || selectedDepartments.includes(departmentId)) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                    // If this option was selected, clear the selection
                    if (option.selected) {
                        assigneeSelect.value = '';
                    }
                }
            });
        }
    }

    // Add event listeners for department checkboxes
    const departmentCheckboxes = document.querySelectorAll('input[name="department_ids[]"]');
    departmentCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', filterUsersByDepartments);
    });

    // Initial filter on page load
    filterUsersByDepartments();

    // Fix datetime-local input
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
