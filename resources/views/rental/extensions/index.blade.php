@extends('layouts.master')

@section('title', 'Duyệt gia hạn - HPFoods')

@section('content')
<style>
.extension-card {
    transition: transform 0.2s ease-in-out;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.extension-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}
.status-pending {
    border-left: 4px solid #ffc107;
}
.status-approved {
    border-left: 4px solid #28a745;
}
.status-rejected {
    border-left: 4px solid #dc3545;
}
.btn-action {
    border-radius: 20px;
    padding: 0.5rem 1rem;
}
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="bi bi-clock-history me-2"></i>Duyệt yêu cầu gia hạn
                </h2>
                <a href="{{ route('rental.admin') }}" class="btn btn-outline-primary btn-action">
                    <i class="bi bi-arrow-left me-1"></i>Quay lại
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Filter Tabs -->
            <ul class="nav nav-tabs mb-4" id="extensionTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                        <i class="bi bi-hourglass-split me-1"></i>Chờ duyệt ({{ $extensions->where('status', 'pending')->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab">
                        <i class="bi bi-check-circle me-1"></i>Đã duyệt ({{ $extensions->where('status', 'approved')->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab">
                        <i class="bi bi-x-circle me-1"></i>Từ chối ({{ $extensions->where('status', 'rejected')->count() }})
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="extensionTabsContent">
                <!-- Pending Extensions -->
                <div class="tab-pane fade show active" id="pending" role="tabpanel">
                    @php $pendingExtensions = $extensions->where('status', 'pending') @endphp
                    @if($pendingExtensions->count() > 0)
                        <div class="row">
                            @foreach($pendingExtensions as $extension)
                                <div class="col-12 col-lg-6 col-xl-4 mb-4">
                                    <div class="card extension-card status-pending">
                                        <div class="card-header bg-warning text-dark">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">
                                                    <i class="bi bi-clock-history me-2"></i>Yêu cầu gia hạn
                                                </h6>
                                                <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <strong>Xe:</strong><br>
                                                <span class="text-primary">{{ $extension->rental->car->license_plate }} - {{ $extension->rental->car->car_type }}</span>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <strong>Người thuê:</strong><br>
                                                <span class="text-muted">{{ $extension->rental->user->name }} ({{ ucfirst($extension->rental->user->role) }})</span>
                                            </div>

                                            <div class="mb-3">
                                                <strong>Thời gian hiện tại:</strong><br>
                                                <small class="text-muted">
                                                    Bắt đầu: {{ $extension->rental->rental_start->format('d/m/Y H:i') }}<br>
                                                    Kết thúc: {{ $extension->rental->rental_end->format('d/m/Y H:i') }}
                                                </small>
                                            </div>

                                            <div class="mb-3">
                                                <strong>Thời gian mới:</strong><br>
                                                <span class="text-success">{{ $extension->new_rental_end->format('d/m/Y H:i') }}</span>
                                            </div>

                                            <div class="mb-3">
                                                <strong>Lý do:</strong><br>
                                                <p class="text-muted">{{ $extension->reason }}</p>
                                            </div>

                                            <div class="mb-3">
                                                <strong>Ngày yêu cầu:</strong><br>
                                                <small class="text-muted">{{ $extension->created_at->format('d/m/Y H:i:s') }}</small>
                                            </div>

                                            <div class="d-flex justify-content-between">
                                                <form action="{{ route('rental.extensions.approve', $extension) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm" 
                                                            onclick="return confirm('Bạn có chắc chắn muốn duyệt gia hạn này?')">
                                                        <i class="bi bi-check-circle me-1"></i>Duyệt
                                                    </button>
                                                </form>
                                                
                                                <button class="btn btn-danger btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#rejectModal{{ $extension->id }}">
                                                    <i class="bi bi-x-circle me-1"></i>Từ chối
                                                </button>
                                                
                                                <a href="{{ route('rental.show', $extension->rental) }}" class="btn btn-outline-info btn-sm">
                                                    <i class="bi bi-eye me-1"></i>Chi tiết
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reject Modal -->
                                <div class="modal fade" id="rejectModal{{ $extension->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    <i class="bi bi-x-circle me-2"></i>Từ chối gia hạn
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('rental.extensions.reject', $extension) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Xe</label>
                                                        <input type="text" class="form-control" 
                                                               value="{{ $extension->rental->car->license_plate }} - {{ $extension->rental->car->car_type }}" readonly>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Người thuê</label>
                                                        <input type="text" class="form-control" 
                                                               value="{{ $extension->rental->user->name }}" readonly>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Lý do yêu cầu</label>
                                                        <textarea class="form-control" rows="3" readonly>{{ $extension->reason }}</textarea>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="rejection_reason" class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
                                                        <textarea class="form-control @error('rejection_reason') is-invalid @enderror" 
                                                                  name="rejection_reason" id="rejection_reason" rows="3" 
                                                                  placeholder="Vui lòng nêu lý do từ chối..." required>{{ old('rejection_reason') }}</textarea>
                                                        @error('rejection_reason')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn btn-danger">
                                                        <i class="bi bi-x-circle me-1"></i>Từ chối
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-hourglass-split display-1 text-muted"></i>
                            <h4 class="text-muted mt-3">Không có yêu cầu gia hạn nào</h4>
                            <p class="text-muted">Tất cả yêu cầu gia hạn đã được xử lý</p>
                        </div>
                    @endif
                </div>

                <!-- Approved Extensions -->
                <div class="tab-pane fade" id="approved" role="tabpanel">
                    @php $approvedExtensions = $extensions->where('status', 'approved') @endphp
                    @if($approvedExtensions->count() > 0)
                        <div class="row">
                            @foreach($approvedExtensions as $extension)
                                <div class="col-12 col-lg-6 col-xl-4 mb-4">
                                    <div class="card extension-card status-approved">
                                        <div class="card-header bg-success text-white">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">
                                                    <i class="bi bi-check-circle me-2"></i>Đã duyệt
                                                </h6>
                                                <span class="badge bg-success">Đã duyệt</span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <strong>Xe:</strong><br>
                                                <span class="text-primary">{{ $extension->rental->car->license_plate }} - {{ $extension->rental->car->car_type }}</span>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <strong>Người thuê:</strong><br>
                                                <span class="text-muted">{{ $extension->rental->user->name }}</span>
                                            </div>

                                            <div class="mb-3">
                                                <strong>Thời gian mới:</strong><br>
                                                <span class="text-success">{{ $extension->new_rental_end->format('d/m/Y H:i') }}</span>
                                            </div>

                                            <div class="mb-3">
                                                <strong>Duyệt bởi:</strong><br>
                                                <small class="text-muted">{{ $extension->approvedBy->name ?? 'N/A' }}</small>
                                            </div>

                                            <div class="mb-3">
                                                <strong>Ngày duyệt:</strong><br>
                                                <small class="text-muted">{{ $extension->approved_at->format('d/m/Y H:i:s') ?? 'N/A' }}</small>
                                            </div>

                                            <div class="d-flex justify-content-center">
                                                <a href="{{ route('rental.show', $extension->rental) }}" class="btn btn-outline-info btn-sm">
                                                    <i class="bi bi-eye me-1"></i>Chi tiết
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-check-circle display-1 text-muted"></i>
                            <h4 class="text-muted mt-3">Chưa có yêu cầu nào được duyệt</h4>
                        </div>
                    @endif
                </div>

                <!-- Rejected Extensions -->
                <div class="tab-pane fade" id="rejected" role="tabpanel">
                    @php $rejectedExtensions = $extensions->where('status', 'rejected') @endphp
                    @if($rejectedExtensions->count() > 0)
                        <div class="row">
                            @foreach($rejectedExtensions as $extension)
                                <div class="col-12 col-lg-6 col-xl-4 mb-4">
                                    <div class="card extension-card status-rejected">
                                        <div class="card-header bg-danger text-white">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">
                                                    <i class="bi bi-x-circle me-2"></i>Đã từ chối
                                                </h6>
                                                <span class="badge bg-danger">Từ chối</span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <strong>Xe:</strong><br>
                                                <span class="text-primary">{{ $extension->rental->car->license_plate }} - {{ $extension->rental->car->car_type }}</span>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <strong>Người thuê:</strong><br>
                                                <span class="text-muted">{{ $extension->rental->user->name }}</span>
                                            </div>

                                            <div class="mb-3">
                                                <strong>Lý do từ chối:</strong><br>
                                                <p class="text-danger">{{ $extension->rejection_reason }}</p>
                                            </div>

                                            <div class="mb-3">
                                                <strong>Ngày từ chối:</strong><br>
                                                <small class="text-muted">{{ $extension->updated_at->format('d/m/Y H:i:s') }}</small>
                                            </div>

                                            <div class="d-flex justify-content-center">
                                                <a href="{{ route('rental.show', $extension->rental) }}" class="btn btn-outline-info btn-sm">
                                                    <i class="bi bi-eye me-1"></i>Chi tiết
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-x-circle display-1 text-muted"></i>
                            <h4 class="text-muted mt-3">Chưa có yêu cầu nào bị từ chối</h4>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
