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
        <i class="bi bi-plus-circle me-2"></i>
        + Tạo công việc mới
      </h2>
    </div>
    <div class="col-6 text-end">
      <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>
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
                <i class="bi bi-cloud-upload fa-2x text-muted mb-2"></i>
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
                <div id="user_selection_disabled" class="border rounded p-3 text-center" style="background: #f8f9fa;">
                  <i class="bi bi-exclamation-triangle text-warning fs-1 mb-2"></i>
                  <p class="mb-0">Vui lòng chọn phòng ban trước khi chọn người nhận</p>
                </div>
                <div id="user_selection_enabled" class="border rounded p-3" style="max-height: 300px; overflow-y: auto; display: none;">
                  @foreach($users->groupBy('department_id') as $departmentId => $departmentUsers)
                    @php
                      $department = $departmentUsers->first()->department;
                      $departmentName = $department ? $department->name : 'Không có phòng ban';
                    @endphp
                    <div class="mb-3 department-user-group" data-department="{{ $departmentId }}">
                      <div class="fw-bold text-primary mb-2" style="background: #f8f9fa; padding: 8px 12px; border-radius: 6px; border-left: 4px solid #007bff;">
                        {{ $departmentName }}
                      </div>
                      @foreach($departmentUsers as $u)
                        <div class="form-check mb-2 user-option ms-3" data-department="{{ $u->department_id ?? '' }}">
                          <input class="form-check-input" type="checkbox" 
                                 name="assignee_ids[]" 
                                 value="{{ $u->id }}" 
                                 id="user_{{ $u->id }}"
                                 {{ in_array($u->id, old('assignee_ids', [])) ? 'checked' : '' }}>
                          <label class="form-check-label" for="user_{{ $u->id }}">
                            {{ $u->name }} - {{ ucfirst($u->role) }}
                          </label>
                        </div>
                      @endforeach
                    </div>
                  @endforeach
                </div>
              </div>
              
              <div id="single_user_section">
                <label class="form-label fw-bold text-dark">Người nhận</label>
                <div id="single_user_disabled" class="border rounded p-3 text-center" style="background: #f8f9fa;">
                  <i class="bi bi-exclamation-triangle text-warning fs-1 mb-2"></i>
                  <p class="mb-0">Vui lòng chọn phòng ban trước khi chọn người nhận</p>
                </div>
                <select name="assignee_id" class="form-select form-select-lg border-2" id="assignee_select" style="display: none;">
                  <option value="">Chọn người nhận</option>
                  @foreach($users->groupBy('department_id') as $departmentId => $departmentUsers)
                    @php
                      $department = $departmentUsers->first()->department;
                      $departmentName = $department ? $department->name : 'Không có phòng ban';
                    @endphp
                    <optgroup label="{{ $departmentName }}" data-department="{{ $departmentId }}">
                      @foreach($departmentUsers as $u)
                        <option value="{{ $u->id }}" 
                                data-department="{{ $u->department_id ?? '' }}"
                                @selected(old('assignee_id')==$u->id)>
                          {{ $u->name }} - {{ ucfirst($u->role) }}
                        </option>
                      @endforeach
                    </optgroup>
                  @endforeach
                </select>
              </div>
              
              @if(auth()->user()->isManager())
                <div class="form-text text-info">
                  <i class="bi bi-info-circle me-1"></i>
                  Bạn chỉ có thể giao việc cho nhân viên cùng phòng ban
                </div>
              @endif
            </div>

            <div class="mb-4">
                                  <label class="form-label fw-bold text-dark">Hạn cuối</label>
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
                  <i class="bi bi-arrow-clockwise me-2"></i>Lặp lại công việc
                </label>
              </div>
              <small class="text-muted">
                <i class="bi bi-info-circle me-1"></i>
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

          </div>
        </div>

        {{-- Chọn Task Followers (chỉ Admin/Manager) --}}
        @if(auth()->user()->isAdmin() || auth()->user()->isManager())
        <div class="card mb-4 border-success">
          <div class="card-header bg-success text-white">
            <h6 class="mb-0">
              <i class="bi bi-people me-2"></i>Chọn Task Followers
            </h6>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label">Chọn người theo dõi task này:</label>
              <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                @foreach($users->groupBy('department_id') as $departmentId => $departmentUsers)
                  @php
                    $department = $departmentUsers->first()->department;
                    $departmentName = $department ? $department->name : 'Không có phòng ban';
                  @endphp
                  <div class="mb-3">
                                                    <div class="fw-bold text-dark mb-2" style="background: #f8f9fa; padding: 8px 12px; border-radius: 6px; border-left: 4px solid #6c757d;">
                      {{ $departmentName }}
                    </div>
                    @foreach($departmentUsers as $user)
                      <div class="form-check mb-2 ms-3 follower-option" data-department="{{ $user->department_id ?? '' }}" data-user-id="{{ $user->id }}" data-user-role="{{ $user->role }}">
                        <input class="form-check-input" type="checkbox" name="followers[]" value="{{ $user->id }}" id="follower_{{ $user->id }}">
                        <label class="form-check-label" for="follower_{{ $user->id }}">
                          {{ $user->name }} - {{ ucfirst($user->role) }}
                        </label>
                      </div>
                    @endforeach
                  </div>
                @endforeach
              </div>
              <small class="form-text text-muted">
                @if(auth()->user()->isManager())
                  Manager chỉ có thể thêm Employee làm follower. Người tham gia task sẽ không thể làm follower.
                @else
                  Những người này sẽ nhận thông báo khi task có thay đổi. Người tham gia task sẽ không thể làm follower.
                @endif
              </small>
            </div>
          </div>
        </div>
        @endif

        {{-- Nút giao việc --}}
        <div class="row mt-5">
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-success btn-lg px-5 py-3 fw-bold shadow-sm">
              <i class="bi bi-rocket me-2"></i>
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
        const singleDepartmentSelect = document.querySelector('select[name="department_id"]');
        
        // Get selected departments from both multi and single selection
        departmentCheckboxes.forEach(checkbox => {
            selectedDepartments.push(checkbox.value);
        });
        
        if (singleDepartmentSelect && singleDepartmentSelect.value) {
            selectedDepartments.push(singleDepartmentSelect.value);
        }

        // Show/hide user selection based on department selection
        const userSelectionDisabled = document.getElementById('user_selection_disabled');
        const userSelectionEnabled = document.getElementById('user_selection_enabled');
        const singleUserDisabled = document.getElementById('single_user_disabled');
        const assigneeSelect = document.getElementById('assignee_select');

        if (selectedDepartments.length === 0) {
            // No department selected - show disabled message
            if (userSelectionDisabled) userSelectionDisabled.style.display = 'block';
            if (userSelectionEnabled) userSelectionEnabled.style.display = 'none';
            if (singleUserDisabled) singleUserDisabled.style.display = 'block';
            if (assigneeSelect) assigneeSelect.style.display = 'none';
        } else {
            // Department selected - enable user selection
            if (userSelectionDisabled) userSelectionDisabled.style.display = 'none';
            if (userSelectionEnabled) userSelectionEnabled.style.display = 'block';
            if (singleUserDisabled) singleUserDisabled.style.display = 'none';
            if (assigneeSelect) assigneeSelect.style.display = 'block';

            // Filter multi-user section
            const userOptions = document.querySelectorAll('.user-option');
            userOptions.forEach(option => {
                const departmentId = option.getAttribute('data-department');
                if (selectedDepartments.includes(departmentId)) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                    // Uncheck hidden options
                    const checkbox = option.querySelector('input[type="checkbox"]');
                    if (checkbox) checkbox.checked = false;
                }
            });

            // Filter single user dropdown optgroups
            if (assigneeSelect) {
                const optgroups = assigneeSelect.querySelectorAll('optgroup');
                optgroups.forEach(optgroup => {
                    const departmentId = optgroup.getAttribute('data-department');
                    if (selectedDepartments.includes(departmentId)) {
                        optgroup.style.display = 'block';
                    } else {
                        optgroup.style.display = 'none';
                        // Clear selection if department is not selected
                        const options = optgroup.querySelectorAll('option');
                        options.forEach(option => {
                            if (option.selected) {
                                assigneeSelect.value = '';
                            }
                        });
                    }
                });
            }
        }

        // Update followers based on selected users
        updateFollowersAvailability();
    }

    // Update followers availability based on selected users
    function updateFollowersAvailability() {
        const selectedUserIds = [];
        
        // Get selected users from multi-user section
        const multiUserCheckboxes = document.querySelectorAll('#user_selection_enabled input[name="assignee_ids[]"]:checked');
        multiUserCheckboxes.forEach(checkbox => {
            selectedUserIds.push(checkbox.value);
        });
        
        // Get selected user from single user section
        const singleUserSelect = document.getElementById('assignee_select');
        if (singleUserSelect && singleUserSelect.value) {
            selectedUserIds.push(singleUserSelect.value);
        }

        // Disable followers who are selected as assignees
        const followerOptions = document.querySelectorAll('.follower-option');
        followerOptions.forEach(option => {
            const userId = option.getAttribute('data-user-id');
            const checkbox = option.querySelector('input[type="checkbox"]');
            const label = option.querySelector('label');
            
            if (selectedUserIds.includes(userId)) {
                checkbox.disabled = true;
                checkbox.checked = false;
                label.style.opacity = '0.5';
                label.style.textDecoration = 'line-through';
            } else {
                checkbox.disabled = false;
                label.style.opacity = '1';
                label.style.textDecoration = 'none';
            }
        });
    }

    // Add event listeners for department checkboxes
    const departmentCheckboxes = document.querySelectorAll('input[name="department_ids[]"]');
    departmentCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', filterUsersByDepartments);
    });

    // Add event listener for single department select
    const singleDepartmentSelect = document.querySelector('select[name="department_id"]');
    if (singleDepartmentSelect) {
        singleDepartmentSelect.addEventListener('change', filterUsersByDepartments);
    }

    // Add event listeners for user selection
    document.addEventListener('change', function(e) {
        if (e.target.name === 'assignee_ids[]' || e.target.name === 'assignee_id') {
            updateFollowersAvailability();
        }
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
                        <i class="bi bi-file me-2"></i>
                        <span class="fw-medium">${file.name}</span>
                        <small class="text-muted ms-2">(${formatFileSize(file.size)})</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile(${i})">
                        <i class="bi bi-x-lg"></i>
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
