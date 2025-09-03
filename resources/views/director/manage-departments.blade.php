@extends('layouts.master')

@section('title', 'Quản lý phòng ban')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="bi bi-building-gear me-2"></i>
                    Quản lý phòng ban
                </h2>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-gear me-2"></i>
                        Cấu hình quản lý phòng ban
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('director.manage-departments') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Hướng dẫn:</strong>
                            <ul class="mb-0 mt-2">
                                <li><strong>Không chọn phòng ban nào:</strong> Bạn sẽ quản lý tất cả phòng ban (mặc định)</li>
                                <li><strong>Chọn một hoặc nhiều phòng ban:</strong> Bạn chỉ quản lý những phòng ban được chọn</li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">
                                <strong>Chọn phòng ban quản lý:</strong>
                            </label>
                            <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                @if($departments->count() > 0)
                                    @foreach($departments as $department)
                                        <div class="form-check mb-2">
                                            <input type="checkbox" 
                                                   name="departments[]" 
                                                   value="{{ $department->id }}" 
                                                   id="department_{{ $department->id }}"
                                                   class="form-check-input"
                                                   {{ in_array($department->id, $managedDepartmentIds) ? 'checked' : '' }}>
                                            <label for="department_{{ $department->id }}" class="form-check-label">
                                                <strong>{{ $department->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $department->code }} - {{ $department->description ?? 'Không có mô tả' }}</small>
                                            </label>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-muted text-center py-3">
                                        <i class="bi bi-exclamation-circle me-2"></i>
                                        Không có phòng ban nào
                                    </div>
                                @endif
                            </div>
                            <div class="form-text">
                                <i class="bi bi-lightbulb me-1"></i>
                                Bỏ trống tất cả để quản lý tất cả phòng ban, hoặc chọn cụ thể những phòng ban bạn muốn quản lý.
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Cập nhật
                            </button>
                            <a href="{{ route('support-requests.quest-detail') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Hiển thị trạng thái hiện tại -->
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Trạng thái hiện tại
                    </h5>
                </div>
                <div class="card-body">
                    @if($managedDepartmentIds->isEmpty())
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>Bạn đang quản lý tất cả phòng ban</strong>
                            <p class="mb-0 mt-2">Hiện tại bạn có quyền quản lý tất cả {{ $departments->count() }} phòng ban trong hệ thống.</p>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="bi bi-gear me-2"></i>
                            <strong>Bạn đang quản lý {{ $managedDepartmentIds->count() }} phòng ban cụ thể:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($managedDepartmentIds as $deptId)
                                    @php
                                        $dept = $departments->find($deptId);
                                    @endphp
                                    @if($dept)
                                        <li><strong>{{ $dept->name }}</strong> ({{ $dept->code }})</li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const checkboxes = document.querySelectorAll('input[name="departments[]"]');
    
    // Validate form submission
    form.addEventListener('submit', function(e) {
        // Không cần validate vì có thể bỏ trống (quản lý tất cả)
        // Form sẽ submit bình thường
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
    });
});
</script>
@endpush
@endsection
