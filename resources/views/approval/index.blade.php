@extends('layouts.master')

@section('title', 'Hệ thống phê duyệt đề xuất')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-clipboard-check fs-1 text-primary me-3"></i>
                    <div>
                        <h2 class="mb-0">Hệ thống phê duyệt đề xuất</h2>
                        <p class="text-muted mb-0">Quản lý và theo dõi các đề xuất công việc</p>
                    </div>
                </div>
                <a href="{{ route('approval.create', 'payment_request') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Tạo đề xuất mới
                </a>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $myRequests->count() }}</h4>
                                    <p class="card-text">Đề xuất của tôi</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-person-lines-fill fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $pendingApprovals->count() }}</h4>
                                    <p class="card-text">Chờ phê duyệt</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-clock-history fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $allRequests->count() }}</h4>
                                    <p class="card-text">Tất cả đề xuất</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-list-ul fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Bộ lọc</h6>
                </div>
                <div class="card-body">
                    <form id="filterForm" method="GET" action="{{ route('approval.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="status" class="form-label">Trạng thái</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">Tất cả</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ phê duyệt</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã phê duyệt</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Đã từ chối</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                                    <option value="forwarded" {{ request('status') == 'forwarded' ? 'selected' : '' }}>Đã chuyển tiếp</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="form_type" class="form-label">Loại đề xuất</label>
                                <input type="text" class="form-control" id="form_type" name="form_type" 
                                       value="{{ request('form_type') }}" 
                                       placeholder="VD: Đề xuất thanh toán, Yêu cầu mua sắm...">
                            </div>
                            <div class="col-md-3">
                                <label for="from_date" class="form-label">Từ ngày</label>
                                <input type="date" class="form-control" id="from_date" name="from_date" 
                                       value="{{ request('from_date') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="to_date" class="form-label">Đến ngày</label>
                                <input type="date" class="form-control" id="to_date" name="to_date" 
                                       value="{{ request('to_date') }}">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-funnel me-1"></i>Áp dụng bộ lọc
                                </button>
                                <a href="{{ route('approval.index') }}" class="btn btn-outline-secondary ms-2">
                                    <i class="bi bi-x-circle me-1"></i>Xóa bộ lọc
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="refreshData()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Làm mới
                    </button>
                    <!-- <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportData()">
                        <i class="bi bi-download me-1"></i>Xuất Excel
                    </button> -->
                </div>
                {{-- Chỉ hiển thị nút bulk actions cho manager/director --}}
                @if(in_array(auth()->user()->role, ['manager', 'director']))
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="bulkApprove()">
                            <i class="bi bi-check-circle me-1"></i>Phê duyệt hàng loạt
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="bulkReject()">
                            <i class="bi bi-x-circle me-1"></i>Từ chối hàng loạt
                        </button>
                    </div>
                @endif
            </div>

            <!-- Results Summary -->
            @if(request()->hasAny(['status', 'form_type', 'from_date', 'to_date']))
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Kết quả lọc:</strong>
                    <span class="me-3">Đề xuất của tôi: {{ $myRequests->count() }}</span>
                    <span class="me-3">Chờ phê duyệt: {{ $pendingApprovals->count() }}</span>
                    <span>Tất cả đề xuất: {{ $allRequests->count() }}</span>
                    <a href="{{ route('approval.index') }}" class="btn btn-sm btn-outline-secondary ms-3">
                        <i class="bi bi-x-circle me-1"></i>Xóa bộ lọc
                    </a>
                </div>
            @endif

            <!-- Main Table -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Danh sách đề xuất</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="approvalTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" class="form-check-input">
                                    </th>
                                    <th width="8%">Mã</th>
                                    <th width="20%">Loại đề xuất</th>
                                    <th width="15%">Trạng thái</th>
                                    <th width="15%">Người gửi</th>
                                    <th width="15%">Người phê duyệt</th>
                                    <th width="12%">Ngày tạo</th>
                                    <th width="10%">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($allRequests as $request)
                                    <tr data-request-id="{{ $request->id }}">
                                        <td>
                                            <input type="checkbox" class="form-check-input request-checkbox" value="{{ $request->id }}" data-status="{{ $request->approval_status }}">
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">#{{ $request->id }}</span>
                                        </td>
                                        <td>
                                            <div>
                                                @if(isset($request->form_data['title']))
                                                    <strong class="text-dark">{{ $request->form_data['title'] }}</strong>
                                                @elseif($request->approvalForm)
                                                    <strong class="text-dark">{{ $request->approvalForm->form_name }}</strong>
                                                @else
                                                    <strong class="text-dark">{{ $request->form_type }}</strong>
                                                @endif
                                                <br>
                                                <small class="text-muted">
                                                    @if($request->approvalForm)
                                                        {{ $request->approvalForm->form_name }}
                                                    @else
                                                        {{ $request->form_type }}
                                                    @endif
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $statusClass = match($request->approval_status) {
                                                    'approved' => 'success',
                                                    'rejected' => 'danger',
                                                    'pending' => 'warning',
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $statusClass }}">
                                                {{ $request->getApprovalStatusText() }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($request->creator)
                                                <div class="d-flex align-items-center">
                                                    <div class="me-2">
                                                        <img src="{{ $request->creator->avatar_url }}" 
                                                             alt="{{ $request->creator->name }}" 
                                                             class="rounded-circle" 
                                                             style="width: 32px; height: 32px; object-fit: cover; border: 2px solid #e9ecef;">
                                                    </div>
                                                    <div>
                                                        <strong class="text-dark">{{ $request->creator->name }}</strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            @php
                                                                $roleText = match($request->creator->role) {
                                                                    'admin' => 'Quản trị viên',
                                                                    'director' => 'Giám đốc',
                                                                    'manager' => 'Quản lý',
                                                                    'employee' => 'Nhân viên',
                                                                    default => ucfirst($request->creator->role)
                                                                };
                                                            @endphp
                                                            {{ $roleText }}
                                                        </small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">Không xác định</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($request->currentApprover)
                                                <div class="d-flex align-items-center">
                                                    <div class="me-2">
                                                        <img src="{{ $request->currentApprover->avatar_url }}" 
                                                             alt="{{ $request->currentApprover->name }}" 
                                                             class="rounded-circle" 
                                                             style="width: 32px; height: 32px; object-fit: cover; border: 2px solid #e9ecef;">
                                                    </div>
                                                    <div>
                                                        <strong class="text-dark">{{ $request->currentApprover->name }}</strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            @php
                                                                $approverRoleText = match($request->currentApprover->role) {
                                                                    'admin' => 'Quản trị viên',
                                                                    'director' => 'Giám đốc',
                                                                    'manager' => 'Quản lý',
                                                                    'employee' => 'Nhân viên',
                                                                    default => ucfirst($request->currentApprover->role)
                                                                };
                                                            @endphp
                                                            {{ $approverRoleText }}
                                                        </small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">Chưa xác định</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>
                                                <strong class="text-dark">{{ $request->created_at->format('d/m/Y') }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $request->created_at->format('H:i') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group-vertical btn-group-sm" role="group">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('approval.show', $request->id) }}" class="btn btn-outline-info btn-sm" title="Xem chi tiết">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    @if($request->canEdit(auth()->id()))
                                                        @php
                                                            $creator = $request->creator;
                                                            $canShowEdit = true;
                                                            if (auth()->user()->isDirector() && $creator && $creator->isAdmin()) {
                                                                $canShowEdit = false;
                                                            }
                                                        @endphp
                                                        @if($canShowEdit)
                                                            <a href="{{ route('approval.edit', $request->id) }}" class="btn btn-outline-warning btn-sm" title="Chỉnh sửa">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                        @endif
                                                    @endif
                                                </div>
                                                {{-- Chỉ hiển thị nút phê duyệt/từ chối cho manager/director được gửi yêu cầu --}}
                                                @php
                                                    $creator = $request->creator;
                                                    $canShowActions = true;
                                                    if (auth()->user()->isDirector() && $creator && $creator->isAdmin()) {
                                                        $canShowActions = false;
                                                    }
                                                @endphp
                                                @if(($request->current_approver_id === auth()->id() || auth()->user()->isAdmin() || auth()->user()->isDirector()) && 
                                                    in_array(auth()->user()->role, ['manager', 'director', 'admin']) && 
                                                    $request->approval_status === 'pending' &&
                                                    $request->created_by_id !== auth()->id() &&
                                                    $canShowActions)
                                                    <div class="btn-group mt-1" role="group">
                                                        <button type="button" class="btn btn-outline-success btn-sm" onclick="approveRequest({{ $request->id }})" title="Phê duyệt">
                                                            <i class="bi bi-check"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="rejectRequest({{ $request->id }})" title="Từ chối">
                                                            <i class="bi bi-x"></i>
                                                        </button>
                                                    </div>
                                                @endif
                                                
                                                {{-- Nút hủy yêu cầu cho người gửi --}}
                                                @if($request->created_by_id === auth()->id() && $request->approval_status === 'pending')
                                                    <div class="btn-group mt-1" role="group">
                                                        <button type="button" class="btn btn-outline-warning btn-sm" onclick="cancelRequest({{ $request->id }})" title="Hủy yêu cầu">
                                                            <i class="bi bi-x-circle"></i>
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-inbox display-1 text-muted"></i>
                                                <h5 class="mt-3">Không có đề xuất nào</h5>
                                                <p class="mb-3">Hệ thống chưa có đề xuất nào</p>
                                                <a href="{{ route('approval.create', 'payment_request') }}" class="btn btn-primary">
                                                    <i class="bi bi-plus-circle me-2"></i>Tạo đề xuất đầu tiên
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== APPROVAL PAGE LOADED ===');
    console.log('My requests count:', {{ $myRequests->count() }});
    console.log('Pending approvals count:', {{ $pendingApprovals->count() }});
    console.log('All requests count:', {{ $allRequests->count() }});
});

