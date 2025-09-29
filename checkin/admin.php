<?php
require_once 'config.php';

// Check if user is admin
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$db = getDB();

// Get statistics
$stats = [];

// Total users
$stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
$stats['total_users'] = $stmt->fetch()['count'];

// Total regions
$stmt = $db->query("SELECT COUNT(*) as count FROM regions");
$stats['total_regions'] = $stmt->fetch()['count'];

// Today's check-ins
$today = date('Y-m-d');
$stmt = $db->prepare("SELECT COUNT(*) as count FROM checkins WHERE checkin_date = ?");
$stmt->execute([$today]);
$stats['today_checkins'] = $stmt->fetch()['count'];

// Success rate today
$stmt = $db->prepare("SELECT COUNT(*) as count FROM checkins WHERE checkin_date = ? AND status = 'success'");
$stmt->execute([$today]);
$stats['today_success'] = $stmt->fetch()['count'];

$stats['success_rate'] = $stats['today_checkins'] > 0 ? round(($stats['today_success'] / $stats['today_checkins']) * 100, 1) : 0;

// Recent check-ins
$sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));
$stmt = $db->prepare("
    SELECT c.*, u.full_name, u.username, r.name as region_name
    FROM checkins c
    JOIN users u ON c.user_id = u.id
    JOIN regions r ON c.region_id = r.id
    WHERE c.checkin_date >= ?
    ORDER BY c.checkin_time DESC
    LIMIT 20
");
$stmt->execute([$sevenDaysAgo]);
$recentCheckins = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Checkin HPFoods</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            line-height: 1.6;
        }
        .header {
            background: linear-gradient(135deg, #dc3545 0%, #6f42c1 100%);
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
            margin-bottom: 2rem;
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #dc3545 0%, #6f42c1 100%);
            color: white;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }
        .card-body { padding: 1.5rem; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 0.8rem;
                margin-bottom: 1.5rem;
            }
        }
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 0.5rem;
                margin-bottom: 1rem;
            }
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-align: center;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #dc3545;
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: #666;
            font-weight: 600;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }
        @media (max-width: 768px) {
            .table {
                font-size: 0.85rem;
            }
        }
        @media (max-width: 480px) {
            .table {
                font-size: 0.8rem;
            }
        }
        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        @media (max-width: 768px) {
            .table th, .table td {
                padding: 8px;
            }
        }
        @media (max-width: 480px) {
            .table th, .table td {
                padding: 6px;
            }
        }
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        .table tr:hover {
            background: #f8f9fa;
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }
        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background: #0056b3;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-success:hover {
            background: #1e7e34;
        }
        .nav-links {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        @media (max-width: 768px) {
            .nav-links {
                gap: 0.5rem;
                margin-bottom: 1.5rem;
            }
        }
        @media (max-width: 480px) {
            .nav-links {
                flex-direction: column;
                gap: 0.3rem;
                margin-bottom: 1rem;
            }
        }
        .nav-link {
            padding: 0.75rem 1.5rem;
            background: white;
            color: #333;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s;
            text-align: center;
            display: block;
        }
        @media (max-width: 768px) {
            .nav-link {
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
            }
        }
        @media (max-width: 480px) {
            .nav-link {
                padding: 0.5rem 0.8rem;
                font-size: 0.85rem;
            }
        }
        .nav-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="logo">👨‍💼 Admin Dashboard - Checkin HPFoods</div>
            <div class="user-info">
                <a href="https://hpfoods.com.vn" target="_blank" class="logout-btn">💼 Quản lý công việc</a>
                <span>Xin chào, <?= htmlspecialchars($user['full_name']) ?></span>
                <a href="logout.php" class="logout-btn">Đăng xuất</a>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Navigation -->
        <div class="nav-links">
            <a href="admin.php" class="nav-link">📊 Dashboard</a>
            <a href="manage_users.php" class="nav-link">👥 Quản lý người dùng</a>
            <a href="manage_regions.php" class="nav-link">🗺️ Quản lý vùng</a>
            <a href="reports.php" class="nav-link">📈 Báo cáo</a>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total_users'] ?></div>
                <div class="stat-label">Tổng người dùng</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total_regions'] ?></div>
                <div class="stat-label">Tổng vùng</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['today_checkins'] ?></div>
                <div class="stat-label">Điểm danh hôm nay</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['success_rate'] ?>%</div>
                <div class="stat-label">Tỷ lệ thành công</div>
            </div>
        </div>

        <!-- Recent Check-ins -->
        <div class="card">
            <div class="card-header">
                <h3>📋 Điểm danh gần đây</h3>
            </div>
            <div class="card-body">
                <?php if (empty($recentCheckins)): ?>
                    <p>Chưa có dữ liệu điểm danh.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nhân viên</th>
                                <th>Vùng</th>
                                <th>Ngày</th>
                                <th>Ca</th>
                                <th>Giờ</th>
                                <th>Khoảng cách</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentCheckins as $checkin): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($checkin['full_name']) ?></strong><br>
                                    <small><?= htmlspecialchars($checkin['username']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($checkin['region_name']) ?></td>
                                <td><?= date('d/m/Y', strtotime($checkin['checkin_date'])) ?></td>
                                <td><?= $checkin['session'] === 'morning' ? 'Sáng' : 'Chiều' ?></td>
                                <td><?= date('H:i', strtotime($checkin['checkin_time'])) ?></td>
                                <td><?= $checkin['distance_meters'] ? round($checkin['distance_meters']) . 'm' : 'N/A' ?></td>
                                <td>
                                    <span class="status-badge status-<?= $checkin['status'] ?>">
                                        <?= $checkin['status'] === 'success' ? '✅ Thành công' : '❌ Thất bại' ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h3>⚡ Thao tác nhanh</h3>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <a href="manage_users.php" class="btn btn-success">👥 Quản lý người dùng</a>
                    <a href="manage_regions.php" class="btn btn-success">🗺️ Quản lý vùng</a>
                    <a href="reports.php" class="btn btn-primary">📊 Báo cáo chi tiết</a>
                    <button onclick="getCurrentLocationAdmin()" class="btn" style="background: #17a2b8; color: white;">📍 Lấy vị trí hiện tại</button>
                </div>
                
                <!-- GPS Tool for Admin -->
                <div id="adminGpsResult" style="margin-top: 1rem; display: none;"></div>
            </div>
        </div>
    </div>

    <script>
        // Get current location for Admin (to create regions)
        async function getCurrentLocationAdmin() {
            const resultDiv = document.getElementById('adminGpsResult');
            resultDiv.style.display = 'block';
            
            if (!navigator.geolocation) {
                resultDiv.innerHTML = '<div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 8px;">❌ GPS không được hỗ trợ trên thiết bị này</div>';
                return;
            }

            resultDiv.innerHTML = '<div style="background: #d1ecf1; color: #0c5460; padding: 1rem; border-radius: 8px;">🔄 Đang lấy vị trí GPS chính xác...</div>';

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
                
                const mapsUrl = `https://www.google.com/maps?q=${lat},${lng}&t=satellite&z=18`;
                
                resultDiv.innerHTML = `
                    <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px;">
                        <h4>✅ Lấy GPS thành công!</h4>
                        <p><strong>Tọa độ hiện tại:</strong></p>
                        <div style="background: #fff; padding: 0.5rem; border-radius: 4px; margin: 0.5rem 0; font-family: monospace; font-weight: bold; border-left: 4px solid #28a745;">
                            Vĩ độ: ${lat.toFixed(8)}<br>
                            Kinh độ: ${lng.toFixed(8)}
                        </div>
                        <p><strong>Độ chính xác:</strong> ±${Math.round(accuracy)}m</p>
                        <p><strong>Thời gian:</strong> ${new Date().toLocaleString('vi-VN')}</p>
                        ${accuracy > 50 ? '<p style="color: #856404;">⚠️ Độ chính xác thấp, thử di chuyển ra ngoài trời</p>' : ''}
                        
                        <div style="margin-top: 1rem;">
                            <a href="${mapsUrl}" target="_blank" 
                               style="background: #007bff; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px; margin-right: 0.5rem;">
                                🗺️ Xem trên Google Maps
                            </a>
                            <a href="manage_regions.php?lat=${lat.toFixed(8)}&lng=${lng.toFixed(8)}" 
                               style="background: #28a745; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px;">
                                ➕ Tạo vùng tại đây
                            </a>
                        </div>
                    </div>
                `;
                
            } catch (error) {
                let message = 'Không thể lấy vị trí GPS';
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        message = 'Bạn đã từ chối truy cập GPS. Vui lòng cho phép truy cập vị trí trong trình duyệt.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        message = 'GPS không khả dụng. Vui lòng kiểm tra GPS và thử lại.';
                        break;
                    case error.TIMEOUT:
                        message = 'Hết thời gian chờ GPS. Vui lòng thử lại.';
                        break;
                }
                resultDiv.innerHTML = `<div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 8px;">❌ ${message}</div>`;
            }
        }
    </script>
</body>
</html>
