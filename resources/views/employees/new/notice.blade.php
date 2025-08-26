@extends('layouts.master')
@section('title', 'Tài khoản đang chờ phê duyệt')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-clock me-2"></i>
                        Tài khoản đang chờ phê duyệt
                    </h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-user-clock fa-4x text-warning mb-3"></i>
                        <h5 class="text-muted">Xin chào, {{ $user->name }}!</h5>
                    </div>
                    
                    <div class="alert alert-info">
                        <p class="mb-2">
                            <strong>Tài khoản của bạn đã được tạo thành công!</strong>
                        </p>
                        <p class="mb-0">
                            Hiện tại tài khoản đang ở trạng thái <strong>"Nhân viên mới"</strong> và cần được quản trị viên phê duyệt để có thể sử dụng đầy đủ các chức năng của hệ thống.
                        </p>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card border-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-info-circle text-info me-2"></i>
                                        Thông tin tài khoản
                                    </h6>
                                    <ul class="list-unstyled">
                                        <li><strong>Tên:</strong> {{ $user->name }}</li>
                                        <li><strong>Email:</strong> {{ $user->email ?? 'Chưa cập nhật' }}</li>
                                        <li><strong>Số điện thoại:</strong> {{ $user->phone ?? 'Chưa cập nhật' }}</li>
                                        <li><strong>Ngày tạo:</strong> {{ $user->created_at->format('d/m/Y H:i') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-tasks text-success me-2"></i>
                                        Các chức năng sẽ có
                                    </h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success me-2"></i>Quản lý công việc</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Tạo báo cáo công việc</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Xem lịch sử hoạt động</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Tham gia thảo luận</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Lưu ý quan trọng
                            </h6>
                            <p class="mb-2">
                                Vui lòng liên hệ quản trị viên hoặc người quản lý để được phê duyệt tài khoản. 
                                Sau khi được phê duyệt, bạn sẽ có thể truy cập đầy đủ các chức năng của hệ thống.
                            </p>
                            <hr>
                            <p class="mb-0">
                                <strong>Thời gian phê duyệt thường từ 1-2 ngày làm việc.</strong>
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary me-2">
                            <i class="fas fa-home me-1"></i>
                            Về trang chủ
                        </a>
                        <button onclick="window.location.reload()" class="btn btn-outline-secondary">
                            <i class="fas fa-sync-alt me-1"></i>
                            Kiểm tra lại
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.fa-4x {
    font-size: 4rem;
}

.list-unstyled li {
    padding: 0.25rem 0;
}
</style>
@endsection
