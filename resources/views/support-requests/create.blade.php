@extends('layouts.master')

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
                                <div class="border rounded p-3 @error('recipients') border-danger @enderror" style="max-height: 200px; overflow-y: auto;">
                                    @if($managers->count() > 0)
                                        @foreach($managers as $manager)
                                            <div class="form-check mb-2">
                                                <input type="checkbox" 
                                                       name="recipients[]" 
                                                       value="{{ $manager->id }}" 
                                                       id="recipient_{{ $manager->id }}"
                                                       class="form-check-input @error('recipients') is-invalid @enderror"
                                                       {{ in_array($manager->id, old('recipients', [])) ? 'checked' : '' }}>
                                                <label for="recipient_{{ $manager->id }}" class="form-check-label">
                                                    <strong>{{ $manager->name }}</strong>
                                                    <span class="badge bg-{{ $manager->role === 'manager' ? 'warning' : 'info' }} ms-1">
                                                        {{ $manager->role === 'manager' ? 'Manager' : 'Director' }}
                                                    </span>
                                                    <br>
                                                    <small class="text-muted">{{ $manager->department->name ?? 'N/A' }}</small>
                                                </label>
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
                                        Có thể chọn Manager trong phòng ban hoặc Director quản lý phòng ban.
                                    @elseif(Auth::user()->isManager())
                                        Có thể chọn Manager từ phòng ban khác hoặc Director quản lý phòng ban của bạn.
                                    @elseif(Auth::user()->isDirector())
                                        Có thể chọn Manager của các phòng ban được quản lý.
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
                                    Deadline
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
            const checkedCount = document.querySelectorAll('input[name="recipients[]"]:checked').length;
            const container = document.querySelector('.border.rounded.p-3');
            
            if (checkedCount > 0) {
                container.classList.remove('border-danger');
                container.classList.add('border-success');
            } else {
                container.classList.remove('border-success');
                container.classList.add('border-danger');
            }
        });
    });
    
    // Select all / Deselect all functionality
    const selectAllBtn = document.createElement('button');
    selectAllBtn.type = 'button';
    selectAllBtn.className = 'btn btn-sm btn-outline-primary mb-2';
    selectAllBtn.innerHTML = '<i class="bi bi-check-square me-1"></i>Chọn tất cả';
    
    const container = document.querySelector('.border.rounded.p-3');
    container.parentNode.insertBefore(selectAllBtn, container);
    
    let allSelected = false;
    selectAllBtn.addEventListener('click', function() {
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = !allSelected;
        });
        allSelected = !allSelected;
        selectAllBtn.innerHTML = allSelected ? 
            '<i class="bi bi-square me-1"></i>Bỏ chọn tất cả' : 
            '<i class="bi bi-check-square me-1"></i>Chọn tất cả';
        
        // Trigger change event
        checkboxes[0].dispatchEvent(new Event('change'));
    });
});
</script>
@endpush
