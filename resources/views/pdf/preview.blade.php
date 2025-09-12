<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Xem trước PDF - {{ $formConfig->form_name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .preview-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .form-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .form-field {
            margin-bottom: 15px;
        }
        .field-label {
            font-weight: bold;
            display: inline-block;
            width: 200px;
            vertical-align: top;
        }
        .field-value {
            display: inline-block;
            width: calc(100% - 220px);
            vertical-align: top;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .table th, .table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .approval-signatures {
            margin-top: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .signature-item {
            margin: 10px 0;
            padding: 10px;
            border-left: 4px solid #28a745;
        }
        .signature-item.rejected {
            border-left-color: #dc3545;
        }
        .signature-text {
            font-weight: bold;
            color: #28a745;
        }
        .signature-text.rejected {
            color: #dc3545;
        }
        .signature-details {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
        .signature-section {
            margin-top: 50px;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            width: 200px;
            display: inline-block;
        }
        .action-buttons {
            text-align: center;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 10px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        .btn-info {
            background-color: #17a2b8;
            color: white;
        }
    </style>
</head>
<body>
    <div class="preview-container">
        <div class="header">
            <div class="form-title">{{ $request->form_data['title'] ?? $formConfig->form_name }}</div>
            <div>Mã đề xuất: {{ $request->id }}</div>
            <div>Ngày tạo: {{ $request->created_at->format('d/m/Y H:i') }}</div>
            <div>Người tạo: {{ $request->creator->name }}</div>
        </div>

        <div class="form-info">
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
                        $hasValue = !empty($request->form_data[$field['name']]);
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
                                    @foreach($request->form_data[$field['name']] as $row)
                                        <tr>
                                            @foreach($field['columns'] as $column)
                                                <td>{{ $row[$column['name']] ?? '' }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
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
        </div>

        <!-- Approval Signatures -->
        @if($request->approval_signatures)
            <div class="approval-signatures">
                <h4>Chữ ký phê duyệt</h4>
                @foreach($request->approval_signatures as $signature)
                    <div class="signature-item {{ $signature['action'] === 'reject' ? 'rejected' : '' }}">
                        <div class="signature-text {{ $signature['action'] === 'reject' ? 'rejected' : '' }}">
                            {{ $signature['role'] === 'manager' ? 'Quản lý' : 'Người điều hành' }} 
                            {{ $signature['action'] === 'approve' ? 'đã phê duyệt' : 'đã từ chối' }}
                        </div>
                        <div class="signature-details">
                            {{ $signature['user_name'] }} - {{ \Carbon\Carbon::parse($signature['action_at'])->format('d/m/Y H:i') }}
                            @if($signature['note'])
                                <br>Ghi chú: {{ $signature['note'] }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="signature-section" style="display: flex; justify-content: space-between; margin-top: 50px;">
            <div style="text-align: center; width: 45%;">
                <div><strong>Người đề xuất</strong></div>
                <div style="height: 50px; border-bottom: 1px solid #000; margin: 10px 0;"></div>
                <div>{{ $request->creator->name }}</div>
                <div>Ngày: {{ $request->created_at->format('d/m/Y') }}</div>
            </div>
            
            <div style="text-align: center; width: 45%;">
                <div><strong>Người phê duyệt</strong></div>
                <div style="height: 50px; border-bottom: 1px solid #000; margin: 10px 0;"></div>
                <div>
                    @if($request->approval_status === 'approved')
                        {{ $request->approvedBy->name ?? 'N/A' }}
                    @elseif($request->approval_status === 'rejected')
                        Công việc đã bị từ chối
                    @else
                        Chưa có người phê duyệt
                    @endif
                </div>
                <div>Ngày: 
                    @if($request->approval_status === 'approved')
                        {{ $request->approved_at->format('d/m/Y') }}
                    @elseif($request->approval_status === 'rejected')
                        {{ $request->rejected_at->format('d/m/Y') }}
                    @else
                        _________________
                    @endif
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <a href="{{ route('approval.print', $request->id) }}" target="_blank" class="btn btn-success">
                <i class="bi bi-printer"></i> In
            </a>
            <a href="{{ route('approval.show', $request->id) }}" class="btn btn-info">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>
</body>
</html>
