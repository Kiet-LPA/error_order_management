@extends('layouts.master')

@section('title', 'Danh sách nhân viên')

@push('styles')
<style>
/* Custom tooltip styles */
.tooltip-inner {
    max-width: 300px !important;
    text-align: left !important;
    white-space: pre-line !important;
    line-height: 1.4 !important;
    padding: 8px 12px !important;
    font-size: 0.875rem !important;
}

.tooltip.show {
    opacity: 1 !important;
}

/* Hover effect for tooltip badges */
.employee-tooltip:hover,
.department-tooltip:hover {
    cursor: help !important;
    transition: opacity 0.2s ease !important;
}

/* Sticky first column CSS */
.table-responsive {
    overflow-x: auto !important;
    overflow-y: visible !important;
    -webkit-overflow-scrolling: touch !important;
    scrollbar-width: thin !important;
    scrollbar-color: #c1c1c1 #f1f1f1 !important;
    scroll-behavior: smooth !important;
}

/* Custom scrollbar cho webkit browsers */
.table-responsive::-webkit-scrollbar {
    height: 8px !important;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1 !important;
    border-radius: 4px !important;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #c1c1c1 !important;
    border-radius: 4px !important;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8 !important;
}

/* Đảm bảo bảng có min-width để scroll */
.table {
    min-width: 1000px !important;
    width: 100% !important;
}

/* CHỈ cột đầu tiên giữ nguyên vị trí */
.table thead th:first-child {
    position: sticky !important;
    left: 0 !important;
    z-index: 20 !important;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    box-shadow: 2px 0 4px rgba(0,0,0,0.1) !important;
    will-change: transform !important;
}

.table tbody td:first-child {
    position: sticky !important;
    left: 0 !important;
    z-index: 15 !important;
    background: white !important;
    box-shadow: 2px 0 4px rgba(0,0,0,0.1) !important;
    border-right: 1px solid #dee2e6 !important;
    will-change: transform !important;
}

/* Cải thiện hiển thị cột tiêu đề */
.table tbody td:first-child .employee-name {
    font-weight: 600 !important;
    color: #495057 !important;
    margin-bottom: 4px !important;
    display: block !important;
}

.table tbody td:first-child small {
    color: #6c757d !important;
    font-size: 0.8rem !important;
    line-height: 1.3 !important;
}

/* Hover effect cho cột sticky */
.table tbody tr:hover td:first-child {
    background: #f8f9fa !important;
}

/* Các cột khác di chuyển bình thường */
.table thead th:not(:first-child) {
    position: relative !important;
    left: auto !important;
    right: auto !important;
    z-index: 10 !important;
}

.table tbody td:not(:first-child) {
    position: relative !important;
    left: auto !important;
    right: auto !important;
    z-index: 5 !important;
}

/* Responsive cho mobile */
@media (max-width: 768px) {
    .table {
        min-width: 800px !important;
    }
    
    .table thead th:first-child {
        min-width: 150px !important;
        max-width: 200px !important;
    }
    
    .table tbody td:first-child {
        min-width: 150px !important;
        max-width: 200px !important;
    }
    
    /* Hide scrollbar on mobile but keep functionality */
    .table-responsive::-webkit-scrollbar {
        height: 4px !important;
    }
}

@media (max-width: 576px) {
    .table {
        min-width: 700px !important;
    }
    
    .table thead th:first-child {
        min-width: 120px !important;
        max-width: 180px !important;
    }
    
    .table tbody td:first-child {
        min-width: 120px !important;
        max-width: 180px !important;
    }
}

/* Tablet */
@media (min-width: 769px) and (max-width: 991px) {
    .table {
        min-width: 900px !important;
    }
    
    .table thead th:first-child {
        min-width: 180px !important;
        max-width: 250px !important;
    }
    
    .table tbody td:first-child {
        min-width: 180px !important;
        max-width: 250px !important;
    }
}

/* Desktop */
@media (min-width: 992px) {
    .table {
        min-width: 1100px !important;
    }
    
    .table thead th:first-child {
        min-width: 200px !important;
        max-width: 300px !important;
    }
    
    .table tbody td:first-child {
        min-width: 200px !important;
        max-width: 300px !important;
    }
}

