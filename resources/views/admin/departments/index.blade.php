@extends('layouts.master')
@section('title', 'Quản lý phòng ban')
@section('content')

<style>
/* Department status styling */
.text-success {
    color: var(--approval-approved-color) !important;
    font-weight: 600;
}

.text-danger {
    color: var(--approval-rejected-color) !important;
    font-weight: 600;
}

.text-dark {
    color: #212529 !important;
}

/* Badge styling improvements */
.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
}

.bg-success {
    background-color: var(--approval-approved-bg) !important;
}

.bg-danger {
    background-color: var(--approval-rejected-bg) !important;
}

.bg-primary {
    background-color: #0d6efd !important;
}

/* Table improvements */
.table td {
    vertical-align: middle;
}

.table tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

/* Icon spacing */
.bi {
    font-size: 0.875rem;
}
</style>
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Danh sách phòng ban</h5>
        <a href="{{ route('departments.create') }}" class="btn btn-primary">Thêm phòng ban</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tên phòng ban</th>
                    <th>Địa chỉ</th>
                    <th>GPS Config</th>
                    <th>Nhân viên</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $dept)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <strong>{{ $dept->name }}</strong>
                            @if($dept->hasGpsConfig())
                                <br><small class="text-success fw-bold"><i class="bi bi-geo-alt-fill me-1"></i>Có GPS</small>
                            @else
                                <br><small class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i>Chưa có GPS</small>
                            @endif
                        </td>
                        <td>
                            @if($dept->address)
                                <small>{{ Str::limit($dept->address, 50) }}</small>
                            @else
                                <small class="text-muted">Chưa có địa chỉ</small>
                            @endif
                        </td>
                        <td>
                            @if($dept->hasGpsConfig())
                                <span class="badge bg-success text-white">
                                    <i class="bi bi-check-circle-fill me-1"></i>Đã cấu hình
                                </span>
                                <br>
                                <span class="text-dark fw-semibold d-block location-name-text"
                                      data-lat="{{ $dept->latitude }}"
                                      data-lng="{{ $dept->longitude }}">Đang tải vị trí...</span>
                                <small class="text-muted">{{ $dept->latitude }}, {{ $dept->longitude }}</small>
                                <br><small class="text-dark fw-semibold">Bán kính: {{ $dept->radius_meters }}m</small>
                            @else
                                <span class="badge bg-danger text-white">
                                    <i class="bi bi-x-circle-fill me-1"></i>Chưa cấu hình
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-primary text-white fw-semibold">
                                <i class="bi bi-people-fill me-1"></i>{{ $dept->users->count() }} nhân viên
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('departments.edit', $dept) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil-fill me-1"></i>Sửa
                            </a>
                            <form action="{{ route('departments.destroy', $dept) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa phòng ban này?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash-fill me-1"></i>Xóa
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">Chưa có phòng ban nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($departments->hasPages())
        <div class="card-footer bg-light border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Hiển thị {{ $departments->firstItem() ?? 0 }} - {{ $departments->lastItem() ?? 0 }} 
                    trong tổng số {{ $departments->total() }} kết quả
                </div>
                
                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0">
                        @if ($departments->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">« Previous</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $departments->previousPageUrl() }}" rel="prev">« Previous</a>
                            </li>
                        @endif

                        @php
                            $start = max(1, $departments->currentPage() - 2);
                            $end = min($departments->lastPage(), $departments->currentPage() + 2);
                        @endphp
                        
                        @if($start > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ $departments->url(1) }}">1</a>
                            </li>
                            @if($start > 2)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                        @endif
                        
                        @for ($page = $start; $page <= $end; $page++)
                            @if ($page == $departments->currentPage())
                                <li class="page-item active">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $departments->url($page) }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endfor
                        
                        @if($end < $departments->lastPage())
                            @if($end < $departments->lastPage() - 1)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                            <li class="page-item">
                                <a class="page-link" href="{{ $departments->url($departments->lastPage()) }}">{{ $departments->lastPage() }}</a>
                            </li>
                        @endif

                        @if ($departments->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $departments->nextPageUrl() }}" rel="next">Next »</a>
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
<script src="{{ asset('js/location-name.js') }}?v=2"></script>
@endsection
