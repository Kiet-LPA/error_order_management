@extends('layouts.master')
@section('title', 'Quản lý báo cáo công việc')

@section('content')
@push('styles')
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
    box-shadow: 0 4px 15px rgba(85, 142, 193, 0.2);
}

.employee-avatar {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #558EC1 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    font-weight: 600;
    margin: 0 auto 16px;
}

.employee-name {
    font-size: 18px;
    font-weight: 600;
    color: #2d3748;
    text-align: center;
    margin-bottom: 8px;
}

.employee-position {
    font-size: 14px;
    color: #718096;
    text-align: center;
    margin-bottom: 16px;
}

.employee-stats {
    display: flex;
    justify-content: space-around;
    gap: 8px;
}

.stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    color: #718096;
}

.stat-item i {
    color: #558EC1;
    font-size: 16px;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #718096;
}

.empty-state-icon {
    font-size: 48px;
    color: #cbd5e0;
    margin-bottom: 16px;
}

.empty-state-title {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 8px;
}

.empty-state-desc {
    font-size: 14px;
}

.report-tree {
    margin-top: 24px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 24px;
}

.tree-item {
    margin-bottom: 16px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}

.tree-toggle {
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    border: none;
    font-size: 16px;
    font-weight: 600;
    color: #2d3748;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    width: 100%;
    text-align: left;
    transition: all 0.3s ease;
}

