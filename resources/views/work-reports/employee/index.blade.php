@extends('layouts.master')
@section('title', 'Báo cáo công việc')

@section('content')
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<style>
/* Inline CSS để đảm bảo styling hoạt động */
.work-report-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.navigation-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 24px;
    margin-bottom: 24px;
}

.navigation-title {
    font-size: 24px;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.navigation-title i {
    color: #558EC1;
}

.create-new-btn {
    background: linear-gradient(135deg, #558EC1 0%, #4A90E2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 12px 24px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: box-shadow 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.create-new-btn:hover {
    box-shadow: 0 4px 15px rgba(85, 142, 193, 0.4);
    color: white;
    text-decoration: none;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
    margin-top: 24px;
}

.quick-action-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    text-align: center;
    transition: box-shadow 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
}

.quick-action-card:hover {
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    text-decoration: none;
    color: inherit;
}

.quick-action-icon {
    font-size: 48px;
    color: #558EC1;
    margin-bottom: 16px;
}

.quick-action-title {
    font-size: 18px;
    font-weight: 600;
    color: #000000;
    margin-bottom: 8px;
}

.quick-action-desc {
    color: #000000;
    font-size: 14px;
    line-height: 1.5;
}

/* Modal styling */
.week-selection-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1050;
}

.week-selection-modal.show {
    display: flex;
}

.week-selection-content {
    background: white;
    border-radius: 12px;
    padding: 24px;
    max-width: 400px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

.week-selection-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 2px solid #e2e8f0;
}

.week-selection-title {
    font-size: 20px;
    font-weight: 600;
    color: #000000;
}

.week-selection-close {
    background: none;
    border: none;
    font-size: 24px;
    color: #000000;
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background-color 0.3s ease;
}

.week-selection-close:hover {
    background: #f7fafc;
    color: #000000;
}

.week-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
    gap: 12px;
    margin-bottom: 20px;
}

.week-option {
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 8px;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.3s ease, background 0.3s ease, box-shadow 0.3s ease;
    font-weight: 600;
    color: #000000;
}

