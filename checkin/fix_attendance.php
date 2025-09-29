<?php
require_once 'config.php';

// Check if user is manager or admin
if (!isLoggedIn() || !in_array($_SESSION['role'], ['manager', 'admin'])) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$db = getDB();
$message = '';
$messageType = '';

// Handle attendance fix
if ($_POST && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'add_checkin') {
        $employeeId = (int)$_POST['employee_id'];
        $checkinDate = $_POST['checkin_date'];
        $session = $_POST['session'];
        $notes = trim($_POST['notes']);
        
        if ($employeeId && $checkinDate && $session) {
            // Check if employee is in manager's region (for managers)
            if ($_SESSION['role'] === 'manager') {
                $stmt = $db->prepare("SELECT region_id FROM users WHERE id = ? AND region_id = ?");
                $stmt->execute([$employeeId, $user['region_id']]);
                if (!$stmt->fetch()) {
                    $message = "Bạn chỉ có thể sửa điểm danh cho nhân viên trong vùng của mình!";
                    $messageType = 'error';
                    goto skip_processing;
                }
            }
            
            // Get employee and region info
            $stmt = $db->prepare("SELECT u.*, r.* FROM users u JOIN regions r ON u.region_id = r.id WHERE u.id = ?");
            $stmt->execute([$employeeId]);
            $employee = $stmt->fetch();
            
            if ($employee) {
                try {
                    // Insert manual check-in with manager note
                    $stmt = $db->prepare("
                        INSERT INTO checkins (user_id, region_id, checkin_date, session, latitude, longitude, distance_meters, ip_address, status, notes)
                        VALUES (?, ?, ?, ?, ?, ?, 0, ?, 'success', ?)
                        ON DUPLICATE KEY UPDATE
                        latitude = VALUES(latitude),
                        longitude = VALUES(longitude),
                        distance_meters = 0,
                        ip_address = VALUES(ip_address),
                        status = 'success',
                        notes = VALUES(notes),
                        checkin_time = NOW()
                    ");
                    
                    $manualNote = "Sửa lỗi bởi " . $user['full_name'] . " (" . $_SESSION['role'] . ")";
                    if ($notes) {
                        $manualNote .= " - Ghi chú: " . $notes;
                    }
                    
                    $stmt->execute([
                        $employeeId,
                        $employee['region_id'],
                        $checkinDate,
                        $session,
                        $employee['latitude'],  // Use region coordinates
                        $employee['longitude'],
                        $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                        $manualNote
                    ]);
                    
                    $message = "Đã thêm điểm danh thành công cho " . $employee['full_name'] . "!";
                    $messageType = 'success';
                } catch (PDOException $e) {
                    $message = "Lỗi: " . $e->getMessage();
                    $messageType = 'error';
                }
            } else {
                $message = "Không tìm thấy nhân viên!";
                $messageType = 'error';
            }
        } else {
            $message = "Vui lòng điền đầy đủ thông tin!";
            $messageType = 'error';
        }
    }
}

skip_processing:

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

// Get recent manual check-ins
$sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));
$stmt = $db->prepare("
    SELECT c.*, u.full_name, u.username, r.name as region_name
    FROM checkins c
    JOIN users u ON c.user_id = u.id
    JOIN regions r ON c.region_id = r.id
    WHERE c.notes LIKE ? AND c.checkin_date >= ?
    ORDER BY c.checkin_time DESC
    LIMIT 20
");
$stmt->execute(['%Sửa lỗi bởi%', $sevenDaysAgo]);
$recentFixes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa lỗi điểm danh - Checkin HPFoods</title>
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
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
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
        .form-group input, .form-group select, .form-group textarea {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        .form-group textarea {
            resize: vertical;
            height: 80px;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-success:hover {
            background: #1e7e34;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background: #0056b3;
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
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="logo">🔧 Sửa lỗi điểm danh</div>
            <div class="nav-links">
                <a href="manager.php" class="nav-link">🏠 Dashboard</a>
                <a href="logout.php" class="nav-link">🚪 Đăng xuất</a>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="warning-box">
            <h4>⚠️ Lưu ý quan trọng:</h4>
            <p>Chức năng này dành cho trường hợp nhân viên có đi làm nhưng quên điểm danh hoặc gặp sự cố kỹ thuật.</p>
            <p>Mọi thao tác sửa lỗi sẽ được ghi lại với tên người thực hiện.</p>
        </div>

        <!-- Add Manual Check-in -->
        <div class="card">
            <div class="card-header">
                <h3>➕ Thêm điểm danh thủ công</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add_checkin">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Chọn nhân viên:</label>
                            <select name="employee_id" required>
                                <option value="">-- Chọn nhân viên --</option>
                                <?php foreach ($employees as $employee): ?>
                                    <option value="<?= $employee['id'] ?>">
                                        <?= htmlspecialchars($employee['full_name']) ?> 
                                        (<?= htmlspecialchars($employee['username']) ?> - <?= htmlspecialchars($employee['region_name']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Ngày:</label>
                            <input type="date" name="checkin_date" required value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Ca làm việc:</label>
                            <select name="session" required>
                                <option value="">-- Chọn ca --</option>
                                <option value="morning">🌅 Ca sáng (04:00 - 11:00)</option>
                                <option value="evening">🌆 Ca chiều (13:00 - 20:00)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Lý do sửa lỗi:</label>
                        <textarea name="notes" placeholder="VD: Nhân viên có mặt nhưng quên điểm danh, sự cố kỹ thuật GPS..." required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-success">✅ Thêm điểm danh</button>
                </form>
            </div>
        </div>

        <!-- Recent Fixes -->
        <?php if (!empty($recentFixes)): ?>
        <div class="card">
            <div class="card-header">
                <h3>📋 Lịch sử sửa lỗi gần đây</h3>
            </div>
            <div class="card-body">
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nhân viên</th>
                                <th>Vùng</th>
                                <th>Ngày</th>
                                <th>Ca</th>
                                <th>Thời gian tạo</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentFixes as $fix): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($fix['full_name']) ?></strong><br>
                                    <small><?= htmlspecialchars($fix['username']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($fix['region_name']) ?></td>
                                <td><?= date('d/m/Y', strtotime($fix['checkin_date'])) ?></td>
                                <td><?= $fix['session'] === 'morning' ? '🌅 Sáng' : '🌆 Chiều' ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($fix['checkin_time'])) ?></td>
                                <td>
                                    <small style="color: #666;">
                                        <?= htmlspecialchars($fix['notes']) ?>
                                    </small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="manager.php" class="btn btn-primary">🏠 Về Dashboard</a>
        </div>
    </div>
</body>
</html>
