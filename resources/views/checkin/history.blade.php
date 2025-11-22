<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử điểm danh - HP Foods</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            line-height: 1.6;
        }
        .header {
            background: #198754;
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo { font-size: 1.5rem; font-weight: 700; }
        .user-info { display: flex; align-items: center; gap: 1rem; }
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .logout-btn:hover { background: rgba(255,255,255,0.3); }
        .container {
            max-width: 1200px;
            margin: 1rem auto;
            padding: 0 1rem;
        }
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        .card-header {
            background: #198754;
            color: white;
            padding: 1rem 1.5rem;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-body { padding: 1.5rem; }
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .history-table th,
        .history-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        .history-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .history-table tr:hover {
            background-color: #f8f9fa;
        }
        .session-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .session-morning {
            background-color: #cce5ff;
            color: #0066cc;
        }
        .session-evening {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .status-success {
            background-color: #d4edda;
            color: #155724;
        }
        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
        }
        .back-btn {
            background: #6c757d;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .back-btn:hover {
            background: #5a6268;
            color: white;
        }
        .no-data {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
        }
        .pagination a,
        .pagination span {
            padding: 0.5rem 0.75rem;
            margin: 0 0.25rem;
            border: 1px solid #dee2e6;
            color: #007bff;
            text-decoration: none;
            border-radius: 4px;
        }
        .pagination .active span {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        @media (max-width: 768px) {
            .header .container {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }
            .card-header {
                flex-direction: column;
                gap: 0.5rem;
            }
            .history-table {
                font-size: 0.875rem;
            }
            .history-table th,
            .history-table td {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="logo">🏢 HP Foods - Lịch sử điểm danh</div>
            <div class="user-info">
                <span>Xin chào, {{ auth()->user()->display_name }}</span>
                <a href="{{ route('checkin.index') }}" class="logout-btn">← Quay lại</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3>📋 Lịch sử điểm danh</h3>
                <a href="{{ route('checkin.index') }}" class="back-btn">Quay lại điểm danh</a>
            </div>
            <div class="card-body">
                @if($checkins->count() > 0)
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Ngày</th>
                                <th>Ca</th>
                                <th>Thời gian</th>
                                <th>Khu vực</th>
                                <th>Vị trí</th>
                                <th>Khoảng cách</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($checkins as $checkin)
                                <tr>
                                    <td>{{ $checkin->checkin_date->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="session-badge session-{{ $checkin->session }}">
                                            {{ $checkin->session === 'checkin' ? '📍 Điểm danh' : '🚪 Kết thúc ca' }}
                                        </span>
                                    </td>
                                    <td>{{ $checkin->checkin_time->format('H:i:s') }}</td>
                                    <td>{{ $checkin->department->name }}</td>
                                    <td>
                                        <small>{{ $checkin->latitude }}, {{ $checkin->longitude }}</small>
                                    </td>
                                    <td>{{ round($checkin->distance_meters) }}m</td>
                                    <td>
                                        @if($checkin->status === 'success')
                                            <span class="status-badge status-success">✅ Thành công</span>
                                        @else
                                            <span class="status-badge status-failed">❌ Thất bại</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="pagination">
                        {{ $checkins->links() }}
                    </div>
                @else
                    <div class="no-data">
                        <h4>📋 Bạn chưa có lịch sử điểm danh nào</h4>
                        <p>Hãy bắt đầu điểm danh để xem lịch sử tại đây!</p>
                        <a href="{{ route('checkin.index') }}" class="back-btn" style="margin-top: 1rem; display: inline-block;">
                            Điểm danh ngay
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
