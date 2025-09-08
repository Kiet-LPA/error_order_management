@extends('layouts.master')

@section('title', 'Nhân viên mới')

@push('styles')
<style>
.card-header.bg-light {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    border-bottom: 2px solid #dee2e6;
}

.form-label.small {
    font-size: 0.75rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.25rem;
}

.form-control-sm, .form-select-sm {
    font-size: 0.875rem;
    padding: 0.375rem 0.5rem;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}

.table td {
    font-size: 0.875rem;
    vertical-align: middle;
}

.badge {
    font-size: 0.75rem;
}

.btn-group .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.avatar-sm {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 50%;
    color: #6c757d;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="bi bi-person-plus me-2"></i>
                    Danh sách nhân viên mới
                </h2>
                <a href="{{ route('employees.new.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Thêm nhân viên mới
                </a>
            </div>


            <div class="card shadow-sm">
                <!-- Header với bộ lọc -->
                <div class="card-header bg-light border-bottom">
                    <form method="GET" action="{{ route('employees.new.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label for="search" class="form-label small mb-1">Tìm kiếm</label>
                            <input type="text" name="search" id="search" class="form-control form-control-sm" 
                                   value="{{ request('search') }}" 
                                   placeholder="Tên, email, SĐT...">
                        </div>
                        
                        <div class="col-md-2">
                            <label for="department" class="form-label small mb-1">Phòng ban</label>
                            <select name="department" id="department" class="form-select form-select-sm">
                                <option value="">Tất cả</option>
                                @foreach(\App\Models\Department::orderBy('name')->get() as $dept)
                                    <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="status" class="form-label small mb-1">Trạng thái</label>
                            <select name="status" id="status" class="form-select form-select-sm">
                                <option value="">Tất cả</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang thử việc</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                                <option value="terminated" {{ request('status') == 'terminated' ? 'selected' : '' }}>Đã chấm dứt</option>
                                <option value="no_contract" {{ request('status') == 'no_contract' ? 'selected' : '' }}>Chưa có hợp đồng</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-search me-1"></i>Tìm
                            </button>
                            <a href="{{ route('employees.new.index') }}" class="btn btn-outline-secondary btn-sm ms-1">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                        </div>
                        
                        <div class="col-md-3 text-end">
                            <small class="text-muted">
                                Tổng: <strong>{{ $newEmployees->total() }}</strong> nhân viên
                            </small>
                        </div>
                    </form>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Họ tên</th>
                                    <th>Email</th>
                                    <th>Số điện thoại</th>
                                    <th>Phòng ban</th>
                                    <th>Lương thử việc</th>
                                    <th>Thời gian thử việc</th>
                                    <th>Ngày bắt đầu</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($newEmployees as $employee)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <i class="bi bi-person-circle fs-4"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ $employee->name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($employee->email)
                                                <a href="mailto:{{ $employee->email }}">{{ $employee->email }}</a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($employee->phone)
                                                <a href="tel:{{ $employee->phone }}">{{ $employee->phone }}</a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($employee->department)
                                                <span class="badge bg-secondary">{{ $employee->department->name }}</span>
                                            @else
                                                <span class="text-muted">Chưa phân công</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($employee->contracts->isNotEmpty())
                                                @php $activeContract = $employee->contracts->first(); @endphp
                                                {{ number_format($activeContract->probation_salary) }} VNĐ
                                            @else
                                                <span class="text-muted">Chưa có</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($employee->contracts->isNotEmpty())
                                                @php $activeContract = $employee->contracts->first(); @endphp
                                                {{ $activeContract->probation_period }} tháng
                                            @else
                                                <span class="text-muted">Chưa có</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($employee->contracts->isNotEmpty())
                                                @php $activeContract = $employee->contracts->first(); @endphp
                                                {{ $activeContract->start_date->format('d/m/Y') }}
                                            @else
                                                <span class="text-muted">Chưa có</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($employee->contracts->isNotEmpty())
                                                @php $activeContract = $employee->contracts->first(); @endphp
                                                @switch($activeContract->status)
                                                    @case('active')
                                                        <span class="badge bg-success">Đang thử việc</span>
                                                        @break
                                                    @case('completed')
                                                        <span class="badge bg-info">Hoàn thành</span>
                                                        @break
                                                    @case('terminated')
                                                        <span class="badge bg-danger">Đã chấm dứt</span>
                                                        @break
                                                @endswitch
                                            @else
                                                <span class="badge bg-warning">Chưa có hợp đồng</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('users.show', $employee) }}" class="btn btn-sm btn-outline-info" 
                                                   title="Xem chi tiết">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('employees.new.edit', $employee) }}" class="btn btn-sm btn-outline-warning"
                                                   title="Chỉnh sửa">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @php
                                                    $hasActiveContract = $employee->contracts->isNotEmpty() && $employee->contracts->first()->status == 'active';
                                                @endphp
                                                @if($hasActiveContract)
                                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                                            data-bs-toggle="modal" data-bs-target="#convertModal{{ $employee->id }}"
                                                            title="Chuyển thành chính thức"
                                                            onclick="console.log('Opening modal for employee {{ $employee->id }}')">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                @else
                                                    <!-- Debug info -->
                                                    <small class="text-muted">
                                                        @if($employee->contracts->isEmpty())
                                                            No contracts
                                                        @else
                                                            Contract: {{ $employee->contracts->first()->status }}
                                                        @endif
                                                    </small>
                                                @endif
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteEmployee({{ $employee->id }}, '{{ $employee->name }}')"
                                                        title="Xóa nhân viên">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                Chưa có nhân viên mới nào
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Pagination -->
            @if($newEmployees->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            {{-- Previous Page Link --}}
                            @if ($newEmployees->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link">« Trước</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $newEmployees->previousPageUrl() }}" rel="prev">« Trước</a>
                                </li>
                            @endif

                            {{-- Pagination Elements --}}
                            @foreach ($newEmployees->getUrlRange(1, $newEmployees->lastPage()) as $page => $url)
                                @if ($page == $newEmployees->currentPage())
                                    <li class="page-item active">
                                        <span class="page-link">{{ $page }}</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                    </li>
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($newEmployees->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $newEmployees->nextPageUrl() }}" rel="next">Sau »</a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <span class="page-link">Sau »</span>
                                </li>
                            @endif
                        </ul>
                    </nav>
                    
                    {{-- Page Info --}}
                    <div class="text-center mt-2">
                        <small class="text-muted">
                            Hiển thị {{ $newEmployees->firstItem() ?? 0 }} đến {{ $newEmployees->lastItem() ?? 0 }} trong tổng số {{ $newEmployees->total() }} kết quả
                        </small>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modals for converting employees -->
@php
    $modalCount = 0;
@endphp
@foreach($newEmployees as $employee)
    @if($employee->contracts->isNotEmpty() && $employee->contracts->first()->status == 'active')
    @php $modalCount++; @endphp
    <!-- Modal for employee {{ $employee->id }} (Modal #{{ $modalCount }}) -->
    <div class="modal fade" id="convertModal{{ $employee->id }}" tabindex="-1" aria-labelledby="convertModalLabel{{ $employee->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="convertModalLabel{{ $employee->id }}">
                        Chuyển nhân viên thành chính thức
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('employees.convert', $employee) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Bạn đang chuyển <strong>{{ $employee->name }}</strong> từ nhân viên thử việc thành nhân viên chính thức.</p>
                        
                        <div class="alert alert-info">
                            <small>
                                <i class="bi bi-info-circle me-1"></i>
                                <strong>Vai trò hiện tại:</strong> {{ ucfirst($employee->role ?? 'employee') }}
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="role{{ $employee->id }}" class="form-label">Vai trò <span class="text-danger">*</span></label>
                            <select class="form-select" id="role{{ $employee->id }}" name="role" required>
                                <option value="">Chọn vai trò</option>
                                <option value="employee" {{ ($employee->role ?? 'employee') == 'employee' ? 'selected' : '' }}>Nhân viên</option>
                                <option value="manager" {{ ($employee->role ?? 'employee') == 'manager' ? 'selected' : '' }}>Quản lý</option>
                                @if(auth()->user()->isAdmin() || auth()->user()->isDirector())
                                    <option value="admin" {{ ($employee->role ?? 'employee') == 'admin' ? 'selected' : '' }}>Quản trị viên</option>
                                @endif
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="official_salary{{ $employee->id }}" class="form-label">Lương chính thức (VNĐ) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="official_salary{{ $employee->id }}" name="official_salary" 
                                   min="0" step="1000000" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="official_start_date{{ $employee->id }}" class="form-label">Ngày bắt đầu làm chính thức <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="official_start_date{{ $employee->id }}" name="official_start_date" 
                                   value="{{ date('Y-m-d') }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="contract_duration{{ $employee->id }}" class="form-label">Hợp đồng trong bao lâu (tháng) <span class="text-danger">*</span></label>
                            <select class="form-select" id="contract_duration{{ $employee->id }}" name="contract_duration" required>
                                <option value="">Chọn thời gian</option>
                                <option value="12">12 tháng</option>
                                <option value="24">24 tháng</option>
                                <option value="36">36 tháng</option>
                                <option value="48">48 tháng</option>
                                <option value="60">60 tháng</option>
                            </select>
                        </div>
                        
                        <div class="alert alert-info">
                            <small>
                                <i class="bi bi-info-circle me-1"></i>
                                Lương thử việc hiện tại: {{ $employee->contracts->isNotEmpty() ? number_format($employee->contracts->first()->probation_salary) : 'N/A' }} VNĐ
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i>Chuyển đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach

@endsection

@push('scripts')
<script>
// Function xóa nhân viên
function deleteEmployee(employeeId, employeeName) {
    if (confirm(`Bạn có chắc chắn muốn xóa nhân viên "${employeeName}"?\n\nHành động này không thể hoàn tác và sẽ xóa tất cả dữ liệu liên quan.`)) {
        // Tạo form để gửi DELETE request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/employees/new/${employeeId}`;
        
        // Thêm CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Thêm method override
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        // Thêm form vào body và submit
        document.body.appendChild(form);
        form.submit();
    }
}

// Auto-submit form khi thay đổi filter
document.addEventListener('DOMContentLoaded', function() {
    const departmentSelect = document.getElementById('department');
    const statusSelect = document.getElementById('status');
    
    if (departmentSelect) {
        departmentSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }
    
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }
});

// Debug function
function debugModals() {
    console.log('=== DEBUG MODALS ===');
    const modals = document.querySelectorAll('[id^="convertModal"]');
    console.log('Found modals:', modals.length);
    modals.forEach((modal, index) => {
        console.log(`Modal ${index + 1}:`, modal.id);
    });
    
    const buttons = document.querySelectorAll('[data-bs-target^="#convertModal"]');
    console.log('Found buttons:', buttons.length);
    buttons.forEach((button, index) => {
        console.log(`Button ${index + 1}:`, button.getAttribute('data-bs-target'));
    });
}

// Run debug on page load
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(debugModals, 1000);
});
</script>

