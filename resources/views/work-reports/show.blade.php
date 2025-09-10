@extends('layouts.master')
@section('title', 'Xem báo cáo công việc')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="bi bi-file-earmark-text text-primary"></i>
                    Báo cáo công việc
                </h2>
                <div>
                    <a href="{{ route('work-reports.edit', $workReport) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i>
                        Chỉnh sửa
                    </a>
                    <a href="{{ route('work-reports.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-week"></i>
                        Thông tin báo cáo
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Nhân viên:</strong></td>
                                    <td>{{ $workReport->user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Phòng ban:</strong></td>
                                    <td>{{ $workReport->user->department->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Năm:</strong></td>
                                    <td>{{ $workReport->year }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tuần:</strong></td>
                                    <td>Tuần {{ $workReport->week }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Trạng thái:</strong></td>
                                    <td>
                                        @if($workReport->status === 'draft')
                                            <span class="badge bg-secondary">Bản nháp</span>
                                        @elseif($workReport->status === 'submitted')
                                            <span class="badge bg-warning">Đã gửi</span>
                                        @elseif($workReport->status === 'approved')
                                            <span class="badge bg-success">Đã duyệt</span>
                                        @elseif($workReport->status === 'rejected')
                                            <span class="badge bg-danger">Bị từ chối</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày tạo:</strong></td>
                                    <td>{{ $workReport->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Cập nhật cuối:</strong></td>
                                    <td>{{ $workReport->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-list-task"></i>
                        Nội dung báo cáo
                    </h5>
                </div>
                <div class="card-body">
                    @if($workReport->daily_work)
                        <h6><strong>Công việc đã làm:</strong></h6>
                        <div class="mb-3">
                            {!! nl2br(e($workReport->daily_work)) !!}
                        </div>
                    @endif

                    @if($workReport->achievements)
                        <h6><strong>Thành tích đạt được:</strong></h6>
                        <div class="mb-3">
                            {!! nl2br(e($workReport->achievements)) !!}
                        </div>
                    @endif

                    @if($workReport->difficulties)
                        <h6><strong>Khó khăn gặp phải:</strong></h6>
                        <div class="mb-3">
                            {!! nl2br(e($workReport->difficulties)) !!}
                        </div>
                    @endif

                    @if($workReport->comments)
                        <h6><strong>Ghi chú thêm:</strong></h6>
                        <div class="mb-3">
                            {!! nl2br(e($workReport->comments)) !!}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    @if($workReport->status === 'rejected' && $workReport->rejection_reason)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-x-circle"></i>
                        Lý do từ chối
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $workReport->rejection_reason }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
