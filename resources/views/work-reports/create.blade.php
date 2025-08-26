@extends('layouts.master')
@section('title', 'Tạo báo cáo công việc')

@section('content')
@push('styles')
<style>
.report-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.report-header {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 24px;
    margin-bottom: 24px;
}

.report-title {
    font-size: 24px;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
}

.report-subtitle {
    color: #718096;
    font-size: 16px;
}

.report-form {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 24px;
}

.form-section {
    margin-bottom: 32px;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.section-title i {
    color: #558EC1;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
    margin-bottom: 16px;
}

.form-group {
    margin-bottom: 16px;
}

.form-label {
    display: block;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
}

.form-control, .form-select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 16px;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    outline: none;
    border-color: #558EC1;
    box-shadow: 0 0 0 3px rgba(85, 142, 193, 0.1);
}

.form-control.error {
    border-color: #e53e3e;
}

.error-message {
    color: #e53e3e;
    font-size: 14px;
    margin-top: 4px;
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

.report-table input,
.report-table textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    font-size: 14px;
}

.report-table textarea {
    resize: vertical;
    min-height: 60px;
}

.add-row-btn {
    background: #48bb78;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 8px 16px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 12px;
}

.add-row-btn:hover {
    background: #38a169;
    transform: translateY(-1px);
}

.remove-row-btn {
    background: #e53e3e;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 4px 8px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.remove-row-btn:hover {
    background: #c53030;
}

.form-actions {
    display: flex;
    gap: 16px;
    justify-content: flex-end;
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid #e2e8f0;
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    font-size: 16px;
}

.btn-primary {
    background: linear-gradient(135deg, #558EC1 0%, #4a90e2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(85, 142, 193, 0.4);
}

.btn-secondary {
    background: #718096;
    color: white;
}

.btn-secondary:hover {
    background: #4a5568;
    transform: translateY(-2px);
}

.custom-fields-section {
    background: #f7fafc;
    border-radius: 8px;
    padding: 16px;
    margin-top: 16px;
}

.custom-field {
    margin-bottom: 16px;
}

.custom-field-label {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
}

.custom-field-input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    font-size: 14px;
}

.custom-field-textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    font-size: 14px;
    resize: vertical;
    min-height: 80px;
}

.existing-reports {
    background: #f7fafc;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 24px;
}

.existing-reports-title {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 12px;
}

.existing-report-item {
    background: white;
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 8px;
    border-left: 4px solid #558EC1;
}

.existing-report-date {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 4px;
}

.existing-report-summary {
    color: #718096;
    font-size: 14px;
}
</style>
@endpush

