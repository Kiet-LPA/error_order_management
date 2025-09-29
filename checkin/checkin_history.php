<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$db = getDB();

// Get date range (default last 30 days)
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Get user's check-in history
$stmt = $db->prepare("
    SELECT c.*, r.name as region_name
    FROM checkins c
    JOIN regions r ON c.region_id = r.id
    WHERE c.user_id = ? AND c.checkin_date BETWEEN ? AND ?
    ORDER BY c.checkin_date DESC, c.session DESC
");
$stmt->execute([$user['id'], $startDate, $endDate]);
$checkins = $stmt->fetchAll();

// Group by date
$checkinsByDate = [];
foreach ($checkins as $checkin) {
    $date = $checkin['checkin_date'];
    if (!isset($checkinsByDate[$date])) {
        $checkinsByDate[$date] = ['morning' => null, 'evening' => null];
    }
    $checkinsByDate[$date][$checkin['session']] = $checkin;
}

// Statistics
$totalDays = count($checkinsByDate);
$successfulDays = 0;
foreach ($checkinsByDate as $dayCheckins) {
    if (($dayCheckins['morning'] && $dayCheckins['morning']['status'] === 'success') ||
        ($dayCheckins['evening'] && $dayCheckins['evening']['status'] === 'success')) {
        $successfulDays++;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử điểm danh - Checkin HPFoods</title>
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
        .logo { font-size: 1.5rem; font-weight: 700; }
        .user-info { display: flex; align-items: center; gap: 1rem; }
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
            margin-bottom: 2rem;
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: #666;
            font-weight: 600;
        }
        .history-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid #667eea;
        }
        .date-header {
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }
        .session-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
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
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .no-data {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="logo">📊 Lịch sử điểm danh</div>
            <div class="user-info">
                <span><?= htmlspecialchars($user['full_name']) ?></span>
                <a href="employee.php" class="back-btn">🏠 Về trang chính</a>
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
            <button type="submit" class="btn btn-primary">📅 Xem lịch sử</button>
        </form>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $totalDays ?></div>
                <div class="stat-label">Tổng số ngày</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $successfulDays ?></div>
                <div class="stat-label">Ngày có điểm danh</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $totalDays > 0 ? round(($successfulDays / $totalDays) * 100, 1) : 0 ?>%</div>
                <div class="stat-label">Tỷ lệ điểm danh</div>
            </div>
        </div>

        <!-- History -->
        <div class="card">
            <div class="card-header">
                <h3>📋 Chi tiết điểm danh (<?= date('d/m/Y', strtotime($startDate)) ?> - <?= date('d/m/Y', strtotime($endDate)) ?>)</h3>
            </div>
            <div class="card-body">
                <?php if (empty($checkinsByDate)): ?>
                    <div class="no-data">
                        <h3>📭 Không có dữ liệu</h3>
                        <p>Không có lịch sử điểm danh trong khoảng thời gian này.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($checkinsByDate as $date => $dayCheckins): ?>
                    <div class="history-item">
                        <div class="date-header">
                            📅 <?= date('d/m/Y - l', strtotime($date)) ?>
                        </div>
                        
                        <!-- Morning Session -->
                        <div class="session-info">
                            <div>
                                <strong>🌅 Sáng (04:00 - 11:00):</strong>
                                <?php if ($dayCheckins['morning']): ?>
                                    <?= date('H:i', strtotime($dayCheckins['morning']['checkin_time'])) ?>
                                    (<?= round($dayCheckins['morning']['distance_meters']) ?>m)
                                <?php else: ?>
                                    <span style="color: #666;">Chưa điểm danh</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <?php if ($dayCheckins['morning']): ?>
                                    <span class="status-badge status-success">✅ Thành công</span>
                                <?php else: ?>
                                    <span class="status-badge status-pending">⏳ Chưa điểm danh</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Evening Session -->
                        <div class="session-info">
                            <div>
                                <strong>🌆 Chiều (13:00 - 20:00):</strong>
                                <?php if ($dayCheckins['evening']): ?>
                                    <?= date('H:i', strtotime($dayCheckins['evening']['checkin_time'])) ?>
                                    (<?= round($dayCheckins['evening']['distance_meters']) ?>m)
                                <?php else: ?>
                                    <span style="color: #666;">Chưa điểm danh</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <?php if ($dayCheckins['evening']): ?>
                                    <span class="status-badge status-success">✅ Thành công</span>
                                <?php else: ?>
                                    <span class="status-badge status-pending">⏳ Chưa điểm danh</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
