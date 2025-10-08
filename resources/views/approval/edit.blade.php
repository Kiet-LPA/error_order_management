@extends('layouts.master')

@section('title', 'Chỉnh sửa đề xuất #' . $approvalRequest->id)

@push('styles')
<style>
/* Bảng chi tiết rộng rãi hơn theo chiều ngang */
.dynamic-table-container .table {
    width: 100% !important;
    table-layout: auto;
    min-width: 1200px; /* Đảm bảo bảng có chiều rộng tối thiểu */
}

.dynamic-table-container .table th,
.dynamic-table-container .table td {
    padding: 15px 12px !important;
    vertical-align: middle;
    word-wrap: break-word;
    white-space: nowrap; /* Ngăn text xuống dòng */
}

/* Làm các cột rộng hơn theo chiều ngang - Thứ tự mới: STT, Tên hàng hóa, Mã hàng, Số lượng, Thành tiền, Nơi mua */
.dynamic-table-container .table th:nth-child(1), /* STT */
.dynamic-table-container .table td:nth-child(1) {
    width: 8%;
    min-width: 60px;
}

.dynamic-table-container .table th:nth-child(2), /* Tên hàng hóa */
.dynamic-table-container .table td:nth-child(2) {
    width: 30%;
    min-width: 250px;
}

.dynamic-table-container .table th:nth-child(3), /* Mã hàng */
.dynamic-table-container .table td:nth-child(3) {
    width: 18%;
    min-width: 150px;
}

.dynamic-table-container .table th:nth-child(4), /* Số lượng */
.dynamic-table-container .table td:nth-child(4) {
    width: 15%;
    min-width: 120px;
}

.dynamic-table-container .table th:nth-child(5), /* Thành tiền */
.dynamic-table-container .table td:nth-child(5) {
    width: 18%;
    min-width: 150px;
}

.dynamic-table-container .table th:nth-child(6), /* Nơi mua */
.dynamic-table-container .table td:nth-child(6) {
    width: 30%;
    min-width: 200px;
}

.dynamic-table-container .table th:nth-child(7), /* Thao tác */
.dynamic-table-container .table td:nth-child(7) {
    width: 10%;
    min-width: 80px;
}

.dynamic-table-container .table input {
    width: 100% !important;
    min-width: 100px;
    border: 1px solid #ddd;
    padding: 10px 12px;
    border-radius: 4px;
    font-size: 14px;
}

.dynamic-table-container .table input:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Làm bảng căng từ trái qua phải */
.dynamic-table-container {
    width: 100%;
    overflow-x: auto;
}

.dynamic-table-container .table-responsive {
    width: 100%;
    margin-bottom: 0;
}

/* Cải thiện giao diện nút */
.dynamic-table-container .btn-group-vertical .btn {
    margin-bottom: 5px;
}

