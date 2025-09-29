<?php
require_once 'config.php';

// Check if user is admin
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';

// Handle coordinate update
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'update_coordinates') {
    $regionId = (int)$_POST['region_id'];
    $newLat = floatval($_POST['new_latitude']);
    $newLng = floatval($_POST['new_longitude']);
    $newAddress = trim($_POST['new_address']);
    
    if ($regionId && $newLat && $newLng) {
        $db = getDB();
        
        // Get old coordinates for comparison
        $stmt = $db->prepare("SELECT * FROM regions WHERE id = ?");
        $stmt->execute([$regionId]);
        $oldRegion = $stmt->fetch();
        
        if ($oldRegion) {
            // Calculate distance between old and new coordinates
            $distance = calculateDistance($oldRegion['latitude'], $oldRegion['longitude'], $newLat, $newLng);
            
            // Update coordinates
            $stmt = $db->prepare("UPDATE regions SET latitude = ?, longitude = ?, address = ? WHERE id = ?");
            $stmt->execute([$newLat, $newLng, $newAddress, $regionId]);
            
            $message = "Cập nhật tọa độ thành công!<br>";
            $message .= "Tọa độ cũ: {$oldRegion['latitude']}, {$oldRegion['longitude']}<br>";
            $message .= "Tọa độ mới: {$newLat}, {$newLng}<br>";
            $message .= "Khoảng cách thay đổi: " . round($distance) . "m";
            $messageType = 'success';
        } else {
            $message = "Không tìm thấy vùng!";
            $messageType = 'error';
        }
    } else {
        $message = "Dữ liệu không hợp lệ!";
        $messageType = 'error';
    }
}

// Get all regions
$db = getDB();
$stmt = $db->query("SELECT * FROM regions ORDER BY name");
$regions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa tọa độ GPS - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            line-height: 1.6;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
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
            background: linear-gradient(135deg, #dc3545 0%, #6f42c1 100%);
            color: white;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }
        .card-body { padding: 1.5rem; }
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
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        .form-group textarea {
            height: 80px;
            resize: vertical;
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
        .coordinates {
            font-family: 'Courier New', monospace;
            background: #e9ecef;
            padding: 0.5rem;
            border-radius: 3px;
            display: inline-block;
        }
        .region-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .quick-fix {
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
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1>🔧 Sửa tọa độ GPS - Khắc phục lỗi khoảng cách</h1>
            </div>
            <div class="card-body">
                <div class="quick-fix">
                    <h3>⚡ Khắc phục nhanh cho "Khu vực Kiến Thành":</h3>
                    <p><strong>Vấn đề:</strong> Tọa độ trong database không khớp với vị trí thực tế</p>
                    <p><strong>Tọa độ hiện tại:</strong> <span class="coordinates">10.031121, 105.787546</span></p>
                    <p><strong>Tọa độ đúng:</strong> <span class="coordinates">10.0259, 105.7692</span></p>
                    
                    <form method="POST" style="margin-top: 1rem;">
                        <input type="hidden" name="action" value="update_coordinates">
                        <input type="hidden" name="region_id" value="1">
                        <input type="hidden" name="new_latitude" value="10.0259">
                        <input type="hidden" name="new_longitude" value="105.7692">
                        <input type="hidden" name="new_address" value="19 Đường Châu Văn Liêm, Tân An, Ninh Kiều, Cần Thơ, Việt Nam">
                        <button type="submit" class="btn btn-success">
                            ✅ Sửa ngay tọa độ Kiến Thành
                        </button>
                    </form>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?>">
                        <?= $message ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- All Regions -->
        <div class="card">
            <div class="card-header">
                <h3>📍 Tất cả vùng làm việc</h3>
            </div>
            <div class="card-body">
                <?php foreach ($regions as $region): ?>
                <div class="region-info">
                    <h4><?= htmlspecialchars($region['name']) ?></h4>
                    <p><strong>Tọa độ hiện tại:</strong> <span class="coordinates"><?= $region['latitude'] ?>, <?= $region['longitude'] ?></span></p>
                    <p><strong>Bán kính:</strong> <?= $region['radius_meters'] ?>m</p>
                    <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($region['address']) ?></p>
                    
                    <div style="margin-top: 0.5rem;">
                        <a href="https://www.google.com/maps?q=<?= $region['latitude'] ?>,<?= $region['longitude'] ?>&t=satellite&z=18" 
                           target="_blank" class="btn btn-primary" style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                            🗺️ Xem trên Maps
                        </a>
                        <a href="manage_regions.php?edit=<?= $region['id'] ?>" 
                           class="btn" style="background: #ffc107; color: #212529; font-size: 0.9rem; padding: 0.5rem 1rem;">
                            ✏️ Chỉnh sửa
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Manual Update Form -->
        <div class="card">
            <div class="card-header">
                <h3>✏️ Cập nhật tọa độ thủ công</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_coordinates">
                    
                    <div class="form-group">
                        <label>Chọn vùng:</label>
                        <select name="region_id" required>
                            <option value="">Chọn vùng cần sửa</option>
                            <?php foreach ($regions as $region): ?>
                                <option value="<?= $region['id'] ?>">
                                    <?= htmlspecialchars($region['name']) ?> (<?= $region['latitude'] ?>, <?= $region['longitude'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Vĩ độ mới (Latitude):</label>
                            <input type="number" name="new_latitude" step="0.000001" required placeholder="10.0259">
                        </div>
                        <div class="form-group">
                            <label>Kinh độ mới (Longitude):</label>
                            <input type="number" name="new_longitude" step="0.000001" required placeholder="105.7692">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Địa chỉ mới:</label>
                        <textarea name="new_address" placeholder="19 Đường Châu Văn Liêm, Tân An, Ninh Kiều, Cần Thơ, Việt Nam"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-success">💾 Cập nhật tọa độ</button>
                </form>
            </div>
        </div>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="admin.php" class="btn btn-primary">🏠 Về Dashboard</a>
            <a href="debug_gps.php" class="btn" style="background: #6c757d; color: white;">🔍 Debug GPS</a>
        </div>
    </div>
</body>
</html>
