@extends('layouts.master')
@section('title', 'Danh sách công việc')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Danh sách công việc</h5>
        <form method="GET" class="d-flex align-items-center">
            <select name="status" class="form-select me-2" onchange="this.form.submit()">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="todo" {{ request('status')=='todo'?'selected':'' }}>Chưa bắt đầu</option>
                <option value="in_progress" {{ request('status')=='in_progress'?'selected':'' }}>Đang làm</option>
                <option value="done" {{ request('status')=='done'?'selected':'' }}>Hoàn thành</option>
            </select>
            <button class="btn btn-primary">Lọc</button>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tiêu đề</th>

                    <th>Người giao</th>
                    <th>Người nhận</th>
                                            <th>Hạn cuối</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $task->title }}</td>

                        <td>{{ $task->creator->name ?? '-' }}</td>
                        <td>{{ $task->assignee->name ?? '-' }}</td>
                        <td>{{ $task->deadline ? $task->deadline->format('d/m/Y') : '-' }}</td>
                        <td>{{ __("statuses.$task->status") ?? strtoupper($task->status) }}</td>
                        <td>
                            <a href="{{ route('task-detail', $task) }}" class="btn btn-sm btn-info">Xem</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Chưa có công việc nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tasks->hasPages())
        <div class="card-footer bg-light border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Hiển thị {{ $tasks->firstItem() ?? 0 }} - {{ $tasks->lastItem() ?? 0 }} 
                    trong tổng số {{ $tasks->total() }} kết quả
                </div>
                
                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0">
                        @if ($tasks->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">« Previous</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $tasks->withQueryString()->previousPageUrl() }}" rel="prev">« Previous</a>
                            </li>
                        @endif

                        @php
                            $start = max(1, $tasks->currentPage() - 2);
                            $end = min($tasks->lastPage(), $tasks->currentPage() + 2);
                        @endphp
                        
                        @if($start > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ $tasks->withQueryString()->url(1) }}">1</a>
                            </li>
                            @if($start > 2)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                        @endif
                        
                        @for ($page = $start; $page <= $end; $page++)
                            @if ($page == $tasks->currentPage())
                                <li class="page-item active">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $tasks->withQueryString()->url($page) }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endfor
                        
                        @if($end < $tasks->lastPage())
                            @if($end < $tasks->lastPage() - 1)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                            <li class="page-item">
                                <a class="page-link" href="{{ $tasks->withQueryString()->url($tasks->lastPage()) }}">{{ $tasks->lastPage() }}</a>
                            </li>
                        @endif

                        @if ($tasks->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $tasks->withQueryString()->nextPageUrl() }}" rel="next">Next »</a>
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
