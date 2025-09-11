@extends('layouts.master')

@section('title', 'Chỉnh sửa đề xuất #' . $approvalRequest->id)

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
                        
                        <div class="row">
                            @foreach($formConfig->form_fields as $field)
                                <div class="col-md-6 mb-3">
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
                                                   @if($field['required']) required @endif>
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
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6>{{ $field['label'] }}</h6>
                                                    <button type="button" class="btn btn-sm btn-primary" onclick="toggleDynamicTable('{{ $field['name'] }}')">
                                                        <i class="bi bi-table"></i> Tạo bảng
                                                    </button>
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
        return;
    }
    
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
</script>
@endpush

