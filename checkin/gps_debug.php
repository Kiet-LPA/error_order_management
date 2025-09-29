<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GPS Debug - So sánh tọa độ</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #007bff 0%, #6610f2 100%);
            color: white;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }
        .card-body { padding: 1.5rem; }
        .info-box {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .success-box {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            color: #2e7d32;
        }
        .error-box {
            background: #ffebee;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            color: #c62828;
        }
        .warning-box {
            background: #fff3e0;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            color: #f57c00;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin: 5px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn:hover { 
            background: #0056b3; 
            transform: translateY(-2px);
        }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #1e7e34; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-warning:hover { background: #e0a800; }
        input[type="number"] {
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            width: 200px;
            margin: 5px;
            font-size: 1rem;
        }
        input[type="number"]:focus {
            border-color: #007bff;
            outline: none;
        }
        .coordinates {
            font-family: 'Courier New', monospace;
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: bold;
            border-left: 4px solid #007bff;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        @media (max-width: 768px) {
            .grid { grid-template-columns: 1fr; }
        }
        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .comparison-table th,
        .comparison-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .comparison-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .accuracy-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1>🔍 GPS Debug - So sánh tọa độ chi tiết</h1>
            </div>
            <div class="card-body">
                <div class="info-box">
                    <h3>📋 Thông tin hệ thống:</h3>
                    <p><strong>Người dùng:</strong> <?= htmlspecialchars($user['full_name']) ?> (<?= htmlspecialchars($user['username']) ?>)</p>
                    <?php if ($user['region_id']): ?>
                        <p><strong>Vùng làm việc:</strong> <?= htmlspecialchars($user['region_name']) ?></p>
                        <p><strong>Tọa độ vùng trong DB:</strong> <span class="coordinates"><?= $user['latitude'] ?>, <?= $user['longitude'] ?></span></p>
                        <p><strong>Bán kính cho phép:</strong> <?= $user['radius_meters'] ?>m</p>
                        <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($user['address'] ?? 'N/A') ?></p>
                    <?php else: ?>
                        <div class="error-box">❌ Chưa được phân công vùng làm việc!</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($user['region_id']): ?>
        <div class="grid">
            <!-- Left: GPS Test -->
            <div class="card">
                <div class="card-header">
                    <h3>📱 Test GPS thực tế</h3>
                </div>
                <div class="card-body">
                    <div class="accuracy-info">
                        <strong>💡 Hướng dẫn:</strong><br>
                        1. Nhấn "Lấy GPS hiện tại" để lấy tọa độ từ điện thoại/máy tính<br>
                        2. Hoặc nhập tọa độ từ Google Maps thủ công<br>
                        3. So sánh với tọa độ vùng làm việc
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <button onclick="getCurrentGPS()" class="btn btn-success">
                            📍 Lấy GPS hiện tại
                        </button>
                        <button onclick="openGoogleMaps()" class="btn btn-warning">
                            🗺️ Mở Google Maps
                        </button>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label><strong>Vĩ độ hiện tại của bạn:</strong></label><br>
                        <input type="number" id="currentLat" placeholder="10.0259" step="0.000001">
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label><strong>Kinh độ hiện tại của bạn:</strong></label><br>
                        <input type="number" id="currentLng" placeholder="105.7692" step="0.000001">
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <button onclick="compareCoordinates()" class="btn">
                            🔍 So sánh tọa độ
                        </button>
                        <button onclick="testCheckin()" class="btn btn-success">
                            ✅ Test điểm danh
                        </button>
                    </div>

                    <div id="gpsAccuracy" style="margin-top: 1rem;"></div>
                </div>
            </div>

            <!-- Right: Results -->
            <div class="card">
                <div class="card-header">
                    <h3>📊 Kết quả so sánh</h3>
                </div>
                <div class="card-body">
                    <div id="comparisonResult">
                        <p style="color: #666; text-align: center; padding: 2rem;">
                            Nhấn "So sánh tọa độ" để xem kết quả
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Test với tọa độ mẫu -->
        <div class="card">
            <div class="card-header">
                <h3>🧪 Test với tọa độ mẫu</h3>
            </div>
            <div class="card-body">
                <p><strong>Thử với các tọa độ này để kiểm tra:</strong></p>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1rem;">
                    <button onclick="setTestCoords(10.0259, 105.7692)" class="btn">
                        📍 Cần Thơ (10.0259, 105.7692)
                    </button>
                    <button onclick="setTestCoords(<?= $user['latitude'] ?>, <?= $user['longitude'] ?>)" class="btn">
                        🎯 Tọa độ vùng DB (<?= $user['latitude'] ?>, <?= $user['longitude'] ?>)
                    </button>
                    <button onclick="setTestCoords(21.0285, 105.8542)" class="btn">
                        📍 Hà Nội (21.0285, 105.8542)
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="employee.php" class="btn">🏠 Về trang chính</a>
            <a href="fix_coordinates.php" class="btn btn-warning">🔧 Sửa tọa độ vùng</a>
        </div>
    </div>

    <script>
        // Calculate distance between two GPS points
        function calculateDistance(lat1, lng1, lat2, lng2) {
            const earthRadius = 6371000; // meters
            
            const lat1Rad = lat1 * Math.PI / 180;
            const lng1Rad = lng1 * Math.PI / 180;
            const lat2Rad = lat2 * Math.PI / 180;
            const lng2Rad = lng2 * Math.PI / 180;
            
            const deltaLat = lat2Rad - lat1Rad;
            const deltaLng = lng2Rad - lng1Rad;
            
            const a = Math.sin(deltaLat/2) * Math.sin(deltaLat/2) +
                     Math.cos(lat1Rad) * Math.cos(lat2Rad) *
                     Math.sin(deltaLng/2) * Math.sin(deltaLng/2);
            
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            
            return earthRadius * c;
        }

        // Get current GPS location
        async function getCurrentGPS() {
            const accuracyDiv = document.getElementById('gpsAccuracy');
            
            if (!navigator.geolocation) {
                accuracyDiv.innerHTML = '<div class="error-box">❌ GPS không được hỗ trợ trên thiết bị này</div>';
                return;
            }

            accuracyDiv.innerHTML = '<div class="info-box">🔄 Đang lấy vị trí GPS chính xác...</div>';

            try {
                const position = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                        enableHighAccuracy: true,
                        timeout: 30000,
                        maximumAge: 0
                    });
                });

                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const accuracy = position.coords.accuracy;
                
                document.getElementById('currentLat').value = lat.toFixed(8);
                document.getElementById('currentLng').value = lng.toFixed(8);
                
                accuracyDiv.innerHTML = `
                    <div class="success-box">
                        <h4>✅ Lấy GPS thành công!</h4>
                        <p><strong>Tọa độ:</strong> <span class="coordinates">${lat.toFixed(8)}, ${lng.toFixed(8)}</span></p>
                        <p><strong>Độ chính xác:</strong> ±${Math.round(accuracy)}m</p>
                        <p><strong>Thời gian:</strong> ${new Date().toLocaleString('vi-VN')}</p>
                        ${accuracy > 50 ? '<p style="color: #f57c00;">⚠️ Độ chính xác thấp, thử di chuyển ra ngoài trời</p>' : ''}
                    </div>
                `;
                
                // Auto compare
                compareCoordinates();
                
            } catch (error) {
                let message = 'Không thể lấy vị trí GPS';
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        message = 'Bạn đã từ chối truy cập GPS. Vui lòng cho phép truy cập vị trí.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        message = 'GPS không khả dụng. Vui lòng kiểm tra GPS và thử lại.';
                        break;
                    case error.TIMEOUT:
                        message = 'Hết thời gian chờ GPS. Vui lòng thử lại.';
                        break;
                }
                accuracyDiv.innerHTML = `<div class="error-box">❌ ${message}</div>`;
            }
        }

        // Compare coordinates
        function compareCoordinates() {
            const currentLat = parseFloat(document.getElementById('currentLat').value);
            const currentLng = parseFloat(document.getElementById('currentLng').value);
            const resultDiv = document.getElementById('comparisonResult');
            
            if (!currentLat || !currentLng) {
                resultDiv.innerHTML = '<div class="error-box">❌ Vui lòng nhập đầy đủ tọa độ</div>';
                return;
            }

            const regionLat = <?= $user['latitude'] ?? 0 ?>;
            const regionLng = <?= $user['longitude'] ?? 0 ?>;
            const maxDistance = <?= $user['radius_meters'] ?? 200 ?>;
            const regionName = '<?= htmlspecialchars($user['region_name'] ?? '') ?>';
            
            const distance = calculateDistance(currentLat, currentLng, regionLat, regionLng);
            const isWithinRange = distance <= maxDistance;
            
            // Create Google Maps comparison URLs
            const yourLocationUrl = `https://www.google.com/maps?q=${currentLat},${currentLng}&t=satellite&z=18`;
            const regionLocationUrl = `https://www.google.com/maps?q=${regionLat},${regionLng}&t=satellite&z=18`;
            const directionsUrl = `https://www.google.com/maps/dir/${regionLat},${regionLng}/${currentLat},${currentLng}`;
            
            resultDiv.innerHTML = `
                <div class="${isWithinRange ? 'success-box' : 'error-box'}">
                    <h4>${isWithinRange ? '✅ TRONG PHẠM VI' : '❌ NGOÀI PHẠM VI'}</h4>
                    <p><strong>Khoảng cách:</strong> ${Math.round(distance)}m</p>
                    <p><strong>Bán kính cho phép:</strong> ${maxDistance}m</p>
                    <p><strong>Chênh lệch:</strong> ${Math.round(distance - maxDistance)}m</p>
                </div>
                
                <table class="comparison-table">
                    <tr>
                        <th>Loại</th>
                        <th>Vĩ độ (Latitude)</th>
                        <th>Kinh độ (Longitude)</th>
                        <th>Thao tác</th>
                    </tr>
                    <tr>
                        <td><strong>Vị trí của bạn</strong></td>
                        <td><span class="coordinates">${currentLat.toFixed(8)}</span></td>
                        <td><span class="coordinates">${currentLng.toFixed(8)}</span></td>
                        <td><a href="${yourLocationUrl}" target="_blank" style="color: #007bff;">🗺️ Xem</a></td>
                    </tr>
                    <tr>
                        <td><strong>Vùng ${regionName}</strong></td>
                        <td><span class="coordinates">${regionLat}</span></td>
                        <td><span class="coordinates">${regionLng}</span></td>
                        <td><a href="${regionLocationUrl}" target="_blank" style="color: #007bff;">🗺️ Xem</a></td>
                    </tr>
                </table>
                
                <div style="margin-top: 1rem; text-align: center;">
                    <a href="${directionsUrl}" target="_blank" class="btn btn-warning">
                        🗺️ So sánh trên Google Maps
                    </a>
                </div>
                
                <div class="warning-box" style="margin-top: 1rem;">
                    <strong>🔍 Phân tích:</strong><br>
                    • <strong>Chênh lệch vĩ độ:</strong> ${Math.abs(currentLat - regionLat).toFixed(8)}°<br>
                    • <strong>Chênh lệch kinh độ:</strong> ${Math.abs(currentLng - regionLng).toFixed(8)}°<br>
                    • <strong>Khoảng cách tính toán:</strong> ${distance.toFixed(2)}m<br>
                    ${distance > 1000 ? '⚠️ <strong>Khoảng cách quá lớn - có thể do tọa độ vùng sai!</strong>' : ''}
                </div>
            `;
        }

        // Test checkin
        function testCheckin() {
            const currentLat = parseFloat(document.getElementById('currentLat').value);
            const currentLng = parseFloat(document.getElementById('currentLng').value);
            
            if (!currentLat || !currentLng) {
                alert('Vui lòng nhập tọa độ trước!');
                return;
            }
            
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'employee.php';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'checkin';
            
            const latInput = document.createElement('input');
            latInput.type = 'hidden';
            latInput.name = 'latitude';
            latInput.value = currentLat;
            
            const lngInput = document.createElement('input');
            lngInput.type = 'hidden';
            lngInput.name = 'longitude';
            lngInput.value = currentLng;
            
            form.appendChild(actionInput);
            form.appendChild(latInput);
            form.appendChild(lngInput);
            
            document.body.appendChild(form);
            form.submit();
        }

        // Set test coordinates
        function setTestCoords(lat, lng) {
            document.getElementById('currentLat').value = lat;
            document.getElementById('currentLng').value = lng;
            compareCoordinates();
        }

        // Open Google Maps
        function openGoogleMaps() {
            const regionLat = <?= $user['latitude'] ?? 0 ?>;
            const regionLng = <?= $user['longitude'] ?? 0 ?>;
            const url = `https://www.google.com/maps?q=${regionLat},${regionLng}&t=satellite&z=18`;
            window.open(url, '_blank');
        }
    </script>
</body>
</html>
