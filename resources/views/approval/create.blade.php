@extends('layouts.master')

@section('title', 'Tạo đề xuất mới' . ($formConfig ? ' - ' . $formConfig->form_name : ''))

@push('styles')
<style>
/* Modern Form Styling */
.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border-radius: 0.5rem;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 0.5rem 0.5rem 0 0 !important;
    border: none;
    padding: 1.5rem;
}

.card-header h3 {
    margin: 0;
    font-weight: 600;
    font-size: 1.5rem;
}

.card-body {
    padding: 2rem;
    background-color: #f8f9fa;
}

/* Form Group Styling */
.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.form-control {
    border: 2px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background-color: white;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    background-color: white;
}

.form-control::placeholder {
    color: #adb5bd;
    font-style: italic;
}

/* Checkbox Group Styling */
.form-check-group {
    background: linear-gradient(145deg, #ffffff, #f8f9fa);
    border: 2px solid #e9ecef;
    border-radius: 0.75rem;
    padding: 1.25rem;
    max-height: 220px;
    overflow-y: auto;
    min-height: 140px;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.06);
}

.form-check-group::-webkit-scrollbar {
    width: 6px;
}

.form-check-group::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.form-check-group::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.form-check-group::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.form-check-group .form-check {
    margin-bottom: 0.75rem;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
    border: 1px solid transparent;
    background-color: rgba(255, 255, 255, 0.7);
}

.form-check-group .form-check:hover {
    background: linear-gradient(145deg, #ffffff, #f8f9fa);
    border-color: #dee2e6;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.form-check-group .form-check:last-child {
    margin-bottom: 0;
}

.form-check-group .form-check-input {
    margin-top: 0.25rem;
    transform: scale(1.2);
    border: 2px solid #dee2e6;
}

.form-check-group .form-check-input:checked {
    background-color: #667eea;
    border-color: #667eea;
}

.form-check-group .form-check-label {
    margin-left: 0.75rem;
    cursor: pointer;
    font-size: 0.95rem;
    font-weight: 500;
    line-height: 1.4;
    color: #495057;
}

.form-check-group .form-check-input:disabled + .form-check-label {
    color: #6c757d;
    opacity: 0.6;
}

/* Info Banner */
.alert-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 0.75rem;
    color: white;
    padding: 1rem 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.alert-info i {
    font-size: 1.2rem;
    margin-right: 0.5rem;
}

/* Button Styling */
.btn {
    border-radius: 0.5rem;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    border: none;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
}

.btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
}

/* Form Text */
.form-text {
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 0.5rem;
    font-style: italic;
}

/* Required Field Indicator */
.text-danger {
    color: #e74c3c !important;
    font-weight: bold;
}

/* Dynamic Table Styling */
.dynamic-table-container .table {
    width: 100% !important;
    table-layout: auto;
    min-width: 1200px;
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.dynamic-table-container .table th,
.dynamic-table-container .table td {
    padding: 15px 12px !important;
    vertical-align: middle;
    word-wrap: break-word;
    white-space: nowrap;
    border-color: #e9ecef;
}

.dynamic-table-container .table th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    border: none;
}

.dynamic-table-container .table input {
    width: 100% !important;
    min-width: 100px;
    border: 2px solid #e9ecef;
    padding: 10px 12px;
    border-radius: 0.5rem;
    font-size: 14px;
    transition: all 0.3s ease;
}

.dynamic-table-container .table input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.dynamic-table-container {
    width: 100%;
    overflow-x: auto;
    border-radius: 0.5rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .card-body {
        padding: 1.5rem;
    }
    
    .form-check-group {
        max-height: 180px;
        padding: 1rem;
    }
    
    .form-check-group .form-check {
        margin-bottom: 0.5rem;
        padding: 0.5rem 0.75rem;
    }
    
    .form-check-group .form-check-label {
        font-size: 0.9rem;
    }
    
    .btn {
        padding: 0.6rem 1.2rem;
        font-size: 0.9rem;
    }
}

/* Animation */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeInUp 0.6s ease-out;
}

