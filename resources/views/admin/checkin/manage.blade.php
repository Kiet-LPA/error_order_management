@extends('layouts.master')
@section('title', 'Quản lý điểm danh')

@section('content')
<style>
.card-header {
    background: linear-gradient(90deg, #558EC1 0%, #5DA444 100%);
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

.filter-section .form-control:focus {
    border-color: #558EC1;
    box-shadow: 0 0 0 0.2rem rgba(85, 142, 193, 0.25);
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-success {
    background: #d4edda;
    color: #155724;
}

.status-failed {
    background: #f8d7da;
    color: #721c24;
}
</style>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">📋 Quản lý điểm danh</h5>
    </div>
    <div class="card-body">
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('admin.checkin.manage') }}" class="row g-3">
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

                @if($user->isAdmin() || $user->isDirector())
                <div class="col-md-3">
                    <label for="department_id" class="form-label fw-semibold">
                        <i class="bi bi-building me-1"></i>Phòng ban
                    </label>
                    <select class="form-select" id="department_id" name="department_id">
                        <option value="">Tất cả phòng ban</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="col-md-2">
                    <label for="session" class="form-label fw-semibold">
                        <i class="bi bi-clock me-1"></i>Ca làm việc
                    </label>
                    <select class="form-select" id="session" name="session">
                        <option value="">Tất cả ca</option>
                        <option value="morning" {{ request('session') == 'morning' ? 'selected' : '' }}>Ca sáng</option>
                        <option value="evening" {{ request('session') == 'evening' ? 'selected' : '' }}>Ca chiều</option>
                    </select>
                </div>

                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- Results -->
        @if($checkins->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nhân viên</th>
                            <th>Phòng ban</th>
                            <th>Ngày</th>
                            <th>Ca</th>
                            <th>Giờ</th>
                            <th>Khoảng cách</th>
                            <th>Trạng thái</th>
                            <th>Ghi chú</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($checkins as $checkin)
                        <tr>
                            <td>
                                <strong>{{ $checkin->user->name }}</strong><br>
                                <small>{{ $checkin->user->email }}</small>
                            </td>
                            <td>{{ $checkin->department->name ?? 'N/A' }}</td>
                            <td>{{ \Carbon\Carbon::parse($checkin->checkin_date)->format('d/m/Y') }}</td>
                            <td>{{ $checkin->session === 'morning' ? '🌅 Sáng' : '🌆 Chiều' }}</td>
                            <td>{{ \Carbon\Carbon::parse($checkin->checkin_time)->format('H:i') }}</td>
                            <td>{{ $checkin->distance_meters ? round($checkin->distance_meters) . 'm' : 'N/A' }}</td>
                            <td>
                                <span class="status-badge status-{{ $checkin->status }}">
                                    {{ $checkin->status === 'success' ? '✅ Thành công' : '❌ Thất bại' }}
                                </span>
                            </td>
                            <td>
                                @if($checkin->notes)
                                    <small class="text-muted">{{ Str::limit($checkin->notes, 50) }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-danger" 
                                            onclick="deleteCheckin({{ $checkin->id }})"
                                            title="Xóa điểm danh">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($checkins->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $checkins->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <h5 class="text-muted mt-3">Không tìm thấy dữ liệu điểm danh</h5>
                <p class="text-muted">Thử thay đổi bộ lọc để xem kết quả khác.</p>
            </div>
        @endif
    </div>
</div>

<!-- Fix Attendance Modal -->
<div class="modal fade" id="fixAttendanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">🔧 Sửa điểm danh thủ công</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="fixAttendanceForm" method="POST" action="{{ route('admin.checkin.fix-attendance') }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Lưu ý:</strong> Chỉ sử dụng cho trường hợp nhân viên có mặt nhưng quên điểm danh hoặc gặp sự cố kỹ thuật.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Chọn nhân viên:</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">-- Chọn nhân viên --</option>
                            @foreach($departments as $dept)
                                <optgroup label="{{ $dept->name }}">
                                    @foreach($dept->users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Ngày:</label>
                                <input type="date" name="checkin_date" class="form-control" required 
                                       value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Ca làm việc:</label>
                                <select name="session" class="form-select" required>
                                    <option value="">-- Chọn ca --</option>
                                    <option value="morning">🌅 Ca sáng (04:00 - 11:00)</option>
                                    <option value="evening">🌆 Ca chiều (13:00 - 20:00)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lý do sửa lỗi:</label>
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="VD: Nhân viên có mặt nhưng quên điểm danh, sự cố kỹ thuật GPS..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">✅ Thêm điểm danh</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Checkin Form -->
<form id="deleteCheckinForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function deleteCheckin(checkinId) {
    if (confirm('Bạn có chắc muốn xóa điểm danh này? Hành động này không thể hoàn tác.')) {
        const form = document.getElementById('deleteCheckinForm');
        form.action = `/admin/checkin/${checkinId}`;
        form.submit();
    }
}

// Show fix attendance modal
function showFixAttendanceModal() {
    const modal = new bootstrap.Modal(document.getElementById('fixAttendanceModal'));
    modal.show();
}
</script>
@endsection
