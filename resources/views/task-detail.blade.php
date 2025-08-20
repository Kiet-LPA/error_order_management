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
        @if($task->priority)
        <div class="col-6">Độ ưu tiên: 
          <span class="badge bg-{{ $task->priority == 'high' ? 'danger' : ($task->priority == 'medium' ? 'warning' : 'info') }}">
            {{ ucfirst($task->priority) }}
          </span>
        </div>
        @endif
        @if($task->tracking_code)
        <div class="col-12 mt-2">
          <div class="d-flex align-items-center">
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary me-2">
              <i class="bi bi-qr-code me-1"></i>
              Mã Tracking: {{ $task->tracking_code }}
            </span>
            <button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('{{ $task->tracking_code }}')" title="Copy mã tracking">
              <i class="bi bi-copy"></i> Copy
            </button>
          </div>
        </div>
        @endif
      </div>
    </div>

    <!-- Mô tả công việc (nếu có) -->
    @if($task->description)
    <div class="card mb-3 border-info">
      <div class="card-header bg-info text-white">
        <h6 class="mb-0">
          <i class="bi bi-file-text me-2"></i>Mô tả công việc
        </h6>
      </div>
      <div class="card-body">
        <div class="task-description">
          {!! nl2br(e($task->description)) !!}
        </div>
      </div>
    </div>
    @endif

    <!-- Lý do từ chối (nếu có) -->
    @if($task->rejection_reason)
    <div class="card mb-3 border-warning">
      <div class="card-header bg-warning text-dark">
        <h6 class="mb-0">
          <i class="bi bi-exclamation-triangle me-2"></i>Lý do từ chối
        </h6>
      </div>
      <div class="card-body">
        <div class="text-warning">
          {!! nl2br(e($task->rejection_reason)) !!}
        </div>
      </div>
    </div>
    @endif

    <!-- Ghi chú hoàn thành (nếu có) -->
    @if($task->finish_note)
    <div class="card mb-3 border-success">
      <div class="card-header bg-success text-white">
        <h6 class="mb-0">
          <i class="bi bi-check-circle me-2"></i>Ghi chú hoàn thành
        </h6>
      </div>
      <div class="card-body">
        <div class="text-success">
          {!! nl2br(e($task->finish_note)) !!}
        </div>
      </div>
    </div>
    @endif

    <div class="comment-section">
      <h6 class="mb-3">Thảo luận</h6>
      <form class="mb-3" action="{{ route('tasks.comment',$task) }}" method="POST">
        @csrf
        <textarea name="content" id="commentTextarea" class="form-control mb-2" rows="3" placeholder="Viết bình luận..." maxlength="500"></textarea>
        <div class="d-flex justify-content-between align-items-center">
          <small class="text-muted">Tối đa 500 ký tự</small>
          <button type="submit" class="btn btn-primary btn-sm" id="submitComment">Gửi bình luận</button>
        </div>
      </form>

      @forelse($task->activities as $act)
        <div class="comment-item">
          <div class="comment-header">
            <strong>{{ $act->user->name }}</strong>
            <small class="text-muted ms-2">{{ $act->created_at->diffForHumans() }}</small>
          </div>
          <div class="comment-content">{{ $act->meta }}</div>
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

<style>
/* Global overflow prevention */
html, body {
  overflow-x: hidden !important;
  max-width: 100% !important;
  word-wrap: break-all !important;
  overflow-wrap: break-all !important;
}

/* Prevent horizontal overflow */
.row {
  overflow-x: hidden;
  max-width: 100%;
}

.col-lg-8, .col-lg-4 {
  overflow-x: hidden;
  word-wrap: break-word;
  max-width: 100%;
}

/* Force text wrapping for all containers */
.container-fluid, .container, .row, .col, .col-lg-8, .col-lg-4 {
  max-width: 100% !important;
  overflow-x: hidden !important;
  width: 100% !important;
  box-sizing: border-box !important;
}

/* Force comment section width */
.comment-section {
  width: 100% !important;
  max-width: 100% !important;
  overflow-x: hidden !important;
  box-sizing: border-box !important;
}

/* Additional CSS for long strings */
.comment-content {
  word-spacing: -2px !important;
  letter-spacing: -0.5px !important;
  font-family: monospace !important;
  font-size: 0.9em !important;
}