.dynamic-table-container .btn-group .btn {
    padding: 8px 12px;
    font-size: 12px;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-pencil-square"></i> Chỉnh sửa đề xuất - {{ $formConfig->form_name }} #{{ $approvalRequest->id }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('approval.show', $approvalRequest->id) }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('approval.update', $approvalRequest->id) }}" id="approvalForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- Hàng 1: Tiêu đề đề xuất và Phòng ban -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="title">
                                        Tiêu đề đề xuất
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('form_data.title') is-invalid @enderror" 
                                           id="title" 
                                           name="form_data[title]" 
                                           value="{{ old('form_data.title', $approvalRequest->form_data['title'] ?? '') }}"
                                           required>
                                    @error('form_data.title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="department">
                                        Phòng ban
                                    </label>
                                    <select class="form-control @error('form_data.department') is-invalid @enderror" 
                                            id="department" 
                                            name="form_data[department]"
                                            onchange="loadManagers(this.value)">
                                        <option value="">Chọn Phòng ban</option>
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
                                                                    {{ old('form_data.department', $approvalRequest->form_data['department'] ?? '') == $option['value'] ? 'selected' : '' }}>
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
                                                                {{ old('form_data.department', $approvalRequest->form_data['department'] ?? '') == $option['value'] ? 'selected' : '' }}>
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
                        
                        <!-- Hàng 2: Mô tả và Người phê duyệt -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="description">
                                        Mô tả
                                        <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control @error('form_data.description') is-invalid @enderror" 
                                              id="description" 
                                              name="form_data[description]" 
                                              rows="3"
                                              required>{{ old('form_data.description', $approvalRequest->form_data['description'] ?? '') }}</textarea>
                                    @error('form_data.description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="manager">
                                        Người phê duyệt
                                    </label>
                                    <select class="form-control @error('current_approver_id') is-invalid @enderror" 
                                            id="manager" 
                                            name="current_approver_id"
                                            @if(empty($approvalRequest->form_data['department'])) disabled @endif>
                                        <option value="">@if(empty($approvalRequest->form_data['department'])) Gửi cho Director/Admin @else Chọn Người phê duyệt @endif</option>
                                        @if($approvalRequest->current_approver_id)
                                            <option value="{{ $approvalRequest->current_approver_id }}" selected>
                                                {{ $approvalRequest->currentApprover->name ?? 'N/A' }}
                                                @if($approvalRequest->currentApprover->role === 'manager')
                                                    (Quản lý)
                                                @elseif($approvalRequest->currentApprover->role === 'director')
                                                    (Giám đốc)
                                                @endif
                                            </option>
                                        @endif
                                    </select>
                                    @error('current_approver_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hàng 3: Phương thức thanh toán và Số tiền -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="payment_method">
                                        Phương thức thanh toán
                                    </label>
                                    <select class="form-control @error('form_data.payment_method') is-invalid @enderror" 
                                            id="payment_method" 
                                            name="form_data[payment_method]">
                                        <option value="">Chọn Phương thức thanh toán</option>
                                        <option value="bank_transfer" {{ old('form_data.payment_method', $approvalRequest->form_data['payment_method'] ?? '') == 'bank_transfer' ? 'selected' : '' }}>Chuyển khoản</option>
                                        <option value="cash" {{ old('form_data.payment_method', $approvalRequest->form_data['payment_method'] ?? '') == 'cash' ? 'selected' : '' }}>Tiền mặt</option>
                                    </select>
                                    @error('form_data.payment_method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="amount">
                                        Số tiền
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('form_data.amount') is-invalid @enderror" 
                                           id="amount" 
                                           name="form_data[amount]" 
                                           value="{{ old('form_data.amount', $approvalRequest->form_data['amount'] ?? '') }}"
                                           placeholder="Nhập số tiền (ví dụ: 5.000.000)..."
                                           autocomplete="off"
                                           required>
                                    <input type="hidden" id="amount_raw" name="amount_raw">
                                    @error('form_data.amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hàng 4: Thông tin ngân hàng - Luôn hiển thị, chỉ enable/disable -->
                        <div class="row">
                            <div class="col-12">
                                <div id="bank_info" class="mt-3">
                                    <h6 class="text-muted mb-3">Thông tin ngân hàng</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="bank_account">Số tài khoản</label>
                                            <input type="text" 
                                                   class="form-control @error('form_data.bank_account') is-invalid @enderror" 
                                                   id="bank_account" 
                                                   name="form_data[bank_account]" 
                                                   value="{{ old('form_data.bank_account', $approvalRequest->form_data['bank_account'] ?? '') }}"
                                                   placeholder="Nhập số tài khoản"
                                                   @if(empty($approvalRequest->form_data['payment_method']) || $approvalRequest->form_data['payment_method'] !== 'bank_transfer') disabled @endif>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="bank_name">Tên ngân hàng</label>
                                            <input type="text" 
                                                   class="form-control @error('form_data.bank_name') is-invalid @enderror" 
                                                   id="bank_name" 
                                                   name="form_data[bank_name]" 
                                                   value="{{ old('form_data.bank_name', $approvalRequest->form_data['bank_name'] ?? '') }}"
                                                   placeholder="Nhập tên ngân hàng"
                                                   @if(empty($approvalRequest->form_data['payment_method']) || $approvalRequest->form_data['payment_method'] !== 'bank_transfer') disabled @endif>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Các trường khác -->
                        <div class="row">
                            @foreach($formConfig->form_fields as $field)
                                @if($field['name'] !== 'payment_method' && $field['name'] !== 'bank_info' && $field['type'] !== 'approver_select' && $field['name'] !== 'title' && $field['name'] !== 'description' && $field['name'] !== 'department' && $field['name'] !== 'amount')
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
                                                   value="{{ old('form_data.' . $field['name'], $approvalRequest->form_data[$field['name']] ?? '') }}"
                                                   @if($field['required']) required @endif
                                                   @if(isset($field['conditional']))
                                                       data-conditional-field="{{ $field['conditional']['field'] }}"
                                                       data-conditional-value="{{ $field['conditional']['value'] }}"
                                                       style="display: none;"
                                                   @endif>
                                        @elseif($field['type'] === 'number')
                                            <input type="number" 
                                                   class="form-control @error('form_data.' . $field['name']) is-invalid @enderror" 
                                                   id="{{ $field['name'] }}" 
                                                   name="form_data[{{ $field['name'] }}]" 
                                                   value="{{ old('form_data.' . $field['name'], $approvalRequest->form_data[$field['name']] ?? '') }}"
                                                   @if(isset($field['step'])) step="{{ $field['step'] }}" @endif
                                                   @if(isset($field['validation']) && str_contains($field['validation'], 'min:')) min="0" @endif
                                                   @if($field['required']) required @endif>
                                        @elseif($field['type'] === 'textarea')
                                            <textarea class="form-control @error('form_data.' . $field['name']) is-invalid @enderror" 
                                                      id="{{ $field['name'] }}" 
                                                      name="form_data[{{ $field['name'] }}]" 
                                                      rows="3"
                                                      @if($field['required']) required @endif>{{ old('form_data.' . $field['name'], $approvalRequest->form_data[$field['name']] ?? '') }}</textarea>
                                        @elseif($field['type'] === 'select')
                                            <select class="form-control @error('form_data.' . $field['name']) is-invalid @enderror" 
                                                    id="{{ $field['name'] }}" 
                                                    name="form_data[{{ $field['name'] }}]"
                                                    @if($field['required']) required @endif>
                                                <option value="">Chọn {{ $field['label'] }}</option>
                                                @foreach($field['options'] as $option)
                                                    <option value="{{ $option['value'] }}" 
                                                            {{ old('form_data.' . $field['name'], $approvalRequest->form_data[$field['name']] ?? '') == $option['value'] ? 'selected' : '' }}>
                                                        {{ $option['label'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @elseif($field['type'] === 'date')
                                            <input type="date" 
                                                   class="form-control @error('form_data.' . $field['name']) is-invalid @enderror" 
                                                   id="{{ $field['name'] }}" 
                                                   name="form_data[{{ $field['name'] }}]" 
                                                   value="{{ old('form_data.' . $field['name'], $approvalRequest->form_data[$field['name']] ?? '') }}"
                                                   @if($field['required']) required @endif>
                                        @elseif($field['type'] === 'bank_info')
                                            <!-- Thông tin ngân hàng - Luôn hiển thị, chỉ enable/disable -->
                                            <div class="col-12 mb-3">
                                                <div id="bank_info" class="mt-3">
                                                    <h6 class="text-muted mb-3">Thông tin ngân hàng</h6>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label for="bank_account">Số tài khoản</label>
                                                            <input type="text" 
                                                                   class="form-control @error('form_data.bank_account') is-invalid @enderror" 
                                                                   id="bank_account" 
                                                                   name="form_data[bank_account]" 
                                                                   value="{{ old('form_data.bank_account', $approvalRequest->form_data['bank_account'] ?? '') }}"
                                                                   placeholder="Nhập số tài khoản"
                                                                   @if(empty($approvalRequest->form_data['payment_method']) || $approvalRequest->form_data['payment_method'] !== 'bank_transfer') disabled @endif>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="bank_name">Tên ngân hàng</label>
                                                            <input type="text" 
                                                                   class="form-control @error('form_data.bank_name') is-invalid @enderror" 
                                                                   id="bank_name" 
                                                                   name="form_data[bank_name]" 
                                                                   value="{{ old('form_data.bank_name', $approvalRequest->form_data['bank_name'] ?? '') }}"
                                                                   placeholder="Nhập tên ngân hàng"
                                                                   @if(empty($approvalRequest->form_data['payment_method']) || $approvalRequest->form_data['payment_method'] !== 'bank_transfer') disabled @endif>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif($field['type'] === 'approver_select')
                                            <!-- Người phê duyệt -->
                                            <select class="form-control @error('current_approver_id') is-invalid @enderror" 
                                                    id="manager" 
                                                    name="current_approver_id"
                                                    required>
                                                <option value="">Chọn Người phê duyệt</option>
                                                @if($approvalRequest->current_approver_id)
                                                    <option value="{{ $approvalRequest->current_approver_id }}" selected>
                                                        {{ $approvalRequest->currentApprover->name ?? 'N/A' }}
                                                        @if($approvalRequest->currentApprover->role === 'manager')
                                                            (Quản lý)
                                                        @elseif($approvalRequest->currentApprover->role === 'director')
                                                            (Giám đốc)
                                                        @endif
                                                    </option>
                                                @endif
                                            </select>
                                        @elseif($field['type'] === 'dynamic_table')
                                            <div class="dynamic-table-container">
                                                <div class="d-flex align-items-center mb-2">
                                                    <button type="button" class="btn btn-sm btn-primary me-3" onclick="toggleDynamicTable('{{ $field['name'] }}')">
                                                        <i class="bi bi-table"></i> Tạo bảng
                                                    </button>
                                                    <h6 class="mb-0">{{ $field['label'] }}</h6>
                                                </div>
                                                <div id="table-container-{{ $field['name'] }}" class="table-responsive" style="display: {{ !empty($approvalRequest->form_data[$field['name']]) ? 'block' : 'none' }};">
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
                                                            @if(!empty($approvalRequest->form_data[$field['name']]))
                                                                @foreach($approvalRequest->form_data[$field['name']] as $index => $row)
                                                                    <tr>
                                                                        @foreach($field['columns'] as $column)
                                                                            <td>
                                                                                @if($column['name'] === 'stt')
                                                                                    <input type="text" class="form-control form-control-sm" name="form_data[{{ $field['name'] }}][{{ $index }}][{{ $column['name'] }}]" value="{{ $row[$column['name']] ?? $index + 1 }}" readonly>
                                                                                @else
                                                                                    <input type="text" class="form-control form-control-sm" name="form_data[{{ $field['name'] }}][{{ $index }}][{{ $column['name'] }}]" value="{{ $row[$column['name']] ?? '' }}" placeholder="{{ $column['label'] }}">
                                                                                @endif
                                                                            </td>
                                                                        @endforeach
                                                                        <td>
                                                                            <button type="button" class="btn btn-sm btn-danger remove-table-row" data-field="{{ $field['name'] }}">
                                                                                <i class="bi bi-trash"></i>
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @else
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
                                                            @endif
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
                        </div>

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Cập nhật đề xuất
                            </button>
                            <a href="{{ route('approval.show', $approvalRequest->id) }}" class="btn btn-secondary btn-lg ml-2">
                                <i class="bi bi-x-circle"></i> Hủy
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
// Autocomplete functionality
let autocompleteTimeout;
let currentSuggestions = [];

function initAutocomplete(input) {
    input.addEventListener('input', function() {
        clearTimeout(autocompleteTimeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            hideSuggestions();
            return;
        }
        
        autocompleteTimeout = setTimeout(() => {
            fetchSuggestions(query, this);
        }, 300);
    });
    
    input.addEventListener('blur', function() {
        // Delay hiding to allow clicking on suggestions
        setTimeout(() => {
            hideSuggestions();
        }, 200);
    });
    
    input.addEventListener('focus', function() {
        if (this.value.trim().length >= 2) {
            fetchSuggestions(this.value.trim(), this);
        }
    });
}

function fetchSuggestions(query, input) {
    fetch(`/approval/suggestions/items?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(suggestions => {
            currentSuggestions = suggestions;
            showSuggestions(suggestions, input);
        })
        .catch(error => {
            console.error('Error fetching suggestions:', error);
        });
}

function showSuggestions(suggestions, input) {
    hideSuggestions(); // Remove existing suggestions
    
    if (suggestions.length === 0) return;
    
    const container = input.closest('td');
    const suggestionList = document.createElement('div');
    suggestionList.className = 'autocomplete-suggestions';
    suggestionList.style.cssText = `
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ccc;
        border-top: none;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    `;
    
    suggestions.forEach(suggestion => {
        const item = document.createElement('div');
        item.className = 'suggestion-item';
        item.style.cssText = `
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        `;
        item.textContent = suggestion;
        
        item.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f5f5f5';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.backgroundColor = 'white';
        });
        
        item.addEventListener('click', function() {
            input.value = suggestion;
            hideSuggestions();
            input.focus();
        });
        
        suggestionList.appendChild(item);
    });
    
    container.style.position = 'relative';
    container.appendChild(suggestionList);
}

function hideSuggestions() {
    document.querySelectorAll('.autocomplete-suggestions').forEach(el => {
        el.remove();
    });
}

function loadManagers(departmentId) {
    const managerSelect = document.getElementById('manager');
    
    // Clear existing options
    managerSelect.innerHTML = '<option value="">Chọn Người phê duyệt</option>';
    
    if (!departmentId) {
        // Disable manager select when no department is selected
        managerSelect.disabled = true;
        managerSelect.style.opacity = '0.6';
        managerSelect.innerHTML = '<option value="">Gửi cho Director/Admin</option>';
        return;
    }
    
    // Enable manager select
    managerSelect.disabled = false;
    managerSelect.style.opacity = '1';
    
    // Show loading
    managerSelect.innerHTML = '<option value="">Đang tải...</option>';
    
    // Fetch managers
    fetch(`/api/managers/${departmentId}`)
        .then(response => response.json())
        .then(managers => {
            managerSelect.innerHTML = '<option value="">Chọn Người phê duyệt</option>';
            
            managers.forEach(manager => {
                const option = document.createElement('option');
                option.value = manager.id;
                
                // Hiển thị role trong tên
                let roleText = '';
                if (manager.role === 'manager') roleText = ' (Quản lý)';
                else if (manager.role === 'director') roleText = ' (Giám đốc)';
                
                option.textContent = manager.name + roleText;
                managerSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading managers:', error);
            managerSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
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
    const fieldConfig = @json($formConfig->form_fields);
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
    
    // Khởi tạo autocomplete cho trường "Tên hàng hóa"
    const itemNameInput = row.querySelector('input[name*="[ten_hang_hoa]"]');
    if (itemNameInput) {
        initAutocomplete(itemNameInput);
    }
    
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
    // Load managers when page loads if department is already selected
    const departmentSelect = document.getElementById('department');
    if (departmentSelect && departmentSelect.value) {
        loadManagers(departmentSelect.value);
    }

    // Initialize autocomplete for existing table rows
    function initExistingAutocomplete() {
        document.querySelectorAll('input[name*="[ten_hang_hoa]"]').forEach(input => {
            initAutocomplete(input);
        });
    }
    
    // Initialize autocomplete on page load
    initExistingAutocomplete();

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
    });
});

// Conditional fields handling
document.addEventListener('DOMContentLoaded', function() {
    // Handle payment method change
    const paymentMethodSelect = document.getElementById('payment_method');
    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', function() {
            const bankInfoField = document.getElementById('bank_info');
            
            if (this.value === 'bank_transfer') {
                if (bankInfoField) {
                    bankInfoField.style.display = 'block';
                    bankInfoField.closest('.col-md-6').style.display = 'block';
                }
            } else {
                if (bankInfoField) {
                    bankInfoField.style.display = 'none';
                    bankInfoField.closest('.col-md-6').style.display = 'none';
                    // Clear bank info fields
                    const bankAccountField = document.getElementById('bank_account');
                    const bankNameField = document.getElementById('bank_name');
                    if (bankAccountField) bankAccountField.value = '';
                    if (bankNameField) bankNameField.value = '';
                }
            }
        });
        
        // Trigger change event on page load
        paymentMethodSelect.dispatchEvent(new Event('change'));
    }
});

// Đảm bảo trường amount chấp nhận số lẻ
document.addEventListener('DOMContentLoaded', function() {
    const amountField = document.getElementById('amount');
    if (amountField) {
        // Khởi tạo giá trị khi load trang
        let initialValue = amountField.value;
        if (initialValue && !isNaN(initialValue)) {
            let rawValue = initialValue.replace(/[^\d]/g, '');
            document.getElementById('amount_raw').value = rawValue;
            if (rawValue) {
                amountField.value = parseInt(rawValue).toLocaleString('vi-VN');
            }
        }
        
        // Xóa validation mặc định của browser
        amountField.addEventListener('invalid', function(e) {
            e.preventDefault();
        });
        
        // Format số tiền với dấu chấm phân cách
        amountField.addEventListener('input', function(e) {
            let value = this.value.replace(/[^\d]/g, ''); // Chỉ giữ lại số
            let rawValue = value;
            
            if (value) {
                // Format với dấu chấm phân cách hàng nghìn
                value = parseInt(value).toLocaleString('vi-VN');
                this.value = value;
            } else {
                this.value = '';
            }
            
            // Lưu giá trị raw vào hidden field
            document.getElementById('amount_raw').value = rawValue;
            
            // Loại bỏ bất kỳ validation nào khác
            this.setCustomValidity('');
        });
        
        // Khi focus, hiển thị giá trị raw để dễ chỉnh sửa
        amountField.addEventListener('focus', function(e) {
            let rawValue = document.getElementById('amount_raw').value;
            if (rawValue) {
                this.value = rawValue;
            }
        });
        
        // Khi blur, format lại
        amountField.addEventListener('blur', function(e) {
            let rawValue = document.getElementById('amount_raw').value;
            if (rawValue) {
                let formattedValue = parseInt(rawValue).toLocaleString('vi-VN');
                this.value = formattedValue;
            }
        });
    }
});
</script>
@endpush

