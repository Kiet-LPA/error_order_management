@extends('layouts.edit')
@section('title','Cập nhật công việc')

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

/* Custom dropdown styling */
.custom-dropdown {
    position: relative;
    width: 100%;
}

.dropdown-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.dropdown-toggle:hover {
    border-color: #558EC1;
    box-shadow: 0 0 0 0.2rem rgba(85, 142, 193, 0.25);
}

.dropdown-toggle.active {
    border-color: #558EC1;
    box-shadow: 0 0 0 0.2rem rgba(85, 142, 193, 0.25);
}

.dropdown-toggle.active i {
    transform: rotate(180deg);
}

.dropdown-toggle i {
    transition: transform 0.3s ease;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1000;
    max-height: 200px;
    overflow-y: auto;
    display: none;
}

.dropdown-menu.show {
    display: block;
}

.dropdown-item {
    padding: 8px 16px;
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.2s ease;
}

.dropdown-item:last-child {
    border-bottom: none;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
}

.dropdown-item .form-check {
    margin: 0;
    width: 100%;
}

.dropdown-item .form-check-input {
    margin-right: 8px;
}

.dropdown-item .form-check-label {
    font-size: 0.9rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
}

.dropdown-item .badge {
    font-size: 0.7rem;
    padding: 2px 6px;
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

/* Đảm bảo menu dropdown luôn nằm trên các input khác */
.custom-dropdown .dropdown-menu {
    z-index: 2000 !important;  /* cao hơn deadline input */
    position: absolute;
}

/* Nếu từng chỉnh datetime-local lên z-index cao, hãy reset lại */
input[type="datetime-local"] {
    z-index: auto !important;   /* hoặc 1, miễn thấp hơn 2000 */
    position: relative !important;
}


input[type="datetime-local"]::-webkit-inner-spin-button,
input[type="datetime-local"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Ensure dropdown doesn't overlap datetime input */
.dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1000;
    max-height: 200px;
    overflow-y: auto;
    display: none;
}

/* Higher z-index for datetime input container */
.form-group:has(input[type="datetime-local"]) {
    position: relative;
    z-index: 1001;
}

/* Ensure datetime input is always on top */
.form-group:has(input[type="datetime-local"]) input[type="datetime-local"] {
    z-index: 1002 !important;
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
                    Cập nhật công việc
                </h2>
                <a href="{{ route('task-detail', $task) }}" class="btn btn-outline-light">
                    <i class="bi bi-arrow-left me-2"></i>
                    Quay lại
                </a>
            </div>
        </div>
    </div>

    {{-- Thông tin phòng ban tự động --}}
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0">
                <i class="bi bi-info-circle me-2"></i>Thông tin phòng ban tự động
            </h6>
        </div>
        <div class="card-body">
            @php
                $currentDepartments = $task->getCurrentDepartments();
            @endphp
            <div class="row">
                <div class="col-md-6">
                    <strong>Phòng ban hiện tại:</strong>
                    @if($currentDepartments->count() > 0)
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            @foreach($currentDepartments as $department)
                                <span class="badge bg-info">{{ $department->name }}</span>
                            @endforeach
                        </div>
                    @else
                        <span class="text-muted">Chưa phân phòng ban</span>
                    @endif
                </div>
                <div class="col-md-6">
                    <strong>Loại task:</strong>
                    @if($task->is_multi_department)
                        <span class="badge bg-warning">Đa phòng ban</span>
                        <small class="text-muted d-block mt-1">
                            <i class="bi bi-info-circle me-1"></i>Tự động phát hiện từ assignees
                        </small>
                    @else
                        <span class="badge bg-primary">Đơn phòng ban</span>
                    @endif
                </div>
            </div>
            <div class="alert alert-info mt-3 mb-0">
                <i class="bi bi-lightbulb me-2"></i>
                <strong>Lưu ý:</strong> Phòng ban sẽ được tự động cập nhật dựa trên assignees của task. 
                Nếu bạn thêm/xóa assignees, phòng ban sẽ được điều chỉnh tương ứng.
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
                              placeholder="Mô tả chi tiết công việc..." maxlength="1000">{{ old('description', $task->description) }}</textarea>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <small class="text-muted">Tối đa 1000 ký tự</small>
                        <small class="text-muted" id="descriptionCounter">0/1000</small>
                    </div>
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

                {{-- Recurring Task --}}
                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_recurring" id="is_recurring" value="1" 
                               {{ old('is_recurring', $task->is_recurring) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_recurring">
                            <i class="bi bi-arrow-repeat me-1"></i>Lặp lại công việc
                        </label>
                    </div>
                    <small class="form-text text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Công việc sẽ được tự động tạo lại với deadline mới mỗi {{ old('recurring_days', $task->recurring_days) ?: 'X' }} ngày sau khi hoàn thành
                    </small>
                    
                    {{-- Recurring Days Input --}}
                    <div id="recurring_days_section" class="mt-2 {{ old('is_recurring', $task->is_recurring) ? '' : 'd-none' }}">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="recurring_days" class="form-label">Số ngày lặp lại</label>
                                <input type="number" name="recurring_days" id="recurring_days" 
                                       class="form-control" min="1" max="365"
                                       value="{{ old('recurring_days', $task->recurring_days) }}" 
                                       placeholder="Ví dụ: 7 (mỗi tuần)">
                                <small class="form-text text-muted">Số ngày sau deadline cũ để tạo deadline mới</small>
                            </div>
                            <div class="col-md-6">
                                <label for="recurring_start_date" class="form-label">Ngày bắt đầu lặp lại</label>
                                <input type="date" name="recurring_start_date" id="recurring_start_date" 
                                       class="form-control"
                                       value="{{ old('recurring_start_date', $task->recurring_start_date ? $task->recurring_start_date->format('Y-m-d') : '') }}">
                                <small class="form-text text-muted">Ngày bắt đầu tính lặp lại (mặc định: ngày tạo task)</small>
                            </div>
                        </div>
                    </div>
                    
                    @if($task->is_recurring)
                        <div class="alert alert-info mt-2">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Trạng thái lặp lại:</strong> 
                            @if($task->recurring_start_date)
                                Bắt đầu từ: {{ $task->recurring_start_date->format('d/m/Y') }}
                                <br>
                            @endif
                            @if($task->last_reset_date)
                                Lần cuối reset: {{ \Carbon\Carbon::parse($task->last_reset_date)->format('d/m/Y H:i') }}
                                <br>Deadline tiếp theo: {{ $task->calculateNextDeadline()->format('d/m/Y H:i') }}
                            @else
                                Chưa được reset lần nào
                            @endif
                        </div>
                    @endif
                </div>



                {{-- Multi-Department Assignment --}}
                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-building me-1"></i>Phòng ban
                    </label>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="is_multi_department" id="is_multi_department" value="1" 
                               {{ old('is_multi_department', $task->is_multi_department) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_multi_department">
                            <i class="bi bi-diagram-3 me-1"></i>Giao việc cho nhiều phòng ban
                        </label>
                    </div>
                    
                    {{-- Single Department --}}
                    <div id="single_department_section" class="{{ old('is_multi_department', $task->is_multi_department) ? 'd-none' : '' }}">
                        <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror">
                            <option value="">Chọn phòng ban</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ old('department_id', $task->department_id) == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Multi-Department --}}
                    <div id="multi_department_section" class="{{ old('is_multi_department', $task->is_multi_department) ? '' : 'd-none' }}">
                        <div class="custom-dropdown">
                            <div class="dropdown-toggle" id="department_dropdown_toggle">
                                <span class="selected-text">Chọn phòng ban...</span>
                                <i class="bi bi-chevron-down"></i>
                            </div>
                            <div class="dropdown-menu" id="department_dropdown_menu">
                                @foreach($departments as $department)
                                    <div class="dropdown-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="department_ids[]" 
                                                   value="{{ $department->id }}" id="dept_{{ $department->id }}"
                                                   {{ in_array($department->id, old('department_ids', $task->departments->pluck('id')->toArray())) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="dept_{{ $department->id }}">
                                                {{ $department->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @error('department_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @error('department_ids')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Multi-User Assignment --}}
                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-people me-1"></i>Người phụ trách
                    </label>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="is_multi_user" id="is_multi_user" value="1" 
                               {{ old('is_multi_user', $task->assignees->count() > 0) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_multi_user">
                            <i class="bi bi-people-fill me-1"></i>Giao việc cho nhiều người
                        </label>
                    </div>
                    
                    {{-- Single User --}}
                    <div id="single_user_section" class="{{ old('is_multi_user', $task->assignees->count() > 0) ? 'd-none' : '' }}">
                        <div id="single_user_disabled" class="border rounded p-3 text-center" style="background: #f8f9fa;">
                            <i class="bi bi-exclamation-triangle text-warning fs-1 mb-2"></i>
                            <p class="mb-0">Vui lòng chọn phòng ban trước khi chọn người nhận</p>
                        </div>
                        <select name="assignee_id" id="assignee_id" class="form-select @error('assignee_id') is-invalid @enderror" style="display: none;">
                            <option value="">Chọn người phụ trách</option>
                            @foreach($users->groupBy('department_id') as $departmentId => $departmentUsers)
                                @php
                                    $department = $departmentUsers->first()->department;
                                    $departmentName = $department ? $department->name : 'Không có phòng ban';
                                @endphp
                                <optgroup label="{{ $departmentName }}" data-department="{{ $departmentId }}">
                                                                @php
                                $sortedUsers = $departmentUsers->sortBy(function($user) {
                                    $roleOrder = [
                                        'employee' => 1,
                                        'manager' => 2,
                                        'admin' => 3
                                    ];
                                    return $roleOrder[$user->role] ?? 999;
                                });
                            @endphp
                            @foreach($sortedUsers as $user)
                                @if($user)
                                    <option value="{{ $user->id }}" {{ old('assignee_id', $task->assignee_id) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name ?? 'Không có tên' }} - {{ ucfirst($user->role) }}
                                    </option>
                                @endif
                            @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Multi-User --}}
                    <div id="multi_user_section" class="{{ old('is_multi_user', $task->assignees->count() > 0) ? '' : 'd-none' }}">
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
                                    @php
                                        $sortedUsers = $departmentUsers->sortBy(function($user) {
                                            $roleOrder = [
                                                'employee' => 1,
                                                'manager' => 2,
                                                'admin' => 3
                                            ];
                                            return $roleOrder[$user->role] ?? 999;
                                        });
                                    @endphp
                                    @foreach($sortedUsers as $user)
                                        @if($user)
                                            @php
                                                $isFromOtherDept = auth()->user()->isManager() && 
                                                                  $user->department_id !== auth()->user()->department_id;
                                                $isAlreadyAssigned = in_array($user->id, old('assignee_ids', $task->assignees->pluck('id')->toArray()));
                                                $isDisabled = $isFromOtherDept && $isAlreadyAssigned;
                                            @endphp
                                            <div class="form-check mb-2 ms-3 user-option {{ $isFromOtherDept ? 'text-muted' : '' }}" 
                                                 data-department="{{ $user->department_id ?? '' }}">
                                                <input class="form-check-input" type="checkbox" name="assignee_ids[]" 
                                                       value="{{ $user->id }}" id="user_{{ $user->id }}"
                                                       {{ $isAlreadyAssigned ? 'checked' : '' }}
                                                       {{ $isDisabled ? 'disabled' : '' }}>
                                                <label class="form-check-label {{ $isDisabled ? 'text-muted' : '' }}" for="user_{{ $user->id }}">
                                                    {{ $user->name ?? 'Không có tên' }} - {{ ucfirst($user->role) }}
                                                    @if($isFromOtherDept)
                                                        <small class="text-muted">(Phòng ban khác)</small>
                                                    @endif
                                                    @if($isDisabled)
                                                        <i class="bi bi-lock-fill ms-1 text-muted"></i>
                                                    @endif
                                                </label>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @error('assignee_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @error('assignee_ids')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Deadline --}}
                <div class="form-group">
                    <label for="deadline" class="form-label">
                        <i class="bi bi-calendar-event me-1"></i>Hạn cuối
                    </label>
                    <input type="datetime-local" name="deadline" id="deadline" 
                           class="form-control @error('deadline') is-invalid @enderror"
                           value="{{ old('deadline', $task->deadline ? $task->deadline->format('Y-m-d\TH:i') : '') }}"
                           min="{{ now()->format('Y-m-d\TH:i') }}"
                           placeholder="dd/mm/yyyy --:--">
                    <small class="form-text text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Không thể chọn ngày giờ trong quá khứ
                    </small>
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
                              placeholder="Nhập lý do từ chối công việc..." maxlength="500">{{ old('rejection_reason', $task->rejection_reason) }}</textarea>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <small class="text-muted">Tối đa 500 ký tự</small>
                        <small class="text-muted" id="rejectionReasonCounter">0/500</small>
                    </div>
                    @error('rejection_reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Chọn Task Followers (chỉ Admin/Manager) --}}
                @if(auth()->user()->isAdmin() || auth()->user()->isDirector() || auth()->user()->isManager())
                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-people me-1"></i>Chọn Người Theo Dõi Công Việc
                    </label>
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
                                @php
                                    $sortedUsers = $departmentUsers->sortBy(function($user) {
                                        $roleOrder = [
                                            'employee' => 1,
                                            'manager' => 2,
                                            'admin' => 3
                                        ];
                                        return $roleOrder[$user->role] ?? 999;
                                    });
                                @endphp
                                @foreach($sortedUsers as $user)
                                    @if($user)
                                        <div class="form-check mb-2 ms-3 follower-option" data-department="{{ $user->department_id ?? '' }}" data-user-id="{{ $user->id }}" data-user-role="{{ $user->role }}">
                                            <input class="form-check-input" type="checkbox" name="followers[]" value="{{ $user->id }}" id="follower_{{ $user->id }}"
                                                   {{ in_array($user->id, old('followers', $task->followers->pluck('id')->toArray())) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="follower_{{ $user->id }}">
                                                {{ $user->name ?? 'Không có tên' }} - {{ ucfirst($user->role) }}
                                            </label>
                                        </div>
                                    @endif
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
                @endif

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

    // Validation for long words
    validateTextarea('description', 'descriptionCounter', 1000);
    validateTextarea('rejection_reason', 'rejectionReasonCounter', 500);

    // Multi-user and multi-department toggle
    const multiUserCheckbox = document.getElementById('is_multi_user');
    const singleUserSection = document.getElementById('single_user_section');
    const multiUserSection = document.getElementById('multi_user_section');

    const multiDepartmentCheckbox = document.getElementById('is_multi_department');
    const singleDepartmentSection = document.getElementById('single_department_section');
    const multiDepartmentSection = document.getElementById('multi_department_section');

    // Multi-user toggle
    if (multiUserCheckbox) {
        multiUserCheckbox.addEventListener('change', function() {
            if (this.checked) {
                singleUserSection.classList.add('d-none');
                multiUserSection.classList.remove('d-none');
                // Clear single user selection
                document.getElementById('assignee_id').value = '';
            } else {
                singleUserSection.classList.remove('d-none');
                multiUserSection.classList.add('d-none');
                // Clear multi user selections
                const checkboxes = multiUserSection.querySelectorAll('input[type="checkbox"]');
                checkboxes.forEach(cb => cb.checked = false);
                updateSelectedText('user');
            }
        });
    }

    // Multi-department toggle
    if (multiDepartmentCheckbox) {
        multiDepartmentCheckbox.addEventListener('change', function() {
            if (this.checked) {
                singleDepartmentSection.classList.add('d-none');
                multiDepartmentSection.classList.remove('d-none');
                // Clear single department selection
                document.getElementById('department_id').value = '';
            } else {
                singleDepartmentSection.classList.remove('d-none');
                multiDepartmentSection.classList.add('d-none');
                // Clear multi department selections
                const checkboxes = multiDepartmentSection.querySelectorAll('input[type="checkbox"]');
                checkboxes.forEach(cb => cb.checked = false);
                updateSelectedText('department');
            }
        });
    }

    // Custom dropdown functionality
    function initDropdowns() {
        // Department dropdown
        const deptToggle = document.getElementById('department_dropdown_toggle');
        const deptMenu = document.getElementById('department_dropdown_menu');
        
        if (deptToggle && deptMenu) {
            deptToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                deptToggle.classList.toggle('active');
                deptMenu.classList.toggle('show');
            });

            // Update selected text when checkboxes change
            const deptCheckboxes = deptMenu.querySelectorAll('input[type="checkbox"]');
            deptCheckboxes.forEach(cb => {
                cb.addEventListener('change', () => updateSelectedText('department'));
            });
        }

        // User dropdown
        const userToggle = document.getElementById('user_dropdown_toggle');
        const userMenu = document.getElementById('user_dropdown_menu');
        
        if (userToggle && userMenu) {
            userToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                userToggle.classList.toggle('active');
                userMenu.classList.toggle('show');
            });

            // Update selected text when checkboxes change
            const userCheckboxes = userMenu.querySelectorAll('input[type="checkbox"]');
            userCheckboxes.forEach(cb => {
                cb.addEventListener('change', () => updateSelectedText('user'));
            });
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.custom-dropdown')) {
                document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
                    toggle.classList.remove('active');
                });
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });
    }

    function updateSelectedText(type) {
        let toggle, checkboxes, placeholder;
        
        if (type === 'department') {
            toggle = document.getElementById('department_dropdown_toggle');
            checkboxes = document.querySelectorAll('#department_dropdown_menu input[type="checkbox"]:checked');
            placeholder = 'Chọn phòng ban...';
        } else if (type === 'user') {
            toggle = document.getElementById('user_dropdown_toggle');
            checkboxes = document.querySelectorAll('#user_dropdown_menu input[type="checkbox"]:checked');
            placeholder = 'Chọn người phụ trách...';
        }

        if (toggle && checkboxes) {
            const selectedText = toggle.querySelector('.selected-text');
            if (checkboxes.length === 0) {
                selectedText.textContent = placeholder;
            } else if (checkboxes.length === 1) {
                const label = checkboxes[0].nextElementSibling.textContent.trim();
                selectedText.textContent = label;
            } else {
                selectedText.textContent = `Đã chọn ${checkboxes.length} mục`;
            }
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
        const assigneeSelect = document.getElementById('assignee_id');

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
        const singleUserSelect = document.getElementById('assignee_id');
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

    // Initialize dropdowns
    initDropdowns();
});

// Function to validate textarea and prevent long words
function validateTextarea(textareaId, counterId, maxLength) {
    const textarea = document.getElementById(textareaId);
    const counter = document.getElementById(counterId);
    const submitBtn = document.querySelector('.btn-submit');
    
    if (textarea && counter) {
        // Update counter on input
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
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>Từ quá dài (>45 ký tự)';
                    submitBtn.classList.remove('btn-submit');
                    submitBtn.classList.add('btn-danger');
                }
            } else {
                this.style.borderColor = '';
                this.style.backgroundColor = '';
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Cập nhật công việc';
                    submitBtn.classList.remove('btn-danger');
                    submitBtn.classList.add('btn-submit');
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
}

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

// Validation cho deadline
document.addEventListener('DOMContentLoaded', function() {
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
});

// Xử lý recurring task checkbox
document.addEventListener('DOMContentLoaded', function() {
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
@endsection
