<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $formConfig->form_name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
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
        .form-info {
            margin-bottom: 20px;
        }
        .form-field {
            margin-bottom: 15px;
            page-break-inside: avoid;
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
            font-size: 10px;
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
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="form-title">{{ $request->form_data['title'] ?? $formConfig->form_name }}</div>
        <div>Mã đề xuất: {{ $request->id }}</div>
        <div>Ngày tạo: {{ $request->created_at->format('d/m/Y H:i') }}</div>
        <div>Người tạo: {{ $request->creator->name }}</div>
    </div>

    <div class="form-info">
        @foreach($formConfig->form_fields as $field)
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

    <div class="footer">
        <p>Được tạo bởi hệ thống phê duyệt đề xuất - {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
