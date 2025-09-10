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



.form-text {
    font-size: 14px;
    margin-top: 4px;
}

.text-muted {
    color: #718096 !important;
}

.alert {
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 16px;
}

.alert-info {
    background: #e6f3ff;
    border: 1px solid #b3d9ff;
    color: #1a365d;
}

.alert i {
    margin-right: 8px;
}
</style>
@endpush

@section('content')
<div class="report-container">
    <div class="report-header">
        <div class="report-title">Tạo báo cáo công việc</div>
        <div class="report-subtitle">
            Tuần {{ $week }} ({{ $weekInfo['start_formatted'] }} - {{ $weekInfo['end_formatted'] }}) - Năm {{ $year }}
        </div>
    </div>



    <form class="report-form" method="POST" action="{{ route('work-reports.store') }}" id="reportForm">
        @csrf
        <!-- Không cần gửi year và week nữa, sẽ tính toán từ ngày báo cáo -->

        <div class="form-section">
            <div class="section-title">
                <i class="bi bi-calendar"></i>
                Thông tin tuần báo cáo
            </div>
            
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>Thông tin tuần hiện tại</strong><br>
                <small>Tuần {{ $week }} của năm {{ $year }} ({{ $weekInfo['start_formatted'] }} đến {{ $weekInfo['end_formatted'] }})</small>
            </div>
        </div>

        <div class="form-section">
            <div class="section-title">
                <i class="bi bi-list-task"></i>
                Báo cáo công việc
            </div>
            
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>Hướng dẫn:</strong> Bạn có thể tạo nhiều báo cáo cho các ngày khác nhau. 
                Hệ thống sẽ tự động phân loại báo cáo theo tuần dựa trên ngày bạn chọn.
                <br><small>Ví dụ: Ngày 26/8 sẽ thuộc tuần 35, ngày 1/9 sẽ thuộc tuần 36.</small>
            </div>
            
            <table class="report-table" id="reportTable">
                <thead>
                    <tr>
                        <th style="width: 50px; color: #000; font-weight: bold;">STT</th>
                        <th style="width: 120px; color: #000; font-weight: bold;">Ngày báo cáo</th>
                        <th style="width: 150px; color: #000; font-weight: bold;">Tên</th>
                        <th style="width: 120px; color: #000; font-weight: bold;">Phòng ban</th>
                        <th style="width: 100px; color: #000; font-weight: bold;">Vị trí</th>
                        <th style="color: #000; font-weight: bold;">Công việc trong ngày</th>
                        <th style="width: 120px; color: #000; font-weight: bold;">Khó khăn</th>
                        <th style="width: 120px; color: #000; font-weight: bold;">Nhận xét</th>
                        <th style="width: 80px; color: #000; font-weight: bold;">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="reportTableBody">
                    <tr class="report-row">
                        <td>1</td>
                        <td>
                            <input type="date" name="report_dates[]" class="form-control" 
                                   value="{{ old('report_dates.0', $selectedDateFormatted ?? now()->format('Y-m-d')) }}" required>
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
                            <textarea name="daily_works[]" class="form-control @error('daily_works.0') error @enderror" 
                                      placeholder="Mô tả công việc đã làm trong ngày..." required>{{ old('daily_works.0') }}</textarea>
                            @error('daily_works.0')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </td>
                        <td>
                            <textarea name="difficulties[]" class="form-control" 
                                      placeholder="Những khó khăn gặp phải...">{{ old('difficulties.0') }}</textarea>
                        </td>
                        <td>
                            <textarea name="comments[]" class="form-control" 
                                      placeholder="Nhận xét, đề xuất...">{{ old('comments.0') }}</textarea>
                        </td>
                        <td>
                            <button type="button" class="remove-row-btn" onclick="removeRow(this)" style="display: none;">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <button type="button" class="add-row-btn" onclick="addRow()">
                <i class="bi bi-plus-circle"></i>
                Thêm hàng báo cáo
            </button>
        </div>


        <!-- Tùy chọn thay thế báo cáo cũ -->
        <div class="form-section">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="replace_existing" name="replace_existing" value="1">
                <label class="form-check-label" for="replace_existing">
                    <i class="bi bi-arrow-left-right text-warning"></i>
                    <strong>Thay thế báo cáo cũ</strong>
                    <br>
                    <small class="text-muted">Nếu đã có báo cáo cho ngày này, hệ thống sẽ xóa báo cáo cũ và tạo báo cáo mới</small>
                </label>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('work-reports.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Quay lại
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i>
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
            <input type="date" name="report_dates[]" class="form-control" value="${today}" required>
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
            <textarea name="daily_works[]" class="form-control" placeholder="Mô tả công việc đã làm trong ngày..." required></textarea>
        </td>
        <td>
            <textarea name="difficulties[]" class="form-control" placeholder="Những khó khăn gặp phải..."></textarea>
        </td>
        <td>
            <textarea name="comments[]" class="form-control" placeholder="Nhận xét, đề xuất..."></textarea>
        </td>
        <td>
            <button type="button" class="remove-row-btn" onclick="removeRow(this)">
                <i class="bi bi-trash"></i>
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
