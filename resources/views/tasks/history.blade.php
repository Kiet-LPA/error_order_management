@extends('layouts.master')
@section('title', 'Lịch sử công việc: ' . $task->title)

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Lịch sử hoạt động của công việc</h5>
        <a href="{{ route('task-detail', $task) }}" class="btn btn-secondary btn-sm float-end">Quay lại</a>
    </div>
    <div class="card-body">
        <ul class="list-group">
            @forelse($task->activities as $act)
                <li class="list-group-item">
                    <strong>{{ $act->user->name }}</strong>
                    <span class="text-muted ms-2">{{ $act->created_at->format('d/m/Y H:i') }}</span>
                    <div>{{ $act->meta }}</div>
                </li>
            @empty
                <li class="list-group-item text-muted">Chưa có hoạt động nào.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
