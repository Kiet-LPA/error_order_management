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
            display: block;
            overflow: hidden;
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
        .alert-success {
            background: #d1e7dd;
            border: 1px solid #badbcc;
            color: #0f5132;
        }
        .alert-success h6 {
            margin: 0 0 0.75rem;
            font-size: 1.05rem;
        }
        .alert-success p {
            margin: 0 0 0.5rem;
        }
        .alert-success p:last-child {
            margin-bottom: 0;
        }
        .gps-result-box {
            margin-top: 0.5rem;
            clear: both;
        }
        .gps-result-meta {
            font-size: 0.9rem;
            opacity: 0.85;
            margin: 0.25rem 0 0.75rem;
        }
        .gps-result-place {
            font-size: 1.15rem;
            font-weight: 700;
            margin: 0.35rem 0 0.5rem;
            line-height: 1.35;
            word-break: break-word;
        }
        .gps-result-warn {
            color: #856404;
            background: #fff3cd;
            border: 1px solid #ffecb5;
            border-radius: 6px;
            padding: 0.5rem 0.75rem;
            margin: 0.75rem 0;
        }
        .gps-result-actions {
            margin-top: 1rem;
            padding-top: 0.25rem;
        }
        .test-btn {
            display: block;
            background: #198754;
            color: white;
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            margin: 1rem 0;
            text-align: center;
            text-decoration: none;
            transition: background 0.2s ease, box-shadow 0.2s ease;
        }
        .test-btn:hover {
            background: #157347;
            box-shadow: 0 4px 12px rgba(25, 135, 84, 0.25);
        }
        .test-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            box-shadow: none;
        }
        .test-btn-secondary {
            background: #0d6efd;
        }
        .test-btn-secondary:hover {
            background: #0b5ed7;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.25);
        }
        @media (max-width: 768px) {
            .header .container {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }
            .test-btn {
                padding: 0.85rem 1rem;
                font-size: 1rem;
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
                
                <button type="button" id="testGpsBtn" class="test-btn" onclick="testGPS()">
                    🧪 Kiểm tra GPS
                </button>
                
                <div id="gpsTestResult" class="gps-result-box" style="display: none;"></div>
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
    window.LOCATION_NAME_API = @json(route('api.location-name'));
    </script>
    <script src="{{ asset('js/location-name.js') }}?v=5"></script>
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
                const checkinUrl = @json(route('checkin.index'));

                resultDiv.innerHTML = `
                    <div class="alert alert-success">
                        <h6>✅ GPS hoạt động bình thường!</h6>
                        <div class="gps-result-place" id="gpsHelpPlaceName">Đang tải tên vị trí...</div>
                        <p class="gps-result-meta">Vĩ độ: ${lat.toFixed(6)} · Kinh độ: ${lng.toFixed(6)}</p>
                        <p><strong>Độ chính xác:</strong> ±${Math.round(accuracy)}m</p>
                        <p><strong>Thời gian:</strong> ${new Date().toLocaleString('vi-VN')}</p>
                        ${accuracy > 50
                            ? '<div class="gps-result-warn">⚠️ Độ chính xác thấp, hãy di chuyển ra ngoài trời</div>'
                            : ''}
                        <div class="gps-result-actions">
                            <a href="${checkinUrl}" class="test-btn test-btn-secondary">🏠 Quay lại điểm danh</a>
                        </div>
                    </div>
                `;

                const placeEl = document.getElementById('gpsHelpPlaceName');
                const fallback = lat.toFixed(5) + ', ' + lng.toFixed(5);
                if (window.LocationName && window.LocationName.resolve) {
                    window.LocationName.resolve(lat, lng).then(function (name) {
                        if (placeEl) placeEl.textContent = name || fallback;
                    }).catch(function () {
                        if (placeEl) placeEl.textContent = fallback;
                    });
                } else if (placeEl) {
                    placeEl.textContent = fallback;
                }

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
