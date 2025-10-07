@extends('layouts.master')
@section('title',$task->title)

@section('content')

<div class="task-header">
  <h3 class="mb-2">{{ $task->title }}</h3>
  <div class="d-flex gap-2">
    <span class="badge priority-badge bg-{{ $task->status=='done'?'success':($task->status=='in_progress'?'primary': 'warning') }}">
      {{ __("statuses.$task->status") ?? strtoupper($task->status) }}
    </span>
    @if($task->priority)
    <span class="badge priority-badge bg-{{ $task->priority == 'high' ? 'danger' : ($task->priority == 'medium' ? 'warning' : 'info') }}">
      Độ ưu tiên: {{ __("priorities.$task->priority") }}
    </span>
    @endif
  </div>
</div>

<!-- Mô tả công việc - Đặt ở đây để đảm bảo hiển thị -->
<div class="card mb-3">
  <div class="card-header bg-info text-white">
    <h6 class="mb-0">
      <i class="bi bi-file-text me-2"></i>Mô tả công việc
    </h6>
  </div>
  <div class="card-body">
    @if($task->description && trim($task->description) !== '')
      <div class="description-content">
        {{ $task->description }}
      </div>
    @else
      <em class="text-muted">Không có mô tả</em>
    @endif
    
    <!-- Debug info -->
    <div class="mt-2 p-2 bg-warning border rounded">
      <small class="text-muted">
        <strong>Debug:</strong><br>
        Description value: "{{ $task->description ?? 'NULL' }}"<br>
        Type: {{ gettype($task->description) }}<br>
        Empty: {{ empty($task->description) ? 'Yes' : 'No' }}<br>
        Length: {{ strlen($task->description ?? '') }}<br>
        Trimmed empty: {{ trim($task->description ?? '') === '' ? 'Yes' : 'No' }}<br>
        Raw attributes: {{ json_encode($task->getAttributes()) }}
      </small>
    </div>
    
  </div>
</div>

<!-- Debug Subtasks -->
<div class="card mb-3">
  <div class="card-header bg-warning text-dark">
    <h6 class="mb-0">🔍 Debug Subtasks</h6>
  </div>
  <div class="card-body">
    <p><strong>hasSubtasks():</strong> {{ $task->hasSubtasks() ? 'true' : 'false' }}</p>
    <p><strong>Subtasks count:</strong> {{ $task->subtasks()->count() }}</p>
    <p><strong>Subtasks loaded:</strong> {{ $task->subtasks->count() }}</p>
    @if($task->subtasks->count() > 0)
      <p><strong>Subtasks:</strong></p>
      <ul>
        @foreach($task->subtasks as $subtask)
          <li>{{ $subtask->title }} (Status: {{ $subtask->status }})</li>
        @endforeach
      </ul>
    @endif
  </div>
</div>