/* Loading State */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid #667eea;
    border-top: 2px solid transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="card shadow-lg">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="bi bi-plus-circle me-2"></i> Tạo đề xuất mới
                    </h3>
                        <a href="{{ route('approval.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($formConfig->description)
                        <div class="alert alert-info mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>{{ $formConfig->description }}</strong>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('approval.store') }}" id="approvalForm">
                        @csrf
                        <input type="hidden" name="form_type" value="{{ $formConfig->form_type }}">
                        
                        <!-- Hàng 1: Tiêu đề đề xuất và Phòng ban -->
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title" class="form-label">
                                        Tiêu đề đề xuất
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('form_data.title') is-invalid @enderror" 
                                           id="title" 
                                           name="form_data[title]" 
                                           value="{{ old('form_data.title') }}"
                                           placeholder="Nhập tiêu đề đề xuất..."
                                           required>
                                    @error('form_data.title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="department" class="form-label">
                                        Phòng ban
                                    </label>
                                    <select class="form-control @error('form_data.department') is-invalid @enderror" 
                                            id="department" 
                                            name="form_data[department]"
                                            onchange="loadManagers(this.value)">
                                        <option value="">Chọn Phòng ban (không bắt buộc)</option>
                                        @if(auth()->user()->role === 'employee')
                                            @php
                                                $userDepartments = auth()->user()->departments;
                                                $userDepartmentIds = $userDepartments->pluck('id')->toArray();
                                            @endphp
                                            @foreach($formConfig->form_fields as $field)
                                                @if($field['name'] === 'department')
                                                    @foreach($field['options'] as $option)
                                                        @if(in_array($option['value'], $userDepartmentIds))
                                                            <option value="{{ $option['value'] }}" 
                                                                    {{ old('form_data.department') == $option['value'] ? 'selected' : '' }}>
                                                                {{ $option['label'] }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        @else
                                            @foreach($formConfig->form_fields as $field)
                                                @if($field['name'] === 'department')
                                                    @foreach($field['options'] as $option)
                                                        <option value="{{ $option['value'] }}" 
                                                                {{ old('form_data.department') == $option['value'] ? 'selected' : '' }}>
                                                            {{ $option['label'] }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('form_data.department')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hàng 2: Mô tả -->
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="description" class="form-label">
                                        Mô tả
                                        <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control @error('form_data.description') is-invalid @enderror" 
                                              id="description" 
                                              name="form_data[description]" 
                                              rows="4"
                                              placeholder="Nhập mô tả chi tiết về đề xuất..."
                                              required>{{ old('form_data.description') }}</textarea>
                                    @error('form_data.description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                                                 
                        <!-- Hàng 3: Người phê duyệt và Người theo dõi -->
                        <div class="row g-4">
                                        <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        Người phê duyệt
                                        <span class="text-danger">*</span>
                                        <small class="text-muted">(Có thể chọn nhiều người)</small>
                                    </label>
                                    <div id="approvers-container" class="form-check-group">
                                        <div class="text-muted text-center py-3">
                                            <i class="bi bi-hourglass-split me-2"></i>Đang tải...
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="bi bi-info-circle me-1"></i>Chỉ hiển thị Manager. Admin/Director tự động tham gia
                                    </small>
                                    @error('approvers')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                        </div>
                                        <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        Người theo dõi
                                        <small class="text-muted">(Tùy chọn - có thể chọn nhiều người)</small>
                                    </label>
                                    <div id="followers-container" class="form-check-group">
                                        <div class="text-muted text-center py-3">
                                            <i class="bi bi-hourglass-split me-2"></i>Đang tải...
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="bi bi-info-circle me-1"></i>Chỉ hiển thị Manager. Admin/Director tự động tham gia
                                    </small>
                                    @error('followers')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hàng 4: Yêu cầu phê duyệt -->
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label">Yêu cầu phê duyệt</label>
                                    <div class="form-check p-3 border rounded-3 bg-light">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="require_all_approvals" 
                                               name="require_all_approvals" 
                                               value="1"
                                               {{ old('require_all_approvals') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="require_all_approvals">
                                            <i class="bi bi-check2-all me-2"></i>Yêu cầu tất cả người phê duyệt đồng ý
                                    </label>
                                </div>
                                    <small class="form-text text-muted mt-2">
                                        <i class="bi bi-info-circle me-1"></i>Nếu không chọn, chỉ cần 1 người phê duyệt là đủ
                                    </small>
                            </div>
                            </div>
                        </div>
                        <!-- Hàng 2.5: Thông tin tài chính -->
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount" class="form-label">
                                        Số tiền
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('form_data.amount') is-invalid @enderror" 
                                           id="amount" 
                                           name="form_data[amount]" 
                                           value="{{ old('form_data.amount') }}"
                                           step="0.01" 
                                           min="0"
                                           placeholder="Nhập số tiền...">
                                    @error('form_data.amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                            </div>
                        </div>
                        
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_method" class="form-label">
                                        Phương thức thanh toán
                                    </label>
                                    <select class="form-control @error('form_data.payment_method') is-invalid @enderror" 
                                            id="payment_method" 
                                            name="form_data[payment_method]">
                                        <option value="">Chọn phương thức thanh toán</option>
                                        <option value="cash" {{ old('form_data.payment_method') == 'cash' ? 'selected' : '' }}>Tiền mặt</option>
                                        <option value="bank_transfer" {{ old('form_data.payment_method') == 'bank_transfer' ? 'selected' : '' }}>Chuyển khoản</option>
                                    </select>
                                    @error('form_data.payment_method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hàng 2.6: Thông tin ngân hàng -->
                        <div class="row g-4" id="bank-info-section" style="display: none;">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label">Thông tin ngân hàng</label>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <input type="text" 
                                                   class="form-control @error('form_data.bank_name') is-invalid @enderror" 
                                                   id="bank_name" 
                                                   name="form_data[bank_name]" 
                                                   value="{{ old('form_data.bank_name') }}" 
                                                   placeholder="Tên ngân hàng">
                                            @error('form_data.bank_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" 
                                                   class="form-control @error('form_data.account_number') is-invalid @enderror" 
                                                   id="account_number" 
                                                   name="form_data[account_number]" 
                                                   value="{{ old('form_data.account_number') }}" 
                                                   placeholder="Số tài khoản">
                                            @error('form_data.account_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                    </div>
                                        <div class="col-md-4">
                                            <input type="text" 
                                                   class="form-control @error('form_data.account_holder') is-invalid @enderror" 
                                                   id="account_holder" 
                                                   name="form_data[account_holder]" 
                                                   value="{{ old('form_data.account_holder') }}" 
                                                   placeholder="Tên chủ tài khoản">
                                            @error('form_data.account_holder')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                </div>
                            </div>
                        </div>
                            </div>
                        </div>
                        <!-- Các trường khác -->
                        <div class="row">
                            @if($formConfig && $formConfig->form_fields)
                                @foreach($formConfig->form_fields as $field)
                                {{-- Debug: Uncomment to see what fields are being processed --}}
                                {{-- <div class="alert alert-info">Processing field: {{ $field['name'] }} ({{ $field['label'] }})</div> --}}
                                
                                @if(!in_array($field['name'], [
                                    'title', 'description', 'department',
                                    'amount', 'payment_method',
                                    'bank_name', 'account_number', 'account_holder',
                                    'items', 'bank_info', 'banking_info'
                                ]) && $field['type'] !== 'approver_select' && 
                                    !in_array($field['label'], [
                                        'Thông tin ngân hàng', 'Bank Information',
                                        'Số tiền', 'Phương thức thanh toán'
                                    ]))
                                    <div class="col-md-6 mb-3" @if($field['type'] === 'dynamic_table') style="width: 100%;" @endif>
                                    <div class="form-group">
                                        <label for="{{ $field['name'] }}">
                                            {{ $field['label'] }}
                                            @if($field['required'])
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>
                                        
                                        @if($field['type'] === 'text')
                                            <input type="text" 
                                                   class="form-control @error('form_data.' . $field['name']) is-invalid @enderror" 
                                                   id="{{ $field['name'] }}" 
                                                   name="form_data[{{ $field['name'] }}]" 
                                                   value="{{ old('form_data.' . $field['name']) }}"
                                                       @if($field['required']) required @endif>
                                        @elseif($field['type'] === 'number')
                                            <input type="number" 
                                                   class="form-control @error('form_data.' . $field['name']) is-invalid @enderror" 
                                                   id="{{ $field['name'] }}" 
                                                   name="form_data[{{ $field['name'] }}]" 
                                                   value="{{ old('form_data.' . $field['name']) }}"
                                                   @if(isset($field['step'])) step="{{ $field['step'] }}" @endif
                                                   @if(isset($field['validation']) && str_contains($field['validation'], 'min:')) min="0" @endif
                                                   @if($field['required']) required @endif>
                                        @elseif($field['type'] === 'textarea')
                                            <textarea class="form-control @error('form_data.' . $field['name']) is-invalid @enderror" 
                                                      id="{{ $field['name'] }}" 
                                                      name="form_data[{{ $field['name'] }}]" 
                                                      rows="3"
                                                      @if($field['required']) required @endif>{{ old('form_data.' . $field['name']) }}</textarea>
                                        @elseif($field['type'] === 'select')
                                                <select class="form-control @error('form_data.' . $field['name']) is-invalid @enderror" 
                                                        id="{{ $field['name'] }}" 
                                                        name="form_data[{{ $field['name'] }}]"
                                                        @if($field['required']) required @endif>
                                                    <option value="">Chọn {{ $field['label'] }}</option>
                                                    @foreach($field['options'] as $option)
                                                        <option value="{{ $option['value'] }}" 
                                                                {{ old('form_data.' . $field['name']) == $option['value'] ? 'selected' : '' }}>
                                                            {{ $option['label'] }}
                                                        </option>
                                                    @endforeach
                                            </select>
                                        @elseif($field['type'] === 'date')
                                            <input type="date" 
                                                   class="form-control @error('form_data.' . $field['name']) is-invalid @enderror" 
                                                   id="{{ $field['name'] }}" 
                                                   name="form_data[{{ $field['name'] }}]" 
                                                   value="{{ old('form_data.' . $field['name']) }}"
                                                   @if($field['required']) required @endif>
                                        @elseif($field['type'] === 'dynamic_table')
            <div class="dynamic-table-container">
                <div class="d-flex align-items-center mb-2">
                    <button type="button" class="btn btn-sm btn-primary me-3" onclick="toggleDynamicTable('{{ $field['name'] }}')">
                        <i class="bi bi-table"></i> Tạo bảng
                    </button>
                    <h6 class="mb-0">{{ $field['label'] }}</h6>
                </div>
                <div id="table-container-{{ $field['name'] }}" class="table-responsive" style="display: none;">
                    <table class="table table-bordered" id="table-{{ $field['name'] }}">
                        <thead>
                            <tr>
                                @foreach($field['columns'] as $column)
                                    <th width="{{ $column['width'] ?? 'auto' }}">{{ $column['label'] }}</th>
                                @endforeach
                                <th width="5%">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-{{ $field['name'] }}">
                            <tr>
                                @foreach($field['columns'] as $index => $column)
                                    <td>
                                        @if($column['name'] === 'stt')
                                            <input type="text" class="form-control form-control-sm" name="form_data[{{ $field['name'] }}][0][{{ $column['name'] }}]" value="1" readonly>
                                        @else
                                            <input type="text" class="form-control form-control-sm" name="form_data[{{ $field['name'] }}][0][{{ $column['name'] }}]" placeholder="{{ $column['label'] }}">
                                        @endif
                                    </td>
                                @endforeach
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger remove-table-row" data-field="{{ $field['name'] }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-success add-table-row" data-field="{{ $field['name'] }}">
                        <i class="bi bi-plus"></i> Thêm hàng
                    </button>
                </div>
            </div>
                                        @endif
                                        
                                        @error('form_data.' . $field['name'])
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                @endif
                                @endforeach
                            @endif
                        </div>

                        <div class="form-group text-center mt-5">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="bi bi-check-circle me-2"></i> Tạo đề xuất
                            </button>
                            <a href="{{ route('approval.index') }}" class="btn btn-secondary btn-lg px-5 ms-3">
                                <i class="bi bi-x-circle me-2"></i> Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function loadUsersForApproval() {
    const approversContainer = document.getElementById('approvers-container');
    const followersContainer = document.getElementById('followers-container');
    
    if (!approversContainer || !followersContainer) {
        console.error('Container elements not found');
            return;
        }
        
    // Show loading with animation
    approversContainer.innerHTML = `
        <div class="text-muted text-center py-4">
            <div class="spinner-border spinner-border-sm me-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            Đang tải danh sách...
        </div>
    `;
    followersContainer.innerHTML = `
        <div class="text-muted text-center py-4">
            <div class="spinner-border spinner-border-sm me-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            Đang tải danh sách...
        </div>
    `;
    
    console.log('Loading users for approval...');
    
    // Fetch all users who can be approvers or followers
    fetch('/api/users/approval-eligible', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            if (!response.ok) {
                console.error('Response not ok:', response.status, response.statusText);
                throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
            }
            return response.json();
        })
        .then(response => {
            console.log('Response loaded:', response);
            // Clear loading content
            approversContainer.innerHTML = '';
            followersContainer.innerHTML = '';
            
            const users = response.data || response;
            console.log('Users array:', users);
            console.log('Users count:', users.length);
            
            if (!users || users.length === 0) {
                approversContainer.innerHTML = `
                    <div class="text-warning text-center py-4">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Không có người dùng nào có quyền phê duyệt
                    </div>
                `;
                followersContainer.innerHTML = `
                    <div class="text-warning text-center py-4">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Không có người dùng nào có quyền phê duyệt
                    </div>
                `;
                return;
            }
            
            users.forEach(user => {
                // Only show managers (admin/director auto-see all)
                if (user.role !== 'manager') {
                    return;
                }
                
                // Display role in name
                let roleText = ' (Quản lý)';
                
                const displayName = user.name + roleText;
                
                // Create checkbox for approvers
                const approverCheckbox = document.createElement('div');
                approverCheckbox.className = 'form-check';
                approverCheckbox.innerHTML = `
                    <input class="form-check-input approver-checkbox" type="checkbox" 
                           name="approvers[]" value="${user.id}" id="approver_${user.id}">
                    <label class="form-check-label" for="approver_${user.id}">
                        ${displayName}
                    </label>
                `;
                
                // Create checkbox for followers
                const followerCheckbox = document.createElement('div');
                followerCheckbox.className = 'form-check';
                followerCheckbox.innerHTML = `
                    <input class="form-check-input follower-checkbox" type="checkbox" 
                           name="followers[]" value="${user.id}" id="follower_${user.id}">
                    <label class="form-check-label" for="follower_${user.id}">
                        ${displayName}
                    </label>
                `;
                
                approversContainer.appendChild(approverCheckbox);
                followersContainer.appendChild(followerCheckbox);
            });
            
            // Add event listeners to prevent selecting same user as both approver and follower
            const approverCheckboxes = approversContainer.querySelectorAll('.approver-checkbox');
            const followerCheckboxes = followersContainer.querySelectorAll('.follower-checkbox');
            
            approverCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const userId = this.value;
                    const followerCheckbox = document.getElementById(`follower_${userId}`);
                    const followerContainer = followerCheckbox.parentElement;
                    
                    if (this.checked) {
                        followerCheckbox.disabled = true;
                        followerContainer.style.opacity = '0.5';
                        followerContainer.style.backgroundColor = '#f8f9fa';
                        followerContainer.style.cursor = 'not-allowed';
                    } else {
                        followerCheckbox.disabled = false;
                        followerContainer.style.opacity = '1';
                        followerContainer.style.backgroundColor = '';
                        followerContainer.style.cursor = '';
                    }
                });
            });
            
            followerCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const userId = this.value;
                    const approverCheckbox = document.getElementById(`approver_${userId}`);
                    const approverContainer = approverCheckbox.parentElement;
                    
                    if (this.checked) {
                        approverCheckbox.disabled = true;
                        approverContainer.style.opacity = '0.5';
                        approverContainer.style.backgroundColor = '#f8f9fa';
                        approverContainer.style.cursor = 'not-allowed';
                    } else {
                        approverCheckbox.disabled = false;
                        approverContainer.style.opacity = '1';
                        approverContainer.style.backgroundColor = '';
                        approverContainer.style.cursor = '';
                    }
                });
            });
        })
        .catch(error => {
            console.error('Error loading users:', error);
            const errorMessage = error.message || 'Không thể tải danh sách người dùng';
            approversContainer.innerHTML = `
                <div class="text-danger text-center py-4">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Lỗi tải dữ liệu: ${errorMessage}
                </div>
            `;
            followersContainer.innerHTML = `
                <div class="text-danger text-center py-4">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Lỗi tải dữ liệu: ${errorMessage}
                </div>
            `;
        });
}

// Toggle dynamic table visibility - Global function
function toggleDynamicTable(fieldName) {
    console.log('Toggle table for field:', fieldName);
    const container = document.getElementById(`table-container-${fieldName}`);
    console.log('Container found:', container);
    
    if (container) {
        if (container.style.display === 'none' || container.style.display === '') {
            container.style.display = 'block';
            console.log('Table shown');
        } else {
            container.style.display = 'none';
            console.log('Table hidden');
        }
    } else {
        console.error('Container not found for field:', fieldName);
    }
}

// Dynamic table functionality for items
let itemIndex = 1;

$(document).ready(function() {
    // Add item row
    $('#add-item').click(function() {
        addItemRow();
    });
    
    // Calculate totals when quantity or price changes
    $(document).on('input', '.item-quantity, .item-price', function() {
        calculateRowTotal($(this).closest('tr'));
        calculateGrandTotal();
    });
    
    // Remove item row
    $(document).on('click', '.remove-item', function() {
        $(this).closest('tr').remove();
        updateItemIndexes();
        calculateGrandTotal();
    });
    
    // Show/hide bank info based on payment method
    $('#payment_method').change(function() {
        toggleBankInfo();
    });
    
    // Initialize bank info visibility
    toggleBankInfo();
});

function toggleBankInfo() {
    const paymentMethod = $('#payment_method').val();
    const bankInfoSection = $('#bank-info-section');
    
    if (paymentMethod === 'bank_transfer') {
        bankInfoSection.show();
    } else {
        bankInfoSection.hide();
        // Clear bank info fields when hidden
        $('#bank_name, #account_number, #account_holder').val('');
    }
}

function addItemRow() {
    const newRow = `
        <tr>
            <td>
                <input type="text" class="form-control item-code" name="form_data[items][${itemIndex}][code]" placeholder="Mã hàng">
            </td>
            <td>
                <input type="text" class="form-control item-name" name="form_data[items][${itemIndex}][name]" placeholder="Tên hàng hóa">
            </td>
            <td>
                <input type="number" class="form-control item-quantity" name="form_data[items][${itemIndex}][quantity]" 
                       value="1" min="1" step="1">
            </td>
            <td>
                <input type="number" class="form-control item-price" name="form_data[items][${itemIndex}][price]" 
                       value="0" min="0" step="0.01">
            </td>
            <td>
                <input type="number" class="form-control item-total" name="form_data[items][${itemIndex}][total]" 
                       value="0" readonly>
            </td>
            <td>
                <input type="text" class="form-control item-location" name="form_data[items][${itemIndex}][location]" 
                       placeholder="Nơi mua">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-item">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
    
    $('#items-tbody').append(newRow);
    itemIndex++;
    
    // Enable remove buttons if more than 1 row
    if ($('#items-tbody tr').length > 1) {
        $('.remove-item').prop('disabled', false);
    }
}

function calculateRowTotal(row) {
    const quantity = parseFloat(row.find('.item-quantity').val()) || 0;
    const price = parseFloat(row.find('.item-price').val()) || 0;
    const total = quantity * price;
    row.find('.item-total').val(total.toFixed(2));
}

function calculateGrandTotal() {
    let grandTotal = 0;
    $('.item-total').each(function() {
        grandTotal += parseFloat($(this).val()) || 0;
    });
    
    // Update amount field if it's empty
    if (!$('#amount').val()) {
        $('#amount').val(grandTotal.toFixed(2));
    }
}

function updateItemIndexes() {
    $('#items-tbody tr').each(function(index) {
        $(this).find('input, select').each(function() {
            const name = $(this).attr('name');
            if (name) {
                const newName = name.replace(/\[\d+\]/, `[${index}]`);
                $(this).attr('name', newName);
            }
        });
    });
}

// Add table row - Global function
function addTableRow(fieldName) {
    console.log('Adding row for field:', fieldName);
    const tbody = document.getElementById(`tbody-${fieldName}`);
    console.log('Tbody found:', tbody);
    
    if (!tbody) {
        console.error('Tbody not found for field:', fieldName);
        return;
    }
    
    const row = document.createElement('tr');
    
    // Get field configuration from PHP
    const fieldConfig = @json($formConfig->form_fields ?? []);
    console.log('Field config:', fieldConfig);
    const field = fieldConfig.find(f => f.name === fieldName);
    console.log('Field found:', field);
    
    if (!field || !field.columns) {
        console.error('Field or columns not found for field:', fieldName);
        return;
    }
    
    let rowHtml = '';
    const rowCount = tbody.children.length + 1; // STT = số hàng hiện tại + 1
    
    field.columns.forEach(column => {
        if (column.name === 'stt') {
            rowHtml += `<td><input type="text" class="form-control form-control-sm" name="form_data[${fieldName}][][${column.name}]" value="${rowCount}" readonly></td>`;
        } else {
            rowHtml += `<td><input type="text" class="form-control form-control-sm" name="form_data[${fieldName}][][${column.name}]" placeholder="${column.label}"></td>`;
        }
    });
    rowHtml += `<td><button type="button" class="btn btn-sm btn-danger remove-table-row" data-field="${fieldName}"><i class="bi bi-trash"></i></button></td>`;
    
    row.innerHTML = rowHtml;
    tbody.appendChild(row);
    
    // Cập nhật lại STT cho tất cả các hàng
    updateRowNumbers(fieldName);
}

// Update row numbers (STT) when rows are added or removed
function updateRowNumbers(fieldName) {
    const tbody = document.getElementById(`tbody-${fieldName}`);
    if (!tbody) return;
    
    const rows = tbody.querySelectorAll('tr');
    rows.forEach((row, index) => {
        const sttInput = row.querySelector('input[name*="[stt]"]');
        if (sttInput) {
            sttInput.value = index + 1;
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Load users for approvers and followers when page loads
    try {
        loadUsersForApproval();
    } catch (error) {
        console.error('Error loading users for approval:', error);
    }

    // Validate number inputs to prevent negative values
    const numberInputs = document.querySelectorAll('input[type="number"]');
    numberInputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.value < 0) {
                this.value = 0;
            }
        });
        
        input.addEventListener('keydown', function(e) {
            // Prevent negative sign
            if (e.key === '-' || e.key === 'e' || e.key === 'E') {
                e.preventDefault();
            }
        });
    });

    // Handle dynamic table rows - use event delegation
    document.addEventListener('click', function(e) {
        try {
        if (e.target.closest('.add-table-row')) {
            const button = e.target.closest('.add-table-row');
            const fieldName = button.getAttribute('data-field');
            console.log('Add row button clicked for field:', fieldName);
            addTableRow(fieldName);
        }
        
        if (e.target.closest('.remove-table-row')) {
            const button = e.target.closest('.remove-table-row');
            const fieldName = button.getAttribute('data-field');
            const row = button.closest('tr');
            console.log('Remove row button clicked');
            row.remove();
            
            // Cập nhật lại STT sau khi xóa
            updateRowNumbers(fieldName);
            }
        } catch (error) {
            console.error('Error handling table row actions:', error);
        }
    });

    // Handle form submission for dynamic tables
    document.getElementById('approvalForm').addEventListener('submit', function(e) {
        // Convert dynamic table data to proper format
        document.querySelectorAll('.dynamic-table').forEach(table => {
            const fieldName = table.getAttribute('data-field');
            const rows = table.querySelectorAll('tbody tr');
            const data = [];
            
            rows.forEach(row => {
                const inputs = row.querySelectorAll('input[type="text"]');
                const rowData = {};
                
                inputs.forEach(input => {
                    const name = input.getAttribute('name');
                    const match = name.match(/\[([^\]]+)\]$/);
                    if (match) {
                        rowData[match[1]] = input.value;
                    }
                });
                
                // Only add row if at least one field has data
                if (Object.values(rowData).some(value => value.trim() !== '')) {
                    data.push(rowData);
                }
            });
            
            // Add hidden input with JSON data
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = `form_data[${fieldName}]`;
            hiddenInput.value = JSON.stringify(data);
            this.appendChild(hiddenInput);
        });
    });
});
</script>
@endpush