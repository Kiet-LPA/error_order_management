<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Điểm danh - HP Foods</title>
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
        }
        .card-body { padding: 1.5rem; }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .status-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            border: 2px solid #e9ecef;
        }
        .status-card.success {
            background: rgba(46, 125, 50, 0.1);
            border-color: #2E7D32;
            color: #1B5E20;
        }
        .status-card.warning {
            background: rgba(76, 175, 80, 0.1);
            border-color: #4CAF50;
            color: #2E7D32;
        }
        .status-card.info {
            background: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
        .checkin-btn {
            background: #2E7D32;
            color: white;
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            margin: 1rem 0;
            transition: all 0.3s ease;
        }
        .checkin-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        .checkin-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .alert-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        .alert-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        .alert-success {
            background: rgba(46, 125, 50, 0.1);
            border: 1px solid #2E7D32;
            color: #1B5E20;
        }
        .region-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .history-link {
            display: inline-block;
            background: #6c757d;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 1rem;
        }
        .history-link:hover {
            background: #5a6268;
            color: white;
        }
        @media (max-width: 768px) {
            .header .container {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }
            .status-grid {
                grid-template-columns: 1fr;
            }
            .card-body {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="logo">🏢 HP Foods - Điểm danh</div>
            <div class="user-info">
                <span>Xin chào, {{ $user->name }}</span>
                <a href="{{ route('kanban') }}" class="logout-btn">← Quay lại</a>
            </div>
        </div>
    </div>

    <div class="container">
        @if($session)
            <div class="alert alert-info">
                ⏰ Thời gian điểm danh ca {{ $session === 'morning' ? 'sáng' : 'chiều' }}: 
                {{ $session === 'morning' ? '4:00 - 11:00' : '13:00 - 20:00' }}
            </div>
        @else
            <div class="alert alert-warning">
                ⚠️ Hiện tại không trong thời gian điểm danh.
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                📍 Khu vực: {{ $department->name }}
            </div>
            <div class="card-body">
                <div class="region-info">
                    <strong>📍 Địa chỉ:</strong> {{ $department->address }}<br>
                    <strong>📏 Bán kính cho phép:</strong> {{ $department->radius_meters }}m
                </div>

                <div class="status-grid">
                    @if($todayCheckins->where('session', 'morning')->first())
                        <div class="status-card success">
                            <h4>✅ Ca sáng</h4>
                            <p>Đã điểm danh</p>
                            <small>{{ $todayCheckins->where('session', 'morning')->first()->checkin_time->format('H:i:s') }}</small>
                        </div>
                    @else
                        <div class="status-card {{ $session === 'morning' ? 'warning' : 'info' }}">
                            <h4>🌅 Ca sáng</h4>
                            <p>{{ $session === 'morning' ? 'Chưa điểm danh' : 'Chưa đến giờ' }}</p>
                        </div>
                    @endif

                    @if($todayCheckins->where('session', 'evening')->first())
                        <div class="status-card success">
                            <h4>✅ Ca chiều</h4>
                            <p>Đã điểm danh</p>
                            <small>{{ $todayCheckins->where('session', 'evening')->first()->checkin_time->format('H:i:s') }}</small>
                        </div>
                    @else
                        <div class="status-card {{ $session === 'evening' ? 'warning' : 'info' }}">
                            <h4>🌆 Ca chiều</h4>
                            <p>{{ $session === 'evening' ? 'Chưa điểm danh' : 'Chưa đến giờ' }}</p>
                        </div>
                    @endif
                </div>

                @if($gpsRequest)
                    <div class="alert alert-warning">
                        <strong>📡 Yêu cầu GPS:</strong> {{ $gpsRequest->gps_code }}<br>
                        <strong>📏 Khoảng cách:</strong> {{ round($gpsRequest->distance_meters) }}m<br>
                        <strong>📊 Trạng thái:</strong> 
                        @if($gpsRequest->status === 'pending')
                            Chờ duyệt
                        @elseif($gpsRequest->status === 'approved')
                            Đã duyệt
                        @else
                            Bị từ chối
                        @endif
                        @if($gpsRequest->admin_notes)
                            <br><strong>📝 Ghi chú:</strong> {{ $gpsRequest->admin_notes }}
                        @endif
                    </div>
                @endif

                @if($session && !$currentSessionCheckin)
                    <button id="checkinBtn" class="checkin-btn" onclick="getLocation()">
                        📍 Điểm danh ca {{ $session === 'morning' ? 'sáng' : 'chiều' }}
                    </button>
                    
                    <!-- GPS Instructions -->
                    <div id="gpsInstructions" class="alert alert-info" style="display: none;">
                        <h6>🔄 Đang lấy vị trí GPS...</h6>
                        <p>Vui lòng cho phép truy cập vị trí khi trình duyệt hỏi.</p>
                        <p><strong>Lưu ý:</strong> Để có kết quả chính xác nhất, hãy:</p>
                        <ul>
                            <li>Di chuyển ra ngoài trời</li>
                            <li>Bật GPS/WiFi trên thiết bị</li>
                            <li>Chờ tín hiệu ổn định</li>
                        </ul>
                    </div>
                    
                    <!-- GPS Error Display -->
                    <div id="gpsError" class="alert alert-danger" style="display: none;">
                        <!-- Error content will be inserted here by JavaScript -->
                    </div>
                @elseif($currentSessionCheckin)
                    <div class="alert alert-success">
                        ✅ Bạn đã điểm danh ca {{ $session === 'morning' ? 'sáng' : 'chiều' }} hôm nay.
                    </div>
                @endif

                <div style="text-align: center;">
                    <a href="{{ route('checkin.history') }}" class="history-link">
                        📋 Xem lịch sử điểm danh
                    </a>
                    <a href="{{ route('checkin.gps-help') }}" class="history-link" style="background: #ffc107; color: #212529; margin-left: 1rem;">
                        🔧 Hướng dẫn GPS
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
    function getLocation() {
        if (!navigator.geolocation) {
            showGpsError('Trình duyệt không hỗ trợ định vị GPS.');
            return;
        }

        const btn = document.getElementById('checkinBtn');
        btn.disabled = true;
        btn.innerHTML = '⏳ Đang lấy vị trí...';
        
        // Hiển thị hướng dẫn GPS
        showGpsInstructions();
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                hideGpsInstructions();
                checkin(position.coords.latitude, position.coords.longitude);
            },
            function(error) {
                btn.disabled = false;
                btn.innerHTML = '📍 Điểm danh ca {{ $session === "morning" ? "sáng" : "chiều" }}';
                hideGpsInstructions();
                
                let message = 'Không thể lấy vị trí GPS. ';
                let instructions = '';
                
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        message += 'Bạn đã từ chối truy cập GPS.';
                        instructions = `
                            <div class="alert alert-danger mt-3">
                                <h6>🔧 Cách khắc phục:</h6>
                                <p>Bạn đã từ chối truy cập GPS. Để cho phép GPS:</p>
                                <ol>
                                    <li><strong>Chrome/Edge:</strong> Nhấp vào icon 🔒 hoặc 🛡️ bên trái địa chỉ trang web → Cho phép "Vị trí"</li>
                                    <li><strong>Firefox:</strong> Nhấp vào icon 🛡️ bên trái địa chỉ trang web → Cho phép "Vị trí"</li>
                                    <li><strong>Safari:</strong> Safari → Tùy chọn → Bảo mật → Cho phép "Vị trí"</li>
                                    <li><strong>Mobile:</strong> Cài đặt → Quyền ứng dụng → Trình duyệt → Vị trí → Cho phép</li>
                                </ol>
                                <p><strong>Sau đó nhấn F5 để tải lại trang và thử lại.</strong></p>
                                <div class="mt-3">
                                    <a href="{{ route('checkin.gps-help') }}" class="btn btn-warning btn-sm">
                                        📖 Xem hướng dẫn chi tiết
                                    </a>
                                </div>
                            </div>
                        `;
                        break;
                    case error.POSITION_UNAVAILABLE:
                        message += 'Vị trí không khả dụng.';
                        instructions = `
                            <div class="alert alert-warning mt-3">
                                <h6>💡 Gợi ý:</h6>
                                <ul>
                                    <li>Kiểm tra GPS/WiFi đã bật chưa</li>
                                    <li>Di chuyển ra ngoài trời để có tín hiệu GPS tốt hơn</li>
                                    <li>Thử lại sau vài phút</li>
                                </ul>
                            </div>
                        `;
                        break;
                    case error.TIMEOUT:
                        message += 'Hết thời gian chờ lấy vị trí.';
                        instructions = `
                            <div class="alert alert-info mt-3">
                                <h6>⏰ Thử lại:</h6>
                                <p>Hệ thống đang tìm kiếm vị trí chính xác. Vui lòng thử lại.</p>
                            </div>
                        `;
                        break;
                    default:
                        message += 'Lỗi không xác định.';
                }
                
                showGpsError(message + instructions);
            },
            {
                enableHighAccuracy: true,
                timeout: 30000, // Tăng timeout lên 30 giây
                maximumAge: 0 // Luôn lấy vị trí mới
            }
        );
    }

    function showGpsInstructions() {
        const instructionsDiv = document.getElementById('gpsInstructions');
        if (instructionsDiv) {
            instructionsDiv.style.display = 'block';
        }
    }

    function hideGpsInstructions() {
        const instructionsDiv = document.getElementById('gpsInstructions');
        if (instructionsDiv) {
            instructionsDiv.style.display = 'none';
        }
    }

    function showGpsError(message) {
        const errorDiv = document.getElementById('gpsError');
        if (errorDiv) {
            errorDiv.innerHTML = message;
            errorDiv.style.display = 'block';
        } else {
            alert(message.replace(/<[^>]*>/g, '')); // Fallback nếu không có div
        }
    }

    function checkin(latitude, longitude) {
        const btn = document.getElementById('checkinBtn');
        btn.innerHTML = '⏳ Đang xử lý...';
        
        fetch('{{ route("checkin.checkin") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                latitude: latitude,
                longitude: longitude
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message);
                btn.disabled = false;
                btn.innerHTML = '📍 Điểm danh ca {{ $session === "morning" ? "sáng" : "chiều" }}';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi điểm danh.');
            btn.disabled = false;
            btn.innerHTML = '📍 Điểm danh ca {{ $session === "morning" ? "sáng" : "chiều" }}';
        });
    }
    </script>
</body>
</html>
