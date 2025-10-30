<?php
require_once 'config.php';

// Check if user is logged in and is employee
if (!isLoggedIn() || !in_array($_SESSION['role'], ['employee', 'manager', 'admin'])) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$message = '';
$messageType = '';

// Handle check-in
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'checkin') {
    $latitude = floatval($_POST['latitude'] ?? 0);
    $longitude = floatval($_POST['longitude'] ?? 0);
    
    if ($latitude && $longitude) {
        $session = getCurrentSession();
        
        if ($session && $user['region_id']) {
            $today = date('Y-m-d');
            
            // Check if already checked in successfully (only success records count)
            $db = getDB();
            $stmt = $db->prepare("SELECT id FROM checkins WHERE user_id = ? AND checkin_date = ? AND session = ? AND status = 'success'");
            $stmt->execute([$user['id'], $today, $session]);
            
            if ($stmt->rowCount() === 0) {
                // Calculate distance
                $distance = calculateDistance($latitude, $longitude, $user['latitude'], $user['longitude']);
                $maxDistance = $user['radius_meters'] ?? 200;
                
                if ($distance <= $maxDistance) {
                    // SUCCESS: Save to database (update if exists)
                    $stmt = $db->prepare("
                        INSERT INTO checkins (user_id, region_id, checkin_date, session, latitude, longitude, distance_meters, ip_address, status, checkin_time)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'success', NOW())
                        ON DUPLICATE KEY UPDATE
                        latitude = VALUES(latitude),
                        longitude = VALUES(longitude), 
                        distance_meters = VALUES(distance_meters),
                        ip_address = VALUES(ip_address),
                        status = 'success',
                        checkin_time = NOW()
                    ");
                    
                    $stmt->execute([
                        $user['id'],
                        $user['region_id'],
                        $today,
                        $session,
                        $latitude,
                        $longitude,
                        $distance,
                        $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
                    ]);
                    
                    $message = "Điểm danh thành công! Khoảng cách: " . round($distance, 2) . "m";
                    $messageType = 'success';
                } else {
                    // FAILED: Don't save to database, just show error
                    $gpsCode = generateGPSCode($user['id'], $user['region_id']);
                    sendGPSRequest($user['id'], $user['region_id'], $distance, $gpsCode, $latitude, $longitude, $session);
                    
                    $message = "Điểm danh thất bại! Bạn đang ở ngoài phạm vi cho phép.<br>";
                    $message .= "Khoảng cách: " . round($distance, 2) . "m (Tối đa: {$maxDistance}m)<br>";
                    $message .= "<strong>Mã GPS của bạn: {$gpsCode}</strong><br>";
                    $message .= "Hãy đến đúng vị trí làm việc và thử lại.";
                    $messageType = 'error';
                }
            } else {
                // Already checked in successfully
                $message = "Bạn đã điểm danh " . ($session === 'morning' ? 'sáng' : 'chiều') . " thành công hôm nay rồi!";
                $messageType = 'success';
            }
        } else {
            $message = "Ngoài giờ điểm danh hoặc chưa được phân công vùng!";
            $messageType = 'error';
        }
    } else {
        $message = "Không thể lấy vị trí GPS!";
        $messageType = 'error';
    }
}

// Get today's successful check-ins only
$today = date('Y-m-d');
$db = getDB();
$stmt = $db->prepare("SELECT * FROM checkins WHERE user_id = ? AND checkin_date = ? AND status = 'success' ORDER BY session");
$stmt->execute([$user['id'], $today]);
$todayCheckins = $stmt->fetchAll();

$morningCheckin = null;
$eveningCheckin = null;