.week-option:hover {
    border-color: #558EC1;
    background: linear-gradient(135deg, #e6f3ff 0%, #d1e7ff 100%);
    box-shadow: 0 4px 12px rgba(85, 142, 193, 0.2);
}

.week-option.selected {
    border-color: #558EC1;
    background: linear-gradient(135deg, #558EC1 0%, #4A90E2 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(85, 142, 193, 0.3);
}

.week-option-number {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 4px;
}

.week-option-label {
    font-size: 12px;
    opacity: 0.8;
}

.week-selection-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.week-selection-btn {
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
}

.week-selection-btn.cancel {
    background: #f7fafc;
    color: #4a5568;
    border: 2px solid #e2e8f0;
}

.week-selection-btn.cancel:hover {
    background: #edf2f7;
    border-color: #cbd5e0;
}

.week-selection-btn.confirm {
    background: linear-gradient(135deg, #558EC1 0%, #4A90E2 100%);
    color: white;
}

.week-selection-btn.confirm:hover {
    box-shadow: 0 4px 12px rgba(85, 142, 193, 0.3);
}

.week-selection-btn.confirm:disabled {
    background: #cbd5e0;
    cursor: not-allowed;
    box-shadow: none;
}

/* Bootstrap Icons fallback */
.bi {
    font-family: "bootstrap-icons" !important;
    font-style: normal;
    font-weight: normal !important;
    font-variant: normal;
    text-transform: none;
    line-height: 1;
    vertical-align: middle;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

.bi.bi-graph-up::before { content: "\F4CA"; }
.bi.bi-plus-circle::before { content: "\F4F8"; }
.bi.bi-calendar-week::before { content: "\F4E8"; }
.bi.bi-calendar-month::before { content: "\F4E7"; }
.bi.bi-list-ul::before { content: "\F4F9"; }
.bi.bi-x-lg::before { content: "\F659"; }
</style>
<div class="work-report-container">
    <div class="navigation-card">
        <div class="navigation-title">
            <i class="bi bi-graph-up"></i>
            Báo cáo công việc
        </div>
        
        <div class="create-new-btn" onclick="selectDateForReport()" onmouseover="this.style.cursor='pointer'" onmouseout="this.style.cursor='default'">
            <i class="bi bi-plus-circle"></i>
            Tạo báo cáo mới
        </div>
        <!-- Fallback link nếu JavaScript không hoạt động -->
        <noscript>
            <a href="{{ route('work-reports.select-date') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i>
                Tạo báo cáo mới (JavaScript disabled)
            </a>
        </noscript>
    </div>

    <div class="quick-actions">
        <div class="quick-action-card" onclick="window.location.href='{{ route('work-reports.current-week') }}'">
            <div class="quick-action-icon">
                <i class="bi bi-calendar-week"></i>
            </div>
            <div class="quick-action-title">Báo cáo tuần này</div>
            <div class="quick-action-desc">
                Xem và tạo báo cáo cho tuần hiện tại
            </div>
        </div>
        
        <div class="quick-action-card" onclick="window.location.href='{{ route('work-reports.current-month') }}'">
            <div class="quick-action-icon">
                <i class="bi bi-calendar-month"></i>
            </div>
            <div class="quick-action-title">Báo cáo tháng này</div>
            <div class="quick-action-desc">
                Xem tổng quan báo cáo tháng hiện tại
            </div>
        </div>
        
        <div class="quick-action-card" onclick="window.location.href='{{ route('work-reports.my-activity') }}'">
            <div class="quick-action-icon">
                <i class="bi bi-graph-up"></i>
            </div>
            <div class="quick-action-title">Hoạt động của tôi</div>
            <div class="quick-action-desc">
                Theo dõi hoạt động và thống kê cá nhân
            </div>
        </div>
        
        <div class="quick-action-card" onclick="window.location.href='{{ route('work-reports.index') }}'">
            <div class="quick-action-icon">
                <i class="bi bi-list-ul"></i>
            </div>
            <div class="quick-action-title">Quản lý báo cáo</div>
            <div class="quick-action-desc">
                Xem và quản lý tất cả báo cáo của bạn
            </div>
        </div>
    </div>
</div>

<!-- Week Selection Modal -->
<div class="week-selection-modal" id="weekSelectionModal" style="display: none;">
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
            @for($week = 5; $week >= 1; $week--)
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
                                @for($y = $currentYear + 1; $y >= $currentYear - 2; $y--)
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
                                @for($m = 12; $m >= 1; $m--)
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
                                @for($w = 5; $w >= 1; $w--)
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
// Kiểm tra JavaScript có hoạt động không
console.log('Work Reports JavaScript loaded successfully');

let selectedYear = null;
let selectedMonth = null;
let selectedWeek = null;

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Work Reports page');
    
    // Force reload CSS nếu cần
    const links = document.querySelectorAll('link[rel="stylesheet"]');
    links.forEach(link => {
        if (link.href.includes('work-reports.css')) {
            console.log('Reloading work-reports.css');
            link.href = link.href.split('?')[0] + '?v=' + Date.now();
        }
    });
    
    // Initialize modal
    const modal = document.getElementById('weekSelectionModal');
    if (modal) {
        modal.style.display = 'none';
        modal.style.pointerEvents = 'none';
        console.log('Modal initialized');
    }
    
    // Xử lý click vào năm
    document.querySelectorAll('.year-card').forEach(card => {
        card.addEventListener('click', function() {
            const year = this.dataset.year;
            const monthList = document.getElementById(`months-${year}`);
            
            // Kiểm tra xem month-list hiện tại có đang mở không
            const isCurrentlyOpen = monthList.classList.contains('active');
            
            // Ẩn tất cả month-list
            document.querySelectorAll('.month-list').forEach(list => {
                list.classList.remove('active');
            });
            
            // Nếu month-list hiện tại chưa mở thì mở nó, nếu đã mở thì đóng nó
            if (!isCurrentlyOpen) {
                monthList.classList.add('active');
            }
        });
    });
});

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
    const modal = document.getElementById('weekSelectionModal');
    modal.style.display = 'flex';
    modal.classList.add('show');
    modal.style.pointerEvents = 'auto';
}

function closeWeekSelection() {
    const modal = document.getElementById('weekSelectionModal');
    modal.classList.remove('show');
    modal.style.display = 'none';
    modal.style.pointerEvents = 'none';
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
        window.location.href = `{{ route('work-reports.create') }}?year=${selectedYear}&week=${selectedWeek}`;
    }
}

