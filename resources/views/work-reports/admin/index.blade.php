@extends('layouts.master')
@section('title', 'Quản lý báo cáo công việc - Admin')

@section('content')
@push('styles')
<style>
.work-report-container {
    max-width: 1600px;
    margin: 0 auto;
    padding: 20px;
}

.management-section {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 24px;
    margin-bottom: 24px;
}

.section-title {
    font-size: 20px;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.section-title i {
    color: #558EC1;
}

/* Hierarchy styles */
.hierarchy-container {
    background: #f7fafc;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
}

.hierarchy-item {
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.hierarchy-label {
    font-weight: 600;
    color: #2d3748;
    min-width: 100px;
    font-size: 16px;
}

.hierarchy-select {
    flex: 1;
    max-width: 300px;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 16px;
    background: white;
    transition: all 0.3s ease;
}

.hierarchy-select:focus {
    outline: none;
    border-color: #558EC1;
    box-shadow: 0 0 0 3px rgba(85, 142, 193, 0.1);
}

.reports-table-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 24px;
    margin-top: 24px;
}

.table-responsive {
    overflow-x: auto;
}

.report-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 16px;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.report-table th,
.report-table td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}

.report-table th {
    background: #f7fafc;
    font-weight: 600;
    color: #2d3748;
}

.report-table tr:hover {
    background: #f7fafc;
}

.no-reports {
    text-align: center;
    padding: 40px 20px;
    color: #718096;
    font-style: italic;
}

/* CSS cho nút reject */
.btn-outline-danger.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.btn-outline-danger:hover:not(:disabled) {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
}

.btn-outline-danger:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    background-color: #6c757d;
    border-color: #6c757d;
}