/* Print styles */
@media print {
    .table thead th:first-child,
    .table tbody td:first-child {
        position: static !important;
        box-shadow: none !important;
    }
}

/* CSS cho giao diện */
.table-hover tbody tr:hover {
    background-color: #f8f9fa;
    transition: background-color 0.2s ease;
}

.badge {
    font-size: 0.8rem;
    font-weight: 500;
}

.btn-group .btn {
    transition: all 0.3s ease;
}

.btn-group .btn:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
}

.table th {
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}

.table td {
    vertical-align: middle;
}

/* Responsive cho mobile */
@media (max-width: 768px) {
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        border-radius: 0.375rem !important;
        margin-bottom: 0.25rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
    /* Filter responsive */
    .filter-section .row {
        margin: 0;
    }
    
    .filter-section .col-md-6 {
        margin-bottom: 1rem;
    }
    
    .filter-section .btn-group {
        width: 100%;
    }
    
    .filter-section .btn-group .btn {
        flex: 1;
    }
}

/* Filter styling */
.filter-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.filter-section small {
    font-weight: 600;
    color: #495057;
}

.filter-section .form-control-sm {
    border-radius: 6px;
    border: 1px solid #ced4da;
}

.filter-section .form-control-sm:focus {
    border-color: #558EC1;
    box-shadow: 0 0 0 0.2rem rgba(85, 142, 193, 0.25);
}

.filter-section .btn-sm {
    border-radius: 6px;
    font-weight: 500;
}

/* Sort indicators */
.sort-indicator {
    display: inline-block;
    margin-left: 0.5rem;
    opacity: 0.5;
    cursor: pointer;
}

.sort-indicator.active {
    opacity: 1;
    color: #558EC1;
}

.sort-indicator:hover {
    opacity: 0.8;
}
</style>
@endpush

@section('content')
{{-- Statistics Section --}}
<div class="row g-3 mb-3">
  <div class="col-md-3">
    <div class="card card-stat p-3 text-center">
      <h5 class="text-primary">{{ $stats['total'] }}</h5>
      <p>Tổng nhân viên</p>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card card-stat p-3 text-center">
      <h5 class="text-danger">{{ $stats['admin'] }}</h5>
      <p>Quản trị viên</p>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card card-stat p-3 text-center">
      <h5 class="text-warning">{{ $stats['manager'] }}</h5>
      <p>Quản lý</p>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card card-stat p-3 text-center">
      <h5 class="text-success">{{ $stats['employee'] }}</h5>
      <p>Nhân viên</p>
    </div>
  </div>
</div>

