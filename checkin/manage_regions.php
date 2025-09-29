<?php
require_once 'config.php';

// Check if user is admin
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$db = getDB();
$message = '';
$messageType = '';

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_region') {
        $name = trim($_POST['name']);
        $latitude = floatval($_POST['latitude']);
        $longitude = floatval($_POST['longitude']);
        $radius_meters = (int)$_POST['radius_meters'];
        $address = trim($_POST['address']);
        
        if ($name && $latitude && $longitude && $radius_meters) {
            try {
                $stmt = $db->prepare("
                    INSERT INTO regions (name, latitude, longitude, radius_meters, address)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $latitude, $longitude, $radius_meters, $address]);
                $message = "Thêm vùng thành công!";
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = "Lỗi: " . $e->getMessage();
                $messageType = 'error';
            }
        } else {
            $message = "Vui lòng điền đầy đủ thông tin!";
            $messageType = 'error';
        }
    }
    
    if ($action === 'update_region') {
        $id = (int)$_POST['region_id'];
        $name = trim($_POST['name']);
        $latitude = floatval($_POST['latitude']);
        $longitude = floatval($_POST['longitude']);
        $radius_meters = (int)$_POST['radius_meters'];
        $address = trim($_POST['address']);
        
        if ($id && $name && $latitude && $longitude && $radius_meters) {
            try {
                $stmt = $db->prepare("
                    UPDATE regions 
                    SET name = ?, latitude = ?, longitude = ?, radius_meters = ?, address = ?
                    WHERE id = ?
                ");
                $stmt->execute([$name, $latitude, $longitude, $radius_meters, $address, $id]);
                $message = "Cập nhật vùng thành công!";
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = "Lỗi: " . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
    
    if ($action === 'delete_region') {
        $id = (int)$_POST['region_id'];
        
        if ($id) {
            try {
                // Check if region has users
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE region_id = ?");
                $stmt->execute([$id]);
                $userCount = $stmt->fetch()['count'];
                
                if ($userCount > 0) {
                    $message = "Không thể xóa vùng này vì còn có {$userCount} nhân viên được phân công!";
                    $messageType = 'error';
                } else {
                    $stmt = $db->prepare("DELETE FROM regions WHERE id = ?");
                    $stmt->execute([$id]);
                    $message = "Xóa vùng thành công!";
                    $messageType = 'success';
                }
            } catch (PDOException $e) {
                $message = "Lỗi: " . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

// Get all regions with user counts
$stmt = $db->query("
    SELECT r.*, COUNT(u.id) as user_count
    FROM regions r
    LEFT JOIN users u ON r.id = u.region_id AND u.is_active = 1
    GROUP BY r.id
    ORDER BY r.created_at DESC
");
$regions = $stmt->fetchAll();

// Get region for editing
$editRegion = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM regions WHERE id = ?");
    $stmt->execute([$editId]);
    $editRegion = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý vùng - Checkin HPFoods</title>
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
        .form-group input, .form-group textarea {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            font-family: inherit;
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
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        .btn-warning:hover {
            background: #e0a800;
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
        .region-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
        .coordinates {
            font-family: 'Courier New', monospace;
            background: #e9ecef;
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="logo">🗺️ Quản lý vùng</div>
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
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Add/Edit Region Form -->
        <div class="card">
            <div class="card-header">
                <h3><?= $editRegion ? '✏️ Chỉnh sửa vùng' : '➕ Thêm vùng mới' ?></h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="<?= $editRegion ? 'update_region' : 'add_region' ?>">
                    <?php if ($editRegion): ?>
                        <input type="hidden" name="region_id" value="<?= $editRegion['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Tên vùng:</label>
                            <input type="text" name="name" required 
                                   value="<?= $editRegion ? htmlspecialchars($editRegion['name']) : '' ?>">
                        </div>
                        <div class="form-group">
                            <label>Vĩ độ (Latitude):</label>
                            <input type="number" name="latitude" step="0.00000001" required 
                                   value="<?= $editRegion ? $editRegion['latitude'] : ($_GET['lat'] ?? '') ?>"
                                   placeholder="Ví dụ: 10.031121" id="latInput">
                        </div>
                        <div class="form-group">
                            <label>Kinh độ (Longitude):</label>
                            <input type="number" name="longitude" step="0.00000001" required 
                                   value="<?= $editRegion ? $editRegion['longitude'] : ($_GET['lng'] ?? '') ?>"
                                   placeholder="Ví dụ: 105.787546" id="lngInput">
                        </div>
                        <div class="form-group">
                            <label>Bán kính cho phép (mét):</label>
                            <input type="number" name="radius_meters" required 
                                   value="<?= $editRegion ? $editRegion['radius_meters'] : '200' ?>"
                                   min="50" max="1000">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Địa chỉ:</label>
                        <textarea name="address" placeholder="Địa chỉ chi tiết của vùng làm việc"><?= $editRegion ? htmlspecialchars($editRegion['address']) : '' ?></textarea>
                    </div>
                    
                    <div style="margin-top: 1rem;">
                        <button type="submit" class="btn btn-success">
                            <?= $editRegion ? '💾 Cập nhật vùng' : '➕ Thêm vùng' ?>
                        </button>
                        <?php if ($editRegion): ?>
                            <a href="manage_regions.php" class="btn btn-secondary" style="background: #6c757d; color: white; margin-left: 0.5rem;">
                                ❌ Hủy
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
                
                <div class="region-info">
                    <h4>💡 Hướng dẫn lấy tọa độ GPS:</h4>
                    <p>1. Mở Google Maps tại <a href="https://maps.google.com" target="_blank">maps.google.com</a></p>
                    <p>2. Nhấp chuột phải vào vị trí muốn lấy tọa độ</p>
                    <p>3. Chọn tọa độ đầu tiên trong menu (ví dụ: 10.031121, 105.787546)</p>
                    <p>4. Copy và paste vào form trên</p>
                </div>
            </div>
        </div>

        <!-- Regions List -->
        <div class="card">
            <div class="card-header">
                <h3>📋 Danh sách vùng (<?= count($regions) ?>)</h3>
            </div>
            <div class="card-body">
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên vùng</th>
                                <th>Tọa độ GPS</th>
                                <th>Bán kính</th>
                                <th>Số nhân viên</th>
                                <th>Địa chỉ</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($regions as $region): ?>
                            <tr>
                                <td><?= $region['id'] ?></td>
                                <td><strong><?= htmlspecialchars($region['name']) ?></strong></td>
                                <td>
                                    <span class="coordinates"><?= $region['latitude'] ?>, <?= $region['longitude'] ?></span><br>
                                    <a href="https://www.google.com/maps?q=<?= $region['latitude'] ?>,<?= $region['longitude'] ?>&t=satellite&z=18" 
                                       target="_blank" style="font-size: 0.8rem; color: #007bff;">
                                        🗺️ Xem trên Maps
                                    </a>
                                </td>
                                <td><?= $region['radius_meters'] ?>m</td>
                                <td>
                                    <span style="background: #e3f2fd; color: #1976d2; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.8rem;">
                                        👥 <?= $region['user_count'] ?> người
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($region['address']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($region['created_at'])) ?></td>
                                <td>
                                    <a href="manage_regions.php?edit=<?= $region['id'] ?>" class="btn btn-warning" 
                                       style="font-size: 0.8rem; padding: 0.25rem 0.5rem;">
                                        ✏️ Sửa
                                    </a>
                                    <?php if ($region['user_count'] == 0): ?>
                                        <form method="POST" style="display: inline; margin-left: 0.25rem;" 
                                              onsubmit="return confirm('Bạn có chắc muốn xóa vùng này?')">
                                            <input type="hidden" name="action" value="delete_region">
                                            <input type="hidden" name="region_id" value="<?= $region['id'] ?>">
                                            <button type="submit" class="btn" style="background: #dc3545; color: white; font-size: 0.8rem; padding: 0.25rem 0.5rem;">
                                                🗑️ Xóa
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span style="font-size: 0.7rem; color: #666; margin-left: 0.25rem;">
                                            (Có <?= $region['user_count'] ?> nhân viên)
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