foreach ($todayCheckins as $checkin) {
    if ($checkin['session'] === 'morning') {
        $morningCheckin = $checkin;
    } else {
        $eveningCheckin = $checkin;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Nhân viên - Checkin HP Foods</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="shortcut icon" href="favicon.png" type="image/png">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            line-height: 1.6;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        @media (max-width: 768px) {
            .header .container {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
                padding: 0 0.5rem;
            }
        }
        .logo { font-size: 1.5rem; font-weight: 700; }
        .user-info { display: flex; align-items: center; gap: 1rem; }
        @media (max-width: 768px) {
            .logo { font-size: 1.2rem; }
            .user-info { 
                flex-direction: column; 
                gap: 0.5rem;
                font-size: 0.9rem;
            }
        }
        @media (max-width: 480px) {
            .logo { font-size: 1rem; }
        }
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
        @media (max-width: 480px) {
            .container {
                margin: 0.5rem auto;
                padding: 0 0.5rem;
            }
        }
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        @media (max-width: 480px) {
            .card {
                margin-bottom: 1rem;
                border-radius: 8px;
            }
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }
        .card-body { padding: 1.5rem; }
        @media (max-width: 768px) {
            .card-header {
                padding: 0.8rem 1rem;
                font-size: 0.95rem;
            }
            .card-body { 
                padding: 1rem; 
            }
        }
        @media (max-width: 480px) {
            .card-header {
                padding: 0.7rem 0.8rem;
                font-size: 0.9rem;
            }
            .card-body { 
                padding: 0.8rem; 
            }
        }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        @media (max-width: 768px) {
            .grid { 
                grid-template-columns: 1fr; 
                gap: 1rem;
            }
        }
        @media (max-width: 480px) {
            .grid { 
                gap: 0.8rem;
            }
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
            width: 100%;
            box-sizing: border-box;
        }
        @media (max-width: 768px) {
            .btn {
                padding: 10px 20px;
                font-size: 0.95rem;
            }
        }
        @media (max-width: 480px) {
            .btn {
                padding: 8px 16px;
                font-size: 0.9rem;
            }
        }
        .btn-primary {
            background: #2E7D32;
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
        }
        .btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .alert-success {
            background: rgba(46, 125, 50, 0.1);
            color: #2E7D32;
            border: 1px solid #2E7D32;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .status-success {
            background: rgba(46, 125, 50, 0.1);
            color: #2E7D32;
        }
        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .time-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }
        @media (max-width: 768px) {
            .time-info {
                padding: 0.8rem;
                font-size: 0.9rem;
            }
        }
        @media (max-width: 480px) {
            .time-info {
                padding: 0.7rem;
                font-size: 0.85rem;
            }
        }
        .map-container {
            height: 300px;
            background: #e9ecef;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            text-align: center;
            padding: 1rem;
            box-sizing: border-box;
        }
        @media (max-width: 768px) {
            .map-container {
                height: 250px;
                font-size: 0.9rem;
            }
        }
        @media (max-width: 480px) {
            .map-container {
                height: 200px;
                font-size: 0.85rem;
                padding: 0.5rem;
            }
        }
        #gpsStatus {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid #007bff;
            font-size: 0.95rem;
        }
        @media (max-width: 768px) {
            #gpsStatus {
                padding: 0.8rem;
                font-size: 0.9rem;
            }
        }
        @media (max-width: 480px) {
            #gpsStatus {
                padding: 0.7rem;
                font-size: 0.85rem;
            }
        }
        
        /* Mobile-first improvements */
        @media (max-width: 480px) {
            body {
                font-size: 14px;
            }
            
            .status-badge {
                font-size: 0.8rem;
                padding: 0.3rem 0.6rem;
            }
            
            .alert {
                font-size: 0.85rem;
                line-height: 1.4;
            }
            
            /* Better touch targets */
            .btn {
                min-height: 44px;
                touch-action: manipulation;
            }
            
            /* Improve readability */
            h2, h3 {
                font-size: 1.1rem;
                line-height: 1.3;
            }
            
            /* Better spacing for mobile */
            .time-info {
                line-height: 1.4;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="logo">🕐 Checkin HP Foods - Hệ thống điểm danh</div>
            <div class="user-info">
                <a href="https://hpfoods.com.vn" target="_blank" class="logout-btn">💼 Quản lý công việc</a>
                <span>Xin chào, <?= htmlspecialchars($user['full_name']) ?></span>
                <a href="logout.php" class="logout-btn">Đăng xuất</a>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Welcome -->
        <div class="card">
            <div class="card-body">
                <h2>Chào mừng, <?= htmlspecialchars($user['full_name']) ?>!</h2>
                <p>Hôm nay là <?= date('d/m/Y - l') ?></p>
                <?php if ($user['region_name']): ?>
                    <p><strong>Vùng làm việc:</strong> <?= htmlspecialchars($user['region_name']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <div class="grid">
            <!-- Check-in Section -->
            <div class="card">
                <div class="card-header">
                    <h3>📍 Điểm danh GPS</h3>
                </div>
                <div class="card-body">
                    <!-- Time Info -->
                    <div class="time-info">
                        <strong>Giờ điểm danh:</strong><br>
                        • Sáng: 04:00 - 11:00<br>
                        • Chiều: 13:00 - 20:00<br>
                        <strong>Hiện tại:</strong> <?= date('H:i:s') ?>
                        <?php 
                        $currentSession = getCurrentSession();
                        if ($currentSession): ?>
                            <span style="color: green;"> - Có thể điểm danh <?= $currentSession === 'morning' ? 'sáng' : 'chiều' ?></span>
                        <?php else: ?>
                            <span style="color: red;"> - Ngoài giờ điểm danh</span>
                        <?php endif; ?>
                    </div>

                    <!-- GPS Status -->
                    <div id="gpsStatus" style="background: #fff3cd; color: #856404;">
                        🌍 <strong>Trạng thái GPS:</strong> Đang kiểm tra...
                    </div>

                    <!-- Check-in Button -->
                    <div style="text-align: center; margin-bottom: 1rem;">
                        <?php 
                        $canCheck = canCheckIn() && $user['region_id'];
                        $currentSession = getCurrentSession();
                        $hasCheckedIn = false;
                        
                        if ($currentSession === 'morning' && $morningCheckin) $hasCheckedIn = true;
                        if ($currentSession === 'evening' && $eveningCheckin) $hasCheckedIn = true;
                        ?>
                        
                        <?php if ($hasCheckedIn): ?>
                            <div class="alert alert-success">
                                ✅ Đã điểm danh <?= $currentSession === 'morning' ? 'sáng' : 'chiều' ?> lúc 
                                <?= date('H:i', strtotime($currentSession === 'morning' ? $morningCheckin['checkin_time'] : $eveningCheckin['checkin_time'])) ?>
                            </div>
                        <?php elseif ($canCheck): ?>
                            <button id="checkinBtn" class="btn btn-primary" onclick="performCheckin()">
                                📍 Điểm danh <?= $currentSession === 'morning' ? 'sáng' : 'chiều' ?>
                            </button>
                        <?php else: ?>
                            <button class="btn" disabled>
                                ⏰ Ngoài giờ điểm danh
                            </button>
                        <?php endif; ?>
                    </div>


                    <!-- Map placeholder -->
                    <div class="map-container" id="map">
                        🗺️ Bản đồ vùng làm việc<br>
                        <small>Cần Google Maps API để hiển thị</small>
                    </div>
                </div>
            </div>

            <!-- Today's Status -->
            <div class="card">
                <div class="card-header">
                    <h3>📅 Trạng thái hôm nay</h3>
                </div>
                <div class="card-body">
                    <div style="margin-bottom: 1rem;">
                        <strong>Điểm danh sáng:</strong><br>
                        <?php if ($morningCheckin): ?>
                            <span class="status-badge status-success">
                                ✅ <?= date('H:i', strtotime($morningCheckin['checkin_time'])) ?>
                                (<?= round($morningCheckin['distance_meters']) ?>m)
                            </span>
                        <?php else: ?>
                            <span class="status-badge status-pending">⏳ Chưa điểm danh</span>
                        <?php endif; ?>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <strong>Điểm danh chiều:</strong><br>
                        <?php if ($eveningCheckin): ?>
                            <span class="status-badge status-success">
                                ✅ <?= date('H:i', strtotime($eveningCheckin['checkin_time'])) ?>
                                (<?= round($eveningCheckin['distance_meters']) ?>m)
                            </span>
                        <?php else: ?>
                            <span class="status-badge status-pending">⏳ Chưa điểm danh</span>
                        <?php endif; ?>
                    </div>

                    <hr style="margin: 1rem 0;">
                    
                    <div style="text-align: center;">
                        <a href="checkin_history.php" class="btn" style="background: #6c757d; color: white; display: inline-block; max-width: 300px; width: auto;">
                            📊 Lịch sử điểm danh
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden form for check-in -->
    <form id="checkinForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="checkin">
        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">
    </form>

    <script>
        // Check GPS support
        function checkGPS() {
            const gpsStatus = document.getElementById('gpsStatus');
            
            if (!navigator.geolocation) {
                gpsStatus.style.background = '#f8d7da';
                gpsStatus.style.color = '#721c24';
                gpsStatus.innerHTML = '❌ <strong>GPS không được hỗ trợ</strong>';
                return;
            }

            // Check HTTPS
            const isSecure = location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
            
            if (!isSecure) {
                gpsStatus.style.background = '#f8d7da';
                gpsStatus.style.color = '#721c24';
                gpsStatus.innerHTML = '⚠️ <strong>Cần HTTPS để sử dụng GPS</strong>';
                return;
            }

            gpsStatus.style.background = '#d4edda';
            gpsStatus.style.color = '#155724';
            gpsStatus.innerHTML = '✅ <strong>GPS sẵn sàng</strong> - Có thể điểm danh';
        }

        // Get current location
        function getCurrentLocation() {
            return new Promise((resolve, reject) => {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        resolve({
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude,
                            accuracy: position.coords.accuracy
                        });
                    },
                    (error) => {
                        let message = 'Không thể lấy vị trí GPS';
                        switch (error.code) {
                            case error.PERMISSION_DENIED:
                                message = 'Bạn đã từ chối truy cập GPS. Vui lòng cho phép truy cập vị trí.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                message = 'GPS không khả dụng. Vui lòng kiểm tra GPS.';
                                break;
                            case error.TIMEOUT:
                                message = 'Hết thời gian chờ GPS. Vui lòng thử lại.';
                                break;
                        }
                        reject(new Error(message));
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 15000,
                        maximumAge: 30000
                    }
                );
            });
        }

        // Perform check-in
        async function performCheckin() {
            const btn = document.getElementById('checkinBtn');
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '🔄 Đang lấy vị trí...';
            btn.disabled = true;

            try {
                const location = await getCurrentLocation();
                
                btn.innerHTML = '📍 Đang điểm danh...';
                
                document.getElementById('latitude').value = location.latitude;
                document.getElementById('longitude').value = location.longitude;
                document.getElementById('checkinForm').submit();
                
            } catch (error) {
                alert('Lỗi: ' + error.message);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }


        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            checkGPS();
        });
    </script>
</body>
</html>