/* CSS cho badge đã từ chối */
.badge.bg-danger.small {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

/* Đảm bảo layout của cột trạng thái */
.d-flex.align-items-center.justify-content-center.gap-2 {
    gap: 0.5rem !important;
}

/* CSS cho cột trạng thái */
.status-column {
    width: 120px;
}
</style>
@endpush

@section('content')
<div class="work-report-container">
    <div class="management-section">
        <div class="section-title">
            <i class="bi bi-diagram-3"></i>
            Quản lý báo cáo theo phân cấp
        </div>
        
        <!-- Phân cấp dropdown -->
        <div class="hierarchy-container">
            <!-- Department Dropdown -->
            <div class="hierarchy-item">
                <label class="hierarchy-label">Phòng ban:</label>
                <select class="hierarchy-select" id="department-select" onchange="loadEmployees()">
                    <option value="">Chọn phòng ban</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Employee Dropdown -->
            <div class="hierarchy-item" id="employee-container" style="display: none;">
                <label class="hierarchy-label">Nhân viên:</label>
                <select class="hierarchy-select" id="employee-select" onchange="loadYears()">
                    <option value="">Chọn nhân viên</option>
                </select>
            </div>
            
            <!-- Year Dropdown -->
            <div class="hierarchy-item" id="year-container" style="display: none;">
                <label class="hierarchy-label">Năm:</label>
                <select class="hierarchy-select" id="year-select" onchange="loadMonths()">
                    <option value="">Chọn năm</option>
                </select>
            </div>
            
            <!-- Month Dropdown -->
            <div class="hierarchy-item" id="month-container" style="display: none;">
                <label class="hierarchy-label">Tháng:</label>
                <select class="hierarchy-select" id="month-select" onchange="loadWeeks()">
                    <option value="">Chọn tháng</option>
                </select>
            </div>
            
            <!-- Week Dropdown -->
            <div class="hierarchy-item" id="week-container" style="display: none;">
                <label class="hierarchy-label">Tuần:</label>
                <select class="hierarchy-select" id="week-select" onchange="loadReports()">
                    <option value="">Chọn tuần</option>
                </select>
            </div>
        </div>
        
        <!-- Bảng báo cáo -->
        <div class="reports-table-container" id="reports-table-container" style="display: none;">
            <div class="section-title">
                <i class="bi bi-table"></i>
                Báo cáo tuần <span id="selected-week-info"></span>
            </div>
            
            <div class="table-responsive">
                <table class="report-table" id="reports-table">
                    <thead>
                        <tr>
                            <th style="color: #000; font-weight: bold;">STT</th>
                            <th style="color: #000; font-weight: bold;">Ngày/Tháng/Năm</th>
                            <th style="color: #000; font-weight: bold;">Tên</th>
                            <th style="color: #000; font-weight: bold;">Phòng ban</th>
                            <th style="color: #000; font-weight: bold;">Vị trí</th>
                            <th style="color: #000; font-weight: bold;">Nội dung</th>
                            <th style="color: #000; font-weight: bold; width: 120px;">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span>Trạng thái</span>
                                    <div class="form-check ms-2">
                                        <input class="form-check-input" type="checkbox" id="selectAllReports" onchange="toggleAllReports(this)">
                                        <label class="form-check-label small" for="selectAllReports">Tất cả</label>
                                    </div>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="reports-table-body">
                        <!-- Dữ liệu báo cáo sẽ được load bằng JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedDepartmentId = null;
let selectedEmployeeId = null;
let selectedYear = null;
let selectedMonth = null;
let selectedWeek = null;

// Load employees khi chọn department
function loadEmployees() {
    const departmentId = document.getElementById('department-select').value;
    if (!departmentId) {
        resetHierarchy();
        return;
    }
    
    selectedDepartmentId = departmentId;
    
    // Reset các dropdown phía dưới
    document.getElementById('year-container').style.display = 'none';
    document.getElementById('week-container').style.display = 'none';
    document.getElementById('reports-table-container').style.display = 'none';
    
    // Load employees cho department này
    fetch(`{{ route('work-reports.employees') }}?department_id=${departmentId}`)
        .then(response => response.json())
        .then(employees => {
            const employeeSelect = document.getElementById('employee-select');
            employeeSelect.innerHTML = '<option value="">Chọn nhân viên</option>';
            
            employees.forEach(employee => {
                employeeSelect.innerHTML += `<option value="${employee.id}">${employee.name}</option>`;
            });
            
            document.getElementById('employee-container').style.display = 'flex';
        })
        .catch(error => {
            console.error('Error loading employees:', error);
        });
}

// Load years khi chọn employee
function loadYears() {
    const employeeId = document.getElementById('employee-select').value;
    if (!employeeId) {
        resetHierarchyFromEmployee();
        return;
    }
    
    selectedEmployeeId = employeeId;
    
    // Reset các dropdown phía dưới
    document.getElementById('month-container').style.display = 'none';
    document.getElementById('week-container').style.display = 'none';
    document.getElementById('reports-table-container').style.display = 'none';
    
    // Load years cho employee này
    fetch(`{{ route('work-reports.employee-reports') }}?user_id=${employeeId}`)
        .then(response => {
            console.log('API response status:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('API response data for years:', data);
            
            const yearSelect = document.getElementById('year-select');
            yearSelect.innerHTML = '<option value="">Chọn năm</option>';
            
            // Lấy các năm có báo cáo
            const reports = data.reports || [];
            console.log('Reports for years:', reports);
            
            const years = [...new Set(reports.map(report => report.year))].sort((a, b) => b - a);
            console.log('Years found:', years);
            
            years.forEach(year => {
                yearSelect.innerHTML += `<option value="${year}">${year}</option>`;
            });
            
            // Thêm năm hiện tại nếu chưa có
            const currentYear = new Date().getFullYear();
            if (!years.includes(currentYear)) {
                yearSelect.innerHTML += `<option value="${currentYear}">${currentYear}</option>`;
            }
            
            document.getElementById('year-container').style.display = 'flex';
        })
        .catch(error => {
            console.error('Error loading years:', error);
        });
}

// Load months khi chọn year
function loadMonths() {
    const year = document.getElementById('year-select').value;
    if (!year) {
        resetHierarchyFromMonth();
        return;
    }
    
    selectedYear = year;
    
    // Reset các dropdown phía dưới
    document.getElementById('week-container').style.display = 'none';
    document.getElementById('reports-table-container').style.display = 'none';
    
    // Load months cho year và employee này
    fetch(`{{ route('work-reports.employee-reports') }}?user_id=${selectedEmployeeId}`)
        .then(response => {
            console.log('API response status for months:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('API response data for months:', data);
            
            const monthSelect = document.getElementById('month-select');
            monthSelect.innerHTML = '<option value="">Chọn tháng</option>';
            
            // Lấy các tháng có báo cáo trong năm được chọn
            const reports = data.reports || [];
            console.log('Reports for months:', reports);
            
            const months = [...new Set(reports.filter(report => report.year == year).map(report => report.month))].sort((a, b) => b - a);
            console.log('Months found for year', year, ':', months);
            
            months.forEach(month => {
                monthSelect.innerHTML += `<option value="${month}">Tháng ${month}</option>`;
            });
            
            document.getElementById('month-container').style.display = 'flex';
        })
        .catch(error => {
            console.error('Error loading months:', error);
        });
}

// Load weeks khi chọn month
function loadWeeks() {
    const month = document.getElementById('month-select').value;
    if (!month) {
        resetHierarchyFromMonth();
        return;
    }
    
    selectedMonth = month;
    
    // Reset bảng báo cáo
    document.getElementById('reports-table-container').style.display = 'none';
    
    // Load weeks cho year, month và employee này
    fetch(`{{ route('work-reports.employee-reports') }}?user_id=${selectedEmployeeId}`)
        .then(response => response.json())
        .then(data => {
            const weekSelect = document.getElementById('week-select');
            weekSelect.innerHTML = '<option value="">Chọn tuần</option>';
            
            // Lấy các tuần có báo cáo trong năm và tháng được chọn
            const reports = data.reports || [];
            const weeks = [...new Set(reports.filter(report => report.year == selectedYear && report.month == month).map(report => report.week))].sort((a, b) => b - a);
            
            weeks.forEach(week => {
                weekSelect.innerHTML += `<option value="${week}">Tuần ${week}</option>`;
            });
            
            document.getElementById('week-container').style.display = 'flex';
        })
        .catch(error => {
            console.error('Error loading weeks:', error);
        });
}

// Load reports khi chọn week
function loadReports() {
    const week = document.getElementById('week-select').value;
    if (!week) {
        document.getElementById('reports-table-container').style.display = 'none';
        return;
    }
    
    selectedWeek = week;
    
    // Hiển thị thông tin tuần được chọn
    document.getElementById('selected-week-info').textContent = 
        `Tuần ${week} - Tháng ${selectedMonth} - Năm ${selectedYear}`;
    
    // Load báo cáo cho tuần này
    fetch(`{{ route('work-reports.show-week') }}?year=${selectedYear}&month=${selectedMonth}&week=${week}&user_id=${selectedEmployeeId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            renderReportsTable(data.reports);
            document.getElementById('reports-table-container').style.display = 'block';
        })
        .catch(error => {
            console.error('Error loading reports:', error);
            document.getElementById('reports-table-body').innerHTML = 
                '<tr><td colspan="8" class="no-reports">Có lỗi xảy ra khi tải dữ liệu</td></tr>';
            document.getElementById('reports-table-container').style.display = 'block';
        });
}

// Render bảng báo cáo
function renderReportsTable(reports) {
    const tbody = document.getElementById('reports-table-body');
    
    if (reports && reports.length > 0) {
        let html = '';
        reports.forEach((report, index) => {
            html += `
                <tr data-report-id="${report.id}">
                    <td>${index + 1}</td>
                    <td>${formatDate(report.report_date)}</td>
                    <td>${report.user.name}</td>
                    <td>${report.department ? report.department.name : 'N/A'}</td>
                    <td>${report.user.position || 'Nhân viên'}</td>
                    <td>${report.daily_work}</td>
                    <td>
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <div class="form-check">
                                <input class="form-check-input report-checkbox" type="checkbox" 
                                       id="report_${report.id}" 
                                       value="${report.id}"
                                       ${report.is_read ? 'checked' : ''}
                                       ${report.rejected_at ? 'disabled' : ''}
                                       ${report.rejected_at ? 'disabled' : ''}
                                       data-checked-by-admin="${report.read_by_admin ? 'true' : 'false'}"
                                       onchange="markReportAsRead(${report.id}, this.checked)">
                                <label class="form-check-label small" for="report_${report.id}">
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                </label>
                            </div>
                            <button type="button" class="btn ${report.rejected_at ? 'btn-danger' : 'btn-outline-danger'} btn-sm" 
                                    onclick="rejectReport(${report.id})" 
                                    title="${report.rejected_at ? 'Click để hoàn tác từ chối' : 'Từ chối báo cáo'}">
                                <i class="bi bi-x"></i>
                            </button>
                            ${report.rejected_at ? '<span class="badge bg-danger small ms-2">Đã từ chối</span>' : ''}
                            ${report.is_read && report.read_by_user ? '<small class="text-muted ms-2">(' + report.read_by_user.name + ')</small>' : ''}
                            ${report.rejected_at && report.rejected_by_user ? '<small class="text-muted ms-2">(' + report.rejected_by_user.name + ')</small>' : ''}
                        </div>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    } else {
        tbody.innerHTML = '<tr><td colspan="7" class="no-reports">Chưa có báo cáo nào cho tuần này</td></tr>';
    }
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN');
}

// Reset hierarchy từ employee trở xuống
function resetHierarchyFromEmployee() {
    document.getElementById('employee-container').style.display = 'none';
    document.getElementById('year-container').style.display = 'none';
    document.getElementById('month-container').style.display = 'none';
    document.getElementById('week-container').style.display = 'none';
    document.getElementById('reports-table-container').style.display = 'none';
    selectedEmployeeId = null;
    selectedYear = null;
    selectedMonth = null;
    selectedWeek = null;
}

// Reset hierarchy từ month trở xuống
function resetHierarchyFromMonth() {
    document.getElementById('month-container').style.display = 'none';
    document.getElementById('week-container').style.display = 'none';
    document.getElementById('reports-table-container').style.display = 'none';
    selectedMonth = null;
    selectedWeek = null;
}

// Reset toàn bộ hierarchy
function resetHierarchy() {
    document.getElementById('employee-container').style.display = 'none';
    document.getElementById('year-container').style.display = 'none';
    document.getElementById('month-container').style.display = 'none';
    document.getElementById('week-container').style.display = 'none';
    document.getElementById('reports-table-container').style.display = 'none';
    selectedDepartmentId = null;
    selectedEmployeeId = null;
    selectedYear = null;
    selectedMonth = null;
    selectedWeek = null;
}

// Hàm tick hàng loạt tất cả báo cáo
function toggleAllReports(checkbox) {
    const reportCheckboxes = document.querySelectorAll('.report-checkbox');
    let checkedCount = 0;
    let totalCount = 0;
    
    reportCheckboxes.forEach(reportCheckbox => {
        // Chỉ tick những báo cáo chưa bị từ chối
        const row = reportCheckbox.closest('tr');
        const rejectButton = row.querySelector('button[onclick*="rejectReport"]');
        const isRejected = rejectButton && rejectButton.disabled;
        
        if (!isRejected) {
            totalCount++;
            reportCheckbox.checked = checkbox.checked;
            // Gọi hàm markReportAsRead cho từng checkbox
            markReportAsRead(reportCheckbox.value, checkbox.checked);
            if (checkbox.checked) checkedCount++;
        }
    });
    
    // Cập nhật trạng thái checkbox "Tất cả"
    if (checkedCount === 0) {
        document.getElementById('selectAllReports').checked = false;
        document.getElementById('selectAllReports').indeterminate = false;
    } else if (checkedCount === totalCount) {
        document.getElementById('selectAllReports').checked = true;
        document.getElementById('selectAllReports').indeterminate = false;
    } else {
        document.getElementById('selectAllReports').indeterminate = true;
    }
}

// Hàm từ chối báo cáo
function rejectReport(reportId) {
    if (confirm('Bạn có chắc chắn muốn từ chối báo cáo này?')) {
        // Gửi request đến server để từ chối báo cáo
        fetch(`{{ route('work-reports.reject') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                report_id: reportId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Cập nhật giao diện ngay lập tức
                const row = document.querySelector(`tr[data-report-id="${reportId}"]`);
                if (row) {
                    const checkbox = row.querySelector('.report-checkbox');
                    const rejectButton = row.querySelector('button[onclick*="rejectReport"]');
                    const statusCell = row.querySelector('td:last-child');
                    
                    if (data.action === 'reject') {
                        // Khi từ chối: disable checkbox, thêm badge
                        if (checkbox) {
                            checkbox.checked = false;
                            checkbox.disabled = true;
                        }
                        
                        if (rejectButton) {
                            rejectButton.classList.remove('btn-outline-danger');
                            rejectButton.classList.add('btn-danger');
                            rejectButton.title = 'Click để hoàn tác từ chối';
                        }
                        
                        // Thêm badge "Đã từ chối"
                        if (statusCell) {
                            const existingBadge = statusCell.querySelector('.badge');
                            if (!existingBadge) {
                                const badge = document.createElement('span');
                                badge.className = 'badge bg-danger small ms-2';
                                badge.textContent = 'Đã từ chối';
                                statusCell.querySelector('.d-flex').appendChild(badge);
                            }
                        }
                    } else if (data.action === 'undo') {
                        // Khi hoàn tác: enable checkbox, bỏ badge
                        if (checkbox) {
                            checkbox.disabled = false;
                        }
                        
                        if (rejectButton) {
                            rejectButton.classList.remove('btn-danger');
                            rejectButton.classList.add('btn-outline-danger');
                            rejectButton.title = 'Từ chối báo cáo';
                        }
                        
                        // Bỏ badge "Đã từ chối"
                        if (statusCell) {
                            const existingBadge = statusCell.querySelector('.badge');
                            if (existingBadge) {
                                existingBadge.remove();
                            }
                        }
                    }
                }
                
                // Hiển thị thông báo
                showNotification(data.message, 'success');
            } else {
                showNotification(data.message || 'Có lỗi xảy ra khi từ chối báo cáo', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Có lỗi xảy ra khi từ chối báo cáo', 'error');
        });
    }
}

// Hàm đánh dấu báo cáo đã đọc
function markReportAsRead(reportId, isRead) {
    // Kiểm tra xem báo cáo có bị từ chối không
    const checkbox = document.getElementById(`report_${reportId}`);
    if (!checkbox) return;
    
    const row = checkbox.closest('tr');
    const rejectButton = row.querySelector('button[onclick*="rejectReport"]');
    const isRejected = rejectButton && rejectButton.disabled;
    
    if (isRejected) {
        showNotification('Không thể thay đổi trạng thái báo cáo đã bị từ chối', 'warning');
        checkbox.checked = false;
        return;
    }
    
    // Admin có thể thay đổi tất cả trạng thái (không cần kiểm tra)
    
    // Gửi request đến server để cập nhật trạng thái
    fetch(`{{ route('work-reports.mark-as-read') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            report_id: reportId,
            is_read: isRead
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cập nhật trạng thái checkbox
            const checkbox = document.getElementById(`report_${reportId}`);
            if (checkbox) {
                checkbox.checked = isRead;
            }
            
            // Hiển thị thông báo
            showNotification(isRead ? 'Đã đánh dấu báo cáo đã đọc' : 'Đã bỏ đánh dấu báo cáo đã đọc', 'success');
        } else {
            showNotification('Có lỗi xảy ra khi cập nhật trạng thái', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Có lỗi xảy ra khi cập nhật trạng thái', 'error');
    });
}

// Hàm hiển thị thông báo
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'danger'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Tự động ẩn sau 3 giây
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}
</script>
@endpush