/* Validation styles */
.textarea-error {
  border-color: #dc3545 !important;
  background-color: #fff5f5 !important;
  box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

.btn-disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.task-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 1.5rem;
  border-radius: 0.5rem;
  margin-bottom: 1.5rem;
}

.task-header h3 {
  margin: 0;
  font-weight: 600;
}

.priority-badge {
  font-size: 0.9rem;
  padding: 0.5rem 1rem;
}

.task-description {
  line-height: 1.6;
  color: #495057;
}

.comment-section, .progress-section {
  background: white;
  padding: 1.5rem;
  border-radius: 0.5rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  margin-bottom: 1.5rem;
  overflow: hidden;
  word-wrap: break-word;
  max-width: 100%;
  box-sizing: border-box;
}

.comment-item {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 0.5rem;
  margin-bottom: 1rem;
  border-left: 4px solid #007bff;
  word-wrap: break-all;
  overflow-wrap: anywhere;
  max-width: 100%;
  box-sizing: border-box;
  overflow: hidden;
  width: 100%;
  display: block;
}

.comment-item strong {
  color: #495057;
}

.comment-header {
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  max-width: 100%;
  overflow: hidden;
  word-wrap: break-word;
  overflow-wrap: break-word;
}

.comment-content {
  word-wrap: break-word !important;
  overflow-wrap: break-word !important;
  word-break: break-all !important;
  white-space: pre-wrap !important;
  max-width: 100% !important;
  overflow: hidden !important;
  line-height: 1.5;
  color: #495057;
  display: block;
  width: 100%;
}

.report-card {
  background: white;
  padding: 1.5rem;
  border-radius: 0.5rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  margin-bottom: 1.5rem;
}

.file-attachment {
  padding: 0.5rem;
  border: 1px solid #dee2e6;
  border-radius: 0.25rem;
  margin-bottom: 0.5rem;
  background: #f8f9fa;
}

.file-attachment a {
  color: #007bff;
  text-decoration: none;
}

.file-attachment a:hover {
  text-decoration: underline;
}

.card {
  border: none;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
  border-bottom: none;
  font-weight: 600;
}

.card-body {
  max-width: 100% !important;
  overflow: hidden !important;
  word-wrap: break-all !important;
  overflow-wrap: anywhere !important;
}

/* Fix textarea overflow */
.form-control {
  word-wrap: break-word;
  overflow-wrap: break-word;
  resize: vertical;
  max-width: 100%;
  box-sizing: border-box;
  overflow: hidden;
}

/* Fix for comment form */
.comment-section form {
  max-width: 100%;
  overflow: hidden;
}

.comment-section textarea {
  max-width: 100%;
  box-sizing: border-box;
  word-wrap: break-all;
  overflow-wrap: break-all;
  resize: vertical;
}

/* Ensure all text content is properly wrapped */
* {
  word-wrap: break-all !important;
  overflow-wrap: anywhere !important;
  max-width: 100% !important;
}

/* Force break for extremely long strings */
.comment-content {
  word-spacing: 0 !important;
  letter-spacing: 0 !important;
  white-space: pre-wrap !important;
  word-break: break-all !important;
  overflow-wrap: anywhere !important;
  word-wrap: break-all !important;
  max-width: 100% !important;
  width: 100% !important;
  display: block !important;
  box-sizing: border-box !important;
  overflow: hidden !important;
  line-height: 1.4 !important;
}

/* Style for broken lines */
.comment-content br {
  display: block;
  content: "";
  margin: 0;
  padding: 0;
}

/* Force break for long strings */
.comment-item, .comment-content, .task-description, .text-warning, .text-success {
  word-break: break-all !important;
  overflow-wrap: anywhere !important;
  word-wrap: break-all !important;
  max-width: 100% !important;
  overflow: hidden !important;
  display: block !important;
  width: 100% !important;
  box-sizing: border-box !important;
  white-space: pre-wrap !important;
}

/* Specific fix for rejection reason */
.text-warning {
  word-break: break-all !important;
  overflow-wrap: anywhere !important;
  word-wrap: break-all !important;
  max-width: 100% !important;
  width: 100% !important;
  display: block !important;
  box-sizing: border-box !important;
  overflow: hidden !important;
  white-space: pre-wrap !important;
  line-height: 1.4 !important;
  font-size: 0.9em !important;
}

