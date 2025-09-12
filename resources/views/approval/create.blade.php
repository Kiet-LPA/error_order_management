@extends('layouts.master')

@section('title', 'Tạo đề xuất mới - ' . $formConfig->form_name)

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
                        <i class="bi bi-plus-circle"></i> Tạo đề xuất mới - {{ $formConfig->form_name }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('approval.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($formConfig->description)
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> {{ $formConfig->description }}
                        </div>
                    @endif

                    @php
                        $nextApprover = null;
                        if (auth()->user()->role === 'employee') {
                            $userDepartments = auth()->user()->departments;
                            if ($userDepartments->count() > 0) {
                                $nextApprover = App\Models\User::where('role', 'manager')
                                    ->whereHas('departments', function($query) use ($userDepartments) {
                                        $query->whereIn('department_id', $userDepartments->pluck('id'));
                                    })
                                    ->first();
                            }
                        } elseif (auth()->user()->role === 'manager') {
                            $nextApprover = App\Models\User::where('role', 'director')->first();
                        }
                    @endphp


                    <form method="POST" action="{{ route('approval.store') }}" id="approvalForm">
                        @csrf
                        <input type="hidden" name="form_type" value="{{ $formConfig->form_type }}">
                        
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
                                           value="{{ old('form_data.title') }}"
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
                                              required>{{ old('form_data.description') }}</textarea>
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
                                            name="current_approver_id">
                                        <option value="">Chọn Người phê duyệt</option>
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
                                        <option value="bank_transfer" {{ old('form_data.payment_method') == 'bank_transfer' ? 'selected' : '' }}>Chuyển khoản</option>
                                        <option value="cash" {{ old('form_data.payment_method') == 'cash' ? 'selected' : '' }}>Tiền mặt</option>
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
                                    <input type="number" 
                                           class="form-control @error('form_data.amount') is-invalid @enderror" 
                                           id="amount" 
                                           name="form_data[amount]" 
                                           value="{{ old('form_data.amount') }}"
                                           step="1000000"
                                           min="0"
                                           required>
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
                                                   value="{{ old('form_data.bank_account') }}" 
                                                   placeholder="Nhập số tài khoản"
                                                   disabled>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="bank_name">Tên ngân hàng</label>
                                            <input type="text" 
                                                   class="form-control @error('form_data.bank_name') is-invalid @enderror" 
                                                   id="bank_name" 
                                                   name="form_data[bank_name]" 
                                                   value="{{ old('form_data.bank_name') }}" 
                                                   placeholder="Nhập tên ngân hàng"
                                                   disabled>
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
                                                   value="{{ old('form_data.' . $field['name']) }}"
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
                                            @if($field['name'] === 'department')
                                                <select class="form-control @error('form_data.' . $field['name']) is-invalid @enderror" 
                                                        id="{{ $field['name'] }}" 
                                                        name="form_data[{{ $field['name'] }}]"
                                                        @if($field['required']) required @endif
                                                        onchange="loadManagers(this.value)">
                                                    <option value="">Chọn {{ $field['label'] }}</option>
                                                    @if(auth()->user()->role === 'employee')
                                                        @php
                                                            $userDepartments = auth()->user()->departments;
                                                            $userDepartmentIds = $userDepartments->pluck('id')->toArray();
                                                        @endphp
                                                        @foreach($field['options'] as $option)
                                                            @if(in_array($option['value'], $userDepartmentIds))
                                                                <option value="{{ $option['value'] }}" 
                                                                        {{ old('form_data.' . $field['name']) == $option['value'] ? 'selected' : '' }}>
                                                                    {{ $option['label'] }}
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        @foreach($field['options'] as $option)
                                                            <option value="{{ $option['value'] }}" 
                                                                    {{ old('form_data.' . $field['name']) == $option['value'] ? 'selected' : '' }}>
                                                                {{ $option['label'] }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            @else
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
                                            @endif
                                        @elseif($field['name'] === 'manager')
                                            <select class="form-control @error('form_data.' . $field['name']) is-invalid @enderror" 
                                                    id="{{ $field['name'] }}" 
                                                    name="form_data[{{ $field['name'] }}]"
                                                    @if($field['required']) required @endif>
                                                <option value="">Chọn {{ $field['label'] }}</option>
                                                <!-- Options will be loaded via AJAX -->
                                            </select>
                                        @elseif($field['type'] === 'date')
                                            <input type="date" 
                                                   class="form-control @error('form_data.' . $field['name']) is-invalid @enderror" 
                                                   id="{{ $field['name'] }}" 
                                                   name="form_data[{{ $field['name'] }}]" 
                                                   value="{{ old('form_data.' . $field['name']) }}"
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
                                                                   value="{{ old('form_data.bank_account') }}" 
                                                                   placeholder="Nhập số tài khoản"
                                                                   disabled>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="bank_name">Tên ngân hàng</label>
                                                            <input type="text" 
                                                                   class="form-control @error('form_data.bank_name') is-invalid @enderror" 
                                                                   id="bank_name" 
                                                                   name="form_data[bank_name]" 
                                                                   value="{{ old('form_data.bank_name') }}" 
                                                                   placeholder="Nhập tên ngân hàng"
                                                                   disabled>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif($field['type'] === 'approver_select')
                                            <!-- Người phê duyệt -->
                                            <div class="form-group">
                                                <label for="manager" class="font-weight-bold">
                                                <select class="form-control @error('current_approver_id') is-invalid @enderror" 
                                                        id="manager" 
                                                        name="current_approver_id"
                                                        required>
                                                    <option value="">Chọn Người phê duyệt</option>
                                                </select>
                                                @error('current_approver_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
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
                        </div>

                        <!-- Người phê duyệt -->

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Tạo đề xuất
                            </button>
                            <a href="{{ route('approval.index') }}" class="btn btn-secondary btn-lg ml-2">
                                <i class="bi bi-x-circle"></i> Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

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

// Calculate row total for number fields - Global function
function calculateRowTotal(input) {
    const row = input.closest('tr');
    const soLuong = row.querySelector('input[name*="[so_luong]"]')?.value || 0;
    const donGia = row.querySelector('input[name*="[don_gia]"]')?.value || 0;
    const thanhTien = row.querySelector('input[name*="[thanh_tien]"]');
    
    if (thanhTien) {
        thanhTien.value = (parseFloat(soLuong) * parseFloat(donGia)).toFixed(2);
    }
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



    // Initialize autocomplete for existing table rows
    function initExistingAutocomplete() {
        document.querySelectorAll('input[name*="[ten_hang_hoa]"]').forEach(input => {
            initAutocomplete(input);
        });
    }
    
    // Initialize autocomplete on page load
    initExistingAutocomplete();

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

// Conditional fields handling
document.addEventListener('DOMContentLoaded', function() {
    // Handle payment method change
    const paymentMethodSelect = document.getElementById('payment_method');
    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', function() {
            const bankAccountField = document.getElementById('bank_account');
            const bankNameField = document.getElementById('bank_name');
            
            if (this.value === 'bank_transfer') {
                // Enable bank info fields
                if (bankAccountField) {
                    bankAccountField.disabled = false;
                    bankAccountField.style.opacity = '1';
                }
                if (bankNameField) {
                    bankNameField.disabled = false;
                    bankNameField.style.opacity = '1';
                }
            } else {
                // Disable bank info fields and clear values
                if (bankAccountField) {
                    bankAccountField.disabled = true;
                    bankAccountField.style.opacity = '0.6';
                    bankAccountField.value = '';
                }
                if (bankNameField) {
                    bankNameField.disabled = true;
                    bankNameField.style.opacity = '0.6';
                    bankNameField.value = '';
                }
            }
        });
        
        // Trigger change event on page load
        paymentMethodSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
@endsection
