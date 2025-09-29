@extends('layouts.master')

@section('title', 'Điểm danh')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle"></i>
                        Chưa được phân công khu vực
                    </h3>
                </div>
                <div class="card-body text-center">
                    <div class="alert alert-warning">
                        <i class="fas fa-map-marker-alt fa-3x mb-3"></i>
                        <h4>Bạn chưa được phân công khu vực điểm danh</h4>
                        <p>Vui lòng liên hệ quản trị viên để được phân công khu vực điểm danh.</p>
                    </div>
                    
                    <a href="{{ route('kanban') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i>
                        Quay lại trang chủ
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