/* Specific fix for comment content */
.comment-content {
  white-space: pre-wrap !important;
  word-break: break-all !important;
  overflow-wrap: break-all !important;
  word-wrap: break-all !important;
  max-width: 100% !important;
  width: 100% !important;
  display: block !important;
  box-sizing: border-box !important;
  padding: 0 !important;
  margin: 0 !important;
  line-height: 1.5 !important;
  overflow: hidden !important;
  hyphens: auto !important;
  -webkit-hyphens: auto !important;
  -moz-hyphens: auto !important;
  -ms-hyphens: auto !important;
}

/* Fix for task description */
.task-description {
  white-space: pre-wrap !important;
  word-wrap: break-word !important;
  overflow-wrap: break-word !important;
  word-break: break-word !important;
  max-width: 100% !important;
  width: 100% !important;
  display: block !important;
  box-sizing: border-box !important;
  line-height: 1.6 !important;
}
</style>
@endsection

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Hiển thị thông báo thành công
        const button = event.target.closest('button');
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="bi bi-check"></i> Copied!';
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

// Function to break long strings
function breakLongStrings() {
    const commentContents = document.querySelectorAll('.comment-content');
    const rejectionReasons = document.querySelectorAll('.text-warning');
    
    // Xử lý comment content
    commentContents.forEach(function(element) {
        const text = element.textContent;
        if (text.length > 30) { // Giảm xuống 30 ký tự
            // Tách chuỗi thành các đoạn 30 ký tự
            const chunks = [];
            for (let i = 0; i < text.length; i += 30) {
                chunks.push(text.slice(i, i + 30));
            }
            // Thay thế nội dung với các đoạn được tách
            element.innerHTML = chunks.join('<br>');
        }
    });
    
    // Xử lý rejection reason
    rejectionReasons.forEach(function(element) {
        const text = element.textContent;
        if (text.length > 30) {
            const chunks = [];
            for (let i = 0; i < text.length; i += 30) {
                chunks.push(text.slice(i, i + 30));
            }
            element.innerHTML = chunks.join('<br>');
        }
    });
}

// Chạy function ngay lập tức
breakLongStrings();
validateCommentInput();

// Chạy function khi trang load
document.addEventListener('DOMContentLoaded', function() {
    breakLongStrings();
    validateCommentInput();
});

// Chạy function sau 1 giây để đảm bảo
setTimeout(breakLongStrings, 1000);

// Chạy function khi có comment mới (nếu cần)
function addComment() {
    // Sau khi thêm comment, chạy lại function
    setTimeout(breakLongStrings, 100);
}

// Function để kiểm tra và ngăn chặn từ dài hơn 40 ký tự
function validateCommentInput() {
    const textarea = document.getElementById('commentTextarea');
    const submitBtn = document.getElementById('submitComment');
    
    if (textarea) {
        textarea.addEventListener('input', function() {
            const text = this.value;
            const words = text.split(/\s+/);
            let hasLongWord = false;
            
            // Kiểm tra từng từ
            for (let word of words) {
                if (word.length > 45) {
                    hasLongWord = true;
                    break;
                }
            }
            
            // Nếu có từ dài hơn 40 ký tự
            if (hasLongWord) {
                this.style.borderColor = '#dc3545';
                this.style.backgroundColor = '#fff5f5';
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Từ quá dài (>45 ký tự)';
                submitBtn.classList.remove('btn-primary');
                submitBtn.classList.add('btn-danger');
            } else {
                this.style.borderColor = '';
                this.style.backgroundColor = '';
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Gửi bình luận';
                submitBtn.classList.remove('btn-danger');
                submitBtn.classList.add('btn-primary');
            }
        });
        
        // Kiểm tra khi submit form
        textarea.closest('form').addEventListener('submit', function(e) {
            const text = textarea.value;
            const words = text.split(/\s+/);
            
            for (let word of words) {
                if (word.length > 45) {
                    e.preventDefault();
                    alert('Không được phép nhập từ dài hơn 45 ký tự!');
                    return false;
                }
            }
        });
    }
}

// Chạy function khi có thay đổi DOM
const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        if (mutation.type === 'childList') {
            setTimeout(breakLongStrings, 100);
        }
    });
});

// Bắt đầu observe
observer.observe(document.body, {
    childList: true,
    subtree: true
});
</script>
@endpush
