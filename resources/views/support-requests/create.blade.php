@extends('layouts.master')

@push('styles')
<style>
.department-group {
    border-left: 3px solid #0d6efd;
    padding-left: 15px;
    margin-bottom: 20px;
}

.department-group h6 {
    font-weight: 600;
    margin-bottom: 10px;
}

.recipient-item {
    transition: all 0.2s ease;
    padding: 5px;
    border-radius: 4px;
}

.recipient-item:hover {
    background-color: #f8f9fa;
}

.recipient-item .form-check-label {
    cursor: pointer;
    width: 100%;
}

.recipient-item .form-check-input:checked + .form-check-label {
    color: #0d6efd;
    font-weight: 500;
}

#recipientsContainer {
    border: 2px solid #dee2e6;
    transition: border-color 0.3s ease;
}

#recipientsContainer.border-success {
    border-color: #198754;
    background-color: #f8fff9;
}

#recipientsContainer.border-danger {
    border-color: #dc3545;
    background-color: #fff8f8;
}

#recipientSearch {
    border: 1px solid #ced4da;
    border-radius: 6px;
}

#recipientSearch:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

#selectAllBtn {
    border-radius: 6px;
    font-size: 0.875rem;
}

.badge {
    font-size: 0.75rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 mb-0">
                    {{ __('Tạo yêu cầu hỗ trợ') }}
                </h2>
                <a href="{{ route('support-requests.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Quay lại danh sách
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('support-requests.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <!-- Tiêu đề -->
                            <div class="col-12 mb-3">
                                <label for="title" class="form-label">
                                    Tiêu đề <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="title" id="title" 
                                       value="{{ old('title') }}"
                                       class="form-control @error('title') is-invalid @enderror"
                                       required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Mô tả -->
                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">
                                    Mô tả chi tiết
                                </label>
                                <textarea name="description" id="description" rows="4"
                                          class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Người nhận yêu cầu -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    Người nhận yêu cầu <span class="text-danger">*</span>
                                </label>
                                
                                <!-- Tìm kiếm -->
                                <div class="mb-2">
                                    <input type="text" id="recipientSearch" class="form-control form-control-sm" 
                                           placeholder="Tìm kiếm theo tên...">
                                </div>
                                
                                <!-- Chọn tất cả -->
                                <div class="mb-2">
                                    <button type="button" id="selectAllBtn" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-check-square me-1"></i>Chọn tất cả
                                    </button>
                                </div>
                                
                                <div class="border rounded p-3 @error('recipients') border-danger @enderror" 
                                     style="max-height: 300px; overflow-y: auto;" id="recipientsContainer">
                                    @if($managers->count() > 0)
                                        @php
                                            $groupedManagers = $managers->groupBy('department.name');
                                        @endphp
                                        @foreach($groupedManagers as $departmentName => $departmentManagers)
                                            <div class="department-group mb-3" data-department="{{ $departmentName }}">
                                                <h6 class="text-primary mb-2 border-bottom pb-1">
                                                    <i class="bi bi-building me-2"></i>{{ $departmentName ?? 'Chưa phân phòng ban' }}
                                                    <small class="text-muted">({{ $departmentManagers->count() }} người)</small>
                                                </h6>
                                                @foreach($departmentManagers as $manager)
                                                    <div class="form-check mb-2 recipient-item" data-name="{{ strtolower($manager->name) }}">
                                                        <input type="checkbox" 
                                                               name="recipients[]" 
                                                               value="{{ $manager->id }}" 
                                                               id="recipient_{{ $manager->id }}"
                                                               class="form-check-input @error('recipients') is-invalid @enderror"
                                                               {{ in_array($manager->id, old('recipients', [])) ? 'checked' : '' }}>
                                                        <label for="recipient_{{ $manager->id }}" class="form-check-label">
                                                            <strong>{{ $manager->name }}</strong>
                                                            <span class="badge bg-{{ $manager->role === 'manager' ? 'warning' : 'info' }} ms-1">
                                                                @if($manager->role === 'manager')
                                                                    Quản lý
                                                                @elseif($manager->role === 'director')
                                                                    Giám đốc
                                                                @else
                                                                    {{ $manager->display_role }}
                                                                @endif
                                                            </span>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="text-muted text-center py-3">
                                            <i class="bi bi-exclamation-circle me-2"></i>
                                            Không có người nhận phù hợp
                                        </div>
                                    @endif
                                </div>
                                <div class="form-text">
                                    @if(Auth::user()->isEmployee())
                                        Có thể chọn Quản lý trong phòng ban hoặc Giám đốc quản lý phòng ban.
                                    @elseif(Auth::user()->isManager())
                                        Có thể chọn Quản lý từ phòng ban khác hoặc Giám đốc quản lý phòng ban của bạn.
                                    @elseif(Auth::user()->isDirector())
                                        Có thể chọn Quản lý của các phòng ban được quản lý.
                                    @endif
                                </div>
                                @error('recipients')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Độ ưu tiên -->
                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">
                                    Độ ưu tiên <span class="text-danger">*</span>
                                </label>
                                <select name="priority" id="priority" 
                                        class="form-select @error('priority') is-invalid @enderror"
                                        required>
                                    <option value="">Chọn độ ưu tiên</option>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Thấp</option>
                                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Trung bình</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>Cao</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Deadline -->
                            <div class="col-md-6 mb-3">
                                <label for="deadline" class="form-label">
                                    Hạn cuối
                                </label>
                                <input type="date" name="deadline" id="deadline" 
                                       value="{{ old('deadline') }}"
                                       class="form-control @error('deadline') is-invalid @enderror">
                                @error('deadline')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Khẩn cấp -->
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_urgent" id="is_urgent" 
                                           value="1" {{ old('is_urgent') ? 'checked' : '' }}
                                           class="form-check-input">
                                    <label for="is_urgent" class="form-check-label">
                                        Yêu cầu khẩn cấp
                                    </label>
                                </div>
                            </div>

                            <!-- File đính kèm -->
                            <div class="col-12 mb-3">
                                <label for="files" class="form-label">
                                    File đính kèm
                                </label>
                                <input type="file" name="files[]" id="files" 
                                       multiple
                                       class="form-control @error('files.*') is-invalid @enderror"
                                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.webp">
                                <div class="form-text">
                                    Có thể chọn nhiều file. Định dạng: PDF, DOC, XLS, PPT, JPG, PNG, GIF. Tối đa 50MB mỗi file.
                                </div>
                                @error('files.*')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('support-requests.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-2"></i>Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Tạo yêu cầu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const checkboxes = document.querySelectorAll('input[name="recipients[]"]');
    const searchInput = document.getElementById('recipientSearch');
    const selectAllBtn = document.getElementById('selectAllBtn');
    const container = document.getElementById('recipientsContainer');
    
    // Validate form submission
    form.addEventListener('submit', function(e) {
        const checkedBoxes = document.querySelectorAll('input[name="recipients[]"]:checked');
        
        if (checkedBoxes.length === 0) {
            e.preventDefault();
            alert('Vui lòng chọn ít nhất một người nhận yêu cầu.');
            return false;
        }
    });
    
    // Add visual feedback for checkbox selection
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            updateVisualFeedback();
            updateSelectAllButton();
        });
    });
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const recipientItems = document.querySelectorAll('.recipient-item');
        const departmentGroups = document.querySelectorAll('.department-group');
        
        recipientItems.forEach(function(item) {
            const name = item.getAttribute('data-name');
            if (name.includes(searchTerm)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
        
        // Hide/show department groups based on visible items
        departmentGroups.forEach(function(group) {
            const visibleItems = group.querySelectorAll('.recipient-item[style*="block"], .recipient-item:not([style*="none"])');
            if (visibleItems.length === 0) {
                group.style.display = 'none';
            } else {
                group.style.display = 'block';
            }
        });
    });
    
    // Select all / Deselect all functionality
    let allSelected = false;
    selectAllBtn.addEventListener('click', function() {
        const visibleCheckboxes = document.querySelectorAll('input[name="recipients[]"]:not([style*="none"])');
        
        visibleCheckboxes.forEach(function(checkbox) {
            checkbox.checked = !allSelected;
        });
        
        allSelected = !allSelected;
        selectAllBtn.innerHTML = allSelected ? 
            '<i class="bi bi-square me-1"></i>Bỏ chọn tất cả' : 
            '<i class="bi bi-check-square me-1"></i>Chọn tất cả';
        
        // Trigger change events
        visibleCheckboxes.forEach(function(checkbox) {
            checkbox.dispatchEvent(new Event('change'));
        });
    });
    
    // Update visual feedback
    function updateVisualFeedback() {
        const checkedCount = document.querySelectorAll('input[name="recipients[]"]:checked').length;
        
        if (checkedCount > 0) {
            container.classList.remove('border-danger');
            container.classList.add('border-success');
        } else {
            container.classList.remove('border-success');
            container.classList.add('border-danger');
        }
    }
    
    // Update select all button state
    function updateSelectAllButton() {
        const visibleCheckboxes = document.querySelectorAll('input[name="recipients[]"]:not([style*="none"])');
        const checkedVisibleCheckboxes = document.querySelectorAll('input[name="recipients[]"]:checked:not([style*="none"])');
        
        if (visibleCheckboxes.length === 0) {
            selectAllBtn.style.display = 'none';
            return;
        }
        
        selectAllBtn.style.display = 'inline-block';
        
        if (checkedVisibleCheckboxes.length === visibleCheckboxes.length) {
            allSelected = true;
            selectAllBtn.innerHTML = '<i class="bi bi-square me-1"></i>Bỏ chọn tất cả';
        } else {
            allSelected = false;
            selectAllBtn.innerHTML = '<i class="bi bi-check-square me-1"></i>Chọn tất cả';
        }
    }
    
    // Initialize
    updateVisualFeedback();
    updateSelectAllButton();
});
</script>
@endpush
