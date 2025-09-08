@extends('layouts.master')

@push('styles')
<style>
.department-filter-dropdown .dropdown-menu {
    border: 1px solid #dee2e6;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border-radius: 0.375rem;
}

.department-filter-dropdown .form-check {
    margin-bottom: 0;
    padding: 0.5rem 1rem;
    transition: background-color 0.15s ease-in-out;
}

.department-filter-dropdown .form-check:hover {
    background-color: #f8f9fa;
}

.department-filter-dropdown .form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.department-filter-dropdown .form-check-label {
    cursor: pointer;
    font-size: 0.9rem;
    color: #495057;
}

.department-filter-dropdown .form-check-label:hover {
    color: #0d6efd;
}

.department-filter-dropdown .dropdown-item {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}

.department-filter-dropdown .dropdown-item:hover {
    background-color: #f8f9fa;
}

.department-filter-dropdown .btn {
    text-align: left;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.department-filter-dropdown .btn::after {
    margin-left: auto;
}

.department-filter-dropdown .dropdown-menu {
    min-width: 100%;
}

.department-filter-dropdown .form-check-input {
    margin-top: 0.125rem;
}

.department-filter-dropdown .form-check-input:focus {
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.department-filter-dropdown .dropdown-item:focus {
    background-color: transparent;
    outline: none;
}
</style>
@endpush

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

            @if(isset($error))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ $error }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Thống kê tổng quan -->
            <div class="row mb-4">
                @php
                    $user = auth()->user();
                    
                    // Khởi tạo giá trị mặc định
                    $totalEmployeeRequests = 0;
                    $pendingEmployeeRequests = 0;
                    $approvedEmployeeRequests = 0;
                    $rejectedEmployeeRequests = 0;
                    $totalManagerRequests = 0;
                    $pendingManagerRequests = 0;
                    $approvedManagerRequests = 0;
                    $rejectedManagerRequests = 0;
                    
                    try {
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
                    } catch (\Exception $e) {
                        // Nếu có lỗi, sử dụng giá trị mặc định (đã khởi tạo ở trên)
                        \Log::warning('Error getting detailed stats in quest-detail view: ' . $e->getMessage());
                    }
                @endphp

                <!-- Thống kê yêu cầu từ Employee -->
                <div class="col-12 mb-4">
                    <h5 class="mb-3 text-primary">
                        <i class="bi bi-person me-2"></i>Thống kê yêu cầu từ Nhân viên
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
                        <i class="bi bi-person-badge me-2"></i>Thống kê yêu cầu từ Quản Lý
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
                                <option value="employee" {{ request('request_type') == 'employee' ? 'selected' : '' }}>Nhân viên</option>
                                <option value="manager" {{ request('request_type') == 'manager' ? 'selected' : '' }}>Quản lý</option>
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
                            <label class="form-label">Lọc theo phòng ban</label>
                            <div class="dropdown department-filter-dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle w-100" type="button" 
                                        id="departmentFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span id="departmentFilterText">
                                        @if(request('department_ids'))
                                            @php
                                                $selectedDeptIds = is_array(request('department_ids')) ? request('department_ids') : [request('department_ids')];
                                                $selectedDepts = \App\Models\Department::whereIn('id', $selectedDeptIds)->get();
                                            @endphp
                                            @if($selectedDepts->count() == 1)
                                                {{ $selectedDepts->first()->name }}
                                            @else
                                                {{ $selectedDepts->count() }} phòng ban đã chọn
                                            @endif
                                        @else
                                            Tất cả phòng ban
                                        @endif
                                    </span>
                                </button>
                                <ul class="dropdown-menu w-100" style="max-height: 300px; overflow-y: auto;">
                                    <li>
                                        <div class="px-3 py-2 border-bottom">
                                            <small class="text-muted">Chọn phòng ban:</small>
                                        </div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="event.preventDefault(); clearDepartmentFilter();">
                                            <i class="bi bi-x-circle me-2 text-danger"></i>Xóa bộ lọc
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    @foreach(\App\Models\Department::orderBy('name')->get() as $dept)
                                        @php
                                            $isSelected = request('department_ids') && 
                                                        (is_array(request('department_ids')) ? in_array($dept->id, request('department_ids')) : request('department_ids') == $dept->id);
                                        @endphp
                                        <li>
                                            <div class="form-check px-3 py-1" onclick="event.stopPropagation();">
                                                <input class="form-check-input department-checkbox" type="checkbox" 
                                                       value="{{ $dept->id }}" id="dept_{{ $dept->id }}"
                                                       {{ $isSelected ? 'checked' : '' }}
                                                       onchange="updateDepartmentFilter()">
                                                <label class="form-check-label w-100" for="dept_{{ $dept->id }}" onclick="event.stopPropagation();">
                                                    <i class="bi bi-building me-2 text-primary"></i>{{ $dept->name }}
                                                </label>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <!-- Hidden inputs để gửi dữ liệu -->
                            <div id="departmentInputs">
                                @if(request('department_ids'))
                                    @if(is_array(request('department_ids')))
                                        @foreach(request('department_ids') as $deptId)
                                            <input type="hidden" name="department_ids[]" value="{{ $deptId }}">
                                        @endforeach
                                    @else
                                        <input type="hidden" name="department_ids[]" value="{{ request('department_ids') }}">
                                    @endif
                                @endif
                            </div>
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
                        
                        if (request('department_ids')) {
                            $departmentIds = is_array(request('department_ids')) ? request('department_ids') : [request('department_ids')];
                            $query->whereIn('source_department_id', $departmentIds);
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
                                                        <i class="bi bi-person me-1"></i>Nhân viên
                                                    </span>
                                                @else
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-person-badge me-1"></i>Quản lý
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
                                                    
                                                    @if($request->canBeDeletedBy($user))
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="deleteSupportRequest({{ $request->id }}, '{{ $request->title }}')">
                                                            <i class="bi bi-trash"></i>
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
                    @elseif(isset($isEmpty) && $isEmpty)
                        <!-- Empty State - Không phải lỗi, chỉ là chưa có dữ liệu -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="bi bi-clipboard-plus display-1 text-primary"></i>
                            </div>
                            <h4 class="text-primary mb-3">Chưa có yêu cầu hỗ trợ nào</h4>
                            <p class="text-muted mb-4">
                                Hệ thống chưa có yêu cầu hỗ trợ nào. Hãy tạo yêu cầu đầu tiên để bắt đầu sử dụng.
                            </p>
                            <div class="d-flex justify-content-center gap-3">
                                <a href="{{ route('support-requests.create') }}" class="btn btn-primary btn-lg">
                                    <i class="bi bi-plus-circle me-2"></i>Tạo yêu cầu hỗ trợ
                                </a>
                                <a href="{{ route('support-requests.index') }}" class="btn btn-outline-secondary btn-lg">
                                    <i class="bi bi-arrow-left me-2"></i>Quay lại danh sách
                                </a>
                            </div>
                        </div>
                    @else
                        <!-- Filtered Empty State - Có dữ liệu nhưng filter không tìm thấy -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="bi bi-search display-1 text-muted"></i>
                            </div>
                            <h5 class="text-muted mb-3">Không tìm thấy yêu cầu hỗ trợ nào</h5>
                            <p class="text-muted mb-4">Hãy thử thay đổi bộ lọc hoặc tìm kiếm để tìm yêu cầu phù hợp.</p>
                            <div class="d-flex justify-content-center gap-3">
                                <button type="button" class="btn btn-outline-primary" onclick="clearFilters()">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Xóa bộ lọc
                                </button>
                                <a href="{{ route('support-requests.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Tạo yêu cầu mới
                                </a>
                            </div>
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
                                // Note: $request sẽ được set bằng JavaScript khi modal được mở
                            @endphp
                            
                            @foreach(\App\Models\User::whereIn('role', ['admin', 'director', 'manager'])->with('department')->get() as $user)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="new_recipients[]" 
                                           value="{{ $user->id }}" id="recipient_{{ $user->id }}">
                                    <label class="form-check-label" for="recipient_{{ $user->id }}">
                                        <strong>{{ $user->name }}</strong> 
                                        <span class="badge bg-info ms-1">
                                            @if($user->role === 'admin')
                                                Quản trị viên
                                            @elseif($user->role === 'director')
                                                Giám đốc
                                            @elseif($user->role === 'manager')
                                                Quản lý
                                            @elseif($user->role === 'employee')
                                                Nhân viên
                                            @else
                                                {{ ucfirst($user->role) }}
                                            @endif
                                        </span>
                                        <small class="text-muted d-block">{{ $user->department->name ?? 'Chưa phân phòng ban' }}</small>
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
    
    // Reset tất cả checkbox về trạng thái ban đầu
    const checkboxes = document.querySelectorAll('#forwardModal input[name="new_recipients[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.disabled = false;
        checkbox.checked = false;
        const label = checkbox.nextElementSibling;
        label.classList.remove('text-muted');
        // Ẩn warning message nếu có
        const warningMsg = label.querySelector('.text-warning');
        if (warningMsg) {
            warningMsg.style.display = 'none';
        }
    });
    
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

// Function xóa support request
function deleteSupportRequest(requestId, requestTitle) {
    if (confirm(`Bạn có chắc chắn muốn xóa yêu cầu hỗ trợ "${requestTitle}"?\n\nHành động này không thể hoàn tác và sẽ xóa tất cả dữ liệu liên quan.`)) {
        // Tạo form để gửi DELETE request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/support-requests/${requestId}`;
        
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

// Function xóa bộ lọc
function clearFilters() {
    // Reset tất cả form fields về giá trị mặc định
    document.getElementById('request_type').value = '';
    document.getElementById('status').value = '';
    document.getElementById('priority').value = '';
    document.getElementById('search').value = '';
    
    // Clear department filter
    clearDepartmentFilter();
    
    // Submit form để reload trang
    document.querySelector('form[method="GET"]').submit();
}

// Function cập nhật department filter
function updateDepartmentFilter() {
    const checkboxes = document.querySelectorAll('.department-checkbox:checked');
    const departmentInputs = document.getElementById('departmentInputs');
    const filterText = document.getElementById('departmentFilterText');
    
    // Clear existing hidden inputs
    departmentInputs.innerHTML = '';
    
    if (checkboxes.length === 0) {
        filterText.textContent = 'Tất cả phòng ban';
    } else if (checkboxes.length === 1) {
        // Lấy tên phòng ban từ label (bỏ icon)
        const label = checkboxes[0].nextElementSibling;
        const deptName = label.textContent.replace(/.*\s/, '').trim(); // Bỏ icon và lấy tên
        filterText.textContent = deptName;
        // Add hidden input
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'department_ids[]';
        input.value = checkboxes[0].value;
        departmentInputs.appendChild(input);
    } else {
        filterText.textContent = `${checkboxes.length} phòng ban đã chọn`;
        // Add hidden inputs for all selected departments
        checkboxes.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'department_ids[]';
            input.value = checkbox.value;
            departmentInputs.appendChild(input);
        });
    }
    
    console.log('Department filter updated:', checkboxes.length, 'departments selected');
}

// Function xóa department filter
function clearDepartmentFilter() {
    const checkboxes = document.querySelectorAll('.department-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    updateDepartmentFilter();
}

// Đảm bảo functions được load khi DOM ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Department filter initialized');
    
    // Test checkbox functionality
    const checkboxes = document.querySelectorAll('.department-checkbox');
    console.log('Found', checkboxes.length, 'department checkboxes');
    
    checkboxes.forEach((checkbox, index) => {
        console.log(`Checkbox ${index + 1}:`, checkbox.value, checkbox.checked);
    });
});
</script>
@endsection