@section('content')
<div class="report-container">
    <div class="report-header">
        <div class="report-title">Tạo báo cáo công việc</div>
        <div class="report-subtitle">
            Tuần {{ $week }} - Tháng {{ $month }} - Năm {{ $year }}
        </div>
    </div>

    @if($existingReports->count() > 0)
        <div class="existing-reports">
            <div class="existing-reports-title">
                <i class="fas fa-info-circle"></i>
                Báo cáo đã có trong tuần này
            </div>
            @foreach($existingReports as $report)
                <div class="existing-report-item">
                    <div class="existing-report-date">
                        {{ \Carbon\Carbon::parse($report->report_date)->format('d/m/Y') }}
                    </div>
                    <div class="existing-report-summary">
                        {{ Str::limit($report->daily_work, 100) }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <form class="report-form" method="POST" action="{{ route('work-reports.store') }}" id="reportForm">
        @csrf
        <input type="hidden" name="year" value="{{ $year }}">
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="week" value="{{ $week }}">

        <div class="form-section">
            <div class="section-title">
                <i class="fas fa-calendar"></i>
                Thông tin báo cáo
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Ngày báo cáo</label>
                    <input type="date" name="report_date" class="form-control @error('report_date') error @enderror" 
                           value="{{ old('report_date', now()->format('Y-m-d')) }}" required>
                    @error('report_date')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="section-title">
                <i class="fas fa-tasks"></i>
                Báo cáo công việc
            </div>
            
            <table class="report-table" id="reportTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">STT</th>
                        <th style="width: 120px;">Ngày/Tháng/Năm</th>
                        <th style="width: 150px;">Tên</th>
                        <th style="width: 120px;">Phòng ban</th>
                        <th style="width: 100px;">Vị trí</th>
                        <th>Công việc trong ngày</th>
                        <th style="width: 120px;">Khó khăn</th>
                        <th style="width: 120px;">Nhận xét</th>
                        <th style="width: 80px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="reportTableBody">
                    <tr class="report-row">
                        <td>1</td>
                        <td>
                            <input type="date" name="report_date" class="form-control" 
                                   value="{{ old('report_date', now()->format('Y-m-d')) }}" required>
                        </td>
                        <td>
                            <input type="text" value="{{ auth()->user()->name }}" readonly class="form-control" style="background: #f7fafc;">
                        </td>
                        <td>
                            <input type="text" value="{{ auth()->user()->department->name ?? 'N/A' }}" readonly class="form-control" style="background: #f7fafc;">
                        </td>
                        <td>
                            <input type="text" value="{{ auth()->user()->position ?? 'Nhân viên' }}" readonly class="form-control" style="background: #f7fafc;">
                        </td>
                        <td>
                            <textarea name="daily_work" class="form-control @error('daily_work') error @enderror" 
                                      placeholder="Mô tả công việc đã làm trong ngày..." required>{{ old('daily_work') }}</textarea>
                            @error('daily_work')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </td>
                        <td>
                            <textarea name="difficulties" class="form-control" 
                                      placeholder="Những khó khăn gặp phải...">{{ old('difficulties') }}</textarea>
                        </td>
                        <td>
                            <textarea name="comments" class="form-control" 
                                      placeholder="Nhận xét, đề xuất...">{{ old('comments') }}</textarea>
                        </td>
                        <td>
                            <button type="button" class="remove-row-btn" onclick="removeRow(this)" style="display: none;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <button type="button" class="add-row-btn" onclick="addRow()">
                <i class="fas fa-plus"></i>
                Thêm hàng báo cáo
            </button>
        </div>

        <!-- Custom fields theo phòng ban -->
        <div class="form-section" id="customFieldsSection" style="display: none;">
            <div class="section-title">
                <i class="fas fa-cogs"></i>
                Thông tin bổ sung ({{ auth()->user()->department->name ?? 'Phòng ban' }})
            </div>
            
            <div class="custom-fields-section" id="customFieldsContent">
                <!-- Custom fields sẽ được load bằng JavaScript -->
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('work-reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Lưu báo cáo
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
let rowCounter = 1;

document.addEventListener('DOMContentLoaded', function() {
    // Load custom fields theo phòng ban
    loadCustomFields();
    
    // Cập nhật STT khi thêm/xóa hàng
    updateRowNumbers();
});

function addRow() {
    const tbody = document.getElementById('reportTableBody');
    const newRow = document.createElement('tr');
    newRow.className = 'report-row';
    
    const today = new Date().toISOString().split('T')[0];
    
    newRow.innerHTML = `
        <td>${rowCounter + 1}</td>
        <td>
            <input type="date" name="report_date" class="form-control" value="${today}" required>
        </td>
        <td>
            <input type="text" value="{{ auth()->user()->name }}" readonly class="form-control" style="background: #f7fafc;">
        </td>
        <td>
            <input type="text" value="{{ auth()->user()->department->name ?? 'N/A' }}" readonly class="form-control" style="background: #f7fafc;">
        </td>
        <td>
            <input type="text" value="{{ auth()->user()->position ?? 'Nhân viên' }}" readonly class="form-control" style="background: #f7fafc;">
        </td>
        <td>
            <textarea name="daily_work" class="form-control" placeholder="Mô tả công việc đã làm trong ngày..." required></textarea>
        </td>
        <td>
            <textarea name="difficulties" class="form-control" placeholder="Những khó khăn gặp phải..."></textarea>
        </td>
        <td>
            <textarea name="comments" class="form-control" placeholder="Nhận xét, đề xuất..."></textarea>
        </td>
        <td>
            <button type="button" class="remove-row-btn" onclick="removeRow(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(newRow);
    rowCounter++;
    updateRowNumbers();
    
    // Hiển thị nút xóa cho tất cả hàng
    document.querySelectorAll('.remove-row-btn').forEach(btn => {
        btn.style.display = 'inline-block';
    });
}

function removeRow(button) {
    const row = button.closest('tr');
    row.remove();
    updateRowNumbers();
    
    // Ẩn nút xóa nếu chỉ còn 1 hàng
    const rows = document.querySelectorAll('.report-row');
    if (rows.length === 1) {
        document.querySelectorAll('.remove-row-btn').forEach(btn => {
            btn.style.display = 'none';
        });
    }
}

function updateRowNumbers() {
    const rows = document.querySelectorAll('.report-row');
    rows.forEach((row, index) => {
        row.cells[0].textContent = index + 1;
    });
}

function loadCustomFields() {
    const departmentName = '{{ auth()->user()->department->name ?? "" }}';
    
    // Custom fields theo phòng ban
    const customFields = {
        'IT': [
            { name: 'projects_worked_on', label: 'Dự án đang làm', type: 'text' },
            { name: 'bugs_fixed', label: 'Lỗi đã sửa', type: 'number' },
            { name: 'code_reviews', label: 'Code review', type: 'number' },
            { name: 'meetings_attended', label: 'Cuộc họp tham gia', type: 'text' }
        ],
        'HR': [
            { name: 'candidates_interviewed', label: 'Ứng viên phỏng vấn', type: 'number' },
            { name: 'contracts_processed', label: 'Hợp đồng xử lý', type: 'number' },
            { name: 'training_sessions', label: 'Buổi đào tạo', type: 'text' },
            { name: 'employee_issues', label: 'Vấn đề nhân viên', type: 'textarea' }
        ],
        'Finance': [
            { name: 'transactions_processed', label: 'Giao dịch xử lý', type: 'number' },
            { name: 'reports_generated', label: 'Báo cáo tạo', type: 'number' },
            { name: 'budget_reviews', label: 'Đánh giá ngân sách', type: 'text' },
            { name: 'audit_tasks', label: 'Công việc kiểm toán', type: 'textarea' }
        ]
    };
    
    const fields = customFields[departmentName] || [];
    
    if (fields.length > 0) {
        const section = document.getElementById('customFieldsSection');
        const content = document.getElementById('customFieldsContent');
        
        let html = '';
        fields.forEach(field => {
            html += `
                <div class="custom-field">
                    <div class="custom-field-label">${field.label}</div>
            `;
            
            if (field.type === 'textarea') {
                html += `
                    <textarea name="custom_fields[${field.name}]" class="custom-field-textarea" 
                              placeholder="Nhập ${field.label.toLowerCase()}..."></textarea>
                `;
            } else {
                html += `
                    <input type="${field.type}" name="custom_fields[${field.name}]" class="custom-field-input" 
                           placeholder="Nhập ${field.label.toLowerCase()}...">
                `;
            }
            
            html += '</div>';
        });
        
        content.innerHTML = html;
        section.style.display = 'block';
    }
}

// Validate form trước khi submit
document.getElementById('reportForm').addEventListener('submit', function(e) {
    const rows = document.querySelectorAll('.report-row');
    let isValid = true;
    
    rows.forEach((row, index) => {
        const dateInput = row.querySelector('input[name="report_date"]');
        const workTextarea = row.querySelector('textarea[name="daily_work"]');
        
        if (!dateInput.value) {
            alert(`Vui lòng chọn ngày cho hàng ${index + 1}`);
            isValid = false;
            return;
        }
        
        if (!workTextarea.value.trim()) {
            alert(`Vui lòng nhập công việc cho hàng ${index + 1}`);
            isValid = false;
            return;
        }
    });
    
    if (!isValid) {
        e.preventDefault();
    }
});
</script>
@endpush
