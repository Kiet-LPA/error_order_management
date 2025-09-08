@extends('layouts.master')

@section('title', 'Chuyển tiếp công việc')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="bi bi-arrow-right-circle me-2"></i>Chuyển tiếp công việc
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Task Info -->
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="bi bi-info-circle me-2"></i>Thông tin công việc
                        </h6>
                        <p class="mb-1"><strong>Tiêu đề:</strong> {{ $task->title }}</p>
                        <p class="mb-1"><strong>Mô tả:</strong> {{ $task->description ?: 'Không có mô tả' }}</p>
                        <p class="mb-1"><strong>Độ ưu tiên:</strong> 
                            <span class="badge bg-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning' : 'success') }}">
                                @if($task->priority === 'high')
                                    Cao
                                @elseif($task->priority === 'medium')
                                    Trung bình
                                @else
                                    Thấp
                                @endif
                            </span>
                        </p>
                        <p class="mb-0"><strong>Trạng thái:</strong> 
                            <span class="badge bg-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'primary' : 'secondary') }}">
                                @if($task->status === 'in_progress')
                                    Đang làm
                                @elseif($task->status === 'completed')
                                    Hoàn thành
                                @elseif($task->status === 'rejected')
                                    Từ chối
                                @elseif($task->status === 'overdue')
                                    Trễ hạn
                                @elseif($task->status === 'finished')
                                    Kết thúc
                                @elseif($task->status === 'pending_approval')
                                    Chờ phê duyệt
                                @else
                                    {{ ucfirst($task->status) }}
                                @endif
                            </span>
                        </p>
                    </div>

                    <!-- Forward Form -->
                    <form method="POST" action="{{ route('tasks.forward', $task) }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="forward_to" class="form-label">
                                <i class="bi bi-person-check me-1"></i>Chuyển tiếp đến Quản lý
                                <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('forward_to') is-invalid @enderror" 
                                    id="forward_to" name="forward_to" required>
                                <option value="">-- Chọn Quản lý --</option>
                                @php
                                    $groupedManagers = $managers->groupBy('department.name');
                                @endphp
                                @foreach($groupedManagers as $departmentName => $departmentManagers)
                                    <optgroup label="{{ $departmentName ?? 'Chưa phân phòng ban' }}">
                                        @foreach($departmentManagers as $manager)
                                            <option value="{{ $manager->id }}" {{ old('forward_to') == $manager->id ? 'selected' : '' }}>
                                                {{ $manager->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('forward_to')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="forward_reason" class="form-label">
                                <i class="bi bi-chat-text me-1"></i>Lý do chuyển tiếp
                                <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('forward_reason') is-invalid @enderror" 
                                      id="forward_reason" name="forward_reason" rows="4" 
                                      placeholder="Nhập lý do chuyển tiếp công việc này..." required>{{ old('forward_reason') }}</textarea>
                            @error('forward_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Giải thích lý do tại sao bạn cần chuyển tiếp công việc này cho Quản lý khác.
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('task-detail', $task) }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Quay lại
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-arrow-right-circle me-2"></i>Chuyển tiếp công việc
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
