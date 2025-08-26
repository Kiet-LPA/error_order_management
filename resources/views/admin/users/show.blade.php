@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="bi bi-person-circle me-2"></i>
                    Chi tiết nhân viên
                </h2>
                <div>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Quay lại
                    </a>
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                        <i class="bi bi-pencil me-1"></i>Chỉnh sửa
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Thông tin cơ bản -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-person me-2"></i>Thông tin cơ bản
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Họ tên:</div>
                                <div class="col-sm-8">{{ $user->name }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Email:</div>
                                <div class="col-sm-8">
                                    @if($user->email)
                                        <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                                    @else
                                        <span class="text-muted">Chưa có</span>
                                    @endif
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Số điện thoại:</div>
                                <div class="col-sm-8">
                                    @if($user->phone)
                                        <a href="tel:{{ $user->phone }}">{{ $user->phone }}</a>
                                    @else
                                        <span class="text-muted">Chưa có</span>
                                    @endif
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Vai trò:</div>
                                <div class="col-sm-8">
                                    @switch($user->role)
                                        @case('admin')
                                            <span class="badge bg-danger">Admin</span>
                                            @break
                                        @case('manager')
                                            <span class="badge bg-warning">Manager</span>
                                            @break
                                        @case('employee')
                                            <span class="badge bg-info">Employee</span>
                                            @break
                                    @endswitch
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Phòng ban:</div>
                                <div class="col-sm-8">
                                    @if($user->department)
                                        <span class="badge bg-secondary">{{ $user->department->name }}</span>
                                    @else
                                        <span class="text-muted">Chưa phân công</span>
                                    @endif
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Loại nhân viên:</div>
                                <div class="col-sm-8">
                                    @if($user->employee_type == 'new')
                                        <span class="badge bg-warning">Nhân viên mới</span>
                                    @else
                                        <span class="badge bg-success">Nhân viên chính thức</span>
                                    @endif
                                </div>
                            </div>
                            @if($user->position)
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Chức vụ:</div>
                                <div class="col-sm-8">{{ $user->position }}</div>
                            </div>
                            @endif
                            @if($user->social_insurance_number)
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Mã số BHXH:</div>
                                <div class="col-sm-8">{{ $user->social_insurance_number }}</div>
                            </div>
                            @endif
                            @if($user->health_insurance_number)
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Mã số BHYT:</div>
                                <div class="col-sm-8">{{ $user->health_insurance_number }}</div>
                            </div>
                            @endif
                            @if($user->personal_identification_number)
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Mã số định danh:</div>
                                <div class="col-sm-8">{{ $user->personal_identification_number }}</div>
                            </div>
                            @endif
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Ngày tạo:</div>
                                <div class="col-sm-8">{{ $user->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thông tin hợp đồng -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-file-earmark-text me-2"></i>Thông tin hợp đồng
                            </h5>
                        </div>
                        <div class="card-body">
                                                            @if($user->activeContract)
                                @php $contract = $user->activeContract; @endphp
                                    <div class="border rounded p-3 mb-3">
                                        <div class="row mb-2">
                                            <div class="col-sm-4 fw-bold">
                                                @if($user->employee_type == 'new')
                                                    Lương thử việc:
                                                @else
                                                    Lương chính thức:
                                                @endif
                                            </div>
                                            <div class="col-sm-8">{{ number_format($contract->probation_salary) }} VNĐ</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-sm-4 fw-bold">Thời gian hợp đồng:</div>
                                            <div class="col-sm-8">{{ $contract->probation_period }} tháng</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-sm-4 fw-bold">Ngày bắt đầu:</div>
                                            <div class="col-sm-8">{{ $contract->start_date->format('d/m/Y') }}</div>
                                        </div>
                                        @if($contract->end_date)
                                        <div class="row mb-2">
                                            <div class="col-sm-4 fw-bold">Ngày kết thúc:</div>
                                            <div class="col-sm-8">{{ $contract->end_date->format('d/m/Y') }}</div>
                                        </div>
                                        @endif
                                        <div class="row mb-2">
                                            <div class="col-sm-4 fw-bold">Trạng thái:</div>
                                            <div class="col-sm-8">
                                                @switch($contract->status)
                                                    @case('active')
                                                        <span class="badge bg-success">Đang hoạt động</span>
                                                        @break
                                                    @case('completed')
                                                        <span class="badge bg-info">Hoàn thành</span>
                                                        @break
                                                    @case('terminated')
                                                        <span class="badge bg-danger">Đã chấm dứt</span>
                                                        @break
                                                @endswitch
                                            </div>
                                        </div>
                                        
                                        @if($contract->images && $contract->images->count() > 0)
                                        <div class="mt-3">
                                            <h6 class="fw-bold">Hình ảnh hợp đồng:</h6>
                                            <div class="row">
                                                @foreach($contract->images as $image)
                                                <div class="col-md-4 mb-2">
                                                    <a href="{{ $image->image_path }}" target="_blank">
                                                        <img src="{{ $image->image_path }}" 
                                                             class="img-thumbnail" 
                                                             alt="Trang {{ $image->page_number }}"
                                                             style="max-height: 100px;">
                                                    </a>
                                                    <small class="d-block text-center">Trang {{ $image->page_number }}</small>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                            @else
                                <p class="text-muted mb-0">Chưa có thông tin hợp đồng</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            
        </div>
    </div>
</div>
@endsection
