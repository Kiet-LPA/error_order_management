@extends('layouts.master')

@section('title', 'Yêu cầu phòng ban')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="bi bi-building me-2"></i>
                    Yêu cầu phòng ban
                </h2>
                <div>
                    <a href="{{ route('support-requests.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Tạo yêu cầu mới
                    </a>
                </div>
            </div>

            <!-- Thống kê -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h4>{{ $stats['total'] }}</h4>
                            <small>Tổng cộng</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h4>{{ $stats['pending'] }}</h4>
                            <small>Chờ duyệt</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h4>{{ $stats['approved'] }}</h4>
                            <small>Đã duyệt</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h4>{{ $stats['rejected'] }}</h4>
                            <small>Đã từ chối</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h4>{{ $stats['forwarded'] }}</h4>
                            <small>Đã chuyển tiếp</small>
                        </div>
                    </div>
                </div>
            </div>

            @if($supportRequests->count() > 0)
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tiêu đề</th>
                                        <th>Người yêu cầu</th>
                                        <th>Người nhận</th>
                                        <th>Độ ưu tiên</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($supportRequests as $request)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <strong>{{ $request->title }}</strong>
                                                @if($request->is_urgent)
                                                    <span class="badge bg-danger ms-2">Khẩn cấp</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <strong>{{ $request->requester->name }}</strong>
                                                <span class="badge bg-{{ $request->requester->role === 'employee' ? 'success' : 'warning' }} ms-1">
                                                    {{ $request->requester->role === 'employee' ? 'Employee' : 'Manager' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($request->recipients)
                                                @foreach($request->recipients as $recipientId)
                                                    @php
                                                        $recipient = \App\Models\User::find($recipientId);
                                                    @endphp
                                                    @if($recipient)
                                                        <div class="d-flex align-items-center mb-1">
                                                            <strong>{{ $recipient->name }}</strong>
                                                            <span class="badge bg-{{ $recipient->role === 'manager' ? 'warning' : 'info' }} ms-1">
                                                                {{ $recipient->role === 'manager' ? 'Manager' : 'Director' }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $priorityColors = [
                                                    'low' => 'success',
                                                    'medium' => 'warning', 
                                                    'high' => 'danger'
                                                ];
                                                $priorityLabels = [
                                                    'low' => 'Thấp',
                                                    'medium' => 'Trung bình',
                                                    'high' => 'Cao'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $priorityColors[$request->priority] }}">
                                                {{ $priorityLabels[$request->priority] }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'approved' => 'success',
                                                    'rejected' => 'danger',
                                                    'forwarded' => 'info'
                                                ];
                                                $statusLabels = [
                                                    'pending' => 'Chờ phê duyệt',
                                                    'approved' => 'Đã phê duyệt',
                                                    'rejected' => 'Đã từ chối',
                                                    'forwarded' => 'Đã chuyển tiếp'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$request->status] }}">
                                                {{ $statusLabels[$request->status] }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $request->created_at->format('d/m/Y H:i') }}
                                            </small>
                                        </td>
                                        <td>
                                            <a href="{{ route('support-requests.show', $request) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye me-1"></i>Xem
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    @if($supportRequests->hasPages())
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                Hiển thị {{ $supportRequests->firstItem() }} - {{ $supportRequests->lastItem() }} 
                                trong tổng số {{ $supportRequests->total() }} kết quả
                            </div>
                            <nav aria-label="Page navigation">
                                <ul class="pagination pagination-sm mb-0">
                                    @if($supportRequests->onFirstPage())
                                        <li class="page-item disabled">
                                            <span class="page-link">Trước</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $supportRequests->previousPageUrl() }}">Trước</a>
                                        </li>
                                    @endif

                                    @foreach($supportRequests->getUrlRange(1, $supportRequests->lastPage()) as $page => $url)
                                        @if($page == $supportRequests->currentPage())
                                            <li class="page-item active">
                                                <span class="page-link">{{ $page }}</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                            </li>
                                        @endif
                                    @endforeach

                                    @if($supportRequests->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $supportRequests->nextPageUrl() }}">Sau</a>
                                        </li>
                                    @else
                                        <li class="page-item disabled">
                                            <span class="page-link">Sau</span>
                                        </li>
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    </div>
                    @endif
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                    </div>
                    <h4 class="text-muted">Chưa có yêu cầu nào</h4>
                    <p class="text-muted">Phòng ban của bạn chưa có yêu cầu hỗ trợ nào.</p>
                    <a href="{{ route('support-requests.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Tạo yêu cầu đầu tiên
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
