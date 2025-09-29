<?php
require_once 'config.php';

// Debug GPS coordinates and distance calculation
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
    <title>Debug GPS - Kiểm tra tọa độ</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            padding: 20px; 
            background: #f5f5f5; 
        }
        .container { 
            max-width: 800px; 
            margin: 0 auto; 
            background: white; 
            padding: 20px; 
            border-radius: 10px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .info-box { 
            background: #e3f2fd; 
            padding: 15px; 
            border-radius: 8px; 
            margin: 10px 0; 
        }
        .error-box { 
            background: #ffebee; 
            padding: 15px; 
            border-radius: 8px; 
            margin: 10px 0; 
            color: #c62828;
        }
        .success-box { 
            background: #e8f5e8; 
            padding: 15px; 
            border-radius: 8px; 
            margin: 10px 0; 
            color: #2e7d32;
        }
        code { 
            background: #f5f5f5; 
            padding: 2px 6px; 
            border-radius: 3px; 
            font-family: 'Courier New', monospace;
        }
        .btn {
            background: #2196f3;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        .btn:hover { background: #1976d2; }
        input[type="number"] {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 200px;
            margin: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Debug GPS - Kiểm tra tọa độ</h1>
        
        <div class="info-box">
            <h3>📋 Thông tin người dùng hiện tại:</h3>
            <p><strong>Tên:</strong> <?= htmlspecialchars($user['full_name']) ?></p>
            <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
            <p><strong>Vai trò:</strong> <?= htmlspecialchars($user['role_name']) ?></p>
            <?php if ($user['region_id']): ?>
                <p><strong>Vùng làm việc:</strong> <?= htmlspecialchars($user['region_name']) ?></p>
                <p><strong>Tọa độ vùng:</strong> <code><?= $user['latitude'] ?>, <?= $user['longitude'] ?></code></p>
                <p><strong>Bán kính cho phép:</strong> <?= $user['radius_meters'] ?>m</p>
                <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($user['address'] ?? 'Không có') ?></p>
                
                <a href="https://www.google.com/maps?q=<?= $user['latitude'] ?>,<?= $user['longitude'] ?>&t=satellite&z=18" 
                   target="_blank" class="btn">
                    🗺️ Xem vùng trên Google Maps
                </a>
            <?php else: ?>
                <div class="error-box">
                    ❌ <strong>Chưa được phân công vùng làm việc!</strong>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($user['region_id']): ?>
        <div class="info-box">
            <h3>🧮 Test tính khoảng cách:</h3>
            <p>Nhập tọa độ hiện tại của bạn để kiểm tra khoảng cách:</p>
            
            <div>
                <label>Vĩ độ của bạn:</label>
                <input type="number" id="testLat" placeholder="Ví dụ: 10.031121" step="0.000001">
            </div>
            <div>
                <label>Kinh độ của bạn:</label>
                <input type="number" id="testLng" placeholder="Ví dụ: 105.787546" step="0.000001">
            </div>
            <div>
                <button onclick="testDistance()" class="btn">📏 Tính khoảng cách</button>
                <button onclick="getMyLocation()" class="btn" style="background: #4caf50;">📱 Lấy vị trí hiện tại</button>
            </div>
            
            <div id="result" style="margin-top: 15px;"></div>
        </div>

        <div class="info-box">
            <h3>🗺️ Tọa độ mẫu để test:</h3>
            <p><strong>Kiến Thành, An Giang:</strong> <code>10.031121, 105.787546</code> 
               <button onclick="setCoords(10.031121, 105.787546)" class="btn" style="font-size: 12px; padding: 4px 8px;">Sử dụng</button></p>
            <p><strong>Hà Nội:</strong> <code>21.0285, 105.8542</code> 
               <button onclick="setCoords(21.0285, 105.8542)" class="btn" style="font-size: 12px; padding: 4px 8px;">Sử dụng</button></p>
            <p><strong>TP.HCM:</strong> <code>10.8231, 106.6297</code> 
               <button onclick="setCoords(10.8231, 106.6297)" class="btn" style="font-size: 12px; padding: 4px 8px;">Sử dụng</button></p>
        </div>
        <?php endif; ?>

        <div class="info-box">
            <h3>🔗 Liên kết hữu ích:</h3>
            <a href="employee.php" class="btn">🏠 Về trang chính</a>
            <a href="https://www.google.com/maps" target="_blank" class="btn" style="background: #ff9800;">🗺️ Mở Google Maps</a>
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

        function testDistance() {
            const testLat = parseFloat(document.getElementById('testLat').value);
            const testLng = parseFloat(document.getElementById('testLng').value);
            const resultDiv = document.getElementById('result');
            
            if (!testLat || !testLng) {
                resultDiv.innerHTML = '<div class="error-box">❌ Vui lòng nhập đầy đủ tọa độ</div>';
                return;
            }

            const regionLat = <?= $user['latitude'] ?? 0 ?>;
            const regionLng = <?= $user['longitude'] ?? 0 ?>;
            const maxDistance = <?= $user['radius_meters'] ?? 200 ?>;
            
            const distance = calculateDistance(testLat, testLng, regionLat, regionLng);
            const isWithinRange = distance <= maxDistance;
            
            resultDiv.innerHTML = `
                <div class="${isWithinRange ? 'success-box' : 'error-box'}">
                    <h4>${isWithinRange ? '✅ TRONG PHẠM VI' : '❌ NGOÀI PHẠM VI'}</h4>
                    <p><strong>Khoảng cách tính được:</strong> ${Math.round(distance)}m</p>
                    <p><strong>Bán kính cho phép:</strong> ${maxDistance}m</p>
                    <p><strong>Chênh lệch:</strong> ${Math.round(distance - maxDistance)}m</p>
                    <hr style="margin: 10px 0;">
                    <p><strong>Tọa độ vùng:</strong> <code>${regionLat}, ${regionLng}</code></p>
                    <p><strong>Tọa độ của bạn:</strong> <code>${testLat}, ${testLng}</code></p>
                    <p><strong>So sánh trên Maps:</strong> 
                       <a href="https://www.google.com/maps/dir/${regionLat},${regionLng}/${testLat},${testLng}" target="_blank" style="color: #1976d2;">
                           🗺️ Xem đường đi
                       </a>
                    </p>
                </div>
            `;
        }

        function setCoords(lat, lng) {
            document.getElementById('testLat').value = lat;
            document.getElementById('testLng').value = lng;
            testDistance();
        }

        async function getMyLocation() {
            const resultDiv = document.getElementById('result');
            
            if (!navigator.geolocation) {
                resultDiv.innerHTML = '<div class="error-box">❌ GPS không được hỗ trợ</div>';
                return;
            }

            resultDiv.innerHTML = '<div class="info-box">🔄 Đang lấy vị trí GPS...</div>';

            try {
                const position = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                        enableHighAccuracy: true,
                        timeout: 15000,
                        maximumAge: 30000
                    });
                });

                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                document.getElementById('testLat').value = lat.toFixed(6);
                document.getElementById('testLng').value = lng.toFixed(6);
                
                testDistance();
                
            } catch (error) {
                resultDiv.innerHTML = `<div class="error-box">❌ Lỗi GPS: ${error.message}</div>`;
            }
        }
    </script>
</body>
</html>
