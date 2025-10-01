<?php
require_once 'config.php';

// Check if user is admin
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$db = getDB();

// Get date range from request or default to last 30 days
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Overall statistics
$stats = [];

// Total check-ins
$stmt = $db->prepare("SELECT COUNT(*) as count FROM checkins WHERE checkin_date BETWEEN ? AND ?");
$stmt->execute([$startDate, $endDate]);
$stats['total_checkins'] = $stmt->fetch()['count'];

// All checkins are successful now (we only save successful ones)
$stats['success_checkins'] = $stats['total_checkins'];
$stats['failed_checkins'] = 0;
$stats['success_rate'] = $stats['total_checkins'] > 0 ? 100 : 0;

// Get attendance statistics by user (who didn't check in on working days)
$stmt = $db->prepare("
    SELECT u.full_name, u.username, u.avatar, r.name as region_name,
           COUNT(DISTINCT c.checkin_date) as checkin_days
    FROM users u
    JOIN regions r ON u.region_id = r.id
    LEFT JOIN checkins c ON u.id = c.user_id AND c.checkin_date BETWEEN ? AND ?
    WHERE u.role_id = 3 AND u.is_active = 1
    GROUP BY u.id, u.full_name, u.username, u.avatar, r.name
    HAVING checkin_days < DATEDIFF(?, ?)
    ORDER BY checkin_days ASC
");
$stmt->execute([$startDate, $endDate, $endDate, $startDate]);
$attendanceByUser = $stmt->fetchAll();

// GPS requests
$stmt = $db->prepare("
    SELECT gr.*, u.full_name, u.username, u.avatar, r.name as region_name
    FROM gps_requests gr
    JOIN users u ON gr.user_id = u.id
    JOIN regions r ON gr.region_id = r.id
    WHERE gr.request_date BETWEEN ? AND ?
    ORDER BY gr.created_at DESC
");
$stmt->execute([$startDate, $endDate]);
$gpsRequests = $stmt->fetchAll();

// Daily statistics (all checkins are successful now)
$stmt = $db->prepare("
    SELECT 
        checkin_date,
        COUNT(*) as total,
        COUNT(*) as success,
        0 as failed
    FROM checkins 
    WHERE checkin_date BETWEEN ? AND ?
    GROUP BY checkin_date
    ORDER BY checkin_date DESC
");
$stmt->execute([$startDate, $endDate]);
$dailyStats = $stmt->fetchAll();

// Handle GPS request actions
if ($_POST && isset($_POST['action'])) {
    $action = $_POST['action'];
    $requestId = (int)$_POST['request_id'];
    $adminNotes = trim($_POST['admin_notes'] ?? '');
    
    if ($action === 'approve' || $action === 'reject') {
        $status = $action === 'approve' ? 'approved' : 'rejected';
        $stmt = $db->prepare("UPDATE gps_requests SET status = ?, admin_notes = ? WHERE id = ?");
        $stmt->execute([$status, $adminNotes, $requestId]);
        header('Location: reports.php?start_date=' . $startDate . '&end_date=' . $endDate);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo hệ thống - Checkin HPFoods</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="responsive.css">
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
        .logo { font-size: 1.5rem; font-weight: 700; }
        .nav-links {
            display: flex;
            gap: 1rem;
        }
        .nav-link {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .nav-link:hover {
            background: rgba(255,255,255,0.2);
        }
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
            background: linear-gradient(135deg, #dc3545 0%, #6f42c1 100%);
            color: white;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }
        .card-body { padding: 1.5rem; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-align: center;
            border-left: 4px solid;
        }
        .stat-card.success { border-left-color: #28a745; }
        .stat-card.failed { border-left-color: #dc3545; }
        .stat-card.total { border-left-color: #007bff; }
        .stat-card.rate { border-left-color: #ffc107; }
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .stat-number.success { color: #28a745; }
        .stat-number.failed { color: #dc3545; }
        .stat-number.total { color: #007bff; }
        .stat-number.rate { color: #ffc107; }
        .stat-label {
            color: #666;
            font-weight: 600;
        }
        .filter-form {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            align-items: end;
            flex-wrap: wrap;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        .form-group input {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
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
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
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
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        .gps-code {
            font-family: 'Courier New', monospace;
            background: #e9ecef;
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-weight: bold;
        }
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
            border: 2px solid #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            vertical-align: middle;
        }
        .user-info {
            display: inline-flex;
            align-items: center;
        }
        .user-name {
            font-weight: 600;
            color: #333;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="logo">📈 Báo cáo hệ thống</div>
            <div class="nav-links">
                <a href="admin.php" class="nav-link">📊 Dashboard</a>
                <a href="manage_users.php" class="nav-link">👥 Người dùng</a>
                <a href="manage_regions.php" class="nav-link">🗺️ Vùng</a>
                <a href="reports.php" class="nav-link">📈 Báo cáo</a>
                <a href="logout.php" class="nav-link">🚪 Đăng xuất</a>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Date Filter -->
        <form class="filter-form" method="GET">
            <div class="form-group">
                <label>Từ ngày:</label>
                <input type="date" name="start_date" value="<?= $startDate ?>">
            </div>
            <div class="form-group">
                <label>Đến ngày:</label>
                <input type="date" name="end_date" value="<?= $endDate ?>">
            </div>
            <button type="submit" class="btn btn-primary">📊 Xem báo cáo</button>
        </form>

        <!-- Overall Statistics -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-number total"><?= $stats['total_checkins'] ?></div>
                <div class="stat-label">Tổng lượt điểm danh</div>
            </div>
            <div class="stat-card success">
                <div class="stat-number success"><?= $stats['success_checkins'] ?></div>
                <div class="stat-label">Điểm danh thành công</div>
            </div>
            <div class="stat-card total">
                <div class="stat-number total"><?= count($attendanceByUser) ?></div>
                <div class="stat-label">Nhân viên thiếu điểm danh</div>
            </div>
            <div class="stat-card rate">
                <div class="stat-number rate"><?= $stats['success_rate'] ?>%</div>
                <div class="stat-label">Tỷ lệ thành công</div>
            </div>
        </div>

        <!-- Attendance by User -->
        <?php if (!empty($attendanceByUser)): ?>
        <div class="card">
            <div class="card-header">
                <h3>📊 Thống kê điểm danh theo nhân viên</h3>
            </div>
            <div class="card-body">
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nhân viên</th>
                                <th>Vùng làm việc</th>
                                <th>Số ngày điểm danh</th>
                                <th>Tỷ lệ tham gia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendanceByUser as $attendance): ?>
                            <?php
                            $totalDays = max(1, (strtotime($endDate) - strtotime($startDate)) / (24*60*60));
                            $attendanceRate = round(($attendance['checkin_days'] / $totalDays) * 100, 1);
                            ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <img src="<?= getUserAvatar($attendance['avatar'], $attendance['full_name']) ?>" 
                                             alt="<?= htmlspecialchars($attendance['full_name']) ?>" 
                                             class="user-avatar">
                                        <div>
                                            <div class="user-name"><?= htmlspecialchars($attendance['full_name']) ?></div>
                                            <small style="color: #666;"><?= htmlspecialchars($attendance['username']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($attendance['region_name']) ?></td>
                                <td>
                                    <span style="color: <?= $attendance['checkin_days'] > 0 ? '#28a745' : '#dc3545' ?>; font-weight: bold;">
                                        <?= $attendance['checkin_days'] ?> ngày
                                    </span>
                                </td>
                                <td>
                                    <span style="color: <?= $attendanceRate >= 80 ? '#28a745' : ($attendanceRate >= 50 ? '#ffc107' : '#dc3545') ?>; font-weight: bold;">
                                        <?= $attendanceRate ?>%
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- GPS Requests -->
        <?php if (!empty($gpsRequests)): ?>
        <div class="card">
            <div class="card-header">
                <h3>📍 Yêu cầu GPS từ điểm danh thất bại</h3>
            </div>
            <div class="card-body">
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nhân viên</th>
                                <th>Vùng</th>
                                <th>Ngày</th>
                                <th>Khoảng cách</th>
                                <th>Mã GPS</th>
                                <th>Trạng thái</th>
                                <th>Ghi chú</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($gpsRequests as $request): ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <img src="<?= getUserAvatar($request['avatar'], $request['full_name']) ?>" 
                                             alt="<?= htmlspecialchars($request['full_name']) ?>" 
                                             class="user-avatar">
                                        <div>
                                            <div class="user-name"><?= htmlspecialchars($request['full_name']) ?></div>
                                            <small style="color: #666;"><?= htmlspecialchars($request['username']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($request['region_name']) ?></td>
                                <td><?= date('d/m/Y', strtotime($request['request_date'])) ?></td>
                                <td><?= round($request['distance_meters']) ?>m</td>
                                <td><span class="gps-code"><?= $request['gps_code'] ?></span></td>
                                <td>
                                    <span class="status-badge status-<?= $request['status'] ?>">
                                        <?php
                                        switch($request['status']) {
                                            case 'pending': echo '⏳ Chờ duyệt'; break;
                                            case 'approved': echo '✅ Đã duyệt'; break;
                                            case 'rejected': echo '❌ Từ chối'; break;
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($request['admin_notes'] ?? '') ?></td>
                                <td>
                                    <?php if ($request['status'] === 'pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                            <input type="text" name="admin_notes" placeholder="Ghi chú..." 
                                                   style="width: 100px; padding: 2px; font-size: 0.8rem;">
                                            <button type="submit" class="btn btn-success" 
                                                    style="font-size: 0.7rem; padding: 2px 6px;">
                                                ✅ Duyệt
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline; margin-left: 5px;">
                                            <input type="hidden" name="action" value="reject">
                                            <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                            <button type="submit" class="btn btn-danger" 
                                                    style="font-size: 0.7rem; padding: 2px 6px;">
                                                ❌ Từ chối
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Daily Statistics -->
        <?php if (!empty($dailyStats)): ?>
        <div class="card">
            <div class="card-header">
                <h3>📅 Thống kê theo ngày</h3>
            </div>
            <div class="card-body">
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ngày</th>
                                <th>Tổng</th>
                                <th>Thành công</th>
                                <th>Thất bại</th>
                                <th>Tỷ lệ thành công</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dailyStats as $day): ?>
                            <?php $rate = $day['total'] > 0 ? round(($day['success'] / $day['total']) * 100, 1) : 0; ?>
                            <tr>
                                <td><strong><?= date('d/m/Y', strtotime($day['checkin_date'])) ?></strong></td>
                                <td><?= $day['total'] ?></td>
                                <td style="color: #28a745; font-weight: bold;"><?= $day['success'] ?></td>
                                <td style="color: #dc3545; font-weight: bold;"><?= $day['failed'] ?></td>
                                <td>
                                    <span style="color: <?= $rate >= 90 ? '#28a745' : ($rate >= 70 ? '#ffc107' : '#dc3545') ?>; font-weight: bold;">
                                        <?= $rate ?>%
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
