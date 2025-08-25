@extends('layouts.master')
@section('title','Danh sách công việc')

@push('styles')
<style>
/* CSS cho drag & drop */
.department-card {
  transition: all 0.2s ease;
  cursor: default;
}

.department-card.dragging {
  opacity: 0.8;
  transform: rotate(2deg) scale(1.02
  );
  z-index: 1000;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
  pointer-events: none;
}

.department-card.drag-over {
    border-top: 3px solid #558EC1;
    margin-top: 15px;
    transform: translateY(5px);
}

.drag-handle {
  font-size: 1.2rem;
  transition: all 0.3s ease;
}

.drag-handle:hover {
  opacity: 1 !important;
  transform: scale(1.1);
  color: #fff !important;
}

.drag-indicator {
  opacity: 0.5;
  transition: opacity 0.3s ease;
}

.department-card:hover .drag-indicator {
  opacity: 1;
}

/* Làm nổi bật drag handle */
.drag-handle {
  background: rgba(255, 255, 255, 0.1);
  padding: 6px 8px;
  border-radius: 6px;
  margin-right: 8px !important;
  cursor: grab;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.drag-handle:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: scale(1.05);
}

.drag-handle:active {
  cursor: grabbing;
  transform: scale(0.95);
}

/* CSS cho giao diện Manager/Employee */
.table-hover tbody tr:hover {
    background-color: #f8f9fa;
    transform: scale(1.01);
    transition: all 0.2s ease;
}



.badge {
    font-size: 0.8rem;
    font-weight: 500;
}

.btn-group .btn {
    transition: all 0.3s ease;
}

.btn-group .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
}

.table th {
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}

.table td {
    vertical-align: middle;
}

/* Responsive cho mobile */
@media (max-width: 768px) {
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        border-radius: 0.375rem !important;
        margin-bottom: 0.25rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
    /* Filter responsive */
    .filter-section .row {
        margin: 0;
    }
    
    .filter-section .col-md-6 {
        margin-bottom: 1rem;
    }
    
    .filter-section .btn-group {
        width: 100%;
    }
    
    .filter-section .btn-group .btn {
        flex: 1;
    }
}

/* Filter styling */
.filter-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.filter-section small {
    font-weight: 600;
    color: #495057;
}

.filter-section .form-control-sm {
    border-radius: 6px;
    border: 1px solid #ced4da;
}

.filter-section .form-control-sm:focus {
    border-color: #558EC1;
    box-shadow: 0 0 0 0.2rem rgba(85, 142, 193, 0.25);
}

.filter-section .btn-sm {
    border-radius: 6px;
    font-weight: 500;
}
</style>
@endpush

@section('content')
<div class="row g-3 mb-3">
  <div class="col-md-2">
    <div class="card card-stat p-3 text-center">
      <h5 class="text-primary">{{ $stats['doing'] }}</h5>
      <p>Đang làm</p>
    </div>
  </div>

  <div class="col-md-2">
    <div class="card card-stat p-3 text-center">
      <h5 class="text-warning">{{ $stats['completed'] ?? 0 }}</h5>
      <p>Chờ duyệt</p>
    </div>
  </div>

  <div class="col-md-2">
    <div class="card card-stat p-3 text-center">
      <h5 class="text-danger">{{ $stats['overdue'] }}</h5>
      <p>Trễ hạn</p>
    </div>
  </div>
  <div class="col-md-2">
    <div class="card card-stat p-3 text-center">
      <h5 class="text-danger">{{ $stats['rejected'] ?? 0 }}</h5>
      <p>Từ chối</p>
    </div>
  </div>
  <div class="col-md-2">
    <div class="card card-stat p-3 text-center">
      <h5 class="text-success">{{ $stats['finished'] ?? 0 }}</h5>
      <p>Kết thúc</p>
    </div>
  </div>
</div>

