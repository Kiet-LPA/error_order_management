@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 mb-0">
                    {{ __('Quản lý yêu cầu hỗ trợ') }}
                </h2>
                <div class="d-flex gap-2">
                    <a href="{{ route('support-requests.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Tạo yêu cầu mới
                    </a>
                    <a href="{{ route('support-requests.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Quay lại danh sách
                    </a>
                </div>
            </div>

            <!-- Thống kê tổng quan -->
            <div class="row mb-4">
                @php
                    $user = auth()->user();
                    
                    // Thống kê yêu cầu từ Employee
                    $employeeStatsQuery = \App\Models\SupportRequest::employeeRequests();
                    if ($user->isManager()) {
                        $employeeStatsQuery->where('source_department_id', $user->department_id);
                    } elseif ($user->isDirector()) {
                        if ($user->managedDepartments()->exists()) {
                            $departmentIds = $user->managedDepartments()->pluck('departments.id');
                            $employeeStatsQuery->whereIn('source_department_id', $departmentIds);
                        }
                    }
                    $totalEmployeeRequests = $employeeStatsQuery->count();
                    $pendingEmployeeRequests = (clone $employeeStatsQuery)->where('status', 'pending')->count();
                    $approvedEmployeeRequests = (clone $employeeStatsQuery)->where('status', 'approved')->count();
                    $rejectedEmployeeRequests = (clone $employeeStatsQuery)->where('status', 'rejected')->count();
                    
                    // Thống kê yêu cầu từ Manager
                    $managerStatsQuery = \App\Models\SupportRequest::managerRequests();
                    if ($user->isManager()) {
                        $managerStatsQuery->where('source_department_id', $user->department_id);
                    } elseif ($user->isDirector()) {
                        if ($user->managedDepartments()->exists()) {
                            $departmentIds = $user->managedDepartments()->pluck('departments.id');
                            $managerStatsQuery->whereIn('source_department_id', $departmentIds);
                        }
                    }
                    $totalManagerRequests = $managerStatsQuery->count();
                    $pendingManagerRequests = (clone $managerStatsQuery)->where('status', 'pending')->count();
                    $approvedManagerRequests = (clone $managerStatsQuery)->where('status', 'approved')->count();
                    $rejectedManagerRequests = (clone $managerStatsQuery)->where('status', 'rejected')->count();
                @endphp

                <!-- Thống kê yêu cầu từ Employee -->
                <div class="col-12 mb-4">
                    <h5 class="mb-3 text-primary">
                        <i class="bi bi-person me-2"></i>Thống kê yêu cầu từ Employee
                    </h5>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="bi bi-file-text text-primary" style="font-size: 2rem;"></i>
                                        </div>
                                        <div class="ms-3">
                                            <p class="text-muted mb-0">Tổng yêu cầu</p>
                                            <h3 class="mb-0">{{ $totalEmployeeRequests }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="bi bi-clock text-warning" style="font-size: 2rem;"></i>
                                        </div>
                                        <div class="ms-3">
                                            <p class="text-muted mb-0">Chờ phê duyệt</p>
                                            <h3 class="mb-0 text-warning">{{ $pendingEmployeeRequests }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                                        </div>
                                        <div class="ms-3">
                                            <p class="text-muted mb-0">Đã phê duyệt</p>
                                            <h3 class="mb-0 text-success">{{ $approvedEmployeeRequests }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="bi bi-x-circle text-danger" style="font-size: 2rem;"></i>
                                        </div>
                                        <div class="ms-3">
                                            <p class="text-muted mb-0">Bị từ chối</p>
                                            <h3 class="mb-0 text-danger">{{ $rejectedEmployeeRequests }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thống kê yêu cầu từ Manager -->
                <div class="col-12 mb-4">
                    <h5 class="mb-3 text-success">
                        <i class="bi bi-person-badge me-2"></i>Thống kê yêu cầu từ Manager
                    </h5>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="bi bi-file-text text-success" style="font-size: 2rem;"></i>
                                        </div>
                                        <div class="ms-3">
                                            <p class="text-muted mb-0">Tổng yêu cầu</p>
                                            <h3 class="mb-0">{{ $totalManagerRequests }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="bi bi-clock text-warning" style="font-size: 2rem;"></i>
                                        </div>
                                        <div class="ms-3">
                                            <p class="text-muted mb-0">Chờ phê duyệt</p>
                                            <h3 class="mb-0 text-warning">{{ $pendingManagerRequests }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                                        </div>
                                        <div class="ms-3">
                                            <p class="text-muted mb-0">Đã phê duyệt</p>
                                            <h3 class="mb-0 text-success">{{ $approvedManagerRequests }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="bi bi-x-circle text-danger" style="font-size: 2rem;"></i>
                                        </div>
                                        <div class="ms-3">
                                            <p class="text-muted mb-0">Bị từ chối</p>
                                            <h3 class="mb-0 text-danger">{{ $rejectedManagerRequests }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bộ lọc và tìm kiếm -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Bộ lọc và tìm kiếm</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('support-requests.quest-detail') }}" class="row g-3">
                        <div class="col-md-2">
                            <label for="request_type" class="form-label">Loại yêu cầu</label>
                            <select name="request_type" id="request_type" class="form-select">
                                <option value="">Tất cả</option>
                                <option value="employee" {{ request('request_type') == 'employee' ? 'selected' : '' }}>Employee</option>
                                <option value="manager" {{ request('request_type') == 'manager' ? 'selected' : '' }}>Manager</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">Tất cả</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ phê duyệt</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã phê duyệt</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Bị từ chối</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="priority" class="form-label">Độ ưu tiên</label>
                            <select name="priority" id="priority" class="form-select">
                                <option value="">Tất cả</option>
                                <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Thấp</option>
                                <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Trung bình</option>
                                <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>Cao</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="source_department_id" class="form-label">Phòng ban gốc</label>
                            <select name="source_department_id" id="source_department_id" class="form-select">
                                <option value="">Tất cả</option>
                                @foreach(\App\Models\Department::orderBy('name')->get() as $dept)
                                    <option value="{{ $dept->id }}" {{ request('source_department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="search" class="form-label">Tìm kiếm</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   value="{{ request('search') }}" placeholder="Tìm theo tiêu đề...">
                        </div>
                        
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-2"></i>Tìm kiếm
                            </button>
                            <a href="{{ route('support-requests.quest-detail') }}" class="btn btn-outline-secondary ms-2">
                                <i class="bi bi-arrow-clockwise me-2"></i>Làm mới
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Danh sách yêu cầu hỗ trợ -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Danh sách yêu cầu hỗ trợ</h5>
                </div>
                <div class="card-body">
                    @php
                        $query = \App\Models\SupportRequest::with(['requester', 'approver', 'department', 'sourceDepartment']);
                        
                        if ($user->isManager()) {
                            $query->where('source_department_id', $user->department_id);
                        } elseif ($user->isDirector()) {
                            if ($user->managedDepartments()->exists()) {
                                $departmentIds = $user->managedDepartments()->pluck('departments.id');
                                $query->whereIn('source_department_id', $departmentIds);
                            }
                        }
                        
                        if (request('request_type')) {
                            $query->where('request_type', request('request_type'));
                        }
                        
                        if (request('status')) {
                            $query->where('status', request('status'));
                        }
                        
                        if (request('priority')) {
                            $query->where('priority', request('priority'));
                        }
                        
                        if (request('source_department_id')) {
                            $query->where('source_department_id', request('source_department_id'));
                        }
                        
                        if (request('search')) {
                            $query->where('title', 'like', '%' . request('search') . '%');
                        }
                        
                        $supportRequests = $query->latest()->paginate(15);
                    @endphp

                    @if($supportRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Loại</th>
                                        <th>Tiêu đề</th>
                                        <th>Người yêu cầu</th>
                                        <th>Phòng ban gốc</th>
                                        <th>Độ ưu tiên</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($supportRequests as $request)
                                        <tr>
                                            <td>
                                                @if($request->request_type === 'employee')
                                                    <span class="badge bg-primary">
                                                        <i class="bi bi-person me-1"></i>Employee
                                                    </span>
                                                @else
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-person-badge me-1"></i>Manager
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $request->title }}</div>
                                                @if($request->is_urgent)
                                                    <span class="badge bg-danger">Khẩn cấp</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>{{ $request->requester->name }}</div>
                                                <small class="text-muted">{{ $request->requester->email }}</small>
                                            </td>
                                            <td>
                                                @if($request->sourceDepartment)
                                                    {{ $request->sourceDepartment->name }}
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $priorityColors = [
                                                        'low' => 'bg-success',
                                                        'medium' => 'bg-warning',
                                                        'high' => 'bg-danger'
                                                    ];
                                                    $priorityLabels = [
                                                        'low' => 'Thấp',
                                                        'medium' => 'Trung bình',
                                                        'high' => 'Cao'
                                                    ];
                                                @endphp
                                                <span class="badge {{ $priorityColors[$request->priority] }}">
                                                    {{ $priorityLabels[$request->priority] }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $request->getStatusColor() }}">
                                                    {{ $request->getStatusLabel() }}
                                                </span>
                                            </td>
                                            <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('support-requests.show', $request) }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    
                                                    @if($request->status === 'pending' && $request->canBeApprovedBy($user))
                                                        <form method="POST" action="{{ route('support-requests.approve', $request) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-success" 
                                                                    onclick="return confirm('Bạn có chắc muốn phê duyệt yêu cầu này?')">
                                                                <i class="bi bi-check"></i>
                                                            </button>
                                                        </form>
                                                        
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="showRejectModal({{ $request->id }})">
                                                            <i class="bi bi-x"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    @if($request->status === 'pending' && $request->canBeForwardedBy($user))
                                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                                onclick="showForwardModal({{ $request->id }})">
                                                            <i class="bi bi-arrow-right"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Phân trang -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $supportRequests->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted"></i>
                            <h5 class="mt-3">Không tìm thấy yêu cầu hỗ trợ nào</h5>
                            <p class="text-muted">Hãy thử thay đổi bộ lọc hoặc tìm kiếm</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal từ chối -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Từ chối yêu cầu hỗ trợ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" id="rejection_reason" rows="4" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Từ chối</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal chuyển tiếp -->
<div class="modal fade" id="forwardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chuyển tiếp yêu cầu hỗ trợ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="forwardForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Người nhận mới <span class="text-danger">*</span></label>
                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                            @php
                                // Lấy danh sách người đã được forward hoặc đã có trong request
                                $currentRecipients = [];
                                if ($request->recipients) {
                                    $currentRecipients = is_string($request->recipients) 
                                        ? json_decode($request->recipients, true) 
                                        : $request->recipients;
                                }
                                
                                // Thêm người đã forward (nếu có)
                                if ($request->forwarded_by) {
                                    $currentRecipients[] = $request->forwarded_by;
                                }
                                
                                // Thêm người tạo request
                                if ($request->requester_id) {
                                    $currentRecipients[] = $request->requester_id;
                                }
                                
                                $currentRecipients = array_unique($currentRecipients);
                            @endphp
                            
                            @foreach(\App\Models\User::whereIn('role', ['admin', 'director', 'manager'])->with('department')->get() as $user)
                                @php
                                    $isDisabled = in_array($user->id, $currentRecipients);
                                    $isAlreadyRecipient = in_array($user->id, $currentRecipients);
                                @endphp
                                
                                <div class="form-check mb-2 {{ $isDisabled ? 'text-muted' : '' }}">
                                    <input class="form-check-input" type="checkbox" name="new_recipients[]" 
                                           value="{{ $user->id }}" id="recipient_{{ $user->id }}"
                                           {{ $isDisabled ? 'disabled' : '' }}>
                                    <label class="form-check-label {{ $isDisabled ? 'text-muted' : '' }}" for="recipient_{{ $user->id }}">
                                        <strong>{{ $user->name }}</strong> 
                                        <span class="badge bg-info ms-1">{{ ucfirst($user->role) }}</span>
                                        <small class="text-muted d-block">{{ $user->department->name ?? 'N/A' }}</small>
                                        @if($isAlreadyRecipient)
                                            <small class="text-warning d-block">
                                                <i class="bi bi-exclamation-triangle"></i> Đã có trong yêu cầu
                                            </small>
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <div class="form-text">Chọn một hoặc nhiều người nhận từ danh sách trên.</div>
                    </div>
                    <div class="mb-3">
                        <label for="forwarding_reason" class="form-label">Lý do chuyển tiếp <span class="text-danger">*</span></label>
                        <textarea name="forwarding_reason" id="forwarding_reason" rows="4" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-info">Chuyển tiếp</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showRejectModal(requestId) {
    document.getElementById('rejectForm').action = `/support-requests/${requestId}/reject`;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function showForwardModal(requestId) {
    document.getElementById('forwardForm').action = `/support-requests/${requestId}/forward`;
    new bootstrap.Modal(document.getElementById('forwardModal')).show();
}

// Validation cho form chuyển tiếp
document.getElementById('forwardForm').addEventListener('submit', function(e) {
    const checkboxes = document.querySelectorAll('input[name="new_recipients[]"]:checked:not(:disabled)');
    if (checkboxes.length === 0) {
        e.preventDefault();
        alert('Vui lòng chọn ít nhất một người nhận.');
        return false;
    }
});
</script>
@endsection
