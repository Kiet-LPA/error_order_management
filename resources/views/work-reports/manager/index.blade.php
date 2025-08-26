@extends('layouts.master')
@section('title', 'Quản lý báo cáo công việc')

@section('content')
<style>
.work-report-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.tab-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 24px;
}

.tab-header {
    display: flex;
    border-bottom: 2px solid #e2e8f0;
}

.tab-button {
    flex: 1;
    padding: 16px 24px;
    background: none;
    border: none;
    font-size: 16px;
    font-weight: 600;
    color: #718096;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.tab-button.active {
    color: #558EC1;
}

.tab-button.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 2px;
    background: #558EC1;
}

.tab-content {
    padding: 24px;
    display: none;
}

.tab-content.active {
    display: block;
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

.employee-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.employee-card {
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.employee-card:hover {
    border-color: #558EC1;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.employee-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #558EC1 0%, #4a90e2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 12px;
}

.employee-name {
    font-size: 18px;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 4px;
}

.employee-position {
    color: #718096;
    font-size: 14px;
    margin-bottom: 12px;
}

.employee-stats {
    display: flex;
    gap: 16px;
    font-size: 12px;
    color: #718096;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 4px;
}

.report-tree {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 24px;
}

.tree-item {
    margin-bottom: 8px;
}

.tree-toggle {
    background: none;
    border: none;
    color: #2d3748;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.tree-toggle:hover {
    background: #f7fafc;
}

.tree-toggle i {
    transition: transform 0.3s ease;
}

.tree-toggle.expanded i {
    transform: rotate(90deg);
}

.tree-content {
    display: none;
    margin-left: 24px;
    margin-top: 8px;
}

.tree-content.active {
    display: block;
}

.year-item {
    margin-bottom: 12px;
}

.month-item {
    margin-bottom: 8px;
    margin-left: 16px;
}

.week-item {
    margin-bottom: 4px;
    margin-left: 16px;
    padding: 6px 12px;
    background: #f7fafc;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.week-item:hover {
    background: #e2e8f0;
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

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #718096;
}

.empty-state-icon {
    font-size: 64px;
    color: #cbd5e0;
    margin-bottom: 16px;
}

.empty-state-title {
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 8px;
    color: #4a5568;
}

.empty-state-desc {
    font-size: 16px;
    line-height: 1.6;
}

.create-new-btn {
    background: linear-gradient(135deg, #558EC1 0%, #4a90e2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 12px 24px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 16px;
}

.create-new-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(85, 142, 193, 0.4);
}
</style>
@endpush

@section('content')
<div class="work-report-container">
    <div class="tab-container">
        <div class="tab-header">
            <button class="tab-button active" onclick="switchTab('create')">
                <i class="fas fa-plus"></i>
                Tạo báo cáo
            </button>
            <button class="tab-button" onclick="switchTab('manage')">
                <i class="fas fa-users"></i>
                Quản lý báo cáo
            </button>
        </div>
        
        <!-- Tab Tạo báo cáo -->
        <div id="create-tab" class="tab-content active">
            <div class="management-section">
                <div class="section-title">
                    <i class="fas fa-chart-line"></i>
                    Tạo báo cáo mới
                </div>
                
                <div class="year-grid">
                    @foreach($years as $year)
                        <div class="year-card" data-year="{{ $year }}">
                            <div class="year-number">{{ $year }}</div>
                            <div class="year-label">Năm</div>
                            
                            <div class="month-list" id="months-{{ $year }}">
                                <div class="month-grid">
                                    @for($month = 1; $month <= 12; $month++)
                                        <div class="month-card" 
                                             data-month="{{ $month }}" 
                                             data-year="{{ $year }}"
                                             onclick="selectMonth({{ $year }}, {{ $month }})">
                                            <div class="month-card-number">{{ $month }}</div>
                                            <div class="month-card-label">Tháng</div>
                                            <div class="month-card-actions">
                                                <button class="month-action-btn" 
                                                        onclick="event.stopPropagation(); showWeekSelection({{ $year }}, {{ $month }})"
                                                        title="Chọn tuần">
                                                    <i class="fas fa-calendar-week"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <button class="create-new-btn" onclick="createNewReport()">
                    <i class="fas fa-plus"></i>
                    Tạo báo cáo mới
                </button>
            </div>
        </div>
        
        <!-- Tab Quản lý báo cáo -->
        <div id="manage-tab" class="tab-content">
            <div class="management-section">
                <div class="section-title">
                    <i class="fas fa-users"></i>
                    Nhân viên phòng ban
                </div>
                
                @if($employees->count() > 0)
                    <div class="employee-grid">
                        @foreach($employees as $employee)
                            <div class="employee-card" onclick="selectEmployee({{ $employee->id }}, '{{ $employee->name }}')">
                                <div class="employee-avatar">
                                    {{ strtoupper(substr($employee->name, 0, 1)) }}
                                </div>
                                <div class="employee-name">{{ $employee->name }}</div>
                                <div class="employee-position">{{ $employee->position ?? 'Nhân viên' }}</div>
                                <div class="employee-stats">
                                    <div class="stat-item">
                                        <i class="fas fa-file-alt"></i>
                                        <span>0 báo cáo</span>
                                    </div>
                                    <div class="stat-item">
                                        <i class="fas fa-calendar"></i>
                                        <span>Tuần này</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="empty-state-title">Chưa có nhân viên</div>
                        <div class="empty-state-desc">
                            Hiện tại chưa có nhân viên nào trong phòng ban của bạn
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- Cây báo cáo -->
            <div class="report-tree" id="report-tree" style="display: none;">
                <div class="section-title">
                    <i class="fas fa-sitemap"></i>
                    Báo cáo của <span id="selected-employee-name"></span>
                </div>
                
                <div id="report-tree-content">
                    <!-- Nội dung cây báo cáo sẽ được load bằng JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Week Selection Modal -->
<div class="week-selection-modal" id="weekSelectionModal">
    <div class="week-selection-content">
        <div class="week-selection-header">
            <div class="week-selection-title">
                Chọn tuần cho <span id="selectedMonthText"></span>
            </div>
            <button class="week-selection-close" onclick="closeWeekSelection()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="week-grid" id="weekGrid">
            @for($week = 1; $week <= 5; $week++)
                <div class="week-option" data-week="{{ $week }}" onclick="selectWeek({{ $week }})">
                    <div class="week-option-number">{{ $week }}</div>
                    <div class="week-option-label">Tuần</div>
                </div>
            @endfor
        </div>
        
        <div class="week-selection-actions">
            <button class="week-selection-btn cancel" onclick="closeWeekSelection()">
                Hủy
            </button>
            <button class="week-selection-btn confirm" onclick="confirmWeekSelection()" disabled>
                Tạo báo cáo
            </button>
        </div>
    </div>
</div>

<!-- Modal tạo báo cáo mới -->
<div class="modal fade" id="createReportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tạo báo cáo mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createReportForm" method="GET" action="{{ route('work-reports.create') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="year" class="form-label">Năm</label>
                            <select class="form-select" id="year" name="year" required>
                                <option value="">Chọn năm</option>
                                @for($y = $currentYear - 2; $y <= $currentYear + 1; $y++)
                                    <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="month" class="form-label">Tháng</label>
                            <select class="form-select" id="month" name="month" required>
                                <option value="">Chọn tháng</option>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>
                                        Tháng {{ $m }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="week" class="form-label">Tuần</label>
                            <select class="form-select" id="week" name="week" required>
                                <option value="">Chọn tuần</option>
                                @for($w = 1; $w <= 5; $w++)
                                    <option value="{{ $w }}" {{ $w == now()->weekOfYear ? 'selected' : '' }}>
                                        Tuần {{ $w }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="submitCreateForm()">Tạo báo cáo</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedEmployeeId = null;
let selectedYear = null;
let selectedMonth = null;
let selectedWeek = null;

document.addEventListener('DOMContentLoaded', function() {
    // Xử lý click vào năm (tab tạo báo cáo)
    document.querySelectorAll('.year-card').forEach(card => {
        card.addEventListener('click', function() {
            const year = this.dataset.year;
            const monthList = document.getElementById(`months-${year}`);
            
            // Ẩn tất cả month-list
            document.querySelectorAll('.month-list').forEach(list => {
                list.classList.remove('active');
            });
            
            // Hiển thị month-list của năm được chọn
            monthList.classList.add('active');
        });
    });
});

function switchTab(tabName) {
    // Ẩn tất cả tab content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Bỏ active tất cả tab button
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
    });
    
    // Hiển thị tab được chọn
    document.getElementById(`${tabName}-tab`).classList.add('active');
    
    // Active tab button được chọn
    event.target.classList.add('active');
}

function selectMonth(year, month) {
    // Hiển thị modal chọn tuần
    showWeekSelection(year, month);
}

function showWeekSelection(year, month) {
    selectedYear = year;
    selectedMonth = month;
    
    // Cập nhật text trong modal
    document.getElementById('selectedMonthText').textContent = `Tháng ${month} - ${year}`;
    
    // Reset selection
    selectedWeek = null;
    document.querySelectorAll('.week-option').forEach(option => {
        option.classList.remove('selected');
    });
    document.querySelector('.week-selection-btn.confirm').disabled = true;
    
    // Hiển thị modal
    document.getElementById('weekSelectionModal').classList.add('show');
}

function closeWeekSelection() {
    document.getElementById('weekSelectionModal').classList.remove('show');
}

function selectWeek(week) {
    selectedWeek = week;
    
    // Update UI
    document.querySelectorAll('.week-option').forEach(option => {
        option.classList.remove('selected');
    });
    event.target.closest('.week-option').classList.add('selected');
    
    // Enable confirm button
    document.querySelector('.week-selection-btn.confirm').disabled = false;
}

function confirmWeekSelection() {
    if (selectedYear && selectedMonth && selectedWeek) {
        // Chuyển đến trang tạo báo cáo
        window.location.href = `{{ route('work-reports.create') }}?year=${selectedYear}&month=${selectedMonth}&week=${selectedWeek}`;
    }
}

function selectEmployee(employeeId, employeeName) {
    selectedEmployeeId = employeeId;
    document.getElementById('selected-employee-name').textContent = employeeName;
    document.getElementById('report-tree').style.display = 'block';
    
    // Load cây báo cáo cho employee
    loadEmployeeReportTree(employeeId);
}

function loadEmployeeReportTree(employeeId) {
    // Hiển thị loading
    document.getElementById('report-tree-content').innerHTML = '<p>Đang tải...</p>';
    
    // Gọi API để lấy dữ liệu báo cáo của employee
    fetch(`{{ route('work-reports.show-week') }}?user_id=${employeeId}`)
        .then(response => response.json())
        .then(data => {
            renderReportTree(data);
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('report-tree-content').innerHTML = '<p>Có lỗi xảy ra khi tải dữ liệu</p>';
        });
}

function renderReportTree(data) {
    // Render cây báo cáo dựa trên dữ liệu
    const treeContent = document.getElementById('report-tree-content');
    
    if (data.reports && data.reports.length > 0) {
        // Nhóm báo cáo theo năm, tháng, tuần
        const groupedReports = groupReportsByHierarchy(data.reports);
        
        let html = '';
        Object.keys(groupedReports).forEach(year => {
            html += `
                <div class="tree-item">
                    <button class="tree-toggle" onclick="toggleTreeItem(this)">
                        <i class="fas fa-chevron-right"></i>
                        ${year}
                    </button>
                    <div class="tree-content">
            `;
            
            Object.keys(groupedReports[year]).forEach(month => {
                html += `
                    <div class="month-item">
                        <button class="tree-toggle" onclick="toggleTreeItem(this)">
                            <i class="fas fa-chevron-right"></i>
                            Tháng ${month}
                        </button>
                        <div class="tree-content">
                `;
                
                Object.keys(groupedReports[year][month]).forEach(week => {
                    html += `
                        <div class="week-item" onclick="viewWeekReports(${year}, ${month}, ${week})">
                            Tuần ${week}
                        </div>
                    `;
                });
                
                html += `
                        </div>
                    </div>
                `;
            });
            
            html += `
                    </div>
                </div>
            `;
        });
        
        treeContent.innerHTML = html;
    } else {
        treeContent.innerHTML = '<p>Chưa có báo cáo nào</p>';
    }
}

function groupReportsByHierarchy(reports) {
    const grouped = {};
    
    reports.forEach(report => {
        if (!grouped[report.year]) {
            grouped[report.year] = {};
        }
        if (!grouped[report.year][report.month]) {
            grouped[report.year][report.month] = {};
        }
        if (!grouped[report.year][report.month][report.week]) {
            grouped[report.year][report.month][report.week] = [];
        }
        grouped[report.year][report.month][report.week].push(report);
    });
    
    return grouped;
}

function toggleTreeItem(button) {
    const content = button.nextElementSibling;
    button.classList.toggle('expanded');
    content.classList.toggle('active');
}

function viewWeekReports(year, month, week) {
    // Hiển thị báo cáo của tuần được chọn
    fetch(`{{ route('work-reports.show-week') }}?year=${year}&month=${month}&week=${week}&user_id=${selectedEmployeeId}`)
        .then(response => response.json())
        .then(data => {
            showWeekReportModal(data);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi tải báo cáo');
        });
}

function showWeekReportModal(data) {
    // Tạo modal hiển thị báo cáo tuần
    let modalHtml = `
        <div class="modal fade" id="weekReportModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Báo cáo tuần ${data.week_info.week} - Tháng ${data.week_info.month} - ${data.week_info.year}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
    `;
    
    if (data.reports && data.reports.length > 0) {
        modalHtml += `
            <table class="report-table">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Ngày/Tháng/Năm</th>
                        <th>Tên</th>
                        <th>Phòng ban</th>
                        <th>Vị trí</th>
                        <th>Công việc trong ngày</th>
                        <th>Khó khăn</th>
                        <th>Nhận xét</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        data.reports.forEach((report, index) => {
            modalHtml += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${report.report_date}</td>
                    <td>${report.user.name}</td>
                    <td>${report.department.name}</td>
                    <td>${report.user.position || 'Nhân viên'}</td>
                    <td>${report.daily_work}</td>
                    <td>${report.difficulties || '-'}</td>
                    <td>${report.comments || '-'}</td>
                </tr>
            `;
        });
        
        modalHtml += `
                </tbody>
            </table>
        `;
    } else {
        modalHtml += '<p>Chưa có báo cáo nào cho tuần này</p>';
    }
    
    modalHtml += `
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Xóa modal cũ nếu có
    const oldModal = document.getElementById('weekReportModal');
    if (oldModal) {
        oldModal.remove();
    }
    
    // Thêm modal mới vào body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Hiển thị modal
    const modal = new bootstrap.Modal(document.getElementById('weekReportModal'));
    modal.show();
}

function createNewReport() {
    const modal = new bootstrap.Modal(document.getElementById('createReportModal'));
    modal.show();
}

function submitCreateForm() {
    const form = document.getElementById('createReportForm');
    const year = document.getElementById('year').value;
    const month = document.getElementById('month').value;
    const week = document.getElementById('week').value;
    
    if (!year || !month || !week) {
        alert('Vui lòng chọn đầy đủ năm, tháng và tuần');
        return;
    }
    
    form.submit();
}

// Close modal when clicking outside
document.getElementById('weekSelectionModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeWeekSelection();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeWeekSelection();
    }
});
</script>
@endpush