@if(config('app.debug'))
<div class="mt-4 p-3 bg-light border rounded">
    <h6>Debug Info:</h6>
    <p>Total employees: {{ $newEmployees->count() }}</p>
    <p>Modals created: {{ $modalCount }}</p>
    <p>Employees with active contracts:</p>
    <ul>
        @foreach($newEmployees as $employee)
            @if($employee->contracts->isNotEmpty() && $employee->contracts->first()->status == 'active')
                <li>Employee {{ $employee->id }}: {{ $employee->name }} (Contract status: {{ $employee->contracts->first()->status }})</li>
            @endif
        @endforeach
    </ul>
</div>
@endif

<!-- Alert for approval process -->
<div class="mt-4 alert alert-info">
    <h6><i class="bi bi-info-circle me-2"></i>Hướng dẫn duyệt tài khoản:</h6>
    <ol class="mb-0">
        <li><strong>Tìm nhân viên</strong> có nút <span class="badge bg-success"><i class="bi bi-check-circle"></i></span> (màu xanh)</li>
        <li><strong>Click nút xanh</strong> để mở modal "Chuyển nhân viên thành chính thức"</li>
        <li><strong>Điền thông tin:</strong> Vai trò, Lương chính thức, Ngày bắt đầu, Thời hạn hợp đồng</li>
        <li><strong>Submit</strong> để duyệt tài khoản</li>
    </ol>
    <p class="mb-0 mt-2"><small class="text-muted">Nếu không thấy nút xanh, nhân viên chưa có hợp đồng active hoặc cần refresh trang (Ctrl+F5)</small></p>
</div>

@endpush
