@extends('layouts.master')
@section('title', 'Báo cáo công việc')

@section('content')
<div class="work-report-container">
    <div class="navigation-card">
        <div class="navigation-title">
            <i class="fas fa-chart-line"></i>
            Báo cáo công việc
        </div>
        
        @if($years->count() > 0)
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
        @else
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="empty-state-title">Chưa có báo cáo nào</div>
                <div class="empty-state-desc">
                    Bắt đầu tạo báo cáo công việc đầu tiên của bạn
                </div>
                <button class="create-new-btn" onclick="createNewReport()">
                    <i class="fas fa-plus"></i>
                    Tạo báo cáo đầu tiên
                </button>
            </div>
        @endif
    </div>

    <div class="quick-actions">
        <div class="quick-action-card">
            <div class="quick-action-icon">
                <i class="fas fa-calendar-week"></i>
            </div>
            <div class="quick-action-title">Báo cáo tuần này</div>
            <div class="quick-action-desc">
                Tạo báo cáo cho tuần hiện tại
            </div>
        </div>
        
        <div class="quick-action-card">
            <div class="quick-action-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="quick-action-title">Báo cáo tháng này</div>
            <div class="quick-action-desc">
                Xem tổng quan báo cáo tháng hiện tại
            </div>
        </div>
        
        <div class="quick-action-card">
            <div class="quick-action-icon">
                <i class="fas fa-history"></i>
            </div>
            <div class="quick-action-title">Lịch sử báo cáo</div>
            <div class="quick-action-desc">
                Xem lại các báo cáo đã tạo trước đó
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
let selectedYear = null;
let selectedMonth = null;
let selectedWeek = null;

document.addEventListener('DOMContentLoaded', function() {
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

// Close month list when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.year-card') && !e.target.closest('.month-card')) {
        document.querySelectorAll('.month-list').forEach(list => {
            list.classList.remove('active');
        });
    }
});
</script>
@endpush
