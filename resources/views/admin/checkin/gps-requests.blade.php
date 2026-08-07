    @extends('layouts.master')
    @section('title', 'Quản lý GPS Requests')

    @section('content')
    <style>
    .card-header {
        background: #198754;
        color: #fff;
        border-bottom: none;
    }
    .card-header h5 {
        color: #fff;
    }

    .filter-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    white-space: nowrap;
    display: inline-block;
    min-width: fit-content;
}

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-approved {
        background: #d4edda;
        color: #155724;
    }

.status-rejected {
    background: #f8d7da;
    color: #721c24;
}

/* Đảm bảo cột Trạng thái có đủ không gian */
.table th:nth-child(7),
.table td:nth-child(7) {
    min-width: 120px;
    width: 120px;
}
    </style>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">📍 Quản lý GPS Requests</h5>
        </div>
        <div class="card-body">
            <!-- Filter Section -->
            <div class="filter-section">
                <form method="GET" action="{{ route('admin.checkin.gps-requests') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="status" class="form-label fw-semibold">
                            <i class="bi bi-funnel me-1"></i>Trạng thái
                        </label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Tất cả trạng thái</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Đã từ chối</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="date_from" class="form-label fw-semibold">
                            <i class="bi bi-calendar me-1"></i>Từ ngày
                        </label>
                        <input type="date" class="form-control" id="date_from" name="date_from" 
                            value="{{ request('date_from') }}">
                    </div>

                    <div class="col-md-3">
                        <label for="date_to" class="form-label fw-semibold">
                            <i class="bi bi-calendar me-1"></i>Đến ngày
                        </label>
                        <input type="date" class="form-control" id="date_to" name="date_to" 
                            value="{{ request('date_to') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i>Lọc
                        </button>
                    </div>
                </form>
            </div>

            <!-- Results -->
            @if($gpsRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nhân viên</th>
                                <th>Phòng ban</th>
                                <th>Ngày yêu cầu</th>
                                <th>Loại</th>
                                <th>Khoảng cách</th>
                                <th>Vị trí GPS</th>
                                <th>Trạng thái</th>
                                <th>Thời gian tạo</th>
                                <th>Ghi chú Admin</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gpsRequests as $gpsRequest)
                            <tr>
                                <td>
                                    <strong>{{ $gpsRequest->user->display_name }}</strong><br>
                                    <small>{{ $gpsRequest->user->email }}</small>
                                </td>
                                <td>{{ $gpsRequest->department->name ?? 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($gpsRequest->request_date)->format('d/m/Y') }}</td>
                                <td>
                                    @if($gpsRequest->session)
                                        <span class="badge {{ $gpsRequest->session === 'checkin' ? 'bg-success' : 'bg-warning' }}">
                                            {{ $gpsRequest->session === 'checkin' ? '📍 Checkin' : '🚪 Checkout' }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ round($gpsRequest->distance_meters) }}m</span>
                                </td>
                                <td>
                                    @if($gpsRequest->latitude && $gpsRequest->longitude)
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold location-name-text"
                                                  data-lat="{{ $gpsRequest->latitude }}"
                                                  data-lng="{{ $gpsRequest->longitude }}"
                                                  @if(!empty($gpsRequest->resolved_location_name))
                                                      data-name="{{ $gpsRequest->resolved_location_name }}"
                                                  @endif>
                                                {{ $gpsRequest->resolved_location_name ?: 'Đang tải vị trí...' }}
                                            </span>
                                            <small class="text-muted mb-1">
                                                <i class="bi bi-geo-alt me-1"></i>
                                                {{ number_format($gpsRequest->latitude, 6) }}, {{ number_format($gpsRequest->longitude, 6) }}
                                            </small>
                                            <a href="{{ $gpsRequest->google_maps_url }}"
                                            target="_blank"
                                            class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-map me-1"></i>Xem bản đồ
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-muted">Không có dữ liệu</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-badge status-{{ $gpsRequest->status }}">
                                        @switch($gpsRequest->status)
                                            @case('pending')
                                                ⏳ Chờ duyệt
                                                @break
                                            @case('approved')
                                                ✅ Đã duyệt
                                                @break
                                            @case('rejected')
                                                ❌ Đã từ chối
                                                @break
                                        @endswitch
                                    </span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($gpsRequest->created_at)->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($gpsRequest->admin_notes)
                                        <small class="text-muted">{{ Str::limit($gpsRequest->admin_notes, 50) }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($gpsRequest->status === 'pending')
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-success" 
                                                    onclick="approveGpsRequest({{ $gpsRequest->id }}, 'approved')"
                                                    title="Duyệt">
                                                <i class="bi bi-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" 
                                                    onclick="approveGpsRequest({{ $gpsRequest->id }}, 'rejected')"
                                                    title="Từ chối">
                                                <i class="bi bi-x"></i>
                                            </button>
                                            <button class="btn btn-sm btn-info" 
                                                    onclick="showGpsRequestDetails({{ $gpsRequest->id }})"
                                                    title="Chi tiết">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    @else
                                        <button class="btn btn-sm btn-info" 
                                                onclick="showGpsRequestDetails({{ $gpsRequest->id }})"
                                                title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($gpsRequests->hasPages())
                    <div class="card-footer bg-light border-0 mt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                Hiển thị {{ $gpsRequests->firstItem() ?? 0 }} - {{ $gpsRequests->lastItem() ?? 0 }} 
                                trong tổng số {{ $gpsRequests->total() }} kết quả
                            </div>
                            
                            <nav aria-label="Page navigation">
                                <ul class="pagination mb-0">
                                    @if ($gpsRequests->onFirstPage())
                                        <li class="page-item disabled">
                                            <span class="page-link">« Previous</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $gpsRequests->previousPageUrl() }}" rel="prev">« Previous</a>
                                        </li>
                                    @endif

                                    @php
                                        $start = max(1, $gpsRequests->currentPage() - 2);
                                        $end = min($gpsRequests->lastPage(), $gpsRequests->currentPage() + 2);
                                    @endphp
                                    
                                    @if($start > 1)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $gpsRequests->url(1) }}">1</a>
                                        </li>
                                        @if($start > 2)
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        @endif
                                    @endif
                                    
                                    @for ($page = $start; $page <= $end; $page++)
                                        @if ($page == $gpsRequests->currentPage())
                                            <li class="page-item active">
                                                <span class="page-link">{{ $page }}</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $gpsRequests->url($page) }}">{{ $page }}</a>
                                            </li>
                                        @endif
                                    @endfor
                                    
                                    @if($end < $gpsRequests->lastPage())
                                        @if($end < $gpsRequests->lastPage() - 1)
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        @endif
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $gpsRequests->url($gpsRequests->lastPage()) }}">{{ $gpsRequests->lastPage() }}</a>
                                        </li>
                                    @endif

                                    @if ($gpsRequests->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $gpsRequests->nextPageUrl() }}" rel="next">Next »</a>
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
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h5 class="text-muted mt-3">Không tìm thấy GPS Requests</h5>
                    <p class="text-muted">Thử thay đổi bộ lọc để xem kết quả khác.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- GPS Request Details Modal -->
    <div class="modal fade" id="gpsRequestModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">📍 Chi tiết GPS Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="gpsRequestModalBody">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Approve/Reject GPS Request Modal -->
    <div class="modal fade" id="approveGpsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveGpsModalTitle">Duyệt GPS Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="approveGpsForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="status" id="approveGpsStatus">
                        
                        <div class="mb-3">
                            <label class="form-label">Ghi chú:</label>
                            <textarea name="admin_notes" class="form-control" rows="3" 
                                    placeholder="Nhập ghi chú cho quyết định này..."></textarea>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Lưu ý:</strong> Nếu duyệt, hệ thống sẽ tự động tạo bản ghi điểm danh thành công.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary" id="approveGpsSubmitBtn">Xác nhận</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function approveGpsRequest(gpsRequestId, status) {
        const modal = new bootstrap.Modal(document.getElementById('approveGpsModal'));
        const form = document.getElementById('approveGpsForm');
        const statusInput = document.getElementById('approveGpsStatus');
        const title = document.getElementById('approveGpsModalTitle');
        const submitBtn = document.getElementById('approveGpsSubmitBtn');

        form.action = `/admin/checkin/gps-requests/${gpsRequestId}/approve`;
        statusInput.value = status;

        if (status === 'approved') {
            title.textContent = '✅ Duyệt GPS Request';
            submitBtn.className = 'btn btn-success';
            submitBtn.textContent = 'Duyệt';
        } else {
            title.textContent = '❌ Từ chối GPS Request';
            submitBtn.className = 'btn btn-danger';
            submitBtn.textContent = 'Từ chối';
        }

        modal.show();
    }

    function showGpsRequestDetails(gpsRequestId) {
        // Load GPS request details via AJAX
        fetch(`/admin/checkin/gps-requests/${gpsRequestId}`)
            .then(response => response.json())
            .then(data => {
                const modalBody = document.getElementById('gpsRequestModalBody');
                modalBody.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Thông tin nhân viên:</h6>
                            <p><strong>Tên:</strong> ${data.user.name}</p>
                            <p><strong>Email:</strong> ${data.user.email}</p>
                            <p><strong>Phòng ban:</strong> ${data.department.name}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Thông tin yêu cầu:</h6>
                            <p><strong>Ngày:</strong> ${data.request_date}</p>
                            <p><strong>Khoảng cách:</strong> ${Math.round(data.distance_meters)}m</p>
                            <p><strong>Mã GPS:</strong> <code>${data.gps_code}</code></p>
                            <p><strong>Trạng thái:</strong> <span class="badge bg-${data.status === 'pending' ? 'warning' : data.status === 'approved' ? 'success' : 'danger'}">${data.status}</span></p>
                            ${data.latitude && data.longitude ? `
                            <p><strong>Vị trí GPS:</strong>
                                <br><span class="fw-semibold" id="gpsDetailPlaceName">Đang tải vị trí...</span>
                                <br><small class="text-muted">${parseFloat(data.latitude).toFixed(6)}, ${parseFloat(data.longitude).toFixed(6)}</small>
                                <br><a href="https://www.google.com/maps?q=${data.latitude},${data.longitude}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                    <i class="bi bi-map me-1"></i>Xem trên Google Maps
                                </a>
                            </p>` : ''}
                        </div>
                    </div>
                    ${data.admin_notes ? `<div class="mt-3"><h6>Ghi chú Admin:</h6><p class="bg-light p-2 rounded">${data.admin_notes}</p></div>` : ''}
                `;
                
                const modal = new bootstrap.Modal(document.getElementById('gpsRequestModal'));
                modal.show();

                if (data.latitude && data.longitude && window.LocationName) {
                    window.LocationName.resolve(data.latitude, data.longitude).then(function(name) {
                        const el = document.getElementById('gpsDetailPlaceName');
                        if (el) el.textContent = name || (parseFloat(data.latitude).toFixed(5) + ', ' + parseFloat(data.longitude).toFixed(5));
                    }).catch(function() {});
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Không thể tải chi tiết GPS request.');
            });
    }
    </script>
    <script src="{{ asset('js/location-name.js') }}?v=3"></script>
    @endsection
