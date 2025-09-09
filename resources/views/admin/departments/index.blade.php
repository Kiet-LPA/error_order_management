@extends('layouts.master')
@section('title', 'Quản lý phòng ban')
@section('content')
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
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $dept)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $dept->name }}</td>
                        <td>
                            <a href="{{ route('departments.edit', $dept) }}" class="btn btn-sm btn-warning">Sửa</a>
                            <form action="{{ route('departments.destroy', $dept) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa phòng ban này?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">Xóa</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center">Chưa có phòng ban nào.</td></tr>
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
@endsection