@if(auth()->user()->isAdmin())
  <div class="mb-3 filter-section">
    {{-- Filter theo phòng ban (chỉ Admin) --}}
    <div class="mb-3">
      <small class="text-muted mb-2 d-block"><i class="bi bi-building me-1"></i>Lọc theo phòng ban:</small>
      <form method="GET" action="{{ route('dashboard') }}" class="d-inline">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <input type="hidden" name="sort" value="{{ request('sort') }}">
        <input type="hidden" name="date_from" value="{{ request('date_from') }}">
        <input type="hidden" name="date_to" value="{{ request('date_to') }}">
        @if(request('statuses'))
          @foreach(request('statuses') as $status)
            <input type="hidden" name="statuses[]" value="{{ $status }}">
          @endforeach
        @endif
        <select name="department_filter" class="form-select form-select-sm d-inline-block w-auto me-2" onchange="this.form.submit()">
          <option value="">Tất cả phòng ban</option>
          @foreach($departments as $dept)
            <option value="{{ $dept->id }}" {{ request('department_filter') == $dept->id ? 'selected' : '' }}>
              {{ $dept->name }}
            </option>
          @endforeach
        </select>
      </form>
    </div>
  </div>
@else
  <div class="mb-3 filter-section">

  {{-- Filter theo trạng thái --}}
  <div class="mb-2">
    <small class="text-muted mb-2 d-block"><i class="bi bi-funnel me-1"></i>Lọc theo trạng thái:</small>
    <form method="GET" action="{{ route('dashboard') }}" class="d-inline me-2">
      <input type="hidden" name="sort" value="{{ request('sort') }}">
      <input type="hidden" name="date_from" value="{{ request('date_from') }}">
      <input type="hidden" name="date_to" value="{{ request('date_to') }}">
      @if(auth()->user()->isAdmin())
        <input type="hidden" name="department_filter" value="{{ request('department_filter') }}">
      @endif
      <button type="submit" class="btn btn-sm btn-outline-secondary{{ !request('status') && !request('statuses') ? ' active' : '' }}">Tất cả</button>
    </form>

    {{-- Multi-select statuses --}}
    <form method="GET" action="{{ route('dashboard') }}" class="d-inline">
      <input type="hidden" name="sort" value="{{ request('sort') }}">
      <input type="hidden" name="date_from" value="{{ request('date_from') }}">
      <input type="hidden" name="date_to" value="{{ request('date_to') }}">
      @if(auth()->user()->isAdmin())
        <input type="hidden" name="department_filter" value="{{ request('department_filter') }}">
      @endif
      <div class="btn-group me-2" role="group" aria-label="Statuses">
        @php
          $selected = collect(request('statuses', []));
        @endphp
        <input type="checkbox" class="btn-check" id="st_doing" autocomplete="off" name="statuses[]" value="in_progress" {{ $selected->contains('in_progress') ? 'checked' : '' }}>
        <label class="btn btn-sm btn-outline-primary" for="st_doing" style="border-color: #558EC1; color: #558EC1;">Đang làm</label>

        <input type="checkbox" class="btn-check" id="st_completed" autocomplete="off" name="statuses[]" value="completed" {{ $selected->contains('completed') ? 'checked' : '' }}>
        <label class="btn btn-sm btn-outline-warning" for="st_completed">Chờ duyệt</label>

        <input type="checkbox" class="btn-check" id="st_rejected" autocomplete="off" name="statuses[]" value="rejected" {{ $selected->contains('rejected') ? 'checked' : '' }}>
        <label class="btn btn-sm btn-outline-danger" for="st_rejected">Từ chối</label>

        <input type="checkbox" class="btn-check" id="st_overdue" autocomplete="off" name="statuses[]" value="overdue" {{ $selected->contains('overdue') ? 'checked' : '' }}>
        <label class="btn btn-sm btn-outline-danger" for="st_overdue">Trễ hạn</label>

        <input type="checkbox" class="btn-check" id="st_finished" autocomplete="off" name="statuses[]" value="finished" {{ $selected->contains('finished') ? 'checked' : '' }}>
        <label class="btn btn-sm btn-outline-success" for="st_finished">Kết thúc</label>
      </div>
      <button type="submit" class="btn btn-sm btn-primary" style="background-color: #558EC1; border-color: #558EC1;">Áp dụng</button>
    </form>
  </div>

  {{-- Filter theo thời gian --}}
  <div class="row g-2">
    <div class="col-md-6">
      <small class="text-muted mb-2 d-block"><i class="bi bi-sort-numeric-down me-1"></i>Sắp xếp theo thời gian:</small>
      <div class="btn-group" role="group">
        <form method="GET" action="{{ route('dashboard') }}" class="d-inline">
          <input type="hidden" name="status" value="{{ request('status') }}">
          <input type="hidden" name="sort" value="newest">
          <input type="hidden" name="date_from" value="{{ request('date_from') }}">
          <input type="hidden" name="date_to" value="{{ request('date_to') }}">
          @if(auth()->user()->isAdmin())
            <input type="hidden" name="department_filter" value="{{ request('department_filter') }}">
          @endif
          @if(request('statuses'))
            @foreach(request('statuses') as $status)
              <input type="hidden" name="statuses[]" value="{{ $status }}">
            @endforeach
          @endif
          <button type="submit" class="btn btn-sm btn-outline-info{{ request('sort')=='newest' ? ' active' : '' }}" style="border-color: #558EC1; color: #558EC1;">
            <i class="bi bi-sort-down me-1"></i>Mới nhất
          </button>
        </form>
        <form method="GET" action="{{ route('dashboard') }}" class="d-inline">
          <input type="hidden" name="status" value="{{ request('status') }}">
          <input type="hidden" name="sort" value="oldest">
          <input type="hidden" name="date_from" value="{{ request('date_from') }}">
          <input type="hidden" name="date_to" value="{{ request('date_to') }}">
          @if(auth()->user()->isAdmin())
            <input type="hidden" name="department_filter" value="{{ request('department_filter') }}">
          @endif
          @if(request('statuses'))
            @foreach(request('statuses') as $status)
              <input type="hidden" name="statuses[]" value="{{ $status }}">
            @endforeach
          @endif
          <button type="submit" class="btn btn-sm btn-outline-info{{ request('sort')=='oldest' ? ' active' : '' }}" style="border-color: #558EC1; color: #558EC1;">
            <i class="bi bi-sort-up me-1"></i>Cũ nhất
          </button>
        </form>
      </div>
    </div>
    
    <div class="col-md-6">
      <small class="text-muted mb-2 d-block"><i class="bi bi-calendar-range me-1"></i>Chọn khoảng thời gian:</small>
      <form method="GET" action="{{ route('dashboard') }}" class="row g-2">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <input type="hidden" name="sort" value="{{ request('sort') }}">
        @if(auth()->user()->isAdmin())
          <input type="hidden" name="department_filter" value="{{ request('department_filter') }}">
        @endif
        @if(request('statuses'))
          @foreach(request('statuses') as $status)
            <input type="hidden" name="statuses[]" value="{{ $status }}">
          @endforeach
        @endif
        <div class="col-5">
          <input type="date" name="date_from" value="{{ request('date_from') }}" 
                 class="form-control form-control-sm" placeholder="Từ ngày">
        </div>
        <div class="col-5">
          <input type="date" name="date_to" value="{{ request('date_to') }}" 
                 class="form-control form-control-sm" placeholder="Đến ngày">
        </div>
        <div class="col-2">
          <button type="submit" class="btn btn-sm btn-primary w-100" style="background-color: #558EC1; border-color: #558EC1;">
            <i class="bi bi-search"></i>
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Nút xóa filter --}}
  @if(request('status') || request('sort') || request('date_from') || request('date_to') || request('department_filter') || request('statuses'))
    <div class="mt-2">
      <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-x-circle me-1"></i>Xóa bộ lọc
      </a>
    </div>
  @endif
