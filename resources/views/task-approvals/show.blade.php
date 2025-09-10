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
                        Phê duyệt Task: {{ $approval->task->title }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Task Details -->
                            <div class="mb-4">
                                <h5>Thông tin Task</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Tiêu đề:</strong> {{ $approval->task->title }}</p>
                                        <p><strong>Mô tả:</strong> {{ $approval->task->description ?: 'Không có mô tả' }}</p>
                                        <p><strong>Người tạo:</strong> {{ $approval->task->creator->name }}</p>
                                        <p><strong>Ngày tạo:</strong> {{ $approval->task->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Hạn cuối:</strong> {{ $approval->task->deadline ? $approval->task->deadline->format('d/m/Y H:i') : 'Không có' }}</p>
                                        <p><strong>Độ ưu tiên:</strong> 
                                            <span class="badge bg-{{ $approval->task->priority == 'high' ? 'danger' : ($approval->task->priority == 'medium' ? 'warning' : 'info') }}">
                                                {{ __("priorities.{$approval->task->priority}") }}
                                            </span>
                                        </p>
                                        <p><strong>Phòng ban:</strong> {{ $approval->department->name }}</p>
                                        <p><strong>Trạng thái:</strong> 
                                            <span class="badge bg-warning">Chờ phê duyệt</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Assignees -->
                            @if($approval->task->assignees->count() > 0)
                            <div class="mb-4">
                                <h5>Người thực hiện</h5>
                                <div class="mt-1">
                                    @foreach($approval->task->assignees as $assignee)
                                        <div class="text-dark mb-1">{{ $assignee->name }} ({{ $assignee->display_role }})</div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Followers -->
                            @if($approval->task->followers->count() > 0)
                            <div class="mb-4">
                                <h5>Người theo dõi</h5>
                                <div class="mt-1">
                                    @foreach($approval->task->followers as $follower)
                                        <div class="text-dark mb-1">{{ $follower->user->name }} ({{ $follower->user->display_role }})</div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <!-- Approval Actions -->
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-white">
                                    <h6 class="mb-0">Hành động phê duyệt</h6>
                                </div>
                                <div class="card-body">
                                    <form id="approvalForm">
                                        <div class="mb-3">
                                            <label for="comment" class="form-label">Ghi chú (tùy chọn)</label>
                                            <textarea class="form-control" id="comment" name="comment" rows="3" placeholder="Nhập ghi chú nếu cần..."></textarea>
                                        </div>
                                        
                                        <div class="d-grid gap-2">
                                            <button type="button" class="btn btn-success" onclick="approveTask()">
                                                <i class="bi bi-check-circle me-2"></i>Phê duyệt
                                            </button>
                                            <button type="button" class="btn btn-danger" onclick="rejectTask()">
                                                <i class="bi bi-x-circle me-2"></i>Từ chối
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Approval Status -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="mb-0">Trạng thái phê duyệt</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Phòng ban:</strong> {{ $approval->department->name }}</p>
                                    <p><strong>Manager:</strong> {{ $approval->manager->name }}</p>
                                    <p><strong>Trạng thái:</strong> 
                                        <span class="badge bg-warning">Chờ phê duyệt</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function approveTask() {
    if (confirm('Bạn có chắc muốn phê duyệt task này?')) {
        const comment = document.getElementById('comment').value;
        
        fetch('{{ route("task-approvals.approve", $approval) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ comment: comment })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Đã phê duyệt task thành công!');
                window.location.href = '{{ route("task-approvals.index") }}';
            } else {
                alert('Lỗi: ' + data.message);
            }
        });
    }
}

function rejectTask() {
    if (confirm('Bạn có chắc muốn từ chối task này?')) {
        const comment = document.getElementById('comment').value;
        
        fetch('{{ route("task-approvals.reject", $approval) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ comment: comment })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Đã từ chối task thành công!');
                window.location.href = '{{ route("task-approvals.index") }}';
            } else {
                alert('Lỗi: ' + data.message);
            }
        });
    }
}
</script>
@endsection
