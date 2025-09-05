@extends('layouts.master')

@section('title', 'Không đủ quyền')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0">
                <div class="card-body text-center py-5">
                    <!-- Icon -->
                    <div class="mb-4">
                        <i class="fas fa-ban text-danger" style="font-size: 4rem;"></i>
                    </div>
                    
                    <!-- Error Code -->
                    <h1 class="display-1 text-danger fw-bold mb-3">403</h1>
                    
                    <!-- Error Message -->
                    <h2 class="h4 text-dark mb-4">Không đủ quyền thao tác</h2>
                    
                    <p class="text-muted mb-4 fs-5">
                        Vui lòng gửi yêu cầu đến tài khoản cao hơn thực hiện
                    </p>
                    
                    <!-- Action Buttons -->
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Quay lại
                        </a>
                        
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>
                            Về trang chủ
                        </a>
                    </div>
                    
                    <!-- Additional Info -->
                    <div class="mt-5 pt-4 border-top">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Nếu bạn cho rằng đây là lỗi, vui lòng liên hệ quản trị viên hệ thống
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 15px;
}

.btn {
    border-radius: 8px;
    padding: 10px 20px;
    font-weight: 500;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-1px);
}

.btn-outline-secondary {
    border-color: #6c757d;
    color: #6c757d;
}

.btn-outline-secondary:hover {
    background-color: #6c757d;
    border-color: #6c757d;
    transform: translateY(-1px);
}

.fas {
    transition: all 0.3s ease;
}

.btn:hover .fas {
    transform: scale(1.1);
}
</style>
@endsection