<div class="mb-3 filter-section">
  {{-- Filter theo phòng ban --}}
  <div class="mb-2">
    <small class="text-muted mb-2 d-block"><i class="bi bi-building me-1"></i>Lọc theo phòng ban:</small>
    <div class="dropdown">
      <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="departmentDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        @if(request('department'))
          @php
            $selectedDepartment = $departments->where('id', request('department'))->first();
          @endphp
          {{ $selectedDepartment ? $selectedDepartment->name : 'Tất cả' }}
        @else
          Tất cả
        @endif
      </button>
      <ul class="dropdown-menu" aria-labelledby="departmentDropdown">
        <li>
          <form method="GET" action="{{ route('users.index') }}" class="d-inline">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="role" value="{{ request('role') }}">
            <input type="hidden" name="sort" value="{{ request('sort') }}">
            <input type="hidden" name="direction" value="{{ request('direction') }}">
            <button type="submit" class="dropdown-item{{ !request('department') ? ' active' : '' }}">
              <i class="bi bi-list-ul me-2"></i>Tất cả
            </button>
          </form>
        </li>
        @foreach($departments as $department)
          <li>
            <form method="GET" action="{{ route('users.index') }}" class="d-inline">
              <input type="hidden" name="search" value="{{ request('search') }}">
              <input type="hidden" name="role" value="{{ request('role') }}">
              <input type="hidden" name="sort" value="{{ request('sort') }}">
              <input type="hidden" name="direction" value="{{ request('direction') }}">
              <input type="hidden" name="department" value="{{ $department->id }}">
              <button type="submit" class="dropdown-item{{ request('department') == $department->id ? ' active' : '' }}">
                <i class="bi bi-building me-2"></i>{{ $department->name }}
              </button>
            </form>
          </li>
        @endforeach
      </ul>
    </div>
  </div>

  {{-- Filter theo vai trò --}}
  <div class="mb-2">
    <small class="text-muted mb-2 d-block"><i class="bi bi-person-badge me-1"></i>Lọc theo vai trò:</small>
    <div class="d-flex flex-wrap gap-2">
      <form method="GET" action="{{ route('users.index') }}">
        <input type="hidden" name="search" value="{{ request('search') }}">
        <input type="hidden" name="department" value="{{ request('department') }}">
        <input type="hidden" name="sort" value="{{ request('sort') }}">
        <input type="hidden" name="direction" value="{{ request('direction') }}">
        <button type="submit" class="btn btn-sm btn-outline-secondary{{ !request('role') ? ' active' : '' }}">Tất cả</button>
      </form>

      <form method="GET" action="{{ route('users.index') }}">
        <input type="hidden" name="search" value="{{ request('search') }}">
        <input type="hidden" name="department" value="{{ request('department') }}">
        <input type="hidden" name="sort" value="{{ request('sort') }}">
        <input type="hidden" name="direction" value="{{ request('direction') }}">
        <input type="hidden" name="role" value="admin">
        <button type="submit" class="btn btn-sm btn-outline-danger{{ request('role') == 'admin' ? ' active' : '' }}">Quản trị viên</button>
      </form>

      <form method="GET" action="{{ route('users.index') }}">
        <input type="hidden" name="search" value="{{ request('search') }}">
        <input type="hidden" name="department" value="{{ request('department') }}">
        <input type="hidden" name="sort" value="{{ request('sort') }}">
        <input type="hidden" name="direction" value="{{ request('direction') }}">
        <input type="hidden" name="role" value="director">
        <button type="submit" class="btn btn-sm btn-outline-info{{ request('role') == 'director' ? ' active' : '' }}">Người điều hành</button>
      </form>

      <form method="GET" action="{{ route('users.index') }}">
        <input type="hidden" name="search" value="{{ request('search') }}">
        <input type="hidden" name="department" value="{{ request('department') }}">
        <input type="hidden" name="sort" value="{{ request('sort') }}">
        <input type="hidden" name="direction" value="{{ request('direction') }}">
        <input type="hidden" name="role" value="manager">
        <button type="submit" class="btn btn-sm btn-outline-warning{{ request('role') == 'manager' ? ' active' : '' }}">Quản lý</button>
      </form>

      <form method="GET" action="{{ route('users.index') }}">
        <input type="hidden" name="search" value="{{ request('search') }}">
        <input type="hidden" name="department" value="{{ request('department') }}">
        <input type="hidden" name="sort" value="{{ request('sort') }}">
        <input type="hidden" name="direction" value="{{ request('direction') }}">
        <input type="hidden" name="role" value="employee">
        <button type="submit" class="btn btn-sm btn-outline-success{{ request('role') == 'employee' ? ' active' : '' }}">Nhân viên</button>
      </form>
    </div>
  </div>

  {{-- Search và Sort --}}
  <div class="row g-3">
    <div class="col-md-6">
      <small class="text-muted mb-2 d-block"><i class="bi bi-search me-1"></i>Tìm kiếm:</small>
      <form method="GET" action="{{ route('users.index') }}" class="d-flex">
        <input type="hidden" name="department" value="{{ request('department') }}">
        <input type="hidden" name="role" value="{{ request('role') }}">
        <input type="hidden" name="sort" value="{{ request('sort') }}">
        <input type="hidden" name="direction" value="{{ request('direction') }}">
        <input type="text" name="search" value="{{ request('search') }}" 
               class="form-control form-control-sm me-2" placeholder="Tìm theo tên, email...">
        <button type="submit" class="btn btn-sm btn-primary" style="background-color: #558EC1; border-color: #558EC1;">
          <i class="bi bi-search"></i>
        </button>
      </form>
    </div>
    
    <div class="col-md-6">
      <small class="text-muted mb-2 d-block"><i class="bi bi-sort-numeric-down me-1"></i>Sắp xếp:</small>
      <div class="btn-group" role="group">
        <form method="GET" action="{{ route('users.index') }}" class="d-inline">
          <input type="hidden" name="search" value="{{ request('search') }}">
          <input type="hidden" name="department" value="{{ request('department') }}">
          <input type="hidden" name="role" value="{{ request('role') }}">
          <input type="hidden" name="sort" value="name">
          <input type="hidden" name="direction" value="{{ request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc' }}">
          <button type="submit" class="btn btn-sm btn-outline-info{{ request('sort') == 'name' ? ' active' : '' }}" style="border-color: #558EC1; color: #558EC1;">
            <i class="bi bi-sort-alpha-{{ request('sort') == 'name' && request('direction') == 'asc' ? 'down' : 'up' }} me-1"></i>Tên
          </button>
        </form>
        <form method="GET" action="{{ route('users.index') }}" class="d-inline">
          <input type="hidden" name="search" value="{{ request('search') }}">
          <input type="hidden" name="department" value="{{ request('department') }}">
          <input type="hidden" name="role" value="{{ request('role') }}">
          <input type="hidden" name="sort" value="created_at">
          <input type="hidden" name="direction" value="{{ request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc' }}">
          <button type="submit" class="btn btn-sm btn-outline-info{{ request('sort') == 'created_at' ? ' active' : '' }}" style="border-color: #558EC1; color: #558EC1;">
            <i class="bi bi-sort-numeric-{{ request('sort') == 'created_at' && request('direction') == 'asc' ? 'down' : 'up' }} me-1"></i>Ngày tạo
          </button>
        </form>
      </div>
    </div>
  </div>

  {{-- Nút xóa filter --}}
  @if(request('search') || request('department') || request('role') || request('sort'))
    <div class="mt-2">
      <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-x-circle me-1"></i>Xóa bộ lọc
      </a>
    </div>
  @endif
