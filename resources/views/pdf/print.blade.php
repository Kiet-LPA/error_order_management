<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $formConfig->form_name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
        }
        .form-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .form-field {
            margin: 15px 0;
        }
        .field-label {
            font-weight: bold;
            width: 200px;
            display: inline-block;
        }
        .field-value {
            display: inline-block;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .table th, .table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .signature {
            margin-top: 50px;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            width: 200px;
            display: inline-block;
        }
        @media print {
            .no-print { display: none; }
            body { margin: 0; padding: 15px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $request->form_data['title'] ?? $formConfig->form_name }}</h2>
        <p>Mã: {{ $request->id }} | Ngày: {{ $request->created_at->format('d/m/Y') }}</p>
        <p>Người tạo: {{ $request->creator->name }}</p>
    </div>

    <!-- Các trường cơ bản -->
    <div class="form-field">
        <span class="field-label">Tiêu đề đề xuất:</span>
        <span class="field-value">{{ $request->form_data['title'] ?? '' }}</span>
    </div>
    
    <div class="form-field">
        <span class="field-label">Phòng ban:</span>
        <span class="field-value">
            @if(!empty($request->form_data['department']))
                @php
                    $department = \App\Models\Department::find($request->form_data['department']);
                @endphp
                {{ $department ? $department->name : 'Không xác định' }}
            @else
                Gửi cho Director/Admin
            @endif
        </span>
    </div>
    
    <div class="form-field">
        <span class="field-label">Mô tả:</span>
        <span class="field-value">{{ $request->form_data['description'] ?? '' }}</span>
    </div>
    
    <div class="form-field">
        <span class="field-label">Người phê duyệt:</span>
        <span class="field-value">
            @if(!empty($request->form_data['manager']))
                @php
                    $manager = \App\Models\User::find($request->form_data['manager']);
                @endphp
                {{ $manager ? $manager->name : 'Không xác định' }}
            @else
                Director/Admin
            @endif
        </span>
    </div>
    
    <div class="form-field">
        <span class="field-label">Phương thức thanh toán:</span>
        <span class="field-value">
            @if($request->form_data['payment_method'] === 'bank_transfer')
                Chuyển khoản
            @elseif($request->form_data['payment_method'] === 'cash')
                Tiền mặt
            @else
                {{ $request->form_data['payment_method'] ?? '' }}
            @endif
        </span>
    </div>
    
    <div class="form-field">
        <span class="field-label">Số tiền:</span>
        <span class="field-value">{{ number_format($request->form_data['amount'] ?? 0) }} VNĐ</span>
    </div>
    
    <!-- Thông tin ngân hàng -->
    @if(!empty($request->form_data['payment_method']) && ($request->form_data['payment_method'] === 'Chuyển khoản' || $request->form_data['payment_method'] === 'bank_transfer'))
    <div class="form-field">
        <span class="field-label">Thông tin ngân hàng:</span>
        <span class="field-value">
            Số tài khoản: {{ $request->form_data['bank_account'] ?? '' }}<br>
            Tên ngân hàng: {{ $request->form_data['bank_name'] ?? '' }}
        </span>
    </div>
    @endif

    @foreach($formConfig->form_fields as $field)
        @if($field['name'] !== 'title' && $field['name'] !== 'description' && $field['name'] !== 'department' && $field['name'] !== 'amount' && $field['name'] !== 'payment_method' && $field['name'] !== 'bank_account' && $field['name'] !== 'bank_name' && $field['type'] !== 'approver_select')
        @php
            $fieldValue = $request->getFormFieldValue($field['name'], $field);
            $hasValue = false;
            
            if ($field['type'] === 'dynamic_table') {
                $tableData = $request->form_data[$field['name']] ?? [];
                $hasValue = !empty($tableData) && is_array($tableData) && count($tableData) > 0;
                // Kiểm tra xem có ít nhất 1 hàng có dữ liệu thực sự
                if ($hasValue) {
                    $hasValue = false;
                    foreach ($tableData as $row) {
                        if (is_array($row)) {
                            foreach ($row as $cellValue) {
                                if (!empty($cellValue) && $cellValue !== '') {
                                    $hasValue = true;
                                    break 2;
                                }
                            }
                        }
                    }
                }
            } elseif ($field['type'] === 'table') {
                $hasValue = !empty($formData[$field['name']]);
            } else {
                $hasValue = !empty($fieldValue) && $fieldValue !== '' && $fieldValue !== 'N/A' && $fieldValue !== null;
            }
        @endphp
        
        @if($hasValue)
            @if($field['type'] === 'table' || $field['type'] === 'dynamic_table')
                <div class="form-field">
                    <h4 style="margin-bottom: 15px; font-weight: bold;">{{ $field['label'] }}</h4>
                    <table class="table">
                        <thead>
                            <tr>
                                @foreach($field['columns'] as $index => $column)
                                    <th>
                                        @if(!empty($column['label']))
                                            {{ $column['label'] }}
                                        @else
                                            Cột {{ $index + 1 }}
                                        @endif
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @if($field['type'] === 'table')
                                @foreach($formData[$field['name']] as $row)
                                    <tr>
                                        @foreach($field['columns'] as $column)
                                            <td>{{ $row[$column['name']] ?? '' }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @else
                                @foreach($request->form_data[$field['name']] as $row)
                                    <tr>
                                        @foreach($field['columns'] as $column)
                                            <td>{{ $row[$column['name']] ?? '' }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            @else
                <div class="form-field">
                    <span class="field-label">{{ $field['label'] }}:</span>
                    <span class="field-value">{{ $fieldValue }}</span>
                </div>
            @endif
            @endif
        @endif
        @endforeach

    <div class="signature" style="display: flex; justify-content: space-between; margin-top: 50px;">
        <!-- Người đề xuất -->
        <div style="text-align: center; width: 45%;">
            <p><strong>Người đề xuất</strong></p>
            <div style="height: 50px; border-bottom: 1px solid #000; margin: 10px 0;"></div>
            <p>{{ $request->creator->name }}</p>
            <p>Ngày: {{ $request->created_at->format('d/m/Y') }}</p>
        </div>
        
        <!-- Người phê duyệt -->
        <div style="text-align: center; width: 45%;">
            <p><strong>Người phê duyệt</strong></p>
            <div style="height: 50px; border-bottom: 1px solid #000; margin: 10px 0;"></div>
            <p>
                @if($request->approval_status === 'approved')
                    {{ $request->approvedBy->name ?? 'N/A' }}
                @elseif($request->approval_status === 'rejected')
                    Công việc đã bị từ chối
                @else
                    Chưa có người phê duyệt
                @endif
            </p>
            <p>Ngày: 
                @if($request->approval_status === 'approved')
                    {{ $request->approved_at->format('d/m/Y') }}
                @elseif($request->approval_status === 'rejected')
                    {{ $request->rejected_at->format('d/m/Y') }}
                @else
                    _________________
                @endif
            </p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