.tree-toggle:hover {
    background: linear-gradient(135deg, #edf2f7 0%, #e2e8f0 100%);
}

.tree-toggle i {
    transition: transform 0.3s ease;
    color: #558EC1;
}

.tree-toggle.expanded i {
    transform: rotate(90deg);
}

.tree-content {
    display: none;
    padding: 16px;
    background: #fafbfc;
    border-top: 1px solid #e2e8f0;
}

.tree-content.active {
    display: block;
}

.month-item {
    margin-bottom: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    overflow: hidden;
}

.month-item .tree-toggle {
    background: linear-gradient(135deg, #f0f4f8 0%, #e6f3ff 100%);
    font-size: 14px;
    padding: 10px 12px;
}

.month-item .tree-toggle:hover {
    background: linear-gradient(135deg, #e6f3ff 0%, #d1ecf1 100%);
}

.month-item .tree-content {
    padding: 12px;
    background: white;
}

.week-item {
    padding: 10px 16px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
    border-radius: 6px;
    margin: 8px 0;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #495057;
}

.week-item:hover {
    background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
    transform: translateX(4px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.week-item i {
    color: #558EC1;
}

.year-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 24px;
    margin-bottom: 24px;
}

.year-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    padding: 24px;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.year-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.year-number {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 8px;
}

.year-label {
    font-size: 14px;
    opacity: 0.9;
}

.month-list {
    display: none;
    margin-top: 20px;
}

.month-list.active {
    display: block;
}

.month-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

.month-card {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 16px;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
}

.month-card:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: scale(1.05);
}

.month-card-number {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 4px;
}

.month-card-label {
    font-size: 12px;
    opacity: 0.8;
}

.month-card-actions {
    margin-top: 8px;
}

.month-action-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    border-radius: 4px;
    padding: 4px 8px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.month-action-btn:hover {
    background: rgba(255, 255, 255, 0.3);
}

.create-new-btn {
    background: linear-gradient(135deg, #558EC1 0%, #764ba2 100%);
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

.week-selection-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.week-selection-modal.show {
    display: flex;
}

.week-selection-content {
    background: white;
    border-radius: 12px;
    padding: 24px;
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

.week-selection-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.week-selection-title {
    font-size: 18px;
    font-weight: 600;
    color: #2d3748;
}

.week-selection-close {
    background: none;
    border: none;
    font-size: 20px;
    color: #718096;
    cursor: pointer;
}

.week-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}

.week-option {
    background: #f7fafc;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.week-option:hover {
    border-color: #558EC1;
    background: #edf2f7;
}

.week-option.selected {
    border-color: #558EC1;
    background: #558EC1;
    color: white;
}

.week-option-number {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 4px;
}

.week-option-label {
    font-size: 12px;
}

.week-selection-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.week-selection-btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.week-selection-btn.cancel {
    background: #e2e8f0;
    color: #4a5568;
}

.week-selection-btn.confirm {
    background: #558EC1;
    color: white;
}

.week-selection-btn.confirm:disabled {
    background: #cbd5e0;
    cursor: not-allowed;
}

.report-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 16px;
}

.report-table th,
.report-table td {
    padding: 12px;
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

/* New styles for dropdown hierarchy */
.hierarchy-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 20px;
}

.hierarchy-item {
    display: flex;
    align-items: center;
    gap: 10px;
}

.hierarchy-label {
    font-size: 16px;
    font-weight: 600;
    color: #4a5568;
}

.hierarchy-select {
    padding: 10px 15px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 16px;
    color: #2d3748;
    background-color: #f7fafc;
    cursor: pointer;
    transition: all 0.3s ease;
}

.hierarchy-select:hover {
    border-color: #cbd5e0;
    background-color: #edf2f7;
}

.hierarchy-select:focus {
    border-color: #558EC1;
    box-shadow: 0 0 0 0.25rem rgba(85, 142, 193, 0.25);
    outline: none;
}

.create-action-container {
    text-align: center;
    margin-top: 20px;
}

.create-new-section {
    margin-top: 20px;
    text-align: center;
}

.section-subtitle {
    font-size: 18px;
    font-weight: 600;
    color: #4a5568;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.section-subtitle i {
    color: #558EC1;
}

.create-new-btn.secondary {
    background: #e2e8f0;
    color: #4a5568;
    border: none;
    border-radius: 8px;
    padding: 12px 24px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.create-new-btn.secondary:hover {
    background: #cbd5e0;
}
</style>
@endpush

<div class="work-report-container">
    <div class="tab-container">
        <div class="tab-header">
            <button class="tab-button active" onclick="switchTab('create')">
                <i class="bi bi-plus-circle"></i>
                Tạo báo cáo
            </button>
            <button class="tab-button" onclick="switchTab('manage')">
                <i class="bi bi-people"></i>
                Quản lý báo cáo
            </button>
        </div>
        
        <!-- Tab Tạo báo cáo -->
        <div id="create-tab" class="tab-content active">
            <div class="management-section">
                <div class="section-title">
                    <i class="bi bi-graph-up"></i>
                    Tạo báo cáo mới
                </div>
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Bạn sẽ tạo báo cáo cho chính mình. Hệ thống sẽ tự động tính toán tuần dựa trên ngày bạn chọn.
                </div>
                
                <!-- Nút tạo báo cáo -->
                <div class="create-action-container">
                    <button class="create-new-btn" onclick="selectDateForReport()">
                        <i class="bi bi-calendar"></i>
                        Chọn ngày để tạo báo cáo
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Tab Quản lý báo cáo -->
        <div id="manage-tab" class="tab-content">
            <div class="management-section">
                <div class="section-title">
                    <i class="bi bi-people"></i>
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
                                        <i class="bi bi-file-text"></i>
                                        <span id="report-count-{{ $employee->id }}">Đang tải...</span>
                                    </div>
                                    <div class="stat-item">
                                        <i class="bi bi-calendar"></i>
                                        <span id="week-count-{{ $employee->id }}">Đang tải...</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="bi bi-people"></i>
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
                    <i class="bi bi-diagram-3"></i>
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
                <i class="bi bi-x-lg"></i>
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

@push('scripts')
<script>
let selectedEmployeeId = null;
let selectedYear = null;
let selectedMonth = null;
let selectedWeek = null;

// Variables for report management

document.addEventListener('DOMContentLoaded', function() {
    // Load số báo cáo cho tất cả nhân viên
    loadAllEmployeeReportCounts();
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

// Load số báo cáo cho tất cả nhân viên
function loadAllEmployeeReportCounts() {
    const employeeCards = document.querySelectorAll('.employee-card');
    
    employeeCards.forEach(card => {
        const onclickAttr = card.getAttribute('onclick');
        if (onclickAttr) {
            const match = onclickAttr.match(/\d+/);
            if (match) {
                const employeeId = match[0];
                loadEmployeeReportCount(employeeId);
            }
        }
    });
}

function loadEmployeeReportCount(employeeId) {
    console.log('Loading report count for employee:', employeeId);
    
    // Load tổng số báo cáo
    fetch('{{ route("work-reports.employee-reports") }}?user_id=' + employeeId)
        .then(response => {
            console.log('API response status:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('API response data for employee', employeeId, ':', data);
            
            const totalReports = data.reports ? data.reports.length : 0;
            console.log('Total reports for employee', employeeId, ':', totalReports);
            
            const reportCountElement = document.getElementById('report-count-' + employeeId);
            if (reportCountElement) {
                reportCountElement.textContent = totalReports + ' báo cáo';
            } else {
                console.error('Element not found: report-count-' + employeeId);
            }
            
            // Load số báo cáo tuần này
            loadCurrentWeekReportCount(employeeId, data.reports);
        })
        .catch(error => {
            console.error('Error loading report count for employee', employeeId, ':', error);
            const reportCountElement = document.getElementById('report-count-' + employeeId);
            if (reportCountElement) {
                reportCountElement.textContent = 'Lỗi tải dữ liệu';
            }
        });
}

function loadCurrentWeekReportCount(employeeId, reports) {
    if (!reports || reports.length === 0) {
        document.getElementById('week-count-' + employeeId).textContent = '0 báo cáo tuần này';
        return;
    }
    
    // Lấy tuần hiện tại
    const now = new Date();
    const currentYear = now.getFullYear();
    const currentWeek = Math.ceil((now.getDate() + new Date(now.getFullYear(), now.getMonth(), 1).getDay()) / 7);
    const currentMonth = now.getMonth() + 1;
    
    // Đếm báo cáo trong tuần hiện tại
    const currentWeekReports = reports.filter(function(report) {
        return report.year == currentYear && 
               report.month == currentMonth && 
               report.week == currentWeek;
    });
    
    document.getElementById('week-count-' + employeeId).textContent = currentWeekReports.length + ' báo cáo tuần này';
}

// Function to navigate to date selection page
function selectDateForReport() {
    window.location.href = '{{ route("work-reports.select-date") }}';
}

// Old functions (kept for compatibility)
function selectMonth(year, month) {
    // Chuyển đến trang chọn ngày
    selectDateForReport();
}

function showWeekSelection(year, month) {
    // Chuyển đến trang chọn ngày
    selectDateForReport();
}

function closeWeekSelection() {
    // Không cần thiết nữa
}

function selectWeek(week) {
    // Không cần thiết nữa
}

function confirmWeekSelection() {
    // Chuyển đến trang chọn ngày
    selectDateForReport();
}

function selectEmployee(employeeId, employeeName) {
    selectedEmployeeId = employeeId;
    document.getElementById('selected-employee-name').textContent = employeeName;
    document.getElementById('report-tree').style.display = 'block';
    
    // Load cây báo cáo cho employee
    loadEmployeeReportTree(employeeId);
}

function loadEmployeeReportTree(employeeId) {
    console.log('Loading report tree for employee:', employeeId);
    
    // Hiển thị loading
    document.getElementById('report-tree-content').innerHTML = '<p>Đang tải...</p>';
    
    // Gọi API để lấy dữ liệu báo cáo của employee
    fetch('{{ route("work-reports.employee-reports") }}?user_id=' + employeeId)
        .then(response => {
            console.log('Report tree API response status:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Report tree API response data:', data);
            renderReportTree(data);
        })
        .catch(error => {
            console.error('Error loading report tree:', error);
            document.getElementById('report-tree-content').innerHTML = '<p>Có lỗi xảy ra khi tải dữ liệu</p>';
        });
}

function renderReportTree(data) {
    console.log('Rendering report tree with data:', data);
    
    // Render cây báo cáo dựa trên dữ liệu
    const treeContent = document.getElementById('report-tree-content');
    
    if (data.reports && data.reports.length > 0) {
        console.log('Found', data.reports.length, 'reports');
        
        // Nhóm báo cáo theo năm, tháng, tuần
        const groupedReports = groupReportsByHierarchy(data.reports);
        console.log('Grouped reports:', groupedReports);
        
        let html = '';
        
        // Sắp xếp năm theo thứ tự giảm dần
        const years = Object.keys(groupedReports).sort((a, b) => b - a);
        
        years.forEach(function(year) {
            html += 
                '<div class="tree-item">' +
                    '<button class="tree-toggle" onclick="toggleTreeItem(this)">' +
                        '<i class="bi bi-chevron-right"></i>' +
                        'Năm ' + year +
                    '</button>' +
                    '<div class="tree-content">';
            
            // Sắp xếp tháng theo thứ tự giảm dần
            const months = Object.keys(groupedReports[year]).sort((a, b) => b - a);
            
            months.forEach(function(month) {
                html += 
                    '<div class="month-item">' +
                        '<button class="tree-toggle" onclick="toggleTreeItem(this)">' +
                            '<i class="bi bi-chevron-right"></i>' +
                            'Tháng ' + month +
                        '</button>' +
                        '<div class="tree-content">';
                
                // Sắp xếp tuần theo thứ tự giảm dần
                const weeks = Object.keys(groupedReports[year][month]).sort((a, b) => b - a);
                
                weeks.forEach(function(week) {
                    const reportCount = groupedReports[year][month][week].length;
                    html += 
                        '<div class="week-item" onclick="viewWeekReports(' + year + ', ' + month + ', ' + week + ')">' +
                            '<i class="bi bi-calendar-week"></i>' +
                            'Tuần ' + week + ' (' + reportCount + ' báo cáo)' +
                        '</div>';
                });
                
                html += 
                    '</div>' +
                    '</div>';
            });
            
            html += 
                '</div>' +
                '</div>';
        });
        
        console.log('Generated HTML:', html);
        treeContent.innerHTML = html;
    } else {
        console.log('No reports found');
        treeContent.innerHTML = '<p>Chưa có báo cáo nào</p>';
    }
}

function groupReportsByHierarchy(reports) {
    console.log('Grouping reports:', reports);
    const grouped = {};
    
    reports.forEach(report => {
        console.log('Processing report:', report);
        
        // Đảm bảo year, month, week là số
        const year = parseInt(report.year);
        const month = parseInt(report.month);
        const week = parseInt(report.week);
        
        console.log('Parsed values - year:', year, 'month:', month, 'week:', week);
        
        if (!grouped[year]) {
            grouped[year] = {};
        }
        if (!grouped[year][month]) {
            grouped[year][month] = {};
        }
        if (!grouped[year][month][week]) {
            grouped[year][month][week] = [];
        }
        grouped[year][month][week].push(report);
    });
    
    console.log('Final grouped result:', grouped);
    return grouped;
}

function toggleTreeItem(button) {
    const content = button.nextElementSibling;
    button.classList.toggle('expanded');
    content.classList.toggle('active');
}

function viewWeekReports(year, month, week) {
    // Hiển thị báo cáo của tuần được chọn
    fetch('{{ route("work-reports.show-week") }}?year=' + year + '&month=' + month + '&week=' + week + '&user_id=' + selectedEmployeeId)
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
    let modalHtml = 
        '<div class="modal fade" id="weekReportModal" tabindex="-1" role="dialog" aria-labelledby="weekReportModalLabel" aria-hidden="false">' +
            '<div class="modal-dialog modal-xl" role="document">' +
                '<div class="modal-content">' +
                    '<div class="modal-header">' +
                        '<h5 class="modal-title" id="weekReportModalLabel">Báo cáo tuần ' + data.week_info.week + ' - Tháng ' + (data.week_info.month || 'N/A') + ' - ' + data.week_info.year + '</h5>' +
                        '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' +
                    '</div>' +
                    '<div class="modal-body">';
    
    if (data.reports && data.reports.length > 0) {
        modalHtml += 
            '<div class="table-responsive">' +
                '<table class="table table-striped table-hover">' +
                    '<thead>' +
                        '<tr>' +
                            '<th style="color: #000; font-weight: bold;">STT</th>' +
                            '<th style="color: #000; font-weight: bold;">Ngày/Tháng/Năm</th>' +
                            '<th style="color: #000; font-weight: bold;">Tên</th>' +
                            '<th style="color: #000; font-weight: bold;">Phòng ban</th>' +
                            '<th style="color: #000; font-weight: bold;">Vị trí</th>' +
                            '<th style="color: #000; font-weight: bold;">Công việc trong ngày</th>' +
                            '<th style="color: #000; font-weight: bold;">Khó khăn</th>' +
                            '<th style="color: #000; font-weight: bold;">Nhận xét</th>' +
                        '</tr>' +
                    '</thead>' +
                    '<tbody>';
        
        data.reports.forEach(function(report, index) {
            modalHtml += 
                '<tr>' +
                    '<td>' + (index + 1) + '</td>' +
                    '<td>' + report.report_date + '</td>' +
                    '<td>' + report.user.name + '</td>' +
                    '<td>' + report.department.name + '</td>' +
                    '<td>' + (report.user.position || 'Nhân viên') + '</td>' +
                    '<td>' + report.daily_work + '</td>' +
                    '<td>' + (report.difficulties || '-') + '</td>' +
                    '<td>' + (report.comments || '-') + '</td>' +
                '</tr>';
        });
        
        modalHtml += 
                    '</tbody>' +
                '</table>' +
            '</div>';
    } else {
        modalHtml += '<div class="alert alert-info"><i class="bi bi-info-circle"></i> Chưa có báo cáo nào cho tuần này</div>';
    }
    
    modalHtml += 
                    '</div>' +
                    '<div class="modal-footer">' +
                        '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';
    
    // Xóa modal cũ nếu có
    const oldModal = document.getElementById('weekReportModal');
    if (oldModal) {
        oldModal.remove();
    }
    
    // Thêm modal mới vào body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Hiển thị modal
    const modalElement = document.getElementById('weekReportModal');
    const modal = new bootstrap.Modal(modalElement);
    
    // Đảm bảo modal được hiển thị đúng cách
    modalElement.addEventListener('shown.bs.modal', function() {
        // Focus vào modal để tránh warning
        modalElement.focus();
    });
    
    modal.show();
}

function selectDateForReport() {
    // Chuyển đến trang chọn ngày
    window.location.href = '{{ route("work-reports.select-date") }}';
}

function createNewReport() {
    const modal = new bootstrap.Modal(document.getElementById('createReportModal'));
    modal.show();
}

function submitCreateForm() {
    // Chuyển đến trang chọn ngày
    selectDateForReport();
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
@endsection
