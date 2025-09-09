@extends('layouts.master')

@section('title', 'Yêu cầu của tôi')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="bi bi-person-lines-fill me-2"></i>Yêu cầu của tôi
                </h2>
                <a href="{{ route('support-requests.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Tạo yêu cầu mới
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(isset($error))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ $error }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($supportRequests->count() > 0)
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tiêu đề</th>
                                        <th>Trạng thái</th>
                                        <th>Loại yêu cầu</th>
                                        <th>Người nhận</th>
                                        <th>Ngày tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($supportRequests as $request)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($request->is_urgent)
                                                    <span class="badge bg-danger me-2">Khẩn cấp</span>
                                                @endif
                                                <div>
                                                    <h6 class="mb-1">{{ $request->title }}</h6>
                                                    <small class="text-muted">{{ Str::limit($request->description, 100) }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $request->getStatusColor() }}">
                                                {{ $request->getStatusLabel() }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $request->isEmployeeRequest() ? 'primary' : 'info' }}">
                                                {{ $request->isEmployeeRequest() ? 'Nhân viên' : 'Quản lý' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($request->recipients && count($request->recipients) > 0)
                                                @php
                                                    $recipients = $request->getRecipients();
                                                @endphp
                                                @foreach($recipients as $recipient)
                                                    <span class="badge bg-secondary me-1">{{ $recipient->name }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">Chưa chỉ định</span>
                                            @endif
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
                                            
                                            @if($request->canBeCancelledByEmployee() && auth()->user()->isEmployee() && $request->requester_id === auth()->user()->id)
                                                <form method="POST" action="{{ route('support-requests.cancel', $request) }}" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn hủy yêu cầu này?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-x-circle me-1"></i>Hủy
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    @if($supportRequests->hasPages())
                    <div class="card-footer bg-light border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                Hiển thị {{ $supportRequests->firstItem() ?? 0 }} - {{ $supportRequests->lastItem() ?? 0 }} 
                                trong tổng số {{ $supportRequests->total() }} kết quả
                            </div>
                            
                            <nav aria-label="Page navigation">
                                <ul class="pagination mb-0">
                                    @if ($supportRequests->onFirstPage())
                                        <li class="page-item disabled">
                                            <span class="page-link">« Previous</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $supportRequests->previousPageUrl() }}" rel="prev">« Previous</a>
                                        </li>
                                    @endif

                                    @php
                                        $start = max(1, $supportRequests->currentPage() - 2);
                                        $end = min($supportRequests->lastPage(), $supportRequests->currentPage() + 2);
                                    @endphp
                                    
                                    @if($start > 1)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $supportRequests->url(1) }}">1</a>
                                        </li>
                                        @if($start > 2)
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        @endif
                                    @endif
                                    
                                    @for ($page = $start; $page <= $end; $page++)
                                        @if ($page == $supportRequests->currentPage())
                                            <li class="page-item active">
                                                <span class="page-link">{{ $page }}</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $supportRequests->url($page) }}">{{ $page }}</a>
                                            </li>
                                        @endif
                                    @endfor
                                    
                                    @if($end < $supportRequests->lastPage())
                                        @if($end < $supportRequests->lastPage() - 1)
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        @endif
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $supportRequests->url($supportRequests->lastPage()) }}">{{ $supportRequests->lastPage() }}</a>
                                        </li>
                                    @endif

                                    @if ($supportRequests->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $supportRequests->nextPageUrl() }}" rel="next">Next »</a>
                                        </li>
                                    @else
                                        <li class="page-item disabled">
                                            <span class="page-link">Next »</span>
                                        </li>
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    </div>
                    @endif
                </div>
            @elseif(isset($isEmpty) && $isEmpty)
                <!-- Empty State - Không phải lỗi, chỉ là chưa có dữ liệu -->
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-clipboard-plus display-1 text-primary"></i>
                        </div>
                        <h4 class="text-primary mb-3">Chưa có yêu cầu hỗ trợ nào</h4>
                        <p class="text-muted mb-4">
                            Bạn chưa tạo yêu cầu hỗ trợ nào. Hãy tạo yêu cầu đầu tiên để bắt đầu sử dụng hệ thống.
                        </p>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="{{ route('support-requests.create') }}" class="btn btn-primary btn-lg">
                                <i class="bi bi-plus-circle me-2"></i>Tạo yêu cầu hỗ trợ
                            </a>
                            <a href="{{ route('support-requests.index') }}" class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-arrow-left me-2"></i>Xem tất cả yêu cầu
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <!-- Error State - Có lỗi thật -->
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-exclamation-triangle display-1 text-warning"></i>
                        </div>
                        <h4 class="text-warning mb-3">Không thể tải dữ liệu</h4>
                        <p class="text-muted mb-4">
                            Có lỗi xảy ra khi tải danh sách yêu cầu của bạn. Vui lòng thử lại sau.
                        </p>
                        <div class="d-flex justify-content-center gap-3">
                            <button type="button" class="btn btn-outline-primary" onclick="window.location.reload()">
                                <i class="bi bi-arrow-clockwise me-2"></i>Thử lại
                            </button>
                            <a href="{{ route('support-requests.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Tạo yêu cầu mới
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
</script>
@endpush
