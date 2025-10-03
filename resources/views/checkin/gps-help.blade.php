<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hướng dẫn GPS - HP Foods</title>
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
        .back-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .back-btn:hover { background: rgba(255,255,255,0.3); }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
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
        .browser-section {
            margin-bottom: 2rem;
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
        }
        .browser-section h3 {
            color: #495057;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .step {
            margin-bottom: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .step strong {
            color: #007bff;
        }
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .alert-danger {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .alert-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        .test-btn {
            background: #198754;
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
        .test-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        .test-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }
        @media (max-width: 768px) {
            .header .container {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="logo">🔧 Hướng dẫn GPS - HP Foods</div>
            <a href="{{ route('checkin.index') }}" class="back-btn">← Quay lại</a>
        </div>
    </div>

    <div class="container">
        <div class="alert alert-info">
            <h5>📍 Tại sao cần GPS?</h5>
            <p>Hệ thống điểm danh cần vị trí GPS để xác minh bạn đang ở đúng khu vực làm việc. Đây là biện pháp bảo mật để đảm bảo tính chính xác của việc điểm danh.</p>
        </div>

        <div class="card">
            <div class="card-header">
                <h4>🔧 Cách cho phép GPS trên các trình duyệt</h4>
            </div>
            <div class="card-body">
                <!-- Chrome/Edge -->
                <div class="browser-section">
                    <h3>🌐 Google Chrome / Microsoft Edge</h3>
                    <div class="step">
                        <strong>Bước 1:</strong> Nhấp vào icon 🔒 hoặc 🛡️ bên trái địa chỉ trang web
                    </div>
                    <div class="step">
                        <strong>Bước 2:</strong> Chọn "Vị trí" → "Cho phép"
                    </div>
                    <div class="step">
                        <strong>Bước 3:</strong> Nhấn F5 để tải lại trang và thử lại
                    </div>
                </div>

                <!-- Firefox -->
                <div class="browser-section">
                    <h3>🦊 Mozilla Firefox</h3>
                    <div class="step">
                        <strong>Bước 1:</strong> Nhấp vào icon 🛡️ bên trái địa chỉ trang web
                    </div>
                    <div class="step">
                        <strong>Bước 2:</strong> Chọn "Vị trí" → "Cho phép"
                    </div>
                    <div class="step">
                        <strong>Bước 3:</strong> Nhấn F5 để tải lại trang và thử lại
                    </div>
                </div>

                <!-- Safari -->
                <div class="browser-section">
                    <h3>🧭 Safari (Mac)</h3>
                    <div class="step">
                        <strong>Bước 1:</strong> Safari → Tùy chọn (Preferences)
                    </div>
                    <div class="step">
                        <strong>Bước 2:</strong> Chọn tab "Bảo mật" (Security)
                    </div>
                    <div class="step">
                        <strong>Bước 3:</strong> Tìm "Vị trí" (Location) → Chọn "Cho phép"
                    </div>
                </div>

                <!-- Mobile -->
                <div class="browser-section">
                    <h3>📱 Trên điện thoại (Android/iOS)</h3>
                    <div class="step">
                        <strong>Bước 1:</strong> Cài đặt → Quyền ứng dụng (App Permissions)
                    </div>
                    <div class="step">
                        <strong>Bước 2:</strong> Chọn trình duyệt bạn đang dùng
                    </div>
                    <div class="step">
                        <strong>Bước 3:</strong> Tìm "Vị trí" (Location) → Cho phép
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4>🧪 Kiểm tra GPS</h4>
            </div>
            <div class="card-body">
                <p>Nhấn nút bên dưới để kiểm tra xem GPS đã hoạt động chưa:</p>
                
                <button id="testGpsBtn" class="test-btn" onclick="testGPS()">
                    🧪 Kiểm tra GPS
                </button>
                
                <div id="gpsTestResult" style="display: none;"></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4>❓ Các vấn đề thường gặp</h4>
            </div>
            <div class="card-body">
                <div class="step">
                    <strong>Vấn đề:</strong> GPS không chính xác<br>
                    <strong>Giải pháp:</strong> Di chuyển ra ngoài trời, tránh các tòa nhà cao tầng
                </div>
                <div class="step">
                    <strong>Vấn đề:</strong> Tín hiệu yếu<br>
                    <strong>Giải pháp:</strong> Bật WiFi và GPS, chờ vài phút để tín hiệu ổn định
                </div>
                <div class="step">
                    <strong>Vấn đề:</strong> Vẫn không hoạt động<br>
                    <strong>Giải pháp:</strong> Thử trình duyệt khác hoặc khởi động lại thiết bị
                </div>
            </div>
        </div>
    </div>

    <script>
    function testGPS() {
        const btn = document.getElementById('testGpsBtn');
        const resultDiv = document.getElementById('gpsTestResult');
        
        btn.disabled = true;
        btn.innerHTML = '⏳ Đang kiểm tra GPS...';
        resultDiv.style.display = 'block';
        resultDiv.innerHTML = '<div class="alert alert-info">🔄 Đang lấy vị trí GPS...</div>';

        if (!navigator.geolocation) {
            resultDiv.innerHTML = '<div class="alert alert-danger">❌ Trình duyệt không hỗ trợ GPS</div>';
            btn.disabled = false;
            btn.innerHTML = '🧪 Kiểm tra GPS';
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const accuracy = position.coords.accuracy;
                
                resultDiv.innerHTML = `
                    <div class="alert alert-success">
                        <h6>✅ GPS hoạt động bình thường!</h6>
                        <p><strong>Vĩ độ:</strong> ${lat.toFixed(6)}</p>
                        <p><strong>Kinh độ:</strong> ${lng.toFixed(6)}</p>
                        <p><strong>Độ chính xác:</strong> ±${Math.round(accuracy)}m</p>
                        <p><strong>Thời gian:</strong> ${new Date().toLocaleString('vi-VN')}</p>
                        ${accuracy > 50 ? '<p class="text-warning">⚠️ Độ chính xác thấp, thử di chuyển ra ngoài trời</p>' : ''}
                        
                        <div class="mt-3">
                            <a href="{{ route('checkin.index') }}" class="test-btn" style="background: #007bff;">
                                🏠 Quay lại điểm danh
                            </a>
                        </div>
                    </div>
                `;
                btn.disabled = false;
                btn.innerHTML = '🧪 Kiểm tra lại GPS';
            },
            function(error) {
                let message = 'Không thể lấy vị trí GPS. ';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        message += 'Bạn chưa cho phép truy cập GPS. Vui lòng làm theo hướng dẫn ở trên.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        message += 'Vị trí không khả dụng. Hãy kiểm tra GPS/WiFi và di chuyển ra ngoài trời.';
                        break;
                    case error.TIMEOUT:
                        message += 'Hết thời gian chờ. Vui lòng thử lại.';
                        break;
                    default:
                        message += 'Lỗi không xác định.';
                }
                
                resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <h6>❌ ${message}</h6>
                        <p>Vui lòng làm theo hướng dẫn ở trên để khắc phục.</p>
                    </div>
                `;
                btn.disabled = false;
                btn.innerHTML = '🧪 Thử lại';
            },
            {
                enableHighAccuracy: true,
                timeout: 30000,
                maximumAge: 0
            }
        );
    }
    </script>
</body>
</html>
