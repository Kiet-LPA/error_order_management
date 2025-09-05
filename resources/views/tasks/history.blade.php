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
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{{ $act->user->name }}</strong>
                            <span class="text-muted ms-2">{{ $act->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div>
                            @php
                                $actionLabels = [
                                    'comment' => 'Bình luận',
                                    'edit_comment' => 'Sửa bình luận',
                                    'delete_comment' => 'Xóa bình luận',
                                    'updated_status' => 'Cập nhật trạng thái',
                                    'created' => 'Tạo mới',
                                    'updated' => 'Cập nhật',
                                    'undo_completion' => 'Hoàn tác'
                                ];
                            @endphp
                            <span class="badge bg-info">{{ $actionLabels[$act->action] ?? ucfirst($act->action) }}</span>
                        </div>
                    </div>GỀN   
                    <div class="mt-2">
                        @php
                            $meta = $act->meta;
                            // Nếu meta là JSON string, decode nó
                            if (is_string($meta)) {
                                $decoded = json_decode($meta, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                    $meta = $decoded;
                                }
                            }
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
                            
                            {{-- Hiển thị thông tin deadline nếu có --}}
                            @if(isset($meta['old_deadline']) && isset($meta['new_deadline']))
                                <div class="mt-1">
                                    <small class="text-muted">
                                        Deadline: {{ \Carbon\Carbon::parse($meta['old_deadline'])->format('d/m/Y H:i') }} 
                                        → {{ \Carbon\Carbon::parse($meta['new_deadline'])->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                            @endif
                        @else
                            {{-- Nếu không phải array, hiển thị trực tiếp --}}
                            {{ $meta }}
                        @endif
                    </div>
                </li>
            @empty
                <li class="list-group-item text-muted">Chưa có hoạt động nào.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