// Refresh data
function refreshData() {
    console.log('Refreshing data...');
    location.reload();
}

// Export data
function exportData() {
    console.log('Exporting data...');
    alert('Chức năng xuất Excel sẽ được triển khai');
}

// Toggle select all
function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.request-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
}

// Bulk approve
function bulkApprove() {
    const selectedRequests = getSelectedRequests();
    if (selectedRequests.length === 0) {
        showAlert('warning', 'Vui lòng chọn ít nhất một đề xuất');
        return;
    }
    
    if (confirm('Bạn có chắc chắn muốn phê duyệt ' + selectedRequests.length + ' đề xuất đã chọn?')) {
        const comment = prompt('Nhập lý do phê duyệt chung (tùy chọn):');
        if (comment === null) return; // User cancelled
        
        // Show loading
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="bi bi-hourglass-split"></i> Đang xử lý...';
        button.disabled = true;
        
        fetch('/approval/bulk-approve', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ 
                request_ids: selectedRequests,
                comment: comment 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('error', data.error || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Có lỗi xảy ra khi phê duyệt hàng loạt');
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
}

// Bulk reject
function bulkReject() {
    const selectedRequests = getSelectedRequests();
    if (selectedRequests.length === 0) {
        showAlert('warning', 'Vui lòng chọn ít nhất một đề xuất');
        return;
    }
    
    if (confirm('Bạn có chắc chắn muốn từ chối ' + selectedRequests.length + ' đề xuất đã chọn?')) {
        const comment = prompt('Nhập lý do từ chối chung (bắt buộc):');
        if (!comment || comment.trim() === '') {
            alert('Vui lòng nhập lý do từ chối');
            return;
        }
        
        // Show loading
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="bi bi-hourglass-split"></i> Đang xử lý...';
        button.disabled = true;
        
        fetch('/approval/bulk-reject', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ 
                request_ids: selectedRequests,
                comment: comment 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('error', data.error || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Có lỗi xảy ra khi từ chối hàng loạt');
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
}

// Get selected requests
function getSelectedRequests() {
    const checkboxes = document.querySelectorAll('.request-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

// Approve request
function approveRequest(requestId) {
    console.log('Approving request:', requestId);
    
    const comment = prompt('Nhập lý do phê duyệt (tùy chọn):');
    if (comment === null) return; // User cancelled
    
    // Show loading
    const button = document.querySelector(`button[onclick="approveRequest(${requestId})"]`);
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="bi bi-hourglass-split"></i>';
    button.disabled = true;
    
    fetch(`/approval/${requestId}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ comment: comment })
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            showAlert('success', data.success);
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert('error', data.error || 'Có lỗi xảy ra');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Có lỗi xảy ra khi phê duyệt');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// Reject request
function rejectRequest(requestId) {
    console.log('Rejecting request:', requestId);
    
    const comment = prompt('Nhập lý do từ chối (bắt buộc):');
    if (!comment || comment.trim() === '') {
        alert('Vui lòng nhập lý do từ chối');
        return;
    }
    
    // Show loading
    const button = document.querySelector(`button[onclick="rejectRequest(${requestId})"]`);
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="bi bi-hourglass-split"></i>';
    button.disabled = true;
    
    fetch(`/approval/${requestId}/reject`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ comment: comment })
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            showAlert('success', data.success);
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert('error', data.error || 'Có lỗi xảy ra');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Có lỗi xảy ra khi từ chối');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// Apply filters
function applyFilters() {
    console.log('Applying filters...');
    document.getElementById('filterForm').submit();
}

// Clear filters
function clearFilters() {
    console.log('Clearing filters...');
    window.location.href = '{{ route("approval.index") }}';
}

// Auto-submit form when date inputs change
document.addEventListener('DOMContentLoaded', function() {
    const fromDateInput = document.getElementById('from_date');
    const toDateInput = document.getElementById('to_date');
    
    if (fromDateInput) {
        fromDateInput.addEventListener('change', function() {
            // Validate date range
            if (toDateInput.value && this.value > toDateInput.value) {
                showAlert('error', 'Ngày bắt đầu không được lớn hơn ngày kết thúc');
                this.value = '';
                return;
            }
        });
    }
    
    if (toDateInput) {
        toDateInput.addEventListener('change', function() {
            // Validate date range
            if (fromDateInput.value && this.value < fromDateInput.value) {
                showAlert('error', 'Ngày kết thúc không được nhỏ hơn ngày bắt đầu');
                this.value = '';
                return;
            }
        });
    }
    
    // Add enter key support for form submission
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        filterForm.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyFilters();
            }
        });
    }
});

// Show alert function
function showAlert(type, message) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert-custom');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show alert-custom`;
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';
    
    alertDiv.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Cancel request
function cancelRequest(requestId) {
    if (confirm('Bạn có chắc chắn muốn hủy yêu cầu này?')) {
        // Tạo form để gửi request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/approval/${requestId}/cancel`;
        
        // Thêm CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);
        
        // Thêm method DELETE
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Auto sticky column 3 when needed
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('approvalTable');
    const container = document.querySelector('.table-responsive');
    
    if (table && container) {
        function checkSticky() {
            const tableWidth = table.scrollWidth;
            const containerWidth = container.clientWidth;
            
            // If table is wider than container, enable sticky for column 3
            if (tableWidth > containerWidth) {
                table.classList.add('sticky-mode');
            } else {
                table.classList.remove('sticky-mode');
            }
        }
        
        // Check on load and resize
        checkSticky();
        window.addEventListener('resize', checkSticky);
        
        // Check on scroll - enable sticky when scrolled past column 3
        container.addEventListener('scroll', function() {
            const scrollLeft = container.scrollLeft;
            
            // Enable sticky when scrolled past column 3 (around 250px)
            if (scrollLeft > 200) {
                table.classList.add('sticky-mode');
            } else if (scrollLeft === 0) {
                checkSticky();
            }
        });
    }
});
</script>

<style>
/* Table container - Force horizontal scroll */
.table-responsive {
    overflow-x: scroll !important;
    overflow-y: visible;
    max-width: 100%;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    -webkit-overflow-scrolling: touch;
    scroll-behavior: smooth;
}

/* Table styling - Responsive width */
#approvalTable {
    min-width: 100% !important;
    width: 100% !important;
    table-layout: auto;
    margin-bottom: 0;
}

/* Column widths - Only column 3 sticky */
#approvalTable th:nth-child(1),
#approvalTable td:nth-child(1) {
    width: 50px !important;
    min-width: 50px !important;
    max-width: 50px !important;
    text-align: center;
    padding: 0.5rem 0.25rem !important;
}

#approvalTable th:nth-child(2),
#approvalTable td:nth-child(2) {
    width: 80px !important;
    min-width: 80px !important;
    max-width: 80px !important;
    padding: 0.5rem 0.25rem !important;
}

#approvalTable th:nth-child(3),
#approvalTable td:nth-child(3) {
    width: 250px !important;
    min-width: 250px !important;
    max-width: 250px !important;
    padding: 0.5rem 0.25rem !important;
}

/* Other columns - More space for content */
#approvalTable th:nth-child(n+4),
#approvalTable td:nth-child(n+4) {
    width: auto !important;
    min-width: 150px !important;
    max-width: none !important;
    padding: 0.5rem 0.25rem !important;
}

/* Sticky columns - Only column 3 when scrolling */
@media (max-width: 1200px) {
    #approvalTable th:nth-child(3),
    #approvalTable td:nth-child(3) {
        position: sticky !important;
        left: 0 !important;
        background-color: white !important;
        z-index: 15;
        border-right: 3px solid #007bff !important;
    }
}

/* Header background for sticky columns */
#approvalTable thead th:nth-child(1),
#approvalTable thead th:nth-child(2),
#approvalTable thead th:nth-child(3) {
    background-color: #f8f9fa !important;
    z-index: 20 !important;
}

/* Dynamic sticky columns - Only column 3 */
#approvalTable.sticky-mode th:nth-child(3),
#approvalTable.sticky-mode td:nth-child(3) {
    position: sticky !important;
    left: 0 !important;
    background-color: white !important;
    z-index: 15;
    border-right: 3px solid #007bff !important;
}

/* Table cells */
#approvalTable td {
    white-space: nowrap;
    vertical-align: middle;
    padding: 0.75rem 0.5rem;
    overflow: hidden;
    text-overflow: ellipsis;
}

#approvalTable th {
    padding: 0.75rem 0.5rem;
    font-weight: 600;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Force scrollbar to be visible */
.table-responsive::-webkit-scrollbar {
    height: 12px !important;
    display: block !important;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 6px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #007bff;
    border-radius: 6px;
    border: 2px solid #f1f1f1;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #0056b3;
}

/* Hover effects for better UX */
#approvalTable tbody tr:hover {
    background-color: #f8f9fa;
}

#approvalTable tbody tr:hover td:nth-child(1),
#approvalTable tbody tr:hover td:nth-child(2),
#approvalTable tbody tr:hover td:nth-child(3) {
    background-color: #e9ecef !important;
}

/* Ensure table can scroll */
.table-responsive {
    position: relative;
}

/* Debug: Make sure table is scrollable */
#approvalTable {
    display: table !important;
}
</style>
@endpush