</div>
@endif

{{-- Giao diện thống nhất: Quản lý chung --}}
<div class="card shadow-sm border-0">
  <div class="card-header text-white d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #558EC1 0%, #5DA444 100%);">
    <h5 class="mb-0">
      <i class="bi bi-list me-2"></i>
      <i class="bi bi-file-text me-2"></i>
      Tất cả công việc
      <span class="badge bg-light text-dark ms-2">{{ $tasks->total() }} công việc</span>
    </h5>
    <button class="btn btn-success">
      <i class="bi bi-gear me-1"></i>Quản lý thống nhất
    </button>
  </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th class="px-4 py-3 fw-semibold">Tiêu đề</th>
              <th class="px-4 py-3 fw-semibold">Phòng ban</th>
              <th class="px-4 py-3 fw-semibold">Người phụ trách</th>
              <th class="px-4 py-3 fw-semibold">Ngày giao</th>
              <th class="px-4 py-3 fw-semibold">Deadline</th>
              <th class="px-4 py-3 fw-semibold">Trạng thái</th>
              <th class="px-4 py-3 fw-semibold text-end">Hành động</th>
            </tr>
          </thead>
          <tbody>
            @forelse($tasks as $task)
              @php
                $st = $task->status;
                $badge = [
                    'in_progress' => 'primary', 
                    'completed' => 'warning',
                    'rejected' => 'danger',
                    'overdue' => 'danger',
                    'finished' => 'success'
                ][$st] ?? 'secondary';
              @endphp
              <tr class="border-bottom">
                <td class="px-4 py-3">
                  <div class="fw-medium text-dark">{{ $task->title }}</div>
                </td>
                <td class="px-4 py-3">
                  @if($task->is_multi_department && $task->departments->count() > 0)
                    @php
                      $deptNames = $task->departments->pluck('name')->implode(', ');
                    @endphp
                    <span class="badge bg-info bg-opacity-10 text-info border border-info" 
                          data-bs-toggle="tooltip" 
                          data-bs-placement="top" 
                          title="Phòng ban: {{ $deptNames }}">
                      <i class="bi bi-diagram-3 me-1"></i>Đa phòng ban
                    </span>
                  @elseif($task->department)
                    <span class="badge bg-light text-dark border">
                      <i class="bi bi-building me-1"></i>{{ $task->department->name }}
                    </span>
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
                <td class="px-4 py-3">
                  @if($task->assignees->count() > 0)
                    @php
                      $assigneeNames = $task->assignees->pluck('name')->implode(', ');
                      $assigneeCount = $task->assignees->count();
                    @endphp
                    <span class="badge bg-light text-dark border" 
                          data-bs-toggle="tooltip" 
                          data-bs-placement="top" 
                          title="Người phụ trách: {{ $assigneeNames }}">
                      <i class="bi bi-people me-1"></i>{{ $assigneeCount }} người
                    </span>
                  @else
                    <span class="badge bg-light text-dark border">
                      <i class="bi bi-person me-1"></i>
                      {{ $task->assignee?->name ?? '—' }}
                    </span>
                  @endif
                </td>
                <td class="px-4 py-3">
                  <span class="text-muted">{{ $task->created_at?->format('d/m/Y') }}</span>
                </td>
                <td class="px-4 py-3">
                  @if($task->deadline)
                    <span class="badge bg-info bg-opacity-10 text-info border border-info">
                      <i class="bi bi-calendar me-1"></i>
                      {{ $task->deadline->format('d/m/Y') }}
                    </span>
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
                <td class="px-4 py-3">
                  <span class="badge rounded-pill px-3 py-2 fw-medium bg-{{ $badge }} bg-opacity-10 text-dark border border-{{ $badge }}">
                    @if($st == 'in_progress')
                      <i class="bi bi-play me-1"></i>Đang làm
                    @elseif($st == 'completed')
                      <i class="bi bi-hourglass-split me-1"></i>Chờ duyệt
                    @elseif($st == 'rejected')
                      <i class="bi bi-x me-1"></i>Từ chối
                    @elseif($st == 'overdue')
                      <i class="bi bi-exclamation-triangle me-1"></i>Trễ hạn
                    @elseif($st == 'finished')
                      <i class="bi bi-flag-checkered me-1"></i>Kết thúc
                    @else
                      {{ strtoupper($st) }}
                    @endif
                  </span>
                </td>
                <td class="px-4 py-3 text-end">
                  <div class="btn-group" role="group">
                    <a href="{{ route('task-detail',$task) }}" class="btn btn-sm btn-outline-primary border-0 rounded-start">
                      <i class="bi bi-eye me-1"></i>Xem
                    </a>
                    <a href="{{ route('tasks.edit',$task) }}" class="btn btn-sm btn-outline-warning border-0">
                      <i class="bi bi-pencil me-1"></i>Sửa
                    </a>
                    <form action="{{ route('tasks.destroy',$task) }}" method="POST" class="d-inline" data-confirm="Xoá công việc này?">
                      @csrf @method('DELETE')
                      <button class="btn btn-sm btn-outline-danger border-0 rounded-end">
                        <i class="bi bi-trash me-1"></i>Xoá
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center py-5">
                  <div class="text-muted">
                    <i class="bi bi-inbox fa-3x mb-3 opacity-50"></i>
                    <h6 class="mb-2">Chưa có công việc nào</h6>
                    <p class="mb-0">Hãy tạo công việc mới để bắt đầu</p>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    @if($tasks->hasPages())
      <div class="card-footer bg-light border-0">
        <div class="d-flex justify-content-center">
          {{ $tasks->appends(request()->query())->links() }}
        </div>
      </div>
    @endif
  </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo tooltip Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    const sortableContainer = document.getElementById('sortable-departments');
    if (!sortableContainer) return;

    let draggedElement = null;
    let isDragging = false;
    let startY = 0;
    let startX = 0;

    // Thêm event listeners cho drag & drop
    const departmentCards = document.querySelectorAll('.department-card');
    
    departmentCards.forEach((card) => {
        const dragHandle = card.querySelector('.drag-handle');
        if (!dragHandle) return;

        // Mouse events
        dragHandle.addEventListener('mousedown', function(e) {
            e.preventDefault();
            e.stopPropagation();
            startDrag(e, card);
        });

        // Touch events cho mobile
        dragHandle.addEventListener('touchstart', function(e) {
            e.preventDefault();
            e.stopPropagation();
            startDrag(e.touches[0], card);
        });
    });

    function startDrag(e, card) {
        if (isDragging) return;
        
        isDragging = true;
        draggedElement = card;
        startY = e.clientY;
        startX = e.clientX;
        
        // Thêm class dragging
        card.classList.add('dragging');
        
        // Tạo ghost element
        const rect = card.getBoundingClientRect();
        card.style.width = rect.width + 'px';
        card.style.position = 'relative';
        card.style.zIndex = '1000';
        card.style.transform = 'rotate(2deg)';
        
        // Thêm event listeners
        document.addEventListener('mousemove', onDrag);
        document.addEventListener('touchmove', onDrag, { passive: false });
        document.addEventListener('mouseup', stopDrag);
        document.addEventListener('touchend', stopDrag);
        
        // Prevent text selection
        document.body.style.userSelect = 'none';
        document.body.style.webkitUserSelect = 'none';
    }

    function onDrag(e) {
        if (!isDragging || !draggedElement) return;
        
        e.preventDefault();
        const clientY = e.clientY || e.touches[0].clientY;
        const clientX = e.clientX || e.touches[0].clientX;
        
        // Kiểm tra khoảng cách tối thiểu để bắt đầu drag
        const deltaY = Math.abs(clientY - startY);
        const deltaX = Math.abs(clientX - startX);
        
        if (deltaY < 5 && deltaX < 5) return;
        
        // Tìm vị trí mới
        const cards = Array.from(document.querySelectorAll('.department-card:not(.dragging)'));
        let newIndex = cards.length;
        
        for (let i = 0; i < cards.length; i++) {
            const rect = cards[i].getBoundingClientRect();
            if (clientY < rect.top + rect.height / 2) {
                newIndex = i;
                break;
            }
        }
        
        // Cập nhật visual feedback
        cards.forEach(card => card.classList.remove('drag-over'));
        if (newIndex >= 0 && newIndex < cards.length) {
            cards[newIndex].classList.add('drag-over');
        }
    }

    function stopDrag() {
        if (!isDragging || !draggedElement) return;
        
        const cards = Array.from(document.querySelectorAll('.department-card'));
        const dragOverCard = document.querySelector('.department-card.drag-over');
        
        if (dragOverCard) {
            const currentIndex = cards.indexOf(draggedElement);
            const newIndex = cards.indexOf(dragOverCard);
            
            if (newIndex !== currentIndex) {
                // Di chuyển element với animation
                const container = sortableContainer;
                
                if (newIndex > currentIndex) {
                    container.insertBefore(draggedElement, dragOverCard.nextSibling);
                } else {
                    container.insertBefore(draggedElement, dragOverCard);
                }
                
                // Lưu thứ tự mới
                saveDepartmentOrder();
            }
        }
        
        // Cleanup
        draggedElement.classList.remove('dragging');
        draggedElement.style.position = '';
        draggedElement.style.zIndex = '';
        draggedElement.style.width = '';
        draggedElement.style.transform = '';
        
        cards.forEach(card => card.classList.remove('drag-over'));
        
        // Reset state
        draggedElement = null;
        isDragging = false;
        
        // Restore text selection
        document.body.style.userSelect = '';
        document.body.style.webkitUserSelect = '';
        
        // Remove event listeners
        document.removeEventListener('mousemove', onDrag);
        document.removeEventListener('touchmove', onDrag);
        document.removeEventListener('mouseup', stopDrag);
        document.removeEventListener('touchend', stopDrag);
    }

    function saveDepartmentOrder() {
        const cards = Array.from(document.querySelectorAll('.department-card'));
        const order = cards.map(card => card.dataset.departmentId);
        localStorage.setItem('departmentOrder', JSON.stringify(order));
        
        // Hiển thị thông báo
        showNotification('Đã lưu thứ tự sắp xếp phòng ban');
    }

    function showNotification(message) {
        // Xóa notification cũ nếu có
        const existingNotification = document.querySelector('.drag-notification');
        if (existingNotification) {
            existingNotification.remove();
        }
        
        // Tạo notification element
        const notification = document.createElement('div');
        notification.className = 'drag-notification position-fixed top-0 end-0 p-3';
        notification.style.zIndex = '9999';
        notification.innerHTML = `
            <div class="alert alert-success alert-dismissible fade show shadow" role="alert" style="min-width: 300px;">
                <i class="bi bi-check-circle me-2"></i>
                <strong>Thành công!</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animation fade in
        const alert = notification.querySelector('.alert');
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-20px)';
        
        setTimeout(() => {
            alert.style.transition = 'all 0.3s ease';
            alert.style.opacity = '1';
            alert.style.transform = 'translateY(0)';
        }, 10);
        
        // Tự động ẩn sau 3 giây
        setTimeout(() => {
            if (notification.parentNode) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }
        }, 3000);
    }

    // Khôi phục thứ tự đã lưu khi load trang
    function restoreDepartmentOrder() {
        const savedOrder = localStorage.getItem('departmentOrder');
        if (!savedOrder) return;
        
        try {
            const order = JSON.parse(savedOrder);
            const container = sortableContainer;
            const cards = Array.from(document.querySelectorAll('.department-card'));
            
            order.forEach(departmentId => {
                const card = cards.find(c => c.dataset.departmentId === departmentId);
                if (card) {
                    container.appendChild(card);
                }
            });
        } catch (e) {
            console.error('Error restoring department order:', e);
        }
    }

    // Khôi phục thứ tự khi load trang
    restoreDepartmentOrder();

    // Function để copy tracking code
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            // Hiển thị thông báo thành công
            const button = event.target.closest('button');
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i>';
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
});
</script>
@endpush