function selectDateForReport() {
    // Chuyển đến trang chọn ngày
    window.location.href = `{{ route('work-reports.select-date') }}`;
}

// Fallback function nếu JavaScript không hoạt động
function fallbackSelectDate() {
    console.log('Fallback: Redirecting to select date page');
    window.location.href = `{{ route('work-reports.select-date') }}`;
}

function createNewReport() {
    const modal = new bootstrap.Modal(document.getElementById('createReportModal'));
    modal.show();
}

function submitCreateForm() {
    const form = document.getElementById('createReportForm');
    const year = document.getElementById('year').value;
    const week = document.getElementById('week').value;
    
    if (!year || !week) {
        alert('Vui lòng chọn đầy đủ năm và tuần');
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

// Close month list when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.year-card') && !e.target.closest('.month-card')) {
        document.querySelectorAll('.month-list').forEach(list => {
            list.classList.remove('active');
        });
    }
});

// Kiểm tra xem JavaScript có hoạt động không
setTimeout(function() {
    console.log('JavaScript check: Modal functionality test');
    
    // Kiểm tra xem modal có tồn tại không
    const modal = document.getElementById('weekSelectionModal');
    if (modal) {
        console.log('Modal found:', modal);
        // Thêm class để cho phép pointer events
        modal.style.pointerEvents = 'auto';
    } else {
        console.error('Modal not found!');
    }
    
    // Kiểm tra xem Bootstrap có sẵn không
    if (typeof bootstrap !== 'undefined') {
        console.log('Bootstrap is available');
    } else {
        console.warn('Bootstrap is not available');
    }
    
    // Kiểm tra Bootstrap Icons
    const testIcon = document.createElement('i');
    testIcon.className = 'bi bi-x-lg';
    document.body.appendChild(testIcon);
    
    const computedStyle = window.getComputedStyle(testIcon, '::before');
    const content = computedStyle.getPropertyValue('content');
    
    console.log('Bootstrap Icons test - content:', content);
    
    if (content && content !== 'none' && content !== 'normal') {
        console.log('Bootstrap Icons are working:', content);
    } else {
        console.warn('Bootstrap Icons may not be loading properly - adding fallback');
        // Thêm fallback CSS với !important
        document.head.insertAdjacentHTML('beforeend', `
            <style>
                .bi.bi-graph-up::before { content: "📈" !important; }
                .bi.bi-plus-circle::before { content: "➕" !important; }
                .bi.bi-calendar-week::before { content: "📅" !important; }
                .bi.bi-calendar-month::before { content: "📆" !important; }
                .bi.bi-list-ul::before { content: "📋" !important; }
                .bi.bi-x-lg::before { content: "❌" !important; }
            </style>
        `);
        console.log('Fallback CSS added');
    }
    
    document.body.removeChild(testIcon);
    
    // Kiểm tra CSS styling
    const testCard = document.querySelector('.quick-action-card');
    if (testCard) {
        const cardStyle = window.getComputedStyle(testCard);
        console.log('Card background:', cardStyle.backgroundColor);
        console.log('Card border-radius:', cardStyle.borderRadius);
        console.log('Card box-shadow:', cardStyle.boxShadow);
        
        if (cardStyle.backgroundColor === 'rgba(0, 0, 0, 0)' || cardStyle.backgroundColor === 'transparent') {
            console.warn('CSS may not be loading properly - adding inline styles');
            // Thêm inline styles nếu CSS không load
            document.querySelectorAll('.quick-action-card').forEach(card => {
                card.style.background = 'white';
                card.style.borderRadius = '12px';
                card.style.padding = '20px';
                card.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
                card.style.textAlign = 'center';
                card.style.transition = 'box-shadow 0.3s ease';
                card.style.cursor = 'pointer';
            });
        }
    }
}, 1000);
</script>
@endpush
