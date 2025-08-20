@extends('layouts.master')

@section('title', 'Nhân viên mới')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="bi bi-person-plus me-2"></i>
                    Danh sách nhân viên mới
                </h2>
                <a href="{{ route('employees.new.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Thêm nhân viên mới
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Họ tên</th>
                                    <th>Email</th>
                                    <th>Số điện thoại</th>
                                    <th>Phòng ban</th>
                                    <th>Lương thử việc</th>
                                    <th>Thời gian thử việc</th>
                                    <th>Ngày bắt đầu</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($newEmployees as $employee)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <i class="bi bi-person-circle fs-4"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ $employee->name }}</div>
                                                    <small class="text-muted">{{ $employee->position ?? 'Chưa có chức vụ' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($employee->email)
                                                <a href="mailto:{{ $employee->email }}">{{ $employee->email }}</a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($employee->phone)
                                                <a href="tel:{{ $employee->phone }}">{{ $employee->phone }}</a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($employee->department)
                                                <span class="badge bg-secondary">{{ $employee->department->name }}</span>
                                            @else
                                                <span class="text-muted">Chưa phân công</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($employee->contracts && $employee->contracts->count() > 0)
                                                {{ number_format($employee->contracts->first()->probation_salary) }} VNĐ
                                            @else
                                                <span class="text-muted">Chưa có</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($employee->contracts && $employee->contracts->count() > 0)
                                                {{ $employee->contracts->first()->probation_period }} tháng
                                            @else
                                                <span class="text-muted">Chưa có</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($employee->contracts && $employee->contracts->count() > 0)
                                                {{ $employee->contracts->first()->start_date->format('d/m/Y') }}
                                            @else
                                                <span class="text-muted">Chưa có</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($employee->contracts && $employee->contracts->count() > 0)
                                                @switch($employee->contracts->first()->status)
                                                    @case('active')
                                                        <span class="badge bg-success">Đang thử việc</span>
                                                        @break
                                                    @case('completed')
                                                        <span class="badge bg-info">Hoàn thành</span>
                                                        @break
                                                    @case('terminated')
                                                        <span class="badge bg-danger">Đã chấm dứt</span>
                                                        @break
                                                @endswitch
                                            @else
                                                <span class="badge bg-warning">Chưa có hợp đồng</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('users.show', $employee) }}" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('users.edit', $employee) }}" class="btn btn-sm btn-outline-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @if($employee->contracts && $employee->contracts->first()->status == 'active')
                                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                                            data-bs-toggle="modal" data-bs-target="#convertModal{{ $employee->id }}">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                Chưa có nhân viên mới nào
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals for converting employees -->
@foreach($newEmployees as $employee)
    @if($employee->contracts && $employee->contracts->first()->status == 'active')
    <div class="modal fade" id="convertModal{{ $employee->id }}" tabindex="-1" aria-labelledby="convertModalLabel{{ $employee->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="convertModalLabel{{ $employee->id }}">
                        Chuyển nhân viên thành chính thức
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('employees.convert', $employee) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Bạn đang chuyển <strong>{{ $employee->name }}</strong> từ nhân viên thử việc thành nhân viên chính thức.</p>
                        

                        
                        <div class="mb-3">
                            <label for="official_salary{{ $employee->id }}" class="form-label">Lương chính thức (VNĐ) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="official_salary{{ $employee->id }}" name="official_salary" 
                                   min="0" step="1000000" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="official_start_date{{ $employee->id }}" class="form-label">Ngày bắt đầu làm chính thức <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="official_start_date{{ $employee->id }}" name="official_start_date" 
                                   value="{{ date('Y-m-d') }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="contract_duration{{ $employee->id }}" class="form-label">Hợp đồng trong bao lâu (tháng) <span class="text-danger">*</span></label>
                            <select class="form-select" id="contract_duration{{ $employee->id }}" name="contract_duration" required>
                                <option value="">Chọn thời gian</option>
                                <option value="12">12 tháng</option>
                                <option value="24">24 tháng</option>
                                <option value="36">36 tháng</option>
                                <option value="48">48 tháng</option>
                                <option value="60">60 tháng</option>
                            </select>
                        </div>
                        
                        <div class="alert alert-info">
                            <small>
                                <i class="bi bi-info-circle me-1"></i>
                                Lương thử việc hiện tại: {{ number_format($employee->contracts->first()->probation_salary) }} VNĐ
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i>Chuyển đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach

@endsection
