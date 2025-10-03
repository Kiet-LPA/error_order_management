<?php
require_once 'config.php';

// Check if user is manager or admin
if (!isLoggedIn() || !in_array($_SESSION['role'], ['manager', 'admin'])) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$db = getDB();

// Get current month or from request
$month = $_GET['month'] ?? date('Y-m');
$startDate = $month . '-01';
$endDate = date('Y-m-t', strtotime($startDate));

// Get employees in managed regions
if ($_SESSION['role'] === 'admin') {
    $stmt = $db->query("
        SELECT u.*, r.name as region_name
        FROM users u
        JOIN regions r ON u.region_id = r.id
        WHERE u.role_id = 3 AND u.is_active = 1
        ORDER BY r.name, u.full_name
    ");
    $employees = $stmt->fetchAll();
} else {
    $stmt = $db->prepare("
        SELECT u.*, r.name as region_name
        FROM users u
        JOIN regions r ON u.region_id = r.id
        WHERE u.region_id = ? AND u.role_id = 3 AND u.is_active = 1
        ORDER BY u.full_name
    ");
    $stmt->execute([$user['region_id']]);
    $employees = $stmt->fetchAll();
}

// Get attendance data for the month
$attendanceData = [];
if (!empty($employees)) {
    $employeeIds = array_column($employees, 'id');
    $placeholders = str_repeat('?,', count($employeeIds) - 1) . '?';
    $params = array_merge($employeeIds, [$startDate, $endDate]);
    
    $stmt = $db->prepare("
        SELECT c.user_id, c.checkin_date, c.session, c.checkin_time, c.distance_meters
        FROM checkins c
        WHERE c.user_id IN ($placeholders) AND c.checkin_date BETWEEN ? AND ?
        ORDER BY c.user_id, c.checkin_date, c.session
    ");
    $stmt->execute($params);
    $checkins = $stmt->fetchAll();
    
    // Group by user and date
    foreach ($checkins as $checkin) {
        $userId = $checkin['user_id'];
        $date = $checkin['checkin_date'];
        
        if (!isset($attendanceData[$userId])) {
            $attendanceData[$userId] = [];
        }
        if (!isset($attendanceData[$userId][$date])) {
            $attendanceData[$userId][$date] = ['morning' => null, 'evening' => null];
        }
        
        $attendanceData[$userId][$date][$checkin['session']] = $checkin;
    }
}

// Calculate statistics
$totalWorkDays = 0;
$currentDate = $startDate;
while ($currentDate <= $endDate) {
    $dayOfWeek = date('N', strtotime($currentDate));
    if ($dayOfWeek <= 5) { // Monday to Friday
        $totalWorkDays++;
    }
    $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo tháng <?= date('m/Y', strtotime($startDate)) ?> - Checkin HP Foods</title>
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
            background: linear-gradient(135deg, #fd7e14 0%, #6f42c1 100%);
            color: white;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }
        .card-body { padding: 1.5rem; }
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
        }
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #fd7e14;
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: #666;
            font-weight: 600;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        .table th, .table td {
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            position: sticky;
            top: 0;
        }
        .table tr:hover {
            background: #f8f9fa;
        }
        .attendance-cell {
            min-width: 60px;
        }
        .attendance-present {
            background: #d4edda;
            color: #155724;
            font-weight: bold;
        }
        .attendance-absent {
            background: #f8d7da;
            color: #721c24;
        }
        .attendance-partial {
            background: #fff3cd;
            color: #856404;
        }
        .employee-name {
            text-align: left;
            font-weight: 600;
            min-width: 150px;
        }
        .summary-stats {
            display: flex;
            justify-content: space-around;
            text-align: center;
            margin-top: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .no-data {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: 1fr; }
            .table { font-size: 0.8rem; }
            .table th, .table td { padding: 4px; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="logo">📊 Báo cáo tháng <?= date('m/Y', strtotime($startDate)) ?></div>
            <div class="nav-links">
                <a href="manager.php" class="nav-link">🏠 Dashboard</a>
                <a href="logout.php" class="nav-link">🚪 Đăng xuất</a>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Month Filter -->
        <form class="filter-form" method="GET">
            <div class="form-group">
                <label>Chọn tháng:</label>
                <input type="month" name="month" value="<?= $month ?>">
            </div>
            <button type="submit" class="btn btn-primary">📅 Xem báo cáo</button>
        </form>

        <!-- Summary Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= count($employees) ?></div>
                <div class="stat-label">Tổng nhân viên</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $totalWorkDays ?></div>
                <div class="stat-label">Ngày làm việc</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= count($attendanceData) ?></div>
                <div class="stat-label">Nhân viên có điểm danh</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $totalWorkDays > 0 ? round((count($attendanceData) / count($employees)) * 100, 1) : 0 ?>%</div>
                <div class="stat-label">Tỷ lệ tham gia</div>
            </div>
        </div>

        <!-- Detailed Attendance Table -->
        <div class="card">
            <div class="card-header">
                <h3>📋 Bảng điểm danh chi tiết tháng <?= date('m/Y', strtotime($startDate)) ?></h3>
            </div>
            <div class="card-body">
                <?php if (empty($employees)): ?>
                    <div class="no-data">
                        <h3>📭 Không có dữ liệu</h3>
                        <p>Không có nhân viên nào trong vùng quản lý.</p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="employee-name">Nhân viên</th>
                                    <?php
                                    $currentDate = $startDate;
                                    while ($currentDate <= $endDate) {
                                        $dayOfWeek = date('N', strtotime($currentDate));
                                        if ($dayOfWeek <= 5) { // Monday to Friday only
                                            echo '<th class="attendance-cell">' . date('d/m', strtotime($currentDate)) . '<br><small>' . date('D', strtotime($currentDate)) . '</small></th>';
                                        }
                                        $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
                                    }
                                    ?>
                                    <th>Tổng ngày</th>
                                    <th>Tỷ lệ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employees as $employee): ?>
                                <?php
                                $presentDays = 0;
                                $userId = $employee['id'];
                                ?>
                                <tr>
                                    <td class="employee-name">
                                        <strong><?= htmlspecialchars($employee['full_name']) ?></strong><br>
                                        <small style="color: #666;"><?= htmlspecialchars($employee['username']) ?></small>
                                    </td>
                                    
                                    <?php
                                    $currentDate = $startDate;
                                    while ($currentDate <= $endDate) {
                                        $dayOfWeek = date('N', strtotime($currentDate));
                                        if ($dayOfWeek <= 5) { // Monday to Friday only
                                            $dayAttendance = $attendanceData[$userId][$currentDate] ?? null;
                                            
                                            if ($dayAttendance) {
                                                $morningPresent = $dayAttendance['morning'] !== null;
                                                $eveningPresent = $dayAttendance['evening'] !== null;
                                                
                                                if ($morningPresent && $eveningPresent) {
                                                    echo '<td class="attendance-cell attendance-present">✅<br><small>Full</small></td>';
                                                    $presentDays++;
                                                } elseif ($morningPresent || $eveningPresent) {
                                                    echo '<td class="attendance-cell attendance-partial">⚠️<br><small>' . ($morningPresent ? 'S' : 'C') . '</small></td>';
                                                    $presentDays += 0.5;
                                                } else {
                                                    echo '<td class="attendance-cell attendance-absent">❌<br><small>-</small></td>';
                                                }
                                            } else {
                                                echo '<td class="attendance-cell attendance-absent">❌<br><small>-</small></td>';
                                            }
                                        }
                                        $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
                                    }
                                    
                                    $attendanceRate = $totalWorkDays > 0 ? round(($presentDays / $totalWorkDays) * 100, 1) : 0;
                                    ?>
                                    
                                    <td><strong><?= $presentDays ?>/<?= $totalWorkDays ?></strong></td>
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
                    
                    <div class="summary-stats">
                        <div>
                            <strong>Chú thích:</strong><br>
                            <span class="attendance-present" style="padding: 2px 6px; border-radius: 3px;">✅ Đầy đủ</span>
                            <span class="attendance-partial" style="padding: 2px 6px; border-radius: 3px; margin: 0 5px;">⚠️ Một phần</span>
                            <span class="attendance-absent" style="padding: 2px 6px; border-radius: 3px;">❌ Vắng</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="manager.php" class="btn btn-primary">🏠 Về Dashboard</a>
            <button onclick="window.print()" class="btn" style="background: #6c757d; color: white;">🖨️ In báo cáo</button>
        </div>
    </div>
</body>
</html>
