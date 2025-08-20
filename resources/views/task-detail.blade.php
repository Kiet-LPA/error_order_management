@extends('layouts.master')
@section('title',$task->title)

@section('content')
<div class="task-header">
  <h3 class="mb-2">{{ $task->title }}</h3>
  <span class="badge priority-badge bg-{{ $task->status=='done'?'success':($task->status=='in_progress'?'primary': 'warning') }}">
    {{ __("statuses.$task->status") ?? strtoupper($task->status) }}
  </span>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="progress-section">
      <h6 class="mb-3">Thông tin chung</h6>
      <div class="row">
        <div class="col-6">Người giao: <strong>{{ $task->creator->name }}</strong></div>
        <div class="col-6">Deadline:
          <strong>{{ $task->deadline? $task->deadline->format('d/m/Y'):'—' }}</strong>
        </div>
        <div class="col-6">Người nhận: <strong>{{ $task->assignee?->name ?? '—' }}</strong></div>
        <div class="col-6">Trạng thái: <strong>{{ __("statuses.$task->status") ?? strtoupper($task->status) }}</strong></div>
        @if($task->tracking_code)
        <div class="col-12 mt-2">
          <div class="d-flex align-items-center">
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary me-2">
              <i class="fas fa-qr-code me-1"></i>
              Mã Tracking: {{ $task->tracking_code }}
            </span>
            <button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('{{ $task->tracking_code }}')" title="Copy mã tracking">
              <i class="fas fa-copy"></i> Copy
            </button>
          </div>
        </div>
        @endif
      </div>
    </div>

    <div class="comment-section">
      <h6 class="mb-3">Thảo luận</h6>
      <form class="mb-3" action="{{ route('tasks.comment',$task) }}" method="POST">
        @csrf
        <textarea name="content" class="form-control mb-2" rows="3" placeholder="Viết bình luận..."></textarea>
        <button class="btn btn-primary btn-sm">Gửi bình luận</button>
      </form>

      @forelse($task->activities as $act)
        <div class="comment-item">
          <strong>{{ $act->user->name }}</strong>
          <small class="text-muted ms-2">{{ $act->created_at->diffForHumans() }}</small>
          <div>{{ $act->meta }}</div>
        </div>
      @empty
        <div class="text-muted">Chưa có bình luận.</div>
      @endforelse
    </div>
  </div>

  <div class="col-lg-4">
    <div class="report-card">
      <h6 class="mb-3">File đính kèm</h6>
      @forelse($task->attachments ?? [] as $file)
        <div class="file-attachment">
          📎 <a href="{{ $file['url'] }}" target="_blank">{{ $file['name'] }}</a>
        </div>
      @empty
        <div class="text-muted">Chưa có tệp.</div>
      @endforelse
    </div>

    <div class="report-card">
      <h6 class="mb-3">Hành động</h6>
      <a href="{{ route('tasks.updateStatus',[$task,'status'=>'done']) }}" class="btn btn-success w-100 mb-2">✅ Hoàn thành</a>
      <a href="{{ route('tasks.updateStatus',[$task,'status'=>'in_progress']) }}" class="btn btn-primary w-100 mb-2">🔄 Cập nhật trạng thái</a>
      <a href="{{ route('tasks.history',$task) }}" class="btn btn-outline-info w-100">👁 Xem lịch sử</a>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Hiển thị thông báo thành công
        const button = event.target.closest('button');
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i> Copied!';
        button.classList.remove('btn-outline-primary');
        button.classList.add('btn-success');
        
        setTimeout(function() {
            button.innerHTML = originalHTML;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-primary');
        }, 2000);
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
        alert('Không thể copy mã tracking. Vui lòng copy thủ công.');
    });
}
</script>
@endpush