</div>

{{-- Danh sách nhân viên --}}
<div class="card shadow-sm border-0">
  <div class="card-header text-white d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #558EC1 0%, #5DA444 100%);">
    <h5 class="mb-0">
      <i class="bi bi-people me-2"></i>
      Danh sách nhân viên
      <span class="badge bg-light text-dark ms-2">{{ $users->total() }} nhân viên</span>
    </h5>
    <a href="{{ route('users.create') }}" class="btn btn-success">
      <i class="bi bi-plus-circle me-1"></i>Thêm nhân viên
    </a>
  </div>
  
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th class="px-4 py-3 fw-semibold">Thông tin nhân viên</th>
            <th class="px-4 py-3 fw-semibold">Email</th>
            <th class="px-4 py-3 fw-semibold">Số điện thoại</th>
            <th class="px-4 py-3 fw-semibold">Phòng ban</th>
            <th class="px-4 py-3 fw-semibold">Vai trò</th>
            <th class="px-4 py-3 fw-semibold">Ngày tạo</th>
            <th class="px-4 py-3 fw-semibold text-end">Hành động</th>
          </tr>
        </thead>
        <tbody>
          @forelse($users as $user)
            <tr class="border-bottom">
              <td class="px-4 py-3">
                <div class="d-flex align-items-center">
                  <img src="{{ $user->avatar_url }}" 
                       alt="{{ $user->name }}" 
                       class="rounded-circle me-3" 
                       style="width: 45px; height: 45px; object-fit: cover; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                  <div>
                    <div class="fw-medium text-dark employee-name">{{ $user->name }}</div>
                    <small class="text-muted">ID: {{ $user->id }}</small>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3">
                <a href="mailto:{{ $user->email }}" class="text-decoration-none">
                  <i class="bi bi-envelope me-1"></i>{{ $user->email }}
                </a>
              </td>
              <td class="px-4 py-3">
                @if($user->phone)
                  <a href="tel:{{ $user->phone }}" class="text-decoration-none">
                    <i class="bi bi-telephone me-1"></i>{{ $user->phone }}
                  </a>
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td class="px-4 py-3">
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
              <td class="px-4 py-3">
                                 @php
                   $roleBadge = [
                       'admin' => ['bg-danger', 'Quản trị viên'],
                       'director' => ['bg-info', 'Người điều hành'],
                       'manager' => ['bg-warning', 'Quản lý'],
                       'employee' => ['bg-success', 'Nhân viên']
                   ][$user->role] ?? ['bg-secondary', 'Không xác định'];
                 @endphp
                <span class="badge {{ $roleBadge[0] }} bg-opacity-10 text-dark border border-{{ $roleBadge[0] }}">
                  <i class="bi bi-person-badge me-1"></i>{{ $roleBadge[1] }}
                </span>
              </td>
              <td class="px-4 py-3">
                <span class="text-muted">{{ $user->created_at->format('d/m/Y') }}</span>
              </td>
              <td class="px-4 py-3 text-end">
                <div class="btn-group" role="group">
                  <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-primary border-0 rounded-start">
                    <i class="bi bi-eye me-1"></i>Xem
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
                      <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-warning border-0">
                        <i class="bi bi-pencil me-1"></i>Sửa
                      </a>
                  @endif
                  @if($canDelete)
                      <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" data-confirm="Xóa nhân viên này?">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger border-0 rounded-end">
                          <i class="bi bi-trash me-1"></i>Xóa
                        </button>
                      </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-5">
                <div class="text-muted">
                  <i class="bi bi-people fa-3x mb-3 opacity-50"></i>
                  <h6 class="mb-2">Chưa có nhân viên nào</h6>
                  <p class="mb-0">Hãy thêm nhân viên mới để bắt đầu</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  
  @if($users->hasPages())
    <div class="card-footer bg-light border-0">
      <div class="d-flex justify-content-between align-items-center">
        <div class="text-muted">
          Hiển thị {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} 
          trong tổng số {{ $users->total() }} kết quả
        </div>
        
        {{-- Pagination đơn giản --}}
        <nav aria-label="Page navigation">
          <ul class="pagination mb-0">
            {{-- Previous Page Link --}}
            @if ($users->onFirstPage())
              <li class="page-item disabled">
                <span class="page-link">« Previous</span>
              </li>
            @else
              <li class="page-item">
                <a class="page-link" href="{{ $users->previousPageUrl() }}" rel="prev">« Previous</a>
              </li>
            @endif

            {{-- Pagination Elements - Chỉ hiển thị 5 trang gần nhất --}}
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

            {{-- Next Page Link --}}
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo tooltip Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Debug: Log số lượng tooltip được khởi tạo
    console.log('Đã khởi tạo', tooltipList.length, 'tooltip');

    // Sticky column enhancement
    // Smooth scroll to first column when clicking on sticky column
    const stickyCells = document.querySelectorAll('.table thead th:first-child, .table tbody td:first-child');
    
    stickyCells.forEach(cell => {
        cell.addEventListener('click', function() {
            const tableContainer = this.closest('.table-responsive');
            tableContainer.scrollTo({
                left: 0,
                behavior: 'smooth'
            });
        });
        
        // Add cursor pointer to indicate clickable
        cell.style.cursor = 'pointer';
    });
    
    // Add visual feedback for scrollable content
    const tableResponsive = document.querySelector('.table-responsive');
    if (tableResponsive) {
        tableResponsive.addEventListener('scroll', function() {
            const isScrolled = this.scrollLeft > 0;
            const stickyCells = this.querySelectorAll('.table thead th:first-child, .table tbody td:first-child');
            
            stickyCells.forEach(cell => {
                if (isScrolled) {
                    cell.style.boxShadow = '2px 0 8px rgba(0,0,0,0.15)';
                } else {
                    cell.style.boxShadow = '2px 0 4px rgba(0,0,0,0.1)';
                }
            });
        });
    }
    
    // Thêm cách khởi tạo tooltip thay thế
    setTimeout(function() {
        // Khởi tạo lại tooltip sau 1 giây
        var tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipElements.forEach(function(element) {
            if (!element.hasAttribute('data-bs-original-title')) {
                var title = element.getAttribute('title');
                if (title) {
                    element.setAttribute('data-bs-original-title', title);
                    new bootstrap.Tooltip(element);
                }
            }
        });
        console.log('Đã khởi tạo lại', tooltipElements.length, 'tooltip');
    }, 1000);
    
    // Confirm delete functionality
    document.querySelectorAll('[data-confirm]').forEach(function(element) {
        element.addEventListener('submit', function(e) {
            if (!confirm(this.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endpush