<!-- Subtasks Section -->
@if($task->hasSubtasks())
<div class="card mb-3">
  <div class="card-header bg-success text-white">
    <div class="d-flex justify-content-between align-items-center">
      <h6 class="mb-0">
        <i class="bi bi-list-task me-2"></i>Các bước thực hiện
      </h6>
      <div class="d-flex align-items-center">
        <span class="badge bg-light text-dark me-2">
          {{ $task->getCompletedSubtasksCount() }}/{{ $task->getTotalSubtasksCount() }} hoàn thành
        </span>
        <div class="progress" style="width: 100px; height: 20px;">
          <div class="progress-bar bg-light" role="progressbar" 
               style="width: {{ $task->getSubtasksProgressPercentage() }}%"
               aria-valuenow="{{ $task->getSubtasksProgressPercentage() }}" 
               aria-valuemin="0" aria-valuemax="100">
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="card-body">
    <div class="subtasks-list">
      @foreach($task->subtasks as $subtask)
        <div class="subtask-item border rounded p-3 mb-3" data-subtask-id="{{ $subtask->id }}">
          <div class="row align-items-center">
            <div class="col-md-1 text-center">
              @if($subtask->isCompleted())
                <i class="bi bi-check-circle-fill text-success fs-4"></i>
              @elseif($subtask->isInProgress())
                <i class="bi bi-clock-fill text-warning fs-4"></i>
              @else
                <i class="bi bi-circle text-muted fs-4"></i>
              @endif
            </div>
            <div class="col-md-6">
              <h6 class="mb-1">{{ $subtask->title }}</h6>
              @if($subtask->description)
                <p class="text-muted small mb-0">{{ $subtask->description }}</p>
              @endif
              <small class="text-muted">
                <i class="bi bi-person me-1"></i>
                {{ $subtask->assignedUser->name ?? 'Chưa phân công' }}
              </small>
            </div>
            <div class="col-md-3">
              <span class="badge bg-{{ $subtask->isCompleted() ? 'success' : ($subtask->isInProgress() ? 'warning' : 'secondary') }}">
                {{ $subtask->isCompleted() ? 'Đã hoàn thành' : ($subtask->isInProgress() ? 'Đang thực hiện' : 'Chờ thực hiện') }}
              </span>
              @if($subtask->completed_at)
                <br><small class="text-muted">Hoàn thành: {{ $subtask->completed_at->format('d/m/Y H:i') }}</small>
              @endif
            </div>
            <div class="col-md-2 text-end">
              @if($subtask->canBeCompletedBy(auth()->user()))
                @if($subtask->isCompleted())
                  <button class="btn btn-outline-secondary btn-sm" 
                          onclick="updateSubtaskStatus({{ $subtask->id }}, 'todo')"
                          title="Đánh dấu chưa hoàn thành">
                    <i class="bi bi-arrow-counterclockwise"></i>
                  </button>
                @else
                  <button class="btn btn-success btn-sm" 
                          onclick="completeSubtask({{ $subtask->id }})"
                          title="Đánh dấu hoàn thành">
                    <i class="bi bi-check"></i> Hoàn thành
                  </button>
                @endif
              @else
                <small class="text-muted">
                  @if($subtask->assignedUser)
                    {{ $subtask->assignedUser->name }} thực hiện
                  @else
                    Chưa phân công
                  @endif
                </small>
              @endif
            </div>
          </div>
        </div>
      @endforeach
    </div>
    
    @if(!$task->allSubtasksCompleted())
      <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Lưu ý:</strong> Công việc chính chỉ có thể hoàn thành khi tất cả các bước thực hiện đã được hoàn thành.
      </div>
    @else
      <div class="alert alert-success">
        <i class="bi bi-check-circle me-2"></i>
        <strong>Tuyệt vời!</strong> Tất cả các bước thực hiện đã hoàn thành. Bạn có thể hoàn thành công việc chính.
      </div>
    @endif
  </div>
</div>
@endif

