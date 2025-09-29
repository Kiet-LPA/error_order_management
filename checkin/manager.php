<?php
require_once 'config.php';

// Check if user is manager or admin
if (!isLoggedIn() || !in_array($_SESSION['role'], ['manager', 'admin'])) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$db = getDB();

// Get manager's region (for managers) or all regions (for admins)
if ($_SESSION['role'] === 'admin') {
    $stmt = $db->query("SELECT * FROM regions ORDER BY name");
    $regions = $stmt->fetchAll();
} else {
    $stmt = $db->prepare("SELECT * FROM regions WHERE id = ?");
    $stmt->execute([$user['region_id']]);
    $regions = $stmt->fetchAll();
}

// Get employees in managed regions
$regionIds = array_column($regions, 'id');
if (!empty($regionIds)) {
    $placeholders = str_repeat('?,', count($regionIds) - 1) . '?';
    $stmt = $db->prepare("
        SELECT u.*, r.name as region_name
        FROM users u
        JOIN regions r ON u.region_id = r.id
        WHERE u.region_id IN ($placeholders) AND u.role_id = 3 AND u.is_active = 1
        ORDER BY r.name, u.full_name
    ");
    $stmt->execute($regionIds);
    $employees = $stmt->fetchAll();
} else {
    $employees = [];
}

// Get today's attendance for managed employees
$today = date('Y-m-d');
$attendanceToday = [];

if (!empty($employees)) {
    $employeeIds = array_column($employees, 'id');
    $placeholders = str_repeat('?,', count($employeeIds) - 1) . '?';
    $params = array_merge($employeeIds, [$today]);
    
    $stmt = $db->prepare("
        SELECT c.*, u.full_name, u.username
        FROM checkins c
        JOIN users u ON c.user_id = u.id
        WHERE c.user_id IN ($placeholders) AND c.checkin_date = ?
        ORDER BY c.checkin_time DESC
    ");
    $stmt->execute($params);
    $attendanceToday = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard - Checkin HPFoods</title>
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
            background: linear-gradient(135deg, #fd7e14 0%, #6f42c1 100%);
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
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #fd7e14 0%, #6f42c1 100%);
            color: white;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }
        .card-body { padding: 1.5rem; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
        @media (max-width: 768px) {
            .grid { grid-template-columns: 1fr; }
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
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
        .status-pending {
            background: #fff3cd;
            color: #856404;
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
        .employee-card {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .employee-name {
            font-weight: 600;
            color: #333;
        }
        .employee-info {
            font-size: 0.9rem;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="logo">👨‍💼 Manager Dashboard - Checkin HPFoods</div>
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
                <p><strong>Vai trò:</strong> <?= $_SESSION['role'] === 'admin' ? 'Administrator' : 'Manager' ?></p>
            </div>
        </div>

        <div class="grid">
            <!-- Managed Regions -->
            <div class="card">
                <div class="card-header">
                    <h3>🗺️ Vùng quản lý</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($regions)): ?>
                        <p>Chưa được phân công vùng nào.</p>
                    <?php else: ?>
                        <?php foreach ($regions as $region): ?>
                            <div class="employee-card">
                                <div class="employee-name"><?= htmlspecialchars($region['name']) ?></div>
                                <div class="employee-info">
                                    📍 <?= htmlspecialchars($region['address']) ?><br>
                                    🌍 GPS: <?= $region['latitude'] ?>, <?= $region['longitude'] ?><br>
                                    📏 Bán kính: <?= $region['radius_meters'] ?>m
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Employees -->
            <div class="card">
                <div class="card-header">
                    <h3>👥 Nhân viên (<?= count($employees) ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($employees)): ?>
                        <p>Chưa có nhân viên nào trong vùng quản lý.</p>
                    <?php else: ?>
                        <?php foreach ($employees as $employee): ?>
                            <div class="employee-card">
                                <div class="employee-name"><?= htmlspecialchars($employee['full_name']) ?></div>
                                <div class="employee-info">
                                    👤 <?= htmlspecialchars($employee['username']) ?><br>
                                    📧 <?= htmlspecialchars($employee['email']) ?><br>
                                    🗺️ <?= htmlspecialchars($employee['region_name']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Today's Attendance -->
        <div class="card">
            <div class="card-header">
                <h3>📅 Điểm danh hôm nay (<?= date('d/m/Y') ?>)</h3>
            </div>
            <div class="card-body">
                <?php if (empty($attendanceToday)): ?>
                    <p>Chưa có ai điểm danh hôm nay.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nhân viên</th>
                                <th>Ca</th>
                                <th>Giờ</th>
                                <th>Khoảng cách</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendanceToday as $checkin): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($checkin['full_name']) ?></strong><br>
                                    <small><?= htmlspecialchars($checkin['username']) ?></small>
                                </td>
                                <td><?= $checkin['session'] === 'morning' ? '🌅 Sáng' : '🌆 Chiều' ?></td>
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
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="monthly_report.php" class="btn btn-primary">📊 Báo cáo tháng</a>
                    <a href="fix_attendance.php" class="btn" style="background: #ffc107; color: #212529;">🔧 Sửa lỗi điểm danh</a>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="admin.php" class="btn btn-primary">👨‍💼 Chuyển sang Admin</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
