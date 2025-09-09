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
            @if(!auth()->user()->isManager())
            <div class="mb-4">
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="is_multi_department" name="is_multi_department" value="1" {{ old('is_multi_department') ? 'checked' : '' }}>
                <label class="form-check-label fw-bold text-dark" for="is_multi_department">
                  <i class="bi bi-diagram-3 me-2"></i>Công việc đa phòng ban
                </label>
              </div>
            @else
            <div class="mb-4" style="display: none;">
              <input type="hidden" name="is_multi_department" value="0">
            @endif
              
              <div id="multi_department_section" style="display: none;">
                <label class="form-label fw-bold text-dark">
                  <i class="bi bi-building me-2"></i>Chọn phòng ban tham gia
                </label>
                <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto; background: #f8f9fa;">
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
              </div>
              
              <div id="single_department_section">
                <label class="form-label fw-bold text-dark">
                  <i class="bi bi-building me-2"></i>Phòng ban phụ trách
                </label>
                <!-- <div class="alert alert-info mb-3">
                  <i class="bi bi-lightbulb me-2"></i>
                  <strong>Lưu ý:</strong> Phòng ban sẽ được tự động phát hiện dựa trên assignees bạn chọn. 
                  Nếu bạn chọn nhân viên từ nhiều phòng ban khác nhau, task sẽ tự động trở thành "Đa phòng ban".
                </div> -->
                @if(auth()->user()->isManager())
                  {{-- Manager: tự động chọn phòng ban của họ --}}
                  <input type="hidden" name="department_id" value="{{ auth()->user()->department_id }}">
                  <div class="form-control border-2 bg-light" style="pointer-events: none;">
                    <i class="bi bi-check-circle text-success me-2"></i>
                    {{ auth()->user()->department->name ?? 'Chưa phân phòng ban' }}
                    <small class="text-muted ms-2">(Tự động chọn phòng ban của bạn)</small>
                  </div>
                @else
                  {{-- Admin/Director: có thể chọn phòng ban --}}
                  <select name="department_id" class="form-select border-2">
                    <option value="">Chọn phòng ban</option>
                    @foreach($departments as $department)
                      <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                        {{ $department->name }}
                      </option>
                    @endforeach
                  </select>
                @endif
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
                <div id="user_selection_disabled" class="border rounded p-3 text-center" style="background: #f8f9fa; {{ auth()->user()->isManager() ? 'display: none;' : '' }}">
                  <i class="bi bi-exclamation-triangle text-warning fs-1 mb-2"></i>
                  <p class="mb-0">Vui lòng chọn phòng ban trước khi chọn người nhận</p>
                </div>
                <div id="user_selection_enabled" class="border rounded p-3" style="max-height: 300px; overflow-y: auto; {{ auth()->user()->isManager() ? 'display: block;' : 'display: none;' }}">
                  @foreach($departments as $department)
                    @php
                      $departmentUsers = $users->where('department_id', $department->id);
                    @endphp
                    <div class="mb-3 department-user-group" data-department="{{ $department->id }}">
                      <div class="fw-bold text-primary mb-2" style="background: #f8f9fa; padding: 8px 12px; border-radius: 6px; border-left: 4px solid #007bff;">
                        {{ $department->name }}
                        @if($departmentUsers->count() == 0)
                          <small class="text-muted">(Không có nhân viên)</small>
                        @endif
                      </div>
                      @if($departmentUsers->count() > 0)
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
                      @else
                        <div class="ms-3 text-muted">
                          <small><i class="bi bi-info-circle me-1"></i>Phòng ban này chưa có nhân viên</small>
                        </div>
                      @endif
                    </div>
                  @endforeach
                </div>
              </div>
              
              <div id="single_user_section">
                <label class="form-label fw-bold text-dark">Người nhận</label>
                <div id="single_user_disabled" class="border rounded p-3 text-center" style="background: #f8f9fa; {{ auth()->user()->isManager() ? 'display: none;' : '' }}">
                  <i class="bi bi-exclamation-triangle text-warning fs-1 mb-2"></i>
                  <p class="mb-0">Vui lòng chọn phòng ban trước khi chọn người nhận</p>
                </div>
                <select name="assignee_id" class="form-select form-select-lg border-2" id="assignee_select" style="{{ auth()->user()->isManager() ? 'display: block;' : 'display: none;' }}">
                  <option value="">Chọn người nhận</option>
                  @foreach($departments as $department)
                    @php
                      $departmentUsers = $users->where('department_id', $department->id);
                    @endphp
                    <optgroup label="{{ $department->name }}{{ $departmentUsers->count() == 0 ? ' (Không có nhân viên)' : '' }}" data-department="{{ $department->id }}">
                      @if($departmentUsers->count() > 0)
                        @foreach($departmentUsers as $u)
                          <option value="{{ $u->id }}" 
                                  data-department="{{ $u->department_id ?? '' }}"
                                  @selected(old('assignee_id')==$u->id)>
                            {{ $u->name }} - {{ ucfirst($u->role) }}
                          </option>
                        @endforeach
                      @else
                        <option value="" disabled>Không có nhân viên</option>
                      @endif
                    </optgroup>
                  @endforeach
                </select>
              </div>
              
              @if(auth()->user()->isManager())
                <div class="form-text text-info">
                  <i class="bi bi-info-circle me-1"></i>
                  <strong>Quản lý chỉ có thể giao việc cho nhân viên trong phòng ban của mình.</strong><br>
                  <strong>Để giao việc cho phòng ban khác:</strong> Sử dụng chức năng "Chuyển tiếp công việc" sau khi tạo công việc.
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
                min="{{ now()->format('Y-m-d\TH:i') }}"
                style="z-index: 9999; position: relative; background-color: white; cursor: pointer;"
              >
              <small class="form-text text-muted">
                <i class="bi bi-info-circle me-1"></i>
                Không thể chọn ngày giờ trong quá khứ
              </small>
            </div>
            
            <div class="mb-4">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_recurring" id="is_recurring" value="1" {{ old('is_recurring') ? 'checked' : '' }}>
                <label class="form-check-label fw-bold text-dark" for="is_recurring">
                  <i class="bi bi-arrow-clockwise me-2"></i>Lặp lại công việc
                </label>
              </div>
              <small class="form-text text-muted">
                <i class="bi bi-info-circle me-1"></i>
                Công việc sẽ được tự động tạo lại với hạn cuối mới mỗi X ngày sau khi hoàn thành
              </small>
              
              {{-- Recurring Days Input --}}
              <div id="recurring_days_section" class="mt-2 {{ old('is_recurring') ? '' : 'd-none' }}">
                <div class="row">
                  <div class="col-md-6">
                    <label for="recurring_days" class="form-label">Số ngày lặp lại</label>
                    <input type="number" name="recurring_days" id="recurring_days" 
                           class="form-control" min="1" max="365"
                           value="{{ old('recurring_days') }}" 
                           placeholder="Ví dụ: 7 (mỗi tuần)">
                    <small class="form-text text-muted">Số ngày sau hạn cuối cũ để tạo hạn cuối mới</small>
                  </div>
                  <div class="col-md-6">
                    <label for="recurring_start_date" class="form-label">Ngày bắt đầu lặp lại</label>
                    <input type="date" name="recurring_start_date" id="recurring_start_date" 
                           class="form-control"
                           value="{{ old('recurring_start_date') }}">
                    <small class="form-text text-muted">Ngày bắt đầu tính lặp lại (mặc định: ngày tạo công việc)</small>
                  </div>
                </div>
              </div>
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
                        @if(auth()->user()->isAdmin() || auth()->user()->isDirector() || auth()->user()->isManager())
        <div class="card mb-4 border-success">
          <div class="card-header bg-success text-white">
            <h6 class="mb-0">
              <i class="bi bi-people me-2"></i>Chọn Người Theo Dõi Công Việc
            </h6>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label">Chọn người theo dõi công việc này:</label>
              <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                @foreach($departments as $department)
                  @php
                    $departmentUsers = $users->where('department_id', $department->id);
                  @endphp
                  <div class="mb-3">
                    <div class="fw-bold text-dark mb-2" style="background: #f8f9fa; padding: 8px 12px; border-radius: 6px; border-left: 4px solid #6c757d;">
                      {{ $department->name }}
                      @if($departmentUsers->count() == 0)
                        <small class="text-muted">(Không có nhân viên)</small>
                      @endif
                    </div>
                    @if($departmentUsers->count() > 0)
                      @foreach($departmentUsers as $user)
                        <div class="form-check mb-2 ms-3 follower-option" data-department="{{ $user->department_id ?? '' }}" data-user-id="{{ $user->id }}" data-user-role="{{ $user->role }}">
                          <input class="form-check-input" type="checkbox" name="followers[]" value="{{ $user->id }}" id="follower_{{ $user->id }}">
                          <label class="form-check-label" for="follower_{{ $user->id }}">
                            {{ $user->name }} - {{ ucfirst($user->role) }}
                          </label>
                        </div>
                      @endforeach
                    @else
                      <div class="ms-3 text-muted">
                        <small><i class="bi bi-info-circle me-1"></i>Phòng ban này chưa có nhân viên</small>
                      </div>
                    @endif
                  </div>
                @endforeach
              </div>
              <small class="form-text text-muted">
                @if(auth()->user()->isManager())
                  Quản lý chỉ có thể thêm Nhân viên làm Người theo dõi. Người tham gia công việc sẽ không thể làm Người theo dõi.
                @else
                  Những người này sẽ nhận thông báo khi công việc có thay đổi. Người tham gia công việc sẽ không thể làm Người theo dõi.
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
              Giao việc
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
    // Multi-department toggle (chỉ cho Admin/Director)
    const multiDeptCheckbox = document.getElementById('is_multi_department');
    const multiDeptSection = document.getElementById('multi_department_section');
    const singleDeptSection = document.getElementById('single_department_section');
    
    // Manager không có checkbox đa phòng ban
    const isManager = {{ auth()->user()->isManager() ? 'true' : 'false' }};
    
    console.log('Multi-department elements:', {
        checkbox: !!multiDeptCheckbox,
        multiSection: !!multiDeptSection,
        singleSection: !!singleDeptSection,
        isManager: isManager
    });
    
    if (multiDeptCheckbox) {
        multiDeptCheckbox.addEventListener('change', function() {
            console.log('Multi-department checkbox changed:', this.checked);
            if (this.checked) {
                if (multiDeptSection) multiDeptSection.style.display = 'block';
                if (singleDeptSection) singleDeptSection.style.display = 'none';
                console.log('Showing multi-department section');
            } else {
                if (multiDeptSection) multiDeptSection.style.display = 'none';
                if (singleDeptSection) singleDeptSection.style.display = 'block';
                console.log('Showing single department section');
            }
            // Trigger filter after toggle
            setTimeout(filterUsersByDepartments, 100);
        });
        
        // Trigger on load if checked
        if (multiDeptCheckbox.checked) {
            if (multiDeptSection) multiDeptSection.style.display = 'block';
            if (singleDeptSection) singleDeptSection.style.display = 'none';
            console.log('Initial state: multi-department checked');
        } else {
            if (multiDeptSection) multiDeptSection.style.display = 'none';
            if (singleDeptSection) singleDeptSection.style.display = 'block';
            console.log('Initial state: single department');
        }
    } else if (isManager) {
        // Manager: luôn hiển thị single department section và tự động chọn phòng ban
        if (singleDeptSection) {
            singleDeptSection.style.display = 'block';
        }
        if (multiDeptSection) {
            multiDeptSection.style.display = 'none';
        }
        
        // Manager: tự động trigger filter vì phòng ban đã được chọn sẵn
        setTimeout(filterUsersByDepartments, 100);
    }
    
    // Multi-user toggle
    const multiUserCheckbox = document.getElementById('is_multi_user');
    const multiUserSection = document.getElementById('multi_user_section');
    const singleUserSection = document.getElementById('single_user_section');
    
    console.log('Multi-user elements:', {
        checkbox: !!multiUserCheckbox,
        multiSection: !!multiUserSection,
        singleSection: !!singleUserSection
    });
    
    if (multiUserCheckbox) {
        multiUserCheckbox.addEventListener('change', function() {
            console.log('Multi-user checkbox changed:', this.checked);
            if (this.checked) {
                if (multiUserSection) multiUserSection.style.display = 'block';
                if (singleUserSection) singleUserSection.style.display = 'none';
                console.log('Showing multi-user section');
            } else {
                if (multiUserSection) multiUserSection.style.display = 'none';
                if (singleUserSection) singleUserSection.style.display = 'block';
                console.log('Showing single user section');
            }
            // Trigger filter after toggle
            setTimeout(filterUsersByDepartments, 100);
        });
        
        // Trigger on load if checked
        if (multiUserCheckbox.checked) {
            if (multiUserSection) multiUserSection.style.display = 'block';
            if (singleUserSection) singleUserSection.style.display = 'none';
            console.log('Initial state: multi-user checked');
        } else {
            if (multiUserSection) multiUserSection.style.display = 'none';
            if (singleUserSection) singleUserSection.style.display = 'block';
            console.log('Initial state: single user');
        }
    }

    // Filter users by selected departments
    function filterUsersByDepartments() {
        console.log('filterUsersByDepartments called');
        const selectedDepartments = [];
        const departmentCheckboxes = document.querySelectorAll('input[name="department_ids[]"]:checked');
        const singleDepartmentSelect = document.querySelector('select[name="department_id"]');
        const managerDepartmentInput = document.querySelector('input[name="department_id"][type="hidden"]');
        
        console.log('Department checkboxes found:', departmentCheckboxes.length);
        console.log('Single department select found:', singleDepartmentSelect);
        console.log('Manager department input found:', managerDepartmentInput);
        
        // Get selected departments from both multi and single selection
        departmentCheckboxes.forEach(checkbox => {
            selectedDepartments.push(checkbox.value);
            console.log('Selected department from checkbox:', checkbox.value);
        });
        
        if (singleDepartmentSelect && singleDepartmentSelect.value) {
            selectedDepartments.push(singleDepartmentSelect.value);
            console.log('Selected department from select:', singleDepartmentSelect.value);
        }
        
        // Manager: lấy phòng ban từ hidden input
        if (managerDepartmentInput && managerDepartmentInput.value) {
            selectedDepartments.push(managerDepartmentInput.value);
            console.log('Selected department from manager input:', managerDepartmentInput.value);
        }

        console.log('Total selected departments:', selectedDepartments);
        
        // Kiểm tra logic đa phòng ban cho Manager (chỉ khi có nhiều phòng ban được chọn)
        const isManager = {{ auth()->user()->isManager() ? 'true' : 'false' }};
        const managerDepartmentId = {{ auth()->user()->department_id ?? 'null' }};
        
        if (isManager && selectedDepartments.length > 1) {
            const hasOwnDepartment = selectedDepartments.includes(managerDepartmentId.toString());
            if (!hasOwnDepartment) {
                alert('Quản lý chỉ có thể tạo công việc đa phòng ban khi có phòng ban của mình tham gia');
                // Uncheck the last selected department (not the manager's department)
                departmentCheckboxes.forEach(checkbox => {
                    if (checkbox.value !== managerDepartmentId.toString() && checkbox.checked) {
                        checkbox.checked = false;
                    }
                });
                return;
            }
        }

        // Show/hide user selection based on department selection
        const userSelectionDisabled = document.getElementById('user_selection_disabled');
        const userSelectionEnabled = document.getElementById('user_selection_enabled');
        const singleUserDisabled = document.getElementById('single_user_disabled');
        const assigneeSelect = document.getElementById('assignee_select');

        console.log('User selection elements:', {
            userSelectionDisabled: !!userSelectionDisabled,
            userSelectionEnabled: !!userSelectionEnabled,
            singleUserDisabled: !!singleUserDisabled,
            assigneeSelect: !!assigneeSelect
        });

        // Kiểm tra xem có phải đa phòng ban không
        const multiDeptElement = document.getElementById('is_multi_department');
        const isMultiDepartment = multiDeptElement ? multiDeptElement.checked : false;

        if (selectedDepartments.length === 0 && !isManager) {
            // No department selected - show disabled message (trừ Manager vì họ đã có phòng ban mặc định)
            console.log('No departments selected, showing disabled state');
            if (userSelectionDisabled) {
                userSelectionDisabled.style.display = 'block';
                console.log('Showing user selection disabled message');
            }
            if (userSelectionEnabled) {
                userSelectionEnabled.style.display = 'none';
                console.log('Hiding user selection enabled');
            }
            if (singleUserDisabled) {
                singleUserDisabled.style.display = 'block';
                console.log('Showing single user disabled message');
            }
            if (assigneeSelect) {
                assigneeSelect.style.display = 'none';
                console.log('Hiding assignee select');
            }
        } else {
            // Department selected OR Manager (Manager luôn có phòng ban mặc định)
            console.log('Departments selected or Manager, enabling user selection');
            if (userSelectionDisabled) {
                userSelectionDisabled.style.display = 'none';
                console.log('Hiding user selection disabled message');
            }
            if (userSelectionEnabled) {
                userSelectionEnabled.style.display = 'block';
                console.log('Showing user selection enabled');
            }
            if (singleUserDisabled) {
                singleUserDisabled.style.display = 'none';
                console.log('Hiding single user disabled message');
            }
            if (assigneeSelect) {
                assigneeSelect.style.display = 'block';
                console.log('Showing assignee select');
            }

            // Filter multi-user section - show/hide entire department groups
            const departmentGroups = document.querySelectorAll('.department-user-group');
            console.log('Department groups found:', departmentGroups.length);
            departmentGroups.forEach(group => {
                const departmentId = group.getAttribute('data-department');
                console.log('Department group:', departmentId, 'Selected departments:', selectedDepartments);
                
                // Nếu không phải đa phòng ban và là Manager, chỉ hiển thị phòng ban của manager
                if (!isMultiDepartment && isManager) {
                    if (departmentId === managerDepartmentId.toString()) {
                        group.style.display = 'block';
                    } else {
                        group.style.display = 'none';
                        // Uncheck all checkboxes in hidden groups
                        const checkboxes = group.querySelectorAll('input[type="checkbox"]');
                        checkboxes.forEach(checkbox => checkbox.checked = false);
                    }
                } else {
                    // Đa phòng ban hoặc không phải Manager - hiển thị theo phòng ban được chọn
                    if (selectedDepartments.includes(departmentId)) {
                        group.style.display = 'block';
                    } else {
                        group.style.display = 'none';
                        // Uncheck all checkboxes in hidden groups
                        const checkboxes = group.querySelectorAll('input[type="checkbox"]');
                        checkboxes.forEach(checkbox => checkbox.checked = false);
                    }
                }
            });

            // Filter single user dropdown optgroups
            if (assigneeSelect) {
                const optgroups = assigneeSelect.querySelectorAll('optgroup');
                console.log('Optgroups found:', optgroups.length);
                optgroups.forEach(optgroup => {
                    const departmentId = optgroup.getAttribute('data-department');
                    console.log('Optgroup department:', departmentId, 'Selected departments:', selectedDepartments);
                    
                    // Nếu không phải đa phòng ban và là Manager, chỉ hiển thị nhân viên cùng phòng ban
                    if (!isMultiDepartment && isManager) {
                        if (departmentId === managerDepartmentId.toString()) {
                            optgroup.style.display = 'block';
                        } else {
                            optgroup.style.display = 'none';
                            const options = optgroup.querySelectorAll('option');
                            options.forEach(option => {
                                if (option.selected) {
                                    assigneeSelect.value = '';
                                }
                            });
                        }
                    } else {
                        // Đa phòng ban hoặc không phải Manager - hiển thị theo phòng ban được chọn
                        if (selectedDepartments.includes(departmentId)) {
                            optgroup.style.display = 'block';
                        } else {
                            optgroup.style.display = 'none';
                            const options = optgroup.querySelectorAll('option');
                            options.forEach(option => {
                                if (option.selected) {
                                    assigneeSelect.value = '';
                                }
                            });
                        }
                    }
                });
            }
        }

        // Update followers based on selected users
        updateFollowersAvailability();
        
        // Kiểm tra logic đa phòng ban cho Manager khi chọn người làm
        if (isManager && selectedDepartments.length > 1) {
            const selectedUserIds = [];
            const multiUserCheckboxes = document.querySelectorAll('#user_selection_enabled input[name="assignee_ids[]"]:checked');
            multiUserCheckboxes.forEach(checkbox => {
                selectedUserIds.push(checkbox.value);
            });
            
            const singleUserSelect = document.getElementById('assignee_select');
            if (singleUserSelect && singleUserSelect.value) {
                selectedUserIds.push(singleUserSelect.value);
            }
            
            if (selectedUserIds.length > 0) {
                // Kiểm tra xem có ít nhất 1 người từ phòng ban của manager không
                const hasOwnDepartmentUser = selectedUserIds.some(userId => {
                    const userOption = document.querySelector(`input[value="${userId}"]`);
                    if (userOption) {
                        const userDepartmentId = userOption.closest('.user-option').getAttribute('data-department');
                        return userDepartmentId === managerDepartmentId.toString();
                    }
                    return false;
                });
                
                if (!hasOwnDepartmentUser) {
                    alert('Manager phải có ít nhất 1 người từ phòng ban của mình tham gia vào công việc đa phòng ban');
                    // Uncheck all user selections
                    multiUserCheckboxes.forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    if (singleUserSelect) {
                        singleUserSelect.value = '';
                    }
                }
            }
        }
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
    console.log('Department checkboxes found for event listeners:', departmentCheckboxes.length);
    departmentCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            console.log('Department checkbox changed:', this.value, this.checked);
            filterUsersByDepartments();
        });
    });

    // Add event listener for single department select
    const singleDepartmentSelect = document.querySelector('select[name="department_id"]');
    if (singleDepartmentSelect) {
        singleDepartmentSelect.addEventListener('change', function() {
            console.log('Single department select changed:', this.value);
            filterUsersByDepartments();
        });
    }

    // Add event listeners for user selection
    document.addEventListener('change', function(e) {
        if (e.target.name === 'assignee_ids[]' || e.target.name === 'assignee_id') {
            console.log('User selection changed:', e.target.name, e.target.value);
            updateFollowersAvailability();
        }
    });

    // Initial filter on page load
    console.log('Running initial filter on page load');
    filterUsersByDepartments();

    // Fix datetime-local input
    const deadlineInputElement = document.querySelector('input[name="deadline"]');
    if (deadlineInputElement) {
        console.log('Create: Deadline input found');
        deadlineInputElement.addEventListener('click', function() {
            console.log('Create: Deadline input clicked');
            this.showPicker && this.showPicker();
        });
        deadlineInputElement.addEventListener('focus', function() {
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

    // Validation cho deadline
    const deadlineInput = document.getElementById('deadline');
    const form = deadlineInput?.closest('form');
    
    if (deadlineInput && form) {
        // Cập nhật min attribute mỗi giây để đảm bảo không chọn quá khứ
        setInterval(function() {
            const now = new Date();
            const nowString = now.toISOString().slice(0, 16);
            deadlineInput.min = nowString;
        }, 1000);
        
        // Validation khi submit form
        form.addEventListener('submit', function(e) {
            const selectedDate = new Date(deadlineInput.value);
            const now = new Date();
            
            if (selectedDate < now) {
                e.preventDefault();
                alert('Không thể chọn deadline là ngày giờ trong quá khứ!');
                deadlineInput.focus();
                return false;
            }
            
            // Validation cho Manager đa phòng ban
            const isManager = {{ auth()->user()->isManager() ? 'true' : 'false' }};
            const managerDepartmentId = {{ auth()->user()->department_id ?? 'null' }};
            
            if (isManager) {
                const multiDeptElement = document.getElementById('is_multi_department');
                const isMultiDept = multiDeptElement ? multiDeptElement.checked : false;
                const selectedDepartments = [];
                const departmentCheckboxes = document.querySelectorAll('input[name="department_ids[]"]:checked');
                const singleDepartmentSelect = document.querySelector('select[name="department_id"]');
                
                departmentCheckboxes.forEach(checkbox => {
                    selectedDepartments.push(checkbox.value);
                });
                
                if (singleDepartmentSelect && singleDepartmentSelect.value) {
                    selectedDepartments.push(singleDepartmentSelect.value);
                }
                
                if (isMultiDept && selectedDepartments.length > 1) {
                    const hasOwnDepartment = selectedDepartments.includes(managerDepartmentId.toString());
                    if (!hasOwnDepartment) {
                        e.preventDefault();
                        alert('Manager chỉ có thể tạo công việc đa phòng ban khi có ít nhất 1 người từ phòng ban của mình tham gia');
                        return false;
                    }
                    
                    // Kiểm tra người làm
                    const selectedUserIds = [];
                    const multiUserCheckboxes = document.querySelectorAll('#user_selection_enabled input[name="assignee_ids[]"]:checked');
                    multiUserCheckboxes.forEach(checkbox => {
                        selectedUserIds.push(checkbox.value);
                    });
                    
                    const singleUserSelect = document.getElementById('assignee_select');
                    if (singleUserSelect && singleUserSelect.value) {
                        selectedUserIds.push(singleUserSelect.value);
                    }
                    
                    if (selectedUserIds.length > 0) {
                        const hasOwnDepartmentUser = selectedUserIds.some(userId => {
                            const userOption = document.querySelector(`input[value="${userId}"]`);
                            if (userOption) {
                                const userDepartmentId = userOption.closest('.user-option').getAttribute('data-department');
                                return userDepartmentId === managerDepartmentId.toString();
                            }
                            return false;
                        });
                        
                        if (!hasOwnDepartmentUser) {
                            e.preventDefault();
                            alert('Manager phải có ít nhất 1 người từ phòng ban của mình tham gia vào công việc đa phòng ban');
                            return false;
                        }
                    }
                }
            }
        });
        
        // Validation khi thay đổi deadline
        deadlineInput.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const now = new Date();
            
            if (selectedDate < now) {
                this.setCustomValidity('Không thể chọn ngày giờ trong quá khứ');
                this.classList.add('is-invalid');
                
                // Hiển thị thông báo lỗi
                let errorDiv = this.parentNode.querySelector('.deadline-error');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback deadline-error';
                    errorDiv.textContent = 'Không thể chọn ngày giờ trong quá khứ';
                    this.parentNode.appendChild(errorDiv);
                }
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
                
                // Xóa thông báo lỗi
                const errorDiv = this.parentNode.querySelector('.deadline-error');
                if (errorDiv) {
                    errorDiv.remove();
                }
            }
        });
    }
    
    // Xử lý recurring task checkbox
    const recurringCheckbox = document.getElementById('is_recurring');
    const recurringDaysSection = document.getElementById('recurring_days_section');
    
    if (recurringCheckbox && recurringDaysSection) {
        // Xử lý sự kiện thay đổi checkbox
        recurringCheckbox.addEventListener('change', function() {
            if (this.checked) {
                recurringDaysSection.classList.remove('d-none');
            } else {
                recurringDaysSection.classList.add('d-none');
            }
        });
        
        // Kiểm tra trạng thái ban đầu
        if (recurringCheckbox.checked) {
            recurringDaysSection.classList.remove('d-none');
        }
    }
});
</script>
@endpush