<div class="row g-3">
  <div class="col-lg-8">
    <div class="progress-section">
      <h6 class="mb-3">Thông tin chung</h6>
      <div class="row">
        <div class="col-6">
          <i class="bi bi-person"></i>Người giao: <strong>{{ $task->creator->name }}</strong>
        </div>
        <div class="col-6">
          <i class="bi bi-people"></i>Người nhận:
          @if($task->assignees->count() > 0)
            @foreach($task->assignees as $assignee)
              <div class="badge bg-primary me-1 mb-1">{{ $assignee->name }}</div>
            @endforeach
          @elseif($task->assignee)
            <strong>{{ $task->assignee->name }}</strong>
            @if($task->assignee->department)
              <span class="badge bg-secondary ms-1">{{ $task->assignee->department->name }}</span>
            @endif
          @else
            <strong>—</strong>
          @endif
        </div>
        <div class="col-6">
          <i class="bi bi-calendar-check"></i>Ngày giao: <strong>{{ $task->created_at->format('d/m/Y') }}</strong>
        </div>
        <div class="col-6">
          <i class="bi bi-calendar-event"></i>Hạn cuối: <strong>{{ $task->deadline ? $task->deadline->format('d/m/Y') : 'Không có' }}</strong>
        </div>
        @if($task->forwards()->count() > 0)
        <div class="col-12">
          <div class="alert alert-info">
            <i class="bi bi-arrow-right-circle me-2"></i>
            <strong>Task đã được forward:</strong>
            @php
              $latestForward = $task->forwards()->first();
            @endphp
            Từ {{ $latestForward->forwardedBy->name ?? 'Người dùng đã xóa' }} 
            đến {{ $latestForward->forwardedTo->name ?? 'Người dùng đã xóa' }}
            @if($task->forwards()->count() > 1)
              <span class="badge bg-secondary ms-1">+{{ $task->forwards()->count() - 1 }} người khác</span>
            @endif
            @if($latestForward->forward_reason)
              <br><small class="text-muted">Lý do: {{ $latestForward->forward_reason }}</small>
            @endif
            @if($latestForward->forwarded_at)
              <br><small class="text-muted">Thời gian: {{ $latestForward->forwarded_at->format('d/m/Y H:i') }}</small>
            @endif
          </div>
        </div>
        @endif
        <div class="col-6">
          <i class="bi bi-check-circle"></i>Trạng thái: <strong>{{ __("statuses.$task->status") ?? strtoupper($task->status) }}</strong>
        </div>
        @if($task->priority)
        <div class="col-6">
          <i class="bi bi-flag"></i>Độ ưu tiên: 
          <span class="badge bg-{{ $task->priority == 'high' ? 'danger' : ($task->priority == 'medium' ? 'warning' : 'info') }}">
            {{ __("priorities.$task->priority") }}
          </span>
        </div>
        @endif
        <div class="col-6">
          <i class="bi bi-building"></i>Phòng ban:
          @if($task->is_multi_department && $task->departments->count() > 0)
            <div class="mt-1">
              @foreach($task->departments as $department)
                <div class="badge bg-info me-1 mb-1">{{ $department->name }}</div>
              @endforeach
            </div>
          @elseif($task->department)
            <div class="mt-1">
              <span class="badge bg-info">{{ $task->department->name }}</span>
            </div>
          @else
            <strong>—</strong>
          @endif
        </div>
        <div class="col-6">
          <i class="bi bi-people"></i>Người theo dõi:
          @if($task->followers->count() > 0)
            <strong>{{ $task->followers->count() }} người:</strong>
            <div class="mt-1">
              @foreach($task->followers as $follower)
                <div class="text-dark mb-1">
                  {{ $follower->name }} - {{ $follower->display_role }}
                  @if($follower->department)
                    <small class="text-muted">({{ $follower->department->name }})</small>
                  @endif
                </div>
              @endforeach
            </div>
          @else
            <strong>Chưa có người theo dõi</strong>
          @endif
        </div>
        
        @if($task->is_recurring)
        <div class="col-12 mt-2">
          <div class="alert alert-info mb-0">
            <i class="bi bi-arrow-repeat me-2"></i>
            <strong>Công việc lặp lại:</strong> Mỗi {{ $task->recurring_days }} ngày
            @if($task->recurring_start_date)
              <br><small>Bắt đầu từ: {{ $task->recurring_start_date->format('d/m/Y') }}</small>
            @endif
            @if($task->last_reset_date)
              <br><small>Lần reset cuối: {{ $task->last_reset_date->format('d/m/Y') }}</small>
            @endif
          </div>
        </div>
        @endif
        
      </div>
    </div>


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

    <!-- Thông báo task lặp lại -->
    @if($task->is_recurring)
    <div class="recurring-notification">
      <div class="recurring-content">
        <i class="bi bi-arrow-repeat recurring-icon"></i>
        <div class="recurring-text">
          <strong>Lặp lại:</strong> 
          <span>Công việc sẽ lặp lại mỗi {{ $task->recurring_days }} ngày từ ngày {{ $task->recurring_start_date ? $task->recurring_start_date->format('d/m/Y') : 'N/A' }}</span>
        </div>
      </div>
    </div>
    @endif

    <!-- Thông tin phê duyệt (nếu task đang chờ phê duyệt) -->
    @if($task->status === 'pending_approval' && $task->approvals->count() > 0)
    <div class="card mb-3 border-warning">
      <div class="card-header bg-warning text-dark">
        <h6 class="mb-0">
          <i class="bi bi-clock me-2"></i>Thông tin phê duyệt
        </h6>
      </div>
      <div class="card-body">
        <div class="alert alert-warning mb-3">
          <i class="bi bi-exclamation-triangle me-2"></i>
          <strong>Task này đang chờ phê duyệt từ các phòng ban liên quan</strong>
        </div>
        
        <div class="row">
          @foreach($task->approvals as $approval)
            <div class="col-md-6 mb-2">
              <div class="d-flex justify-content-between align-items-center p-2 border rounded">
                <div>
                  <strong>{{ $approval->department->name }}</strong>
                  <br>
                  <small class="text-muted">Manager: {{ $approval->manager->name }}</small>
                </div>
                <div>
                  @if($approval->status === 'pending')
                    <span class="badge bg-warning">Chờ phê duyệt</span>
                  @elseif($approval->status === 'approved')
                    <span class="badge bg-success">Đã phê duyệt</span>
                  @elseif($approval->status === 'rejected')
                    <span class="badge bg-danger">Từ chối</span>
                  @endif
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
    @endif

    <!-- Quản lý Task Followers (chỉ Admin/Manager) -->
                        @if(auth()->user()->isAdmin() || auth()->user()->isDirector() || auth()->user()->isManager())
    <div class="card mb-3 border-success">
                      <div class="card-header bg-dark text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-people me-2"></i>Quản lý Task Followers
                    </h6>
                </div>
      <div class="card-body">
        <!-- Danh sách followers hiện tại -->
        <div class="mb-3">
          <strong>Người đang theo dõi:</strong>
          <div id="currentFollowers">
                      @if($task->followers->count() > 0)
            @foreach($task->followers as $follower)
              <div class="d-flex justify-content-between align-items-center mb-2 p-2" style="background: #f8f9fa; border-radius: 8px;">
                                 <div>
                   <span class="fw-medium">{{ $follower->name }}</span>
                   <small class="text-muted d-block">
                     {{ $follower->role }}
                     @if($follower->department)
                       - {{ $follower->department->name }}
                     @endif
                   </small>
                 </div>
                 <button class="btn btn-sm btn-outline-danger" onclick="removeFollower({{ $follower->id }})">
                  <i class="bi bi-x"></i>
                </button>
              </div>
            @endforeach
            @else
              <div class="text-muted text-center py-3">
                <i class="bi bi-people text-muted"></i>
                <div>Chưa có người theo dõi</div>
              </div>
            @endif
          </div>
        </div>
        
        <!-- Thêm follower mới -->
        <div class="mb-3">
          <label for="newFollower" class="form-label">Thêm người theo dõi:</label>
          <select class="form-select mb-2" id="newFollower">
            <option value="">Chọn người dùng...</option>
          </select>
          <button class="btn btn-primary btn-sm" onclick="addFollower()">Thêm</button>
        </div>
      </div>
    </div>
    @endif

    <!-- Follow button cho tất cả users -->
    <div class="card mb-3 border-dark">
      <div class="card-header bg-dark text-white">
        <h6 class="mb-0">
          <i class="bi bi-eye me-2"></i>Hành động
        </h6>
      </div>
      <div class="card-body">
        @if(!$task->isFollowedBy(auth()->user()))
          <button class="btn btn-dark" onclick="followTask({{ $task->id }})">
            <i class="bi bi-eye me-1"></i>Theo dõi
          </button>
        @else
          <div class="text-success">
            <i class="bi bi-check-circle me-1"></i>Đang theo dõi
          </div>
        @endif
      </div>
    </div>

    <div class="comment-section">
      <h6 class="mb-3">Thảo luận</h6>
      <form class="mb-3" action="{{ route('tasks.comment',$task) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <textarea name="content" id="commentTextarea" class="form-control mb-2" rows="3" placeholder="Viết bình luận..." maxlength="1000"></textarea>
        
        <!-- File upload section -->
        <div class="file-upload-section mb-3">
          <div class="upload-area" id="uploadArea">
            <div class="upload-content">
              <i class="bi bi-cloud-upload fs-1 text-muted mb-2"></i>
              <p class="mb-1">Kéo thả file vào đây hoặc <span class="text-primary">chọn file</span></p>
              <small class="text-muted">Hỗ trợ: Ảnh, Video, PDF, Word, Excel, PowerPoint (Tối đa 1GB)</small>
            </div>
            <input type="file" name="attachments[]" id="fileInput" multiple accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" style="display: none;">
          </div>
          
          <!-- File preview -->
          <div id="filePreview" class="mt-2" style="display: none;">
            <h6 class="mb-2">File đã chọn:</h6>
            <div id="fileList" class="d-flex flex-wrap gap-2"></div>
          </div>
        </div>
        
        <div class="d-flex justify-content-between align-items-center">
          <small class="text-muted">Tối đa 1000 ký tự | File tối đa 1GB</small>
          <button type="submit" class="btn btn-primary btn-sm" id="submitComment">Gửi bình luận</button>
        </div>
      </form>

      @forelse($task->activities as $act)
        <div class="comment-item">
          <div class="comment-header">
            <strong>{{ $act->user->name }}</strong>
            <small class="text-muted ms-2">{{ $act->created_at->diffForHumans() }}</small>
          </div>
          <div class="comment-content">
            @php
              $meta = json_decode($act->meta, true);
              $content = is_array($meta) ? ($meta['content'] ?? $meta['description'] ?? $act->meta) : $act->meta;
              $attachments = is_array($meta) ? ($meta['attachments'] ?? []) : [];
            @endphp
            
            @if(is_array($meta))
              {{-- Hiển thị description nếu có --}}
              @if(isset($meta['description']))
                {{ $meta['description'] }}
              @endif
              
              {{-- Hiển thị content nếu có (cho comment) --}}
              @if(isset($meta['content']))
                <div class="mt-2 p-2 bg-light rounded">
                  <strong>Nội dung:</strong> {{ $meta['content'] }}
                </div>
              @endif
              
              {{-- Hiển thị new_content nếu có (cho edit comment) --}}
              @if(isset($meta['new_content']))
                <div class="mt-2 p-2 bg-light rounded">
                  <strong>Nội dung mới:</strong> {{ $meta['new_content'] }}
                </div>
              @endif
              
              {{-- Hiển thị old_content nếu có (cho edit comment) --}}
              @if(isset($meta['old_content']))
                <div class="mt-1 p-2 bg-secondary bg-opacity-10 rounded">
                  <strong>Nội dung cũ:</strong> <del>{{ $meta['old_content'] }}</del>
                </div>
              @endif
              
              {{-- Hiển thị thông tin trạng thái nếu có --}}
              @if(isset($meta['old_status']) && isset($meta['new_status']))
                <div class="mt-1">
                  <small class="text-muted">
                    Từ: <span class="badge bg-secondary">{{ $meta['old_status'] }}</span> 
                    → <span class="badge bg-primary">{{ $meta['new_status'] }}</span>
                  </small>
                </div>
              @endif
            @else
              {{ $content }}
            @endif
            
            @if(!empty($attachments))
              <div class="comment-attachments mt-2">
                <h6 class="text-muted mb-2"><i class="bi bi-paperclip me-1"></i>File đính kèm:</h6>
                <div class="d-flex flex-wrap gap-2">
                  @foreach($attachments as $attachment)
                    <div class="attachment-item">
                      @if(str_starts_with($attachment['type'], 'image/'))
                        <a href="#" onclick="openImageModal('{{ $attachment['url'] }}', '{{ $attachment['name'] }}')" class="attachment-link">
                          <img src="{{ $attachment['url'] }}" alt="{{ $attachment['name'] }}" class="attachment-preview" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                        </a>
                      @elseif(str_starts_with($attachment['type'], 'video/'))
                        <a href="{{ $attachment['url'] }}" target="_blank" class="attachment-link">
                          <div class="attachment-preview bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 4px;">
                            <i class="bi bi-play-circle fs-4 text-primary"></i>
                          </div>
                        </a>
                      @else
                        <a href="{{ $attachment['url'] }}" target="_blank" class="attachment-link">
                          <div class="attachment-preview bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 4px;">
                            @php
                              $iconMap = [
                                'application/pdf' => 'bi-file-pdf text-danger',
                                'application/msword' => 'bi-file-word text-primary',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'bi-file-word text-primary',
                                'application/vnd.ms-excel' => 'bi-file-excel text-success',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'bi-file-excel text-success',
                                'application/vnd.ms-powerpoint' => 'bi-file-ppt text-warning',
                                'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'bi-file-ppt text-warning'
                              ];
                              $icon = $iconMap[$attachment['type']] ?? 'bi-file-earmark text-muted';
                            @endphp
                            <i class="bi {{ $icon }} fs-4"></i>
                          </div>
                        </a>
                      @endif
                      <div class="attachment-info mt-1">
                        <small class="text-muted d-block">{{ $attachment['name'] }}</small>
                        <small class="text-muted">{{ number_format($attachment['size'] / 1024, 1) }} KB</small>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            @endif
          </div>
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
      {{-- Employee/Manager: Nút hoàn thành và gửi duyệt (nếu được assign) --}}
      @if($task->status === 'in_progress' && auth()->user()->canSubmitTask($task))
        @php
          $progress = $task->getSubmissionProgress();
          $hasSubmitted = $task->hasUserSubmitted(auth()->user());
        @endphp
        
        @if($hasSubmitted)
          <button class="btn btn-success w-100 mb-2" disabled>
            ✅ Đã gửi báo cáo hoàn thành 
            @if($progress['total'] > 1)
              ({{ $progress['submitted'] }}/{{ $progress['total'] }} người)
            @endif
          </button>
        @else
          <a href="{{ route('tasks.updateStatus',[$task,'status'=>'pending_approval']) }}" class="btn btn-success w-100 mb-2">
            ✅ Hoàn thành và gửi duyệt
            @if($progress['total'] > 1)
              ({{ $progress['submitted'] }}/{{ $progress['total'] }} người)
            @endif
          </a>
        @endif
        
        @if($progress['total'] > 1)
          <div class="progress mb-2">
            <div class="progress-bar" role="progressbar" style="width: {{ $progress['progress'] }}%">
              {{ $progress['submitted'] }}/{{ $progress['total'] }} người đã gửi
            </div>
          </div>
        @endif
      @endif
      
      {{-- Admin/Director/Manager: Nút hoàn thành (chỉ khi có quyền) --}}
      @if(in_array($task->status, ['pending_approval']) && auth()->user()->canApproveTask($task))
        <a href="{{ route('tasks.updateStatus',[$task,'status'=>'finished']) }}" class="btn btn-success w-100 mb-2">✅ Hoàn thành</a>
        <a href="{{ route('tasks.updateStatus',[$task,'status'=>'rejected']) }}" class="btn btn-danger w-100 mb-2">❌ Từ chối</a>
      @endif
      
      {{-- Admin/Director/Manager: Nút chuyển trạng thái (chỉ khi có quyền) --}}
      @if(in_array($task->status, ['in_progress', 'overdue']) && auth()->user()->canEditTask($task))
        <a href="{{ route('tasks.updateStatus',[$task,'status'=>'pending_approval']) }}" class="btn btn-primary w-100 mb-2">🔄 Chuyển sang chờ duyệt</a>
      @endif
      
      {{-- Employee/Manager: Nút làm lại khi bị từ chối --}}
      @if($task->status === 'rejected' && auth()->user()->canSubmitTask($task))
        <a href="{{ route('tasks.updateStatus',[$task,'status'=>'pending_approval']) }}" class="btn btn-warning w-100 mb-2">🔄 Đã làm lại & gửi duyệt</a>
      @endif
      
      {{-- Nút xem lịch sử (chỉ khi có quyền xem task) --}}
      @if(auth()->user()->canViewTask($task))
        <a href="{{ route('tasks.history',$task) }}" class="btn btn-outline-info w-100 mb-2">👁 Xem lịch sử</a>
      @endif
      
      {{-- Nút chuyển tiếp (chỉ khi có quyền) --}}
      @if((auth()->user()->isAdmin() || auth()->user()->isDirector() || auth()->user()->isManager()) && auth()->user()->canViewTask($task))
        <a href="{{ route('tasks.forward.form', $task) }}" class="btn btn-outline-warning w-100 mb-2">
          <i class="bi bi-arrow-right-circle me-2"></i>Chuyển tiếp
        </a>
      @endif
      
      {{-- Nút chỉnh sửa (chỉ khi có quyền) --}}
      @if(auth()->user()->canEditTask($task))
        <a href="{{ route('tasks.edit', $task) }}" class="btn btn-outline-secondary w-100 mb-2">✏️ Chỉnh sửa</a>
      @endif
      
      @if(auth()->user()->canSubmitTask($task) && $task->status === 'in_progress')
        @php
          $userSubmission = $task->getUserSubmission(auth()->user());
          $progress = $task->getSubmissionProgress();
        @endphp
        @if($userSubmission && $userSubmission->canUndo())
          <form action="{{ route('tasks.undo-completion', $task) }}" method="POST" class="mt-2" onsubmit="return confirm('Bạn có chắc chắn muốn rút lại báo cáo hoàn thành này?')">
            @csrf
            <button type="submit" class="btn btn-warning w-100">
              <i class="bi bi-arrow-counterclockwise me-2"></i>Rút lại báo cáo hoàn thành
              @if($progress['total'] > 1)
                ({{ $progress['submitted'] }}/{{ $progress['total'] }} người)
              @endif
            </button>
          </form>
        @endif
      @endif
      
      @if(auth()->user()->canDeleteTask($task))
        <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="mt-2" onsubmit="return confirm('Bạn có chắc chắn muốn xóa công việc này? Hành động này không thể hoàn tác.')">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger w-100">
            <i class="bi bi-trash me-2"></i>Xóa công việc
          </button>
        </form>
      @endif
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

