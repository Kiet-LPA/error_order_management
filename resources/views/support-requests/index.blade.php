@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 mb-0">
                    {{ __('Yêu cầu hỗ trợ') }}
                </h2>
                @if(auth()->user()->isEmployee())
                    <a href="{{ route('support-requests.create') }}" 
                       class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Tạo yêu cầu hỗ trợ
                    </a>
                @endif
            </div>

            <div class="card">
                <div class="card-body">
                    @if($supportRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Tiêu đề</th>
                                        <th>Người yêu cầu</th>
                                        <th>Phòng ban</th>
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
                                                <div class="fw-bold">{{ $request->title }}</div>
                                                @if($request->is_urgent)
                                                    <span class="badge bg-danger">Khẩn cấp</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>{{ $request->requester->name }}</div>
                                                <small class="text-muted">{{ $request->requester->email }}</small>
                                            </td>
                                            <td>{{ $request->department->name }}</td>
                                            <td>
                                                @php
                                                    $priorityColors = [
                                                        'low' => 'bg-success',
                                                        'medium' => 'bg-warning',
                                                        'high' => 'bg-danger'
                                                    ];
                                                    $priorityLabels = [
                                                        'low' => 'Thấp',
                                                        'medium' => 'Trung bình',
                                                        'high' => 'Cao'
                                                    ];
                                                @endphp
                                                <span class="badge {{ $priorityColors[$request->priority] }}">
                                                    {{ $priorityLabels[$request->priority] }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'bg-warning',
                                                        'approved' => 'bg-success',
                                                        'rejected' => 'bg-danger'
                                                    ];
                                                    $statusLabels = [
                                                        'pending' => 'Chờ phê duyệt',
                                                        'approved' => 'Đã phê duyệt',
                                                        'rejected' => 'Bị từ chối'
                                                    ];
                                                @endphp
                                                <span class="badge {{ $statusColors[$request->status] }}">
                                                    {{ $statusLabels[$request->status] }}
                                                </span>
                                            </td>
                                            <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('support-requests.show', $request) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> Xem
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted"></i>
                            <h5 class="mt-3">Chưa có yêu cầu hỗ trợ nào</h5>
                            @if(auth()->user()->isEmployee())
                                <p class="text-muted">Bạn có thể tạo yêu cầu hỗ trợ đầu tiên</p>
                                <a href="{{ route('support-requests.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Tạo yêu cầu mới
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
