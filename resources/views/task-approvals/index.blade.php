@extends('layouts.master')
@section('title', 'Phê duyệt Task')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="bi bi-check-circle me-2"></i>
                        Danh sách Task cần phê duyệt
                    </h4>
                </div>
                <div class="card-body">
                    @if($pendingApprovals->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Task</th>
                                        <th>Phòng ban</th>
                                        <th>Người tạo</th>
                                        <th>Ngày tạo</th>
                                        <th>Trạng thái</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingApprovals as $approval)
                                        <tr>
                                            <td>
                                                <strong>{{ $approval->task->title }}</strong>
                                                <br>
                                                <small class="text-muted">{{ Str::limit($approval->task->description, 100) }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $approval->department->name }}</span>
                                            </td>
                                            <td>{{ $approval->task->creator->name }}</td>
                                            <td>{{ $approval->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <span class="badge bg-warning">Chờ phê duyệt</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('task-approvals.show', $approval) }}" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="bi bi-eye me-1"></i>Xem chi tiết
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-check-circle text-success fs-1 mb-3"></i>
                            <h5>Không có task nào cần phê duyệt</h5>
                            <p class="text-muted">Tất cả task đã được xử lý</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