/* File upload styling */
.upload-area {
  border: 2px dashed #dee2e6;
  border-radius: 8px;
  padding: 2rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  background: #f8f9fa;
}

.upload-area:hover {
  border-color: #558EC1;
  background: #f0f8ff;
}

.upload-area.dragover {
  border-color: #558EC1;
  background: #e3f2fd;
  transform: scale(1.02);
}

.upload-content {
  pointer-events: none;
}

.upload-content span {
  pointer-events: auto;
  cursor: pointer;
  text-decoration: underline;
}

.file-preview-item {
  background: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  padding: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  max-width: 200px;
}

.file-preview-item img {
  width: 40px;
  height: 40px;
  object-fit: cover;
  border-radius: 4px;
}

.file-preview-item video {
  width: 40px;
  height: 40px;
  object-fit: cover;
  border-radius: 4px;
}

.file-preview-item .file-info {
  flex: 1;
  min-width: 0;
}

.file-preview-item .file-name {
  font-size: 0.875rem;
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.file-preview-item .file-size {
  font-size: 0.75rem;
  color: #6c757d;
}

.file-preview-item .remove-file {
  color: #dc3545;
  cursor: pointer;
  padding: 0.25rem;
}

.file-preview-item .remove-file:hover {
  background: #f8d7da;
  border-radius: 4px;
}

/* Comment attachments styling */
.attachment-item {
  display: inline-block;
  text-align: center;
  margin-right: 1rem;
  margin-bottom: 1rem;
}

.attachment-link {
  text-decoration: none;
  color: inherit;
}

.attachment-link:hover {
  text-decoration: none;
  color: inherit;
}

.attachment-preview {
  border: 1px solid #dee2e6;
  transition: all 0.3s ease;
}

.attachment-preview:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.attachment-info {
  max-width: 80px;
  overflow: hidden;
}

.attachment-info small {
  display: block;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Button undo styling */
.btn-undo {
  background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 12px 20px;
  font-weight: 500;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
}

.btn-undo:hover {
  background: linear-gradient(135deg, #d97706 0%, #ea580c 100%);
  color: #fff;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
}

.btn-undo:active {
  transform: translateY(0);
  box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
}

/* Task recurring notification styling - giống hệt như trong ảnh */
.recurring-notification {
  background: linear-gradient(135deg, #dbeafe 0%, #e0f2fe 100%);
  border: 2px solid #10b981;
  border-radius: 12px;
  padding: 16px 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.15);
  position: relative;
  overflow: hidden;
}

.recurring-notification::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(45deg, rgba(16, 185, 129, 0.05) 0%, rgba(14, 165, 233, 0.05) 100%);
  pointer-events: none;
}

.recurring-content {
  display: flex;
  align-items: center;
  position: relative;
  z-index: 1;
}

.recurring-icon {
  font-size: 1.8rem;
  color: #10b981;
  margin-right: 16px;
  animation: pulse 2s infinite;
  filter: drop-shadow(0 2px 4px rgba(16, 185, 129, 0.3));
}

.recurring-text {
  flex: 1;
  color: #1e40af;
  font-size: 0.95rem;
  line-height: 1.5;
}

.recurring-text strong {
  color: #10b981;
  font-weight: 600;
  margin-right: 8px;
}

.recurring-text span {
  color: #374151;
  font-weight: 400;
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

/* Hover effect */
.recurring-notification:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(16, 185, 129, 0.25);
  transition: all 0.3s ease;
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

.description-content {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 0.5rem;
  border: 1px solid #dee2e6;
  white-space: pre-wrap;
  word-wrap: break-word;
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

// File upload functionality
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('fileInput');
    const filePreview = document.getElementById('filePreview');
    const fileList = document.getElementById('fileList');
    const MAX_FILE_SIZE = 1024 * 1024 * 1024; // 1GB
    const MAX_TOTAL_SIZE = 1024 * 1024 * 1024; // 1GB total
    
    let selectedFiles = [];
    let totalSize = 0;
    
    // Click to select files
    uploadArea.addEventListener('click', function() {
        fileInput.click();
    });
    
    // Handle file selection
    fileInput.addEventListener('change', function(e) {
        handleFiles(e.target.files);
    });
    
    // Drag and drop functionality
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });
    
    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
    });
    
    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        handleFiles(e.dataTransfer.files);
    });
    
    function handleFiles(files) {
        for (let file of files) {
            // Check file size
            if (file.size > MAX_FILE_SIZE) {
                alert(`File "${file.name}" quá lớn. Kích thước tối đa là 1GB.`);
                continue;
            }
            
            // Check total size
            if (totalSize + file.size > MAX_TOTAL_SIZE) {
                alert(`Tổng kích thước file vượt quá 1GB.`);
                continue;
            }
            
            // Check file type
            const allowedTypes = [
                'image/', 'video/', 
                'application/pdf', 
                'application/msword', 
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            ];
            
            let isValidType = false;
            for (let type of allowedTypes) {
                if (file.type.startsWith(type)) {
                    isValidType = true;
                    break;
                }
            }
            
            if (!isValidType) {
                alert(`File "${file.name}" không được hỗ trợ.`);
                continue;
            }
            
            // Add file to list
            selectedFiles.push(file);
            totalSize += file.size;
            addFilePreview(file);
        }
        
        updateFileInput();
        updateFilePreviewVisibility();
    }
    
    function addFilePreview(file) {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-preview-item';
        fileItem.dataset.fileName = file.name;
        
        let previewContent = '';
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                fileItem.querySelector('img').src = e.target.result;
            };
            reader.readAsDataURL(file);
            previewContent = '<img src="" alt="Preview">';
        } else if (file.type.startsWith('video/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                fileItem.querySelector('video').src = e.target.result;
            };
            reader.readAsDataURL(file);
            previewContent = '<video src="" muted></video>';
        } else {
            // Document files
            const iconMap = {
                'application/pdf': 'bi-file-pdf',
                'application/msword': 'bi-file-word',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'bi-file-word',
                'application/vnd.ms-excel': 'bi-file-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'bi-file-excel',
                'application/vnd.ms-powerpoint': 'bi-file-ppt',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation': 'bi-file-ppt'
            };
            const icon = iconMap[file.type] || 'bi-file-earmark';
            previewContent = `<i class="bi ${icon} fs-4 text-primary"></i>`;
        }
        
        fileItem.innerHTML = `
            ${previewContent}
            <div class="file-info">
                <div class="file-name">${file.name}</div>
                <div class="file-size">${formatFileSize(file.size)}</div>
            </div>
            <div class="remove-file" onclick="removeFile('${file.name}')">
                <i class="bi bi-x"></i>
            </div>
        `;
        
        fileList.appendChild(fileItem);
    }
    
    function removeFile(fileName) {
        const fileIndex = selectedFiles.findIndex(f => f.name === fileName);
        if (fileIndex > -1) {
            totalSize -= selectedFiles[fileIndex].size;
            selectedFiles.splice(fileIndex, 1);
            
            const fileItem = fileList.querySelector(`[data-file-name="${fileName}"]`);
            if (fileItem) {
                fileItem.remove();
            }
            
            updateFileInput();
            updateFilePreviewVisibility();
        }
    }
    
    function updateFileInput() {
        // Create new FileList-like object
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        fileInput.files = dt.files;
    }
    
    function updateFilePreviewVisibility() {
        if (selectedFiles.length > 0) {
            filePreview.style.display = 'block';
        } else {
            filePreview.style.display = 'none';
        }
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Make removeFile function global
    window.removeFile = removeFile;
    
    // QR Code Modal function
    function showQRModal(trackingCode, qrCodeBase64) {
        const modalHtml = `
            <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="qrModalLabel">
                                <i class="bi bi-qr-code me-2"></i>
                                QR Code - ${trackingCode}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="data:image/png;base64,${qrCodeBase64}" 
                                 alt="QR Code" 
                                 style="max-width: 100%; height: auto;">
                            <div class="mt-3">
                                <strong>Tracking Code:</strong> ${trackingCode}
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                            <button type="button" class="btn btn-primary" onclick="downloadQRCode('${trackingCode}', '${qrCodeBase64}')">
                                <i class="bi bi-download me-1"></i>
                                Tải xuống
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        const existingModal = document.getElementById('qrModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add new modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('qrModal'));
        modal.show();
    }
    
    function downloadQRCode(trackingCode, qrCodeBase64) {
        const link = document.createElement('a');
        link.href = `data:image/png;base64,${qrCodeBase64}`;
        link.download = `QR_${trackingCode}.png`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Task Followers functions
    function loadAvailableFollowers() {
        fetch(`{{ route('tasks.followers.available', $task) }}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('newFollower');
                    select.innerHTML = '<option value="">Chọn người dùng...</option>';
                    
                    data.users_by_department.forEach(department => {
                        const optgroup = document.createElement('optgroup');
                        optgroup.label = department.department_name;
                        
                        department.users.forEach(user => {
                            const option = document.createElement('option');
                            option.value = user.id;
                            option.textContent = `${user.name} (${user.role})`;
                            optgroup.appendChild(option);
                        });
                        
                        select.appendChild(optgroup);
                    });
                }
            });
    }

    function addFollower() {
        const select = document.getElementById('newFollower');
        const userId = select.value;
        
        if (!userId) {
            alert('Vui lòng chọn người dùng để thêm.');
            return;
        }
        
        fetch(`{{ route('tasks.followers.add', $task) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ user_id: userId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Đã thêm Task Follower thành công!');
                location.reload();
            } else {
                alert('Lỗi: ' + data.message);
            }
        });
    }

    function removeFollower(userId) {
        if (!confirm('Bạn có chắc muốn xóa người này khỏi danh sách Task Follower?')) {
            return;
        }
        
        fetch(`{{ route('tasks.followers.remove', $task) }}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ user_id: userId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Đã xóa Task Follower thành công!');
                location.reload();
            } else {
                alert('Lỗi: ' + data.message);
            }
        });
    }

    function followTask(taskId) {
        fetch(`{{ route('tasks.followers.follow', $task) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Đã theo dõi task thành công!');
                location.reload();
            } else {
                alert('Lỗi: ' + data.message);
            }
        });
    }



    // Subtasks functions
    function completeSubtask(subtaskId) {
        if (!confirm('Bạn có chắc muốn đánh dấu bước thực hiện này là hoàn thành?')) {
            return;
        }
        
        fetch(`{{ route('tasks.subtasks.complete', $task) }}`.replace('{subtask}', subtaskId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                location.reload(); // Reload để cập nhật UI
            } else {
                showAlert('error', data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Có lỗi xảy ra khi hoàn thành bước thực hiện');
        });
    }

    function updateSubtaskStatus(subtaskId, status) {
        if (!confirm(`Bạn có chắc muốn thay đổi trạng thái bước thực hiện này?`)) {
            return;
        }
        
        fetch(`{{ route('tasks.subtasks.update-status', $task) }}`.replace('{subtask}', subtaskId), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                location.reload(); // Reload để cập nhật UI
            } else {
                showAlert('error', data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Có lỗi xảy ra khi cập nhật trạng thái');
        });
    }

    function showAlert(type, message) {
        // Tạo alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Thêm vào body
        document.body.appendChild(alertDiv);
        
        // Tự động ẩn sau 5 giây
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    // Load followers khi trang load
    document.addEventListener('DOMContentLoaded', function() {
        loadAvailableFollowers();
    });
});
</script>
@endpush
