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
                    <th>Deadline</th>
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
    <div class="card-footer">
        {{ $tasks->withQueryString()->links() }}
    </div>
</div>
@endsection
