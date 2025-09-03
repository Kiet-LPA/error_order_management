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
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                Hiển thị {{ $supportRequests->firstItem() }} đến {{ $supportRequests->lastItem() }} 
                                trong tổng số {{ $supportRequests->total() }} yêu cầu
                            </div>
                            <div>
                                {{ $supportRequests->links() }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted mb-3"></i>
                        <h4 class="text-muted">Chưa có yêu cầu nào</h4>
                        <p class="text-muted mb-4">Bạn chưa tạo yêu cầu hỗ trợ nào.</p>
                        <a href="{{ route('support-requests.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Tạo yêu cầu đầu tiên
                        </a>
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
