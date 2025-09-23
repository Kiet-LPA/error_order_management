@extends('layouts.master')

@section('title', 'Quản lý nhân viên')

@section('content')
<style>
.card-header {
    background: linear-gradient(90deg, #558EC1 0%, #5DA444 100%);
    color: #fff;
    border-bottom: none;
}
.card-header h5 {
    color: #fff;
}

/* Table styling */
.table thead th {
    background: rgba(85, 142, 193, 0.1);
    border-bottom: 2px solid #558EC1;
    color: #374151;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s;
}
.table thead th:hover {
    background: rgba(85, 142, 193, 0.2);
}
.table tbody tr:hover {
    background: rgba(85, 142, 193, 0.05);
}

/* Filter section */
.filter-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.filter-section .form-control:focus {
    border-color: #558EC1;
    box-shadow: 0 0 0 0.2rem rgba(85, 142, 193, 0.25);
}

/* Stats cards */
.stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.stats-card h3 {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.stats-card p {
    margin-bottom: 0;
    opacity: 0.9;
}

/* Sort indicators */
.sort-indicator {
    display: inline-block;
    margin-left: 0.5rem;
    opacity: 0.5;
}

.sort-indicator.active {
    opacity: 1;
    color: #558EC1;
}
</style>

{{-- Statistics Section --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <h3>{{ $stats['total'] }}</h3>
            <p><i class="bi bi-people me-2"></i>Tổng nhân viên</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <h3>{{ $stats['admin'] }}</h3>
            <p><i class="bi bi-shield-check me-2"></i>Quản trị viên</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <h3>{{ $stats['manager'] }}</h3>
            <p><i class="bi bi-person-badge me-2"></i>Quản lý</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <h3>{{ $stats['employee'] }}</h3>
            <p><i class="bi bi-person me-2"></i>Nhân viên</p>
        </div>
    </div>
</div>

{{-- Filter Section --}}
<div class="filter-section">
    <form method="GET" action="{{ route('users.index') }}" class="row g-3">
        {{-- Search --}}
        <div class="col-md-4">
            <label for="search" class="form-label fw-semibold">
                <i class="bi bi-search me-1"></i>Tìm kiếm
            </label>
            <input type="text" class="form-control" id="search" name="search" 
                   value="{{ request('search') }}" 
                   placeholder="Tên, email, số điện thoại...">
        </div>

        {{-- Department Filter --}}
        <div class="col-md-3">
            <label for="department" class="form-label fw-semibold">
                <i class="bi bi-building me-1"></i>Phòng ban
            </label>
            <select class="form-select" id="department" name="department">
                <option value="">Tất cả phòng ban</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Role Filter --}}
        <div class="col-md-2">
            <label for="role" class="form-label fw-semibold">
                <i class="bi bi-person-badge me-1"></i>Vai trò
            </label>
            <select class="form-select" id="role" name="role">
                <option value="">Tất cả vai trò</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Quản trị viên</option>
                <option value="manager" {{ request('role') == 'manager' ? 'selected' : '' }}>Quản lý</option>
                <option value="employee" {{ request('role') == 'employee' ? 'selected' : '' }}>Nhân viên</option>
            </select>
        </div>

        {{-- Per Page --}}
        <div class="col-md-2">
            <label for="per_page" class="form-label fw-semibold">
                <i class="bi bi-list-ul me-1"></i>Hiển thị
            </label>
            <select class="form-select" id="per_page" name="per_page">
                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                <option value="15" {{ request('per_page') == 15 || !request('per_page') ? 'selected' : '' }}>15</option>
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
            </select>
        </div>

        {{-- Action Buttons --}}
        <div class="col-md-1 d-flex align-items-end">
            <div class="d-grid gap-2 w-100">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel me-1"></i>Lọc
                </button>
            </div>
        </div>
    </form>

    {{-- Clear Filters --}}
    @if(request('search') || request('department') || request('role') || request('per_page') != 15)
        <div class="mt-3">
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-x-circle me-1"></i>Xóa bộ lọc
            </a>
        </div>
    @endif
</div>

{{-- Users Table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-people me-2"></i>Danh sách nhân viên
            <span class="badge bg-light text-dark ms-2">{{ $users->total() }} kết quả</span>
        </h5>
        <a href="{{ route('users.create') }}" class="btn btn-light">
            <i class="bi bi-plus-circle me-1"></i>Thêm nhân viên
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'id', 'direction' => request('sort') == 'id' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                               class="text-decoration-none text-dark">
                                #
                                <span class="sort-indicator {{ request('sort') == 'id' ? 'active' : '' }}">
                                    @if(request('sort') == 'id')
                                        @if(request('direction') == 'asc')
                                            <i class="bi bi-arrow-up"></i>
                                        @else
                                            <i class="bi bi-arrow-down"></i>
                                        @endif
                                    @else
                                        <i class="bi bi-arrow-down-up"></i>
                                    @endif
                                </span>
                            </a>
                        </th>
                        <th>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                               class="text-decoration-none text-dark">
                                Tên
                                <span class="sort-indicator {{ request('sort') == 'name' ? 'active' : '' }}">
                                    @if(request('sort') == 'name')
                                        @if(request('direction') == 'asc')
                                            <i class="bi bi-arrow-up"></i>
                                        @else
                                            <i class="bi bi-arrow-down"></i>
                                        @endif
                                    @else
                                        <i class="bi bi-arrow-down-up"></i>
                                    @endif
                                </span>
                            </a>
                        </th>
                        <th>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'email', 'direction' => request('sort') == 'email' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                               class="text-decoration-none text-dark">
                                Email
                                <span class="sort-indicator {{ request('sort') == 'email' ? 'active' : '' }}">
                                    @if(request('sort') == 'email')
                                        @if(request('direction') == 'asc')
                                            <i class="bi bi-arrow-up"></i>
                                        @else
                                            <i class="bi bi-arrow-down"></i>
                                        @endif
                                    @else
                                        <i class="bi bi-arrow-down-up"></i>
                                    @endif
                                </span>
                            </a>
                        </th>
                        <th>Số điện thoại</th>
                        <th>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'role', 'direction' => request('sort') == 'role' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                               class="text-decoration-none text-dark">
                                Vai trò
                                <span class="sort-indicator {{ request('sort') == 'role' ? 'active' : '' }}">
                                    @if(request('sort') == 'role')
                                        @if(request('direction') == 'asc')
                                            <i class="bi bi-arrow-up"></i>
                                        @else
                                            <i class="bi bi-arrow-down"></i>
                                        @endif
                                    @else
                                        <i class="bi bi-arrow-down-up"></i>
                                    @endif
                                </span>
                            </a>
                        </th>
                        <th>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'department_id', 'direction' => request('sort') == 'department_id' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                               class="text-decoration-none text-dark">
                                Phòng ban
                                <span class="sort-indicator {{ request('sort') == 'department_id' ? 'active' : '' }}">
                                    @if(request('sort') == 'department_id')
                                        @if(request('direction') == 'asc')
                                            <i class="bi bi-arrow-up"></i>
                                        @else
                                            <i class="bi bi-arrow-down"></i>
                                        @endif
                                    @else
                                        <i class="bi bi-arrow-down-up"></i>
                                    @endif
                                </span>
                            </a>
                        </th>
                        <th>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                               class="text-decoration-none text-dark">
                                Ngày tạo
                                <span class="sort-indicator {{ request('sort') == 'created_at' ? 'active' : '' }}">
                                    @if(request('sort') == 'created_at')
                                        @if(request('direction') == 'asc')
                                            <i class="bi bi-arrow-up"></i>
                                        @else
                                            <i class="bi bi-arrow-down"></i>
                                        @endif
                                    @else
                                        <i class="bi bi-arrow-down-up"></i>
                                    @endif
                                </span>
                            </a>
                        </th>
                        <th class="text-end">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $user->avatar_url }}" 
                                         alt="{{ $user->name }}" 
                                         class="rounded-circle border border-2 border-primary me-2" 
                                         style="width: 32px; height: 32px; object-fit: cover;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <i class="bi bi-person-circle text-primary me-2" style="display: none;"></i>
                                    <div class="fw-semibold">{{ $user->name }}</div>
                                </div>
                            </td>
                            <td>
                                @if($user->email)
                                    <a href="mailto:{{ $user->email }}" class="text-decoration-none">
                                        {{ $user->email }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($user->phone)
                                    <a href="tel:{{ $user->phone }}" class="text-decoration-none">
                                        {{ $user->phone }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $roleBadges = [
                                        'admin' => 'danger',
                                        'manager' => 'warning',
                                        'employee' => 'info'
                                    ];
                                    $roleLabels = [
                                        'admin' => 'Quản trị viên',
                                        'manager' => 'Quản lý',
                                        'employee' => 'Nhân viên'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $roleBadges[$user->role] ?? 'secondary' }}">
                                    {{ $roleLabels[$user->role] ?? $user->role }}
                                </span>
                            </td>
                            <td>
                                @if($user->departments->count() > 0)
                                    @php
                                        $visibleDepartments = $user->departments->take(2);
                                        $hiddenDepartments = $user->departments->skip(2);
                                        $allDepartmentNames = $user->departments->pluck('name')->join(', ');
                                    @endphp
                                    
                                    @foreach($visibleDepartments as $department)
                                        <span class="badge bg-light text-dark border me-1">
                                            <i class="bi bi-building me-1"></i>{{ $department->name }}
                                        </span>
                                    @endforeach
                                    
                                    @if($hiddenDepartments->count() > 0)
                                        <span class="badge bg-secondary me-1" 
                                              data-bs-toggle="tooltip" 
                                              data-bs-placement="top" 
                                              title="{{ $allDepartmentNames }}">
                                            <i class="bi bi-three-dots me-1"></i>+{{ $hiddenDepartments->count() }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $user->created_at->format('d/m/Y') }}
                                </small>
                            </td>
                            <td class="text-end">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @php
                                        $canEdit = true;
                                        $canDelete = true;
                                        if (auth()->user()->isDirector() && ($user->isAdmin() || $user->isDirector())) {
                                            $canEdit = false;
                                            $canDelete = false;
                                        }
                                    @endphp
                                    @if($canEdit)
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endif
                                    @if($canDelete)
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" 
                                              onsubmit="return confirm('Bạn có chắc muốn xóa nhân viên này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Xóa">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-people fa-3x mb-3 opacity-50"></i>
                                    <h6 class="mb-2">Không tìm thấy nhân viên nào</h6>
                                    <p class="mb-0">Hãy thử thay đổi bộ lọc hoặc tạo nhân viên mới</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    {{-- Thông tin kết quả --}}
    @if($users->hasPages())
        <div class="card-footer bg-light border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Hiển thị {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} 
                    trong tổng số {{ $users->total() }} kết quả
                </div>
                
                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0">
                        @if ($users->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">« Previous</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->previousPageUrl() }}" rel="prev">« Previous</a>
                            </li>
                        @endif

                        @php
                            $start = max(1, $users->currentPage() - 2);
                            $end = min($users->lastPage(), $users->currentPage() + 2);
                        @endphp
                        
                        @if($start > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->url(1) }}">1</a>
                            </li>
                            @if($start > 2)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                        @endif
                        
                        @for ($page = $start; $page <= $end; $page++)
                            @if ($page == $users->currentPage())
                                <li class="page-item active">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $users->url($page) }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endfor
                        
                        @if($end < $users->lastPage())
                            @if($end < $users->lastPage() - 1)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->url($users->lastPage()) }}">{{ $users->lastPage() }}</a>
                            </li>
                        @endif

                        @if ($users->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->nextPageUrl() }}" rel="next">Next »</a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">Next »</span>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </div>
    @endif
</div>
@endsection